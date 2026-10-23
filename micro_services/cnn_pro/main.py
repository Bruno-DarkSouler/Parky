import os
os.environ["YOLO_CONFIG_DIR"] = "./src/"
from ultralytics import YOLO
import json
import requests
import cv2
import time
import numpy as np
from dotenv import load_dotenv

# Load env variables
load_dotenv()

url_dvr = "http://dvr_connector:8000/getFrame"
url_data = "https://parkingfinder.bauti.dev/api/cnn/cameras"

CNN_TOKEN = os.getenv("CNN_PRO_TOKEN")



# Cargar el modelo 
model = YOLO('yolov8m.pt') 

def procesar_estacionamiento(path_imagen, coordenadas_json):
    results = model(path_imagen)
    detecciones = results[0].boxes  # Cajas detectadas por YOLO
    
    estado_estacionamiento = []

    for spot in coordenadas_json:
        # Coordenadas del JSON: [x1, y1, x2, y2] (Puntos delarea seleccionada)
        area_esperada = [spot['x1'], spot['y1'], spot['x2'], spot['y2']]
        ocupado = False
        confianza_deteccion = 0

        for det in detecciones:
            # Filtrar solo vehículos (clases 2: auto, 5: colectivo, 7: camion en COCO) (Falta para motos)
            if int(det.cls) in [2, 5, 7]:
                bbox_auto = det.xyxy[0].tolist() # [x1, y1, x2, y2]
                
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

def calcular_interseccion(boxA, boxB):
    # Lógica simple para determinar qué tanto se solapan dos rectángulos
    xA = max(boxA[0], boxB[0])
    yA = max(boxA[1], boxB[1])
    xB = min(boxA[2], boxB[2])
    yB = min(boxA[3], boxB[3])
    
    interArea = max(0, xB - xA) * max(0, yB - yA)
    areaA = (boxA[2] - boxA[0]) * (boxA[3] - boxA[1])
    print(interArea / float(areaA))
    return interArea / float(areaA)

def read_item(img, coordenadas):

    space = procesar_estacionamiento(img, json.loads(coordenadas))

    payload = {
        "id": 1,
        "ocuppied": space[0]["ocupado"]
    }

    response = requests.post(url_data, json=payload)
    print("Actualizado", response.status_code)

    if response.status_code == 200:
        # print(response.json())
        print("Tomori Deidad")


    message = json.dumps(space)
    print(message)
    return {"mensaje": message}

def get_cameras_data():
    index = 1
    data_lenght = 10

    while data_lenght >= 10:
        response = requests.get(url_data, params={"page": index}, headers={"X-CNN-Token": CNN_TOKEN})

        data = response.json()

        for i in range(len(data["data"])):
            cam_data = data["data"][i]
            
            get_camera_frame(cam_data["dvr"]["ip"], cam_data["dvr"]["user"], cam_data["dvr"]["port"], cam_data["channel"], cam_data["camera_zones"])

        index = index + 1
        data_lenght = len(data["data"])

    print("=============================================================================================================================\n==============================================================Ave Mujica=====================================================\n=============================================================================================================================\n")



def get_camera_frame(ip, user, password, channel, camera_zones):
    # data_send = {"ip": "192.168.1.100", "usuario": "admin", "contrasena": "password123", "canal": 1, "debug": True}

    data_send = {"ip": ip, "usuario": user, "contrasena": password, "canal": channel, "debug": True}
    
    response = requests.post(url_dvr, params=data_send)

    print(f"=================================================== Camera {ip} ============================================================")
    if response.status_code == 200:
        for i in range(len(camera_zones)):
            # 2. Convertir los bytes JPEG recibidos a una matriz de OpenCV (Numpy)
            image_bytes = np.frombuffer(response.content, dtype=np.uint8)
            frame = cv2.imdecode(image_bytes, cv2.IMREAD_COLOR)

            print(f"---------------------Zone {i + 1}-------------------------")

            procesar_estacionamiento(frame, [{'x1': camera_zones[i]["geometry"][0]["x1"], 'y1': camera_zones[i]["geometry"][0]["y1"], 'x2': camera_zones[i]["geometry"][0]["x2"], 'y2': camera_zones[i]["geometry"][0]["y2"], 'id': "sus"}])

    print("=============================================================================================================================")

    


while 1:


    # data = response

    # if response.status_code == 200:
    #     # 2. Convertir los bytes JPEG recibidos a una matriz de OpenCV (Numpy)
    #     image_bytes = np.frombuffer(response.content, dtype=np.uint8)
    #     frame = cv2.imdecode(image_bytes, cv2.IMREAD_COLOR)

    #     procesar_estacionamiento(frame, [{'x1': 700, 'y1': 0, 'x2': 1100, 'y2': 200, 'id': "sus"}])

    get_cameras_data()
    
    time.sleep(10)