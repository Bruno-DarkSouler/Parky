import os
import cv2
import numpy as np
import psycopg2
import requests
import xml.etree.ElementTree as ET
import datetime
import random
from fastapi import FastAPI, HTTPException, Body, Request, Query
from fastapi.responses import Response, JSONResponse
from onvif import ONVIFCamera
from dotenv import load_dotenv

# Load env variables
load_dotenv()

app = FastAPI(title="DVR Connector API")
SRC_DIR = os.path.join(os.path.dirname(__file__), "src")


# Config database credentials
DB_HOST = os.getenv("DB_HOST")
DB_PORT = os.getenv("DB_PORT")
DB_NAME = os.getenv("DB_DATABASE")
DB_USER = os.getenv("DB_USERNAME")
DB_PASS = os.getenv("DB_PASSWORD")

def is_env_true(val) -> bool:
    return str(val).lower() in ("true", "1", "t", "yes")

DEBUG_MODE = (
    is_env_true(os.getenv("IS_DEBUG")) or
    is_env_true(os.getenv("DEBUG")) or
    is_env_true(os.getenv("APP_DEBUG"))
)


DEBUG_MOCK_CHANNELS = [
    {
        "id": 1,
        "inputPort": 1,
        "videoInputEnabled": True,
        "name": "Camera 01 - Entrada Principal",
        "videoFormat": "PAL",
        "portType": "HDTVI",
        "resDesc": "1080p"
    },
    {
        "id": 2,
        "inputPort": 2,
        "videoInputEnabled": True,
        "name": "Camera 02 - Estacionamiento Sector A",
        "videoFormat": "PAL",
        "portType": "HDTVI",
        "resDesc": "1080p"
    },
    {
        "id": 3,
        "inputPort": 3,
        "videoInputEnabled": True,
        "name": "Camera 03 - Estacionamiento Sector B",
        "videoFormat": "PAL",
        "portType": "HDTVI",
        "resDesc": "1080p"
    },
    {
        "id": 4,
        "inputPort": 4,
        "videoInputEnabled": False,
        "name": "Camera 04 - Acceso Trasero",
        "videoFormat": "PAL",
        "portType": "HDTVI",
        "resDesc": "720p"
    }
]

# Helper to parse Hikvision XML
def parse_hikvision_channels_xml(xml_content: str):
    channels = []
    try:
        root = ET.fromstring(xml_content)
        for elem in root.iter():
            tag_name = elem.tag.rsplit('}', 1)[-1]
            if tag_name == 'VideoInputChannel':
                channel_data = {}
                for child in elem:
                    c_tag = child.tag.rsplit('}', 1)[-1]
                    val = child.text
                    if val is not None:
                        val = val.strip()
                        if val.lower() == 'true':
                            val = True
                        elif val.lower() == 'false':
                            val = False
                        elif val.isdigit():
                            val = int(val)
                    channel_data[c_tag] = val
                if channel_data:
                    channels.append(channel_data)
    except Exception as e:
        print(f"Error parsing XML: {e}")
    return channels

# Helper to generate test JPEG frame for debug mode
def generate_debug_frame(channel: int = 1, ip: str = "192.168.1.100") -> bytes:
    img = np.zeros((480, 640, 3), dtype=np.uint8)
    img[:, :] = (30, 30, 35)  # Dark slate background

    # Simulated camera header
    cv2.rectangle(img, (0, 0), (640, 45), (15, 15, 20), -1)
    cv2.putText(img, f"DVR DEBUG MODE - CAM {channel} ({ip})", (15, 28),
                cv2.FONT_HERSHEY_SIMPLEX, 0.65, (0, 220, 255), 2)

    now_str = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    cv2.putText(img, now_str, (430, 28), cv2.FONT_HERSHEY_SIMPLEX, 0.55, (200, 200, 200), 1)

    # Draw simulated parking spaces
    for i in range(1, 5):
        x = i * 120 + 20
        cv2.line(img, (x, 150), (x - 30, 420), (200, 200, 200), 2)
        cv2.putText(img, f"SLOT {i}", (x - 20, 140), cv2.FONT_HERSHEY_SIMPLEX, 0.5, (180, 180, 180), 1)

    cv2.line(img, (40, 420), (580, 420), (200, 200, 200), 2)

    # Draw simulated car in slot 2
    cv2.rectangle(img, (160, 240), (250, 380), (120, 70, 40), -1)
    cv2.rectangle(img, (170, 260), (240, 360), (180, 100, 60), -1)
    cv2.putText(img, "CAR", (190, 310), cv2.FONT_HERSHEY_SIMPLEX, 0.5, (255, 255, 255), 1)

    # Footer status
    cv2.rectangle(img, (0, 445), (640, 480), (20, 20, 25), -1)
    cv2.putText(img, f"LIVE TEST STREAM - CHANNEL {channel}", (15, 468),
                cv2.FONT_HERSHEY_SIMPLEX, 0.45, (0, 255, 120), 1)

    _, buffer = cv2.imencode('.jpg', img, [int(cv2.IMWRITE_JPEG_QUALITY), 85])
    return buffer.tobytes()

def get_debug_frame(channel: int = 1, ip: str = "192.168.1.100") -> bytes:
    # Check for case1.jpg and case2.jpg in src/
    case_files = ["case1.jpg", "case2.jpg"]
    existing_files = [
        os.path.join(SRC_DIR, f) for f in case_files
        if os.path.exists(os.path.join(SRC_DIR, f))
    ]
    
    if not existing_files and os.path.exists(SRC_DIR):
        existing_files = [
            os.path.join(SRC_DIR, f) for f in os.listdir(SRC_DIR)
            if f.lower().endswith(('.jpg', '.jpeg', '.png'))
        ]
        
    if existing_files:
        chosen = random.choice(existing_files)
        try:
            with open(chosen, "rb") as f:
                return f.read()
        except Exception as e:
            print(f"Error reading debug image {chosen}: {e}")

    return generate_debug_frame(channel=channel, ip=ip)

# Connect to database

def get_db_connection():
    return psycopg2.connect(
        host=DB_HOST,
        port=DB_PORT,
        database=DB_NAME,
        user=DB_USER,
        password=DB_PASS
    )

# Get DVR data from database
def get_dvr_data(camera_id: int):
    try:
        conn = get_db_connection()
        cur = conn.cursor()
        query = """
            SELECT d.ip, d.port, d.user, d.password, c.channel
            FROM cameras c
            JOIN dvrs d ON c.dvr_id = d.id
            WHERE c.id = %s
        """
        cur.execute(query, (camera_id,))
        result = cur.fetchone()
        cur.close()
        conn.close()
        return result
    except Exception as e:
        print(f"Error connecting to DB: {e}")
        return None

# Get RTSP URI from DVR
def get_rtsp_uri(ip, port, user, password, channel_idx):
    try:
        mycam = ONVIFCamera(ip, port, user, password)
        media = mycam.create_media_service()
        profiles = media.GetProfiles()
        
        if channel_idx >= len(profiles):
            return None
            
        profile = profiles[channel_idx]
        obj = media.create_type('GetStreamUri')
        obj.ProfileToken = profile.token
        obj.StreamSetup = {
            'Stream': 'RTP-Unicast', 
            'Transport': {'Protocol': 'RTSP'}
        }
        uri_response = media.GetStreamUri(obj)
        return uri_response.Uri
    except Exception as e:
        print(f"Error getting RTSP URI: {e}")
        return None

# Capture frame from RTSP URI
def capture_frame(rtsp_uri):
    try:
        cap = cv2.VideoCapture(rtsp_uri)
        if not cap.isOpened():
            return None
        
        ret, frame = cap.read()
        cap.release()
        
        if not ret:
            return None
            
        _, buffer = cv2.imencode('.jpg', frame)
        return buffer.tobytes()
    except Exception as e:
        print(f"Error capturing frame: {e}")
        return None

def fetch_hikvision_channels(ip: str, port: int, user: str, passw: str):
    url = f"http://{ip}:{port}/ISAPI/System/Video/inputs/channels"
    
    # Digest Auth
    try:
        res = requests.get(url, auth=requests.auth.HTTPDigestAuth(user, passw), timeout=5)
        if res.status_code == 200:
            return parse_hikvision_channels_xml(res.text)
    except Exception as e:
        print(f"Digest auth error: {e}")
        
    # Basic Auth
    try:
        res = requests.get(url, auth=requests.auth.HTTPBasicAuth(user, passw), timeout=5)
        if res.status_code == 200:
            return parse_hikvision_channels_xml(res.text)
    except Exception as e:
        print(f"Basic auth error: {e}")

    # No Auth fallback
    try:
        res = requests.get(url, timeout=5)
        if res.status_code == 200:
            return parse_hikvision_channels_xml(res.text)
    except Exception as e:
        print(f"No-auth error: {e}")

    return None

def fetch_hikvision_frame(ip: str, port: int, user: str, passw: str, channel: int):
    # Try Hikvision ISAPI picture snapshots
    channel_candidates = [f"{channel}01", f"{channel}", f"{channel}00"]
    for ch in channel_candidates:
        url = f"http://{ip}:{port}/ISAPI/Streaming/channels/{ch}/picture"
        for auth_cls in [requests.auth.HTTPDigestAuth, requests.auth.HTTPBasicAuth]:
            try:
                res = requests.get(url, auth=auth_cls(user, passw), timeout=5)
                if res.status_code == 200 and len(res.content) > 100:
                    return res.content
            except Exception:
                pass

    # RTSP fallback
    rtsp_urls = [
        f"rtsp://{user}:{passw}@{ip}:554/Streaming/Channels/{channel}01",
        f"rtsp://{user}:{passw}@{ip}:554/h264/ch{channel}/main/av_stream",
        f"rtsp://{user}:{passw}@{ip}:554/ch{channel}"
    ]
    for rtsp_url in rtsp_urls:
        frame_bytes = capture_frame(rtsp_url)
        if frame_bytes:
            return frame_bytes

    return None

async def parse_request_data(request: Request):
    data = {}
    # Extract query params
    for key, val in request.query_params.items():
        data[key] = val
        
    # Extract JSON body if present
    if request.method in ("POST", "PUT", "PATCH"):
        try:
            body_data = await request.json()
            if isinstance(body_data, dict):
                data.update(body_data)
        except Exception:
            pass
            
    ip = data.get("ip", "")
    user = data.get("usuario") or data.get("user") or data.get("username", "")
    password = data.get("contrasena") or data.get("password") or data.get("pass", "")
    port = data.get("port", 80)
    channel = data.get("canal") or data.get("channel") or data.get("channel_id") or data.get("id", 1)
    
    raw_debug = data.get("debug")
    if raw_debug is None:
        raw_debug = data.get("is_debug")
    if raw_debug is None:
        raw_debug = data.get("IS_DEBUG")
        
    if raw_debug is None:
        debug = DEBUG_MODE
    elif isinstance(raw_debug, str):
        debug = raw_debug.lower() in ("true", "1", "t", "yes")
    else:
        debug = bool(raw_debug)

        
    if str(ip).lower() in ("debug", "test", "localhost", "127.0.0.1", "0.0.0.0"):
        debug = True
        
    try:
        port = int(port)
    except (ValueError, TypeError):
        port = 80
        
    try:
        channel = int(channel)
    except (ValueError, TypeError):
        channel = 1
        
    return {
        "ip": str(ip).strip(),
        "user": str(user).strip(),
        "password": str(password).strip(),
        "port": port,
        "channel": channel,
        "debug": debug
    }

# ----------------- ENDPOINTS ----------------- #

@app.api_route("/getChannels", methods=["GET", "POST"])
@app.api_route("/get_channels", methods=["GET", "POST"])
async def get_channels_endpoint(request: Request):
    params = await parse_request_data(request)
    
    ip = params["ip"]
    user = params["user"]
    password = params["password"]
    port = params["port"]
    debug = params["debug"]

    # Debug mode explicitly enabled or missing credentials/dummy IP
    if debug or not ip:
        return JSONResponse(content=DEBUG_MOCK_CHANNELS)

    # Real DVR fetch
    channels = fetch_hikvision_channels(ip, port, user, password)
    
    if channels is not None:
        return JSONResponse(content=channels)
        
    # If connection failed but debug is allowed or default fallback
    if debug or DEBUG_MODE:
        return JSONResponse(content=DEBUG_MOCK_CHANNELS)
        
    raise HTTPException(status_code=504, detail="Could not connect to DVR or retrieve channels from /ISAPI/System/Video/inputs/channels")


@app.api_route("/getFrame", methods=["GET", "POST"])
@app.api_route("/get_frame", methods=["GET", "POST"])
async def get_frame_endpoint(request: Request):
    params = await parse_request_data(request)
    
    ip = params["ip"]
    user = params["user"]
    password = params["password"]
    port = params["port"]
    channel = params["channel"]
    debug = params["debug"]

    if debug or not ip:
        jpeg_bytes = get_debug_frame(channel=channel, ip=ip or "127.0.0.1")
        return Response(content=jpeg_bytes, media_type="image/jpeg")

    # Real DVR frame fetch
    jpeg_bytes = fetch_hikvision_frame(ip, port, user, password, channel)
    
    if jpeg_bytes:
        return Response(content=jpeg_bytes, media_type="image/jpeg")
        
    if debug or DEBUG_MODE:
        jpeg_bytes = get_debug_frame(channel=channel, ip=ip)
        return Response(content=jpeg_bytes, media_type="image/jpeg")

    raise HTTPException(status_code=504, detail=f"Failed to capture frame from DVR (IP: {ip}, Channel: {channel})")


# Legacy capture endpoint
@app.post("/capture")
async def capture_image(payload: dict = Body(...)):
    camera_id = payload.get("id")
    if not camera_id:
        raise HTTPException(status_code=400, detail="ID is required")
    
    # 1. Get DVR connection data from database
    data = get_dvr_data(camera_id)
    if not data:
        raise HTTPException(status_code=404, detail="Camera or DVR connection data not found")
    
    ip, port, user, password, channel = data
    
    # Check debug
    if DEBUG_MODE or str(ip).lower() in ("debug", "test"):
        jpeg_bytes = get_debug_frame(channel=int(channel), ip=str(ip))
        return Response(content=jpeg_bytes, media_type="image/jpeg")

    
    # 2. Get RTSP URI via ONVIF
    rtsp_uri = get_rtsp_uri(ip, port, user, password, int(channel))
    if not rtsp_uri:
        raise HTTPException(status_code=500, detail="Could not retrieve RTSP URI from DVR")

    if "://" in rtsp_uri and "@" not in rtsp_uri:
        protocol, rest = rtsp_uri.split("://")
        rtsp_uri = f"{protocol}://{user}:{password}@{rest}"

    # 3. Capture image
    image_bytes = capture_frame(rtsp_uri)
    if not image_bytes:
        raise HTTPException(status_code=500, detail="Failed to capture image from stream")
    
    return Response(content=image_bytes, media_type="image/jpeg")

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)
