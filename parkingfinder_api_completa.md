# Especificación Técnica de la API: ParkingFinder (Completa)

Este documento contiene la especificación técnica detallada para el desarrollo de la API de **ParkingFinder**, diseñada bajo una arquitectura RESTful, optimizada para Progressive Web Apps (PWA), aplicaciones nativas Android y compatibilidad con Android Auto mediante la `CarAppLibrary`.

---

## 1. Arquitectura General y Seguridad

* **Protocolo:** HTTPS / RESTful.
* **Formato de intercambio:** JSON (Content-Type: `application/json`).
* **Autenticación:** Basada en **JWT (JSON Web Tokens)**.
    * Cada flujo protegido requerirá el encabezado `Authorization: Bearer <access_token>`.
    * Se implementará un mecanismo de doble token: `access_token` (vida corta: 15 min) y `refresh_token` (vida larga: 7 días, almacenado en base de datos para revocación).
* **Manejo de Errores:** Estándar HTTP Status Codes (`200 OK`, `201 Created`, `400 Bad Request`, `401 Unauthorized`, `403 Forbidden`, `422 Unprocessable Entity`, `500 Internal Server Error`) acompañados de un payload estructurado: `{"error": true, "code": "ERR_CODE", "message": "Descripción detallada"}`.

---

## 2. Modelo de Datos Detallado (Esquema de Base de Datos)

Se adopta un enfoque basado en identificadores **UUID** para mitigar vulnerabilidades de enumeración y asegurar la escalabilidad. Se introduce el concepto de **Zonas de Cámara Dinámicas** para resolver el cálculo de espacio libre según las dimensiones de cada vehículo.

### Tabla: `users`
Representa las cuentas de usuario y administradores del sistema.
* `id` (UUID, PK)
* `name` (String, max 255)
* `email` (String, Unique, Indexed)
* `email_verified_at` (Timestamp, Nullable)
* `password` (String, Bcrypt/Argon2 Hash)
* `remember_token` (String, Nullable)
* `status` (Integer, Default: 1) - *1: Activo, 0: Suspendido*
* `created_at` (Timestamp)
* `updated_at` (Timestamp)

### Tabla: `dvrs`
Unidades físicas de grabación y procesamiento conectadas a la red.
* `id` (UUID, PK)
* `user_id` (UUID, FK -> `users.id`) - *Usuario dueño o administrador del nodo*
* `lat` (Decimal, 10,8) - *Precisión GPS*
* `lon` (Decimal, 11,8) - *Precisión GPS*
* `ip` (String / INET) - *Dirección IP del equipo o proxy reverse*
* `port` (Integer)
* `user` (String) - *Credencial de acceso al flujo RTSP/API local*
* `password` (String, Encrypted)
* `status` (Integer) - *1: Online, 0: Offline, 2: Mantenimiento*
* `created_at` (Timestamp)
* `updated_at` (Timestamp)

### Tabla: `cameras`
Canales de video individuales vinculados a un DVR.
* `id` (UUID, PK)
* `dvr_id` (UUID, FK -> `dvrs.id`)
* `channel` (Integer) - *Número de canal físico en el DVR*
* `name` (String) - *Ej: "Frente Calle Ader"*
* `status` (Integer)
* `created_at` (Timestamp)
* `updated_at` (Timestamp)

### Tabla: `camera_zones` (Núcleo Espacial Dinámico)
Representa el tramo de calle o área física continua que una cámara vigila.
* `id` (UUID, PK)
* `camera_id` (UUID, FK -> `cameras.id`)
* `geometry` (JSONB) - *Polígono coordenado (píxeles de la cámara) que delimita la zona de tracking*
* `total_length_meters` (Decimal, 5,2) - *Longitud total real del frente medido en metros (Ej: 15.00 metros)*
* `homography_matrix` (JSONB) - *Matriz de transformación para convertir píxeles de imagen a metros lineales reales en el eje de la calle*
* `status` (Integer)
* `created_at` (Timestamp)
* `updated_at` (Timestamp)

### Tabla: `occupancy_log`
Historial y estado actual del procesamiento de imágenes enviado por la IA (Webhooks).
* `id` (UUID, PK)
* `camera_zone_id` (UUID, FK -> `camera_zones.id`)
* `detected_objects_coords` (JSONB) - *Arreglo de objetos detectados con sus posiciones reales transformadas a metros lineales en la recta [0, total_length_meters]. Ejemplo: `[{"start": 5.0, "end": 9.2}, {"start": 12.1, "end": 14.5}]`*
* `created_at` (Timestamp, Indexed)

### Tabla: `cars`
Vehículos del usuario. Sus dimensiones determinan si "entran" en los espacios libres dinámicos.
* `id` (UUID, PK)
* `user_id` (UUID, FK -> `users.id`)
* `plate` (String, Unique) - *Patente/Placa*
* `width_meters` (Decimal, 3,2)
* `length_meters` (Decimal, 3,2) - *Dimensión crítica para el cálculo del slot*
* `status` (Integer)
* `created_at` (Timestamp)
* `updated_at` (Timestamp)

---

## 3. Lógica del Negocio: Cálculo Dinámico de Disponibilidad

Cuando un usuario busca estacionamiento a través de la PWA o Android Auto, la API **no consulta un slot estático preasignado**, sino que ejecuta la siguiente lógica al vuelo:

1. El cliente llama a `GET /api/v1/parking/available` enviando su ubicación (`lat`, `lon`) y el `car_id`.
2. La API obtiene el `length_meters` del auto elegido. El espacio requerido se define como:  
   $$	ext{Espacio Requerido} = 	ext{length\_meters} + 1.0	ext{ metro (margen de maniobra: 0.5m adelante y 0.5m atrás)}$$
3. Se buscan las `camera_zones` cercanas geográficamente mediante un radio de cobertura sobre la ubicación del `dvr`.
4. Para cada zona, se toma el último registro de `occupancy_log` y se analiza la recta continua desde `0` hasta `total_length_meters`:
   * Se calculan los segmentos libres resultantes de restar las posiciones ocupadas por los objetos detectados.
   * Si un segmento libre es mayor o igual al **Espacio Requerido**, se cuenta como **1 espacio disponible para ese vehículo específico**.
5. La API retorna un listado de zonas optimizadas con el número real de espacios útiles según el tamaño de su auto.

---

## 4. Endpoints de la API

### 4.1 Autenticación (`/api/v1/auth`)

* **`POST /register`**
  * **Payload:** `{"name": "...", "email": "...", "password": "..."}`
  * **Response:** `211 Created` -> `{"success": true, "message": "Usuario registrado"}`
* **`POST /login`**
  * **Payload:** `{"email": "...", "password": "..."}`
  * **Response:** `200 OK` -> `{"access_token": "JWT_STRING", "refresh_token": "JWT_STRING", "user": { ... }}`
* **`POST /refresh`**
  * **Payload:** `{"refresh_token": "JWT_STRING"}`
  * **Response:** `200 OK` -> `{"access_token": "JWT_STRING"}`
* **`POST /logout`**
  * **Payload:** `{"refresh_token": "JWT_STRING"}` (Protegido por Bearer token)
  * **Response:** `200 OK` -> `{"success": true}`

### 4.2 Gestión de Vehículos (`/api/v1/cars`)

* **`GET /`** (Protegido)
  * **Response:** `200 OK` -> `[{"id": "...", "plate": "...", "length_meters": 4.50, ...}]`
* **`POST /`** (Protegido)
  * **Payload:** `{"plate": "...", "width_meters": 1.80, "length_meters": 4.50}`
  * **Response:** `201 Created`
* **`PUT /{id}`** (Protegido)
  * **Payload:** Modificaciones parciales de dimensiones o patente.
* **`DELETE /{id}`** (Protegido)
  * **Response:** `200 OK` -> `{"deleted": true}`

### 4.3 Ingesta IoT / Webhooks de Cámaras (`/api/v1/iot`)

Este endpoint es consumido directamente por el software de análisis de imagen montado localmente o en el servidor de streaming.

* **`POST /webhook/camera-data`**
  * **Headers:** `X-Camera-Token: secure_webhook_secret_token`
  * **Payload:**
    ```json
    {
      "camera_zone_id": "uuid-de-la-zona",
      "timestamp": "2026-06-26T22:45:00Z",
      "detected_objects_coords": [
        {"start": 5.0, "end": 9.5}
      ]
    }
    ```
  * **Lógica interna:** Inserta directamente en la tabla `occupancy_log`.
  * **Response:** `200 OK` -> `{"processed": true}`

### 4.4 Búsqueda y Disponibilidad (`/api/v1/parking`)

* **`GET /available`** (Protegido - Optimizado para PWA y Móvil)
  * **Query Params:** `lat` (Decimal), `lon` (Decimal), `car_id` (UUID), `radius_meters` (Integer, opcional, default 500)
  * **Response:** `200 OK`
    ```json
    {
      "search_center": {"lat": -34.5678, "lon": -58.4567},
      "results": [
        {
          "zone_id": "uuid-zona",
          "street_name": "Av. Eduardo Ader 1500",
          "distance_meters": 120,
          "available_slots_for_this_car": 2,
          "geometry_info": { "total_length": 15.0 },
          "coordinates": {"lat": -34.5680, "lon": -58.4560}
        }
      ]
    }
    ```

### 4.5 Endpoints Específicos para Android Auto (`/api/v1/android-auto`)

La librería `CarAppLibrary` exige payloads minimalistas para evitar procesamiento excesivo en la unidad del tablero del auto.

* **`GET /nearby`** (Protegido)
  * **Query Params:** `lat`, `lon`, `car_id`
  * **Response:** `200 OK` -> Retorna únicamente una lista simplificada reduciendo el peso de la respuesta a menos de 2KB:
    ```json
    [
      {
        "title": "Ader 1500",
        "slots": 2,
        "lat": -34.5680,
        "lon": -58.4560
      }
    ]
    ```
