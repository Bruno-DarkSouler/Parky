import os
os.environ["YOLO_CONFIG_DIR"] = "./src/"
from ultralytics import YOLO
import json
import requests
import cv2
import time
import threading
import numpy as np
from dotenv import load_dotenv

# Load env variables
load_dotenv()

# Configuration
CNN_TOKEN = os.getenv("CNN_PRO_TOKEN", "G810IIXfOGA8HMnqRj98lpVxQ33lx9FmTVwd")
API_BASE_URL = os.getenv("API_BASE_URL", "http://parking_finder/api")
url_dvr = os.getenv("DVR_URL", "http://dvr_connector:8000/getFrame")
url_data = f"{API_BASE_URL}/v1/cnn/cameras"
url_availability = f"{API_BASE_URL}/v1/cnn/availability"
url_people = f"{API_BASE_URL}/v1/people"

PARKING_INTERVAL = int(os.getenv("PARKING_INTERVAL", "15"))
PEOPLE_INTERVAL = int(os.getenv("PEOPLE_INTERVAL", "10"))
PEOPLE_CAMERA_IP = os.getenv("PEOPLE_CAMERA_IP", "debug")
PEOPLE_CAMERA_CHANNEL = int(os.getenv("PEOPLE_CAMERA_CHANNEL", "1"))

# Threading lock for YOLO model inference
model_lock = threading.Lock()

# Load YOLO model
print("[CNN PRO] Cargando modelo YOLO (yolov8m.pt)...")
model = YOLO('yolov8m.pt')
print("[CNN PRO] Modelo YOLO cargado exitosamente.")


def calcular_interseccion(boxA, boxB):
    # Lógica para determinar qué tanto se solapan dos rectángulos
    xA = max(boxA[0], boxB[0])
    yA = max(boxA[1], boxB[1])
    xB = min(boxA[2], boxB[2])
    yB = min(boxA[3], boxB[3])
    
    interArea = max(0, xB - xA) * max(0, yB - yA)
    areaA = (boxA[2] - boxA[0]) * (boxA[3] - boxA[1])
    if float(areaA) == 0:
        return 0.0
    return interArea / float(areaA)


def procesar_estacionamiento(path_imagen, coordenadas_json):
    with model_lock:
        results = model(path_imagen)
    detecciones = results[0].boxes  # Cajas detectadas por YOLO
    
    estado_estacionamiento = []

    for spot in coordenadas_json:
        # Coordenadas del JSON: [x1, y1, x2, y2] (Puntos del area seleccionada)
        area_esperada = [spot['x1'], spot['y1'], spot['x2'], spot['y2']]
        ocupado = False
        confianza_deteccion = 0

        for det in detecciones:
            # Filtrar solo vehículos (clases 2: auto, 5: colectivo, 7: camion en COCO) (Falta para motos)
            if int(det.cls) in [2, 5, 7]:
                bbox_auto = det.xyxy[0].tolist()  # [x1, y1, x2, y2]
                
                # Calcular solapamiento (IoU o simple intersección)
                if calcular_interseccion(area_esperada, bbox_auto) > 0.5:
                    ocupado = True
                    confianza_deteccion = float(det.conf)
                    break
        
        estado_estacionamiento.append({
            "ocupado": ocupado,
            "confianza": confianza_deteccion
        })
    
    return estado_estacionamiento


def normalizar_geometria(geometry):
    """
    Normaliza los diferentes formatos de geometria que pueden venir de la base de datos
    a una lista de rectangulos [{'x1': ..., 'y1': ..., 'x2': ..., 'y2': ..., 'id': ...}].
    """
    if not geometry:
        return [{'x1': 50, 'y1': 50, 'x2': 300, 'y2': 300, 'id': 'default'}]
    
    if isinstance(geometry, str):
        try:
            geometry = json.loads(geometry)
        except Exception:
            return [{'x1': 50, 'y1': 50, 'x2': 300, 'y2': 300, 'id': 'default'}]
            
    if isinstance(geometry, dict):
        geometry = [geometry]

    if isinstance(geometry, list) and len(geometry) > 0:
        first = geometry[0]
        if isinstance(first, dict):
            if 'x1' in first and 'y1' in first and 'x2' in first and 'y2' in first:
                return [{
                    'x1': int(first['x1']),
                    'y1': int(first['y1']),
                    'x2': int(first['x2']),
                    'y2': int(first['y2']),
                    'id': first.get('id', 'spot')
                }]
            elif 'x' in first and 'y' in first:
                xs = [p['x'] for p in geometry if isinstance(p, dict) and 'x' in p]
                ys = [p['y'] for p in geometry if isinstance(p, dict) and 'y' in p]
                if xs and ys:
                    min_x, max_x = min(xs), max(xs)
                    min_y, max_y = min(ys), max(ys)
                    if min_x == max_x:
                        max_x += 150
                    if min_y == max_y:
                        max_y += 150
                    return [{'x1': int(min_x), 'y1': int(min_y), 'x2': int(max_x), 'y2': int(max_y), 'id': 'polygon'}]

    return [{'x1': 50, 'y1': 50, 'x2': 300, 'y2': 300, 'id': 'fallback'}]


def actualizar_disponibilidad_en_bd(camera_zone_id, is_available):
    """
    Envía el resultado de ocupación a la API para registrarlo en la base de datos.
    """
    try:
        payload = {
            "camera_zone_id": camera_zone_id,
            "is_available": is_available,
            "status": "available" if is_available else "full"
        }
        res = requests.post(
            url_availability,
            json=payload,
            headers={"X-CNN-Token": CNN_TOKEN, "Content-Type": "application/json"},
            timeout=8
        )
        if res.status_code == 200:
            print(f"[PARKING] Zona {camera_zone_id} actualizada -> Disponible: {is_available}")
        else:
            print(f"[PARKING] Error actualizando zona {camera_zone_id}: HTTP {res.status_code}")
    except Exception as e:
        print(f"[PARKING] Excepción actualizando disponibilidad de zona {camera_zone_id}: {e}")


def get_camera_frame(ip, user, password, port, channel, camera_zones):
    """
    Obtiene el frame actual del DVR y procesa cada zona de estacionamiento.
    """
    data_send = {
        "ip": ip or "debug",
        "usuario": user or "admin",
        "contrasena": password or "secret",
        "port": port or 80,
        "canal": channel or 1,
        "debug": True
    }
    
    try:
        response = requests.post(url_dvr, params=data_send, timeout=10)
    except Exception as e:
        print(f"[PARKING] Error al conectar con DVR ({ip}, canal {channel}): {e}")
        return

    if response.status_code != 200 or not response.content:
        print(f"[PARKING] Respuesta inválida de DVR ({ip}): HTTP {response.status_code}")
        return

    try:
        image_bytes = np.frombuffer(response.content, dtype=np.uint8)
        frame = cv2.imdecode(image_bytes, cv2.IMREAD_COLOR)
        if frame is None:
            print(f"[PARKING] Error al decodificar la imagen de la cámara {ip}")
            return

        print(f"=================================================== Camera {ip} ============================================================")
        for i, zone in enumerate(camera_zones):
            zone_id = zone.get("id")
            geometry = zone.get("geometry")
            coords = normalizar_geometria(geometry)

            print(f"---------------------Zone {i + 1} (ID: {zone_id})-------------------------")
            resultado = procesar_estacionamiento(frame, coords)
            
            if resultado:
                ocupado = resultado[0].get("ocupado", False)
                confianza = resultado[0].get("confianza", 0.0)
                is_available = not ocupado
                print(f"[PARKING] Resultado: Ocupado={ocupado}, Confianza={confianza:.2f}")

                # Registrar resultado en la base de datos
                if zone_id:
                    actualizar_disponibilidad_en_bd(zone_id, is_available)

        print("=============================================================================================================================")
    except Exception as e:
        print(f"[PARKING] Error procesando zonas de cámara {ip}: {e}")


def get_cameras_data():
    """
    Recorre la paginación de la API de cámaras del sistema y procesa cada una.
    Evita procesar cámaras duplicadas entre páginas.
    """
    page = 1
    processed_cameras = set()

    while True:
        try:
            response = requests.get(
                url_data,
                params={"page": page, "per_page": 10},
                headers={"X-CNN-Token": CNN_TOKEN},
                timeout=10
            )

            if response.status_code != 200:
                print(f"[PARKING] Error en API al obtener cámaras (HTTP {response.status_code}): {response.text}")
                break

            data = response.json()
            cameras = data.get("data", [])
            if not cameras:
                break

            for cam_data in cameras:
                cam_id = cam_data.get("id")
                if cam_id in processed_cameras:
                    print(f"[PARKING] Cámara {cam_id} ya procesada en esta iteración. Saltando duplicado.")
                    continue
                processed_cameras.add(cam_id)

                dvr_info = cam_data.get("dvr") or {}
                ip = dvr_info.get("ip", "debug")
                user = dvr_info.get("user", "admin")
                password = dvr_info.get("password", "secret")
                port = dvr_info.get("port", 80)
                channel = cam_data.get("channel", 1)
                camera_zones = cam_data.get("camera_zones", [])

                get_camera_frame(ip, user, password, port, channel, camera_zones)

            # Control de paginación
            pagination = data.get("pagination", {})
            has_more = pagination.get("has_more_pages", False)
            current_page = pagination.get("current_page", page)
            last_page = pagination.get("last_page", page)

            if not has_more or current_page >= last_page:
                break

            page += 1

        except Exception as e:
            print(f"[PARKING] Error en bucle de paginación de cámaras: {e}")
            break


# =============================================================================
# DETECCIÓN DE PERSONAS (PROCESO 2)
# =============================================================================

def contar_personas_en_imagen(frame, conf_threshold=0.3):
    """
    Analiza una imagen mediante YOLO y cuenta la cantidad de personas detectadas (clase 0 en COCO).
    """
    with model_lock:
        results = model(frame)
    detecciones = results[0].boxes
    conteo = 0
    for det in detecciones:
        # En el dataset COCO, la clase 0 corresponde a persona
        if int(det.cls) == 0 and float(det.conf) >= conf_threshold:
            conteo += 1
    return conteo


def ejecutar_conteo_personas():
    """
    Obtiene una imagen de la cámara correspondiente, detecta personas y envía el número a la API.
    """
    try:
        data_send = {
            "ip": PEOPLE_CAMERA_IP,
            "usuario": "admin",
            "contrasena": "secret",
            "canal": PEOPLE_CAMERA_CHANNEL,
            "debug": True
        }
        
        response = requests.post(url_dvr, params=data_send, timeout=10)
        if response.status_code != 200 or not response.content:
            print(f"[PERSONAS] No se pudo obtener frame para conteo de personas (HTTP {response.status_code})")
            return

        image_bytes = np.frombuffer(response.content, dtype=np.uint8)
        frame = cv2.imdecode(image_bytes, cv2.IMREAD_COLOR)
        if frame is None:
            print("[PERSONAS] Error al decodificar la imagen de la cámara de personas")
            return

        # Conteo con YOLO
        personas_detectadas = contar_personas_en_imagen(frame)
        print(f"[PERSONAS] Cantidad de personas detectadas en la imagen: {personas_detectadas}")

        # Enviar medición a la API mediante HTTP POST
        payload = {"cantidad": personas_detectadas}
        res = requests.post(
            url_people,
            json=payload,
            headers={"Content-Type": "application/json"},
            timeout=8
        )
        if res.status_code in (200, 201):
            print(f"[PERSONAS] Medición registrada exitosamente en API: {personas_detectadas} personas")
        else:
            print(f"[PERSONAS] Error al registrar en API: HTTP {res.status_code} - {res.text}")

    except Exception as e:
        print(f"[PERSONAS] Excepción en ciclo de conteo de personas: {e}")


# =============================================================================
# EJECUCIÓN MULTI-HILO INDEPENDIENTE
# =============================================================================

def bucle_estacionamiento():
    print("[HILO ESTACIONAMIENTOS] Iniciando monitoreo periódico...")
    while True:
        try:
            get_cameras_data()
        except Exception as e:
            print(f"[HILO ESTACIONAMIENTOS] Error en ejecución: {e}")
        time.sleep(PARKING_INTERVAL)


def bucle_personas():
    print("[HILO PERSONAS] Iniciando conteo periódico de personas...")
    while True:
        try:
            ejecutar_conteo_personas()
        except Exception as e:
            print(f"[HILO PERSONAS] Error en ejecución: {e}")
        time.sleep(PEOPLE_INTERVAL)


if __name__ == "__main__":
    print("[CNN PRO] Iniciando servicios de procesamiento...")
    
    # Hilo para estacionamientos
    hilo_estac = threading.Thread(target=bucle_estacionamiento, name="Hilo-Estacionamiento", daemon=True)
    hilo_estac.start()

    # Hilo para detección de personas
    hilo_pers = threading.Thread(target=bucle_personas, name="Hilo-Personas", daemon=True)
    hilo_pers.start()

    # Mantener el proceso principal activo
    try:
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        print("[CNN PRO] Deteniendo contenedor CNN PRO...")
