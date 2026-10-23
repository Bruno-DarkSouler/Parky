<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Parky - Slots Dinámicos</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body, html { height: 100%; width: 100%; overflow: hidden; background-color: #ffffff; }
        
        #app-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            height: 100dvh;
            width: 100vw;
        }

        /* HEADER */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            background: #ffffff;
            z-index: 10;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .logo {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .logo-parking { color: #7C7CE0; }
        .logo-finder { color: #52B788; }
        
        .car-selector-container {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .car-selector {
            border: 2px solid #7C7CE0;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 14px;
            font-weight: 600;
            color: #1F2937;
            background-color: #F3F4F6;
            outline: none;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg width='10' height='6' viewBox='0 0 10 6' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1L5 5L9 1' stroke='%237C7CE0' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            padding-right: 30px;
        }
        .btn-add-car-header {
            background: #52B788;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 12px;
            font-weight: 700;
            cursor: pointer;
            font-size: 14px;
        }

        /* MAP */
        .map-container {
            flex: 1;
            width: 100%;
            z-index: 1;
            min-height: 0;
        }
        .leaflet-control-container { display: none !important; }

        /* BOTTOM CONTROLS WRAPPER */
        .bottom-controls {
            display: flex;
            flex-direction: column;
            width: 100%;
            background: #ffffff;
            z-index: 10;
        }

        /* ACTION AREA */
        .action-area {
            background: #ffffff;
            padding: 12px 16px;
            z-index: 10;
        }
        .btn-search {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            background-color: #52B788;
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(82, 183, 136, 0.4);
            transition: transform 0.1s;
        }
        .btn-search:active {
            transform: scale(0.98);
        }
        
        .nav-actions {
            display: none;
            width: 100%;
            gap: 12px;
        }
        .btn-action {
            flex: 1;
            padding: 16px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            border: none;
            cursor: pointer;
        }

        /* BOTTOM NAV */
        .bottom-nav {
            display: flex;
            justify-content: space-around;
            align-items: center;
            background: #ffffff;
            border-top: 1px solid #E5E7EB;
            padding: 10px 0 20px 0;
            z-index: 10;
        }
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            text-decoration: none;
            color: #4B5563;
            font-size: 12px;
            font-weight: 600;
        }
        .nav-item.active {
            color: #7C7CE0;
        }
        .nav-item svg {
            width: 26px;
            height: 26px;
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
        }

        /* PINS */
        .custom-pin {
            background: none;
            border: none;
        }
        .user-dot-container {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 70px;
            height: 70px;
            background: rgba(66, 133, 244, 0.2);
            border-radius: 50%;
        }
        .user-dot {
            width: 20px;
            height: 20px;
            background: #4285F4;
            border: 3px solid #ffffff;
            border-radius: 50%;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        }

        /* POPUP DESIGN */
        .leaflet-popup-content-wrapper {
            font-family: 'Inter', sans-serif;
            border-radius: 14px;
            padding: 4px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .zone-popup-card {
            width: 280px;
            max-width: 85vw;
        }
        .zone-popup-header {
            font-size: 15px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 4px;
        }
        .zone-popup-meta {
            font-size: 12px;
            color: #6B7280;
            margin-bottom: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 12px;
            margin-bottom: 10px;
        }
        .status-available { background: #D1FAE5; color: #065F46; }
        .status-full { background: #FEE2E2; color: #991B1B; }

        /* ZONE VISUAL BAR */
        .zone-bar-container {
            margin: 10px 0;
            background: #E5E7EB;
            border-radius: 8px;
            padding: 8px;
        }
        .zone-bar-title {
            font-size: 11px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 4px;
            display: flex;
            justify-content: space-between;
        }
        .zone-bar {
            position: relative;
            height: 24px;
            background: #D1D5DB;
            border-radius: 6px;
            overflow: hidden;
            display: flex;
        }
        .zone-segment {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
            color: white;
            text-shadow: 0 1px 2px rgba(0,0,0,0.5);
            transition: all 0.2s;
        }
        .seg-occupied { background: #EF4444; }
        .seg-free-fit { background: #10B981; }
        .seg-free-small { background: #F59E0B; }

        .gap-list {
            list-style: none;
            margin-top: 8px;
            font-size: 11px;
        }
        .gap-item {
            padding: 4px 0;
            border-bottom: 1px dashed #E5E7EB;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* --- ADD VEHICLE MODAL --- */
        #add-vehicle-modal {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255, 255, 255, 0.98);
            z-index: 9999;
            display: none;
            flex-direction: column;
            padding: 24px 20px;
            overflow-y: auto;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        #add-vehicle-modal.active {
            display: flex;
        }
        .av-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .av-back {
            font-size: 28px;
            font-weight: bold;
            color: #000;
            text-decoration: none;
        }
        .av-title-container {
            text-align: center;
            width: 100%;
            margin-bottom: 20px;
        }
        .av-title {
            font-size: 24px;
            font-weight: 800;
            color: #111827;
        }
        .av-subtitle {
            font-size: 13px;
            color: #6B7280;
            margin-top: 4px;
        }

        .av-form-group {
            margin-bottom: 16px;
        }
        .av-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 6px;
        }
        .av-input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            background: #FAFAFA;
        }
        .av-input:focus {
            border-color: #52B788;
            background: #FFFFFF;
        }
        .av-btn-submit {
            width: 100%;
            padding: 15px;
            background: #52B788;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            margin-top: 10px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(82, 183, 136, 0.4);
        }
        .av-btn-cancel {
            width: 100%;
            padding: 14px;
            background: transparent;
            color: #4B5563;
            border: 1px solid #D1D5DB;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            margin-top: 10px;
            cursor: pointer;
        }
        
        .map-blurred {
            filter: blur(8px);
            pointer-events: none;
        }

        /* --- SIDEBAR --- */
        .sidebar-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            z-index: 9999; opacity: 0; visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        .sidebar-overlay.visible { opacity: 1; visibility: visible; }
        .sidebar {
            position: fixed; top: 0; left: 0; width: 280px; height: 100%;
            background-color: #ffffff; z-index: 10000;
            transform: translateX(-100%);
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex; flex-direction: column;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
            overflow-y: auto;
        }
        .sidebar.open { transform: translateX(0); }

        .sidebar-header {
            padding: 1.8rem 1.5rem 1rem;
            background: #ffffff;
        }
        .sidebar-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #52B788;
            letter-spacing: -0.3px;
        }
        .sidebar-profile {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f3f4f6;
        }
        .sidebar-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .sidebar-profile-info {
            display: flex;
            flex-direction: column;
        }
        .sidebar-profile-name {
            font-size: 1rem;
            font-weight: 700;
            color: #1e2a38;
        }
        .sidebar-nav {
            display: flex;
            flex-direction: column;
            padding: 0.75rem 0;
        }
        .sidebar-nav-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.9rem 1.5rem;
            text-decoration: none;
            color: #374151;
            font-size: 0.95rem;
            font-weight: 600;
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        .sidebar-nav-item:hover, .sidebar-nav-item:active {
            background-color: #f3f4f6;
            color: #7C7CE0;
        }
        .sidebar-nav-item svg {
            flex-shrink: 0;
        }

        /* --- LANDSCAPE & COMPACT MOBILE RESPONSIVENESS --- */
        @media (orientation: landscape) and (max-height: 650px), (max-height: 520px) {
            .header {
                padding: 6px 14px;
                box-shadow: 0 1px 6px rgba(0,0,0,0.08);
            }
            .logo {
                font-size: 18px;
            }
            .car-selector-container {
                gap: 6px;
            }
            .car-selector {
                padding: 4px 8px;
                padding-right: 24px;
                font-size: 12px;
                height: 32px;
                background-position: right 6px center;
                max-width: 180px;
                text-overflow: ellipsis;
            }
            .btn-add-car-header {
                padding: 4px 10px;
                font-size: 12px;
                height: 32px;
                white-space: nowrap;
            }

            .bottom-controls {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                padding: 6px 14px;
                border-top: 1px solid #E5E7EB;
                gap: 12px;
            }
            .action-area {
                padding: 0;
                flex: 1;
                background: transparent;
            }
            .btn-search {
                padding: 8px 14px;
                font-size: 13px;
                border-radius: 8px;
                box-shadow: 0 2px 6px rgba(82, 183, 136, 0.3);
                white-space: nowrap;
                text-overflow: ellipsis;
                overflow: hidden;
            }
            .btn-action {
                padding: 8px 12px;
                font-size: 12px;
                border-radius: 8px;
            }
            .bottom-nav {
                padding: 0;
                border-top: none;
                gap: 14px;
                flex-shrink: 0;
                background: transparent;
            }
            .nav-item {
                flex-direction: row;
                gap: 4px;
                font-size: 11px;
            }
            .nav-item svg {
                width: 18px;
                height: 18px;
            }

            /* Add Vehicle Modal landscape optimization */
            #add-vehicle-modal {
                padding: 12px 18px;
            }
            .av-header {
                margin-bottom: 2px;
            }
            .av-title-container {
                margin-bottom: 8px;
            }
            .av-title {
                font-size: 18px;
            }
            .av-subtitle {
                font-size: 11px;
            }
            #add-vehicle-form {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 6px 12px;
            }
            .av-form-group {
                margin-bottom: 0;
            }
            .av-label {
                font-size: 11px;
                margin-bottom: 2px;
            }
            .av-input {
                padding: 6px 10px;
                font-size: 13px;
            }
            .av-btn-submit {
                grid-column: 1 / -1;
                padding: 9px;
                font-size: 13px;
                margin-top: 4px;
            }
            .av-btn-cancel {
                grid-column: 1 / -1;
                padding: 7px;
                font-size: 12px;
                margin-top: 2px;
            }

            /* Zone popup card max height in landscape */
            .zone-popup-card {
                max-height: 55vh;
                overflow-y: auto;
            }
            .zone-popup-header {
                font-size: 13px;
            }
            .zone-popup-meta {
                font-size: 11px;
                margin-bottom: 4px;
            }
            .status-badge {
                padding: 2px 6px;
                font-size: 11px;
                margin-bottom: 6px;
            }
            .zone-bar-container {
                margin: 6px 0;
                padding: 6px;
            }
            .sidebar {
                width: 250px;
            }
            .sidebar-header {
                padding: 1rem 1.25rem 0.5rem;
            }
            .sidebar-title {
                font-size: 1.25rem;
            }
            .sidebar-profile {
                padding: 0.6rem 1.25rem;
            }
            .sidebar-profile-name {
                font-size: 0.9rem;
            }
            .sidebar-nav {
                overflow-y: auto;
                padding: 0.4rem 0;
            }
            .sidebar-nav-item {
                padding: 0.6rem 1.25rem;
                font-size: 0.88rem;
                gap: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <!-- MODAL AGREGAR VEHICULO -->
    <div id="add-vehicle-modal">
        <div class="av-header">
            <a href="#" class="av-back" onclick="closeAddVehicleModal()">&#8249;</a>
        </div>
        <div class="av-title-container">
            <h1 class="av-title">Agregar Vehículo</h1>
            <p class="av-subtitle">Ingresa los datos y dimensiones de tu auto</p>
        </div>

        <form id="add-vehicle-form" onsubmit="submitVehicle(event)">
            <div class="av-form-group">
                <label class="av-label">Marca</label>
                <input type="text" id="av-brand" class="av-input" placeholder="Ej: Fiat" required>
            </div>
            <div class="av-form-group">
                <label class="av-label">Modelo</label>
                <input type="text" id="av-model" class="av-input" placeholder="Ej: 600 (Fitito)" required>
            </div>
            <div class="av-form-group">
                <label class="av-label">Largo (en metros)</label>
                <input type="number" step="0.01" id="av-length" class="av-input" placeholder="Ej: 3.30" required min="1.0" max="10.0">
            </div>
            <div class="av-form-group">
                <label class="av-label">Patente</label>
                <input type="text" id="av-plate" class="av-input" placeholder="Ej: FIT-600" required>
            </div>
            
            <button type="submit" class="av-btn-submit">Guardar vehículo</button>
            <button type="button" class="av-btn-cancel" onclick="closeAddVehicleModal()">Ahora no</button>
        </form>
    </div>

<div id="app-container">
    <header class="header">
        <div class="logo">
            <span class="logo-parking">Par</span><span class="logo-finder">ky</span>
        </div>
        <div class="car-selector-container">
            <select class="car-selector" id="vehicle-selector">
                <option value="">Cargando autos...</option>
            </select>
            <button class="btn-add-car-header" onclick="openAddVehicleModal()">+ Auto</button>
        </div>
    </header>

    <div id="map" class="map-container"></div>

    <div class="bottom-controls">
        <div class="action-area">
            <button class="btn-search" id="btn-search">Buscar estacionamiento disponible</button>
            <div class="nav-actions" id="nav-actions">
                <button class="btn-action" id="btn-recenter" style="background:#4285F4; display:none;">Centrar</button>
                <button class="btn-action" id="btn-cancel" style="background:#F94144;">Cancelar Ruta</button>
            </div>
        </div>

        <nav class="bottom-nav">
            <a href="#" class="nav-item active">
                <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="10" r="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Mapa</span>
            </a>

            <a href="#" class="nav-item" id="nav-menu">
                <svg viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12" stroke-linecap="round" stroke-linejoin="round"/><line x1="3" y1="6" x2="21" y2="6" stroke-linecap="round" stroke-linejoin="round"/><line x1="3" y1="18" x2="21" y2="18" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Menú</span>
            </a>
        </nav>
    </div>
</div>

@include('partials.sidebar')

<!-- LEAFLET & ROUTING -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // 1. TTS Synthesizer
        let ttsVoice = null;

        if ('speechSynthesis' in window) {
            const pickVoice = () => {
                const voices = window.speechSynthesis.getVoices();
                const priority = [
                    'Microsoft Sabina', 'Microsoft Helena', 'Microsoft Laura',
                    'Google español', 'Google Español'
                ];
                for (const name of priority) {
                    const v = voices.find(v => v.name === name);
                    if (v) { ttsVoice = v; return; }
                }
                ttsVoice = voices.find(x => x.lang === 'es-AR') || voices.find(x => x.lang.startsWith('es')) || voices[0];
            };
            pickVoice();
            window.speechSynthesis.onvoiceschanged = pickVoice;
        }

        const TTS = {
            speak(text) {
                if (!('speechSynthesis' in window)) return;
                window.speechSynthesis.cancel();
                
                const utt = new SpeechSynthesisUtterance(text);
                utt.lang = 'es-AR';
                utt.rate = 0.95;
                if (ttsVoice) utt.voice = ttsVoice;
                window.speechSynthesis.speak(utt);
            }
        };

        // 2. MAP SETUP
        const map = L.map('map', { zoomControl: false }).setView([-34.5292, -58.5499], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '',
            maxZoom: 19
        }).addTo(map);

        window.addEventListener('resize', () => map.invalidateSize());
        window.addEventListener('orientationchange', () => setTimeout(() => map.invalidateSize(), 300));

        const markersGroup = L.layerGroup().addTo(map);

        let userLat = null;
        let userLng = null;
        let userMarker = null;
        let parkingSpots = [];
        let routeControl = null;
        let isNavigating = false;
        let mapAutoFollow = true;

        // 3. FETCH CARS
        function loadCars() {
            fetch('/api/v1/cars', { credentials: 'same-origin' })
                .then(res => res.json())
                .then(cars => {
                    const sel = document.getElementById('vehicle-selector');
                    sel.innerHTML = '';
                    const carList = Array.isArray(cars) ? cars : (cars.data || []);
                    
                    if (carList.length === 0) {
                        openAddVehicleModal(true);
                        return;
                    }

                    carList.forEach(car => {
                        const opt = document.createElement('option');
                        opt.value = car.id;
                        const plate = car.plate ? car.plate.toUpperCase() : 'S/N';
                        const model = (car.brand ? car.brand + ' ' : '') + (car.model || 'Vehículo');
                        const len = car.length_meters ? ` (${car.length_meters}m)` : '';
                        opt.textContent = `${model} [${plate}]${len}`;
                        sel.appendChild(opt);
                    });

                    // Preselect car from localStorage if available
                    const savedCarId = localStorage.getItem('selected_car_id');
                    if (savedCarId && carList.some(c => c.id == savedCarId)) {
                        sel.value = savedCarId;
                    } else if (sel.options.length > 0) {
                        sel.value = sel.options[0].value;
                        localStorage.setItem('selected_car_id', sel.options[0].value);
                    }

                    // Load spots after cars loaded
                    const searchLat = userLat || -34.5292;
                    const searchLng = userLng || -58.5499;
                    loadParkingSpots(searchLat, searchLng);
                })
                .catch(err => console.warn('[Cars API]', err));
        }

        loadCars();

        // Re-load parking spots when vehicle changes
        document.getElementById('vehicle-selector').addEventListener('change', (e) => {
            if (e.target.value) {
                localStorage.setItem('selected_car_id', e.target.value);
            }
            const searchLat = userLat || -34.5292;
            const searchLng = userLng || -58.5499;
            loadParkingSpots(searchLat, searchLng);
        });

        // Modal functions
        window.openAddVehicleModal = function(force = false) {
            document.getElementById('map').classList.add('map-blurred');
            document.getElementById('add-vehicle-modal').classList.add('active');
            if (force) {
                document.querySelector('.av-btn-cancel').style.display = 'none';
            } else {
                document.querySelector('.av-btn-cancel').style.display = 'block';
            }
        };

        window.closeAddVehicleModal = function() {
            const sel = document.getElementById('vehicle-selector');
            if (sel.options.length === 0 && document.querySelector('.av-btn-cancel').style.display === 'none') {
                alert("Debes agregar al menos un vehículo para consultar la disponibilidad del mapa.");
                return;
            }
            document.getElementById('add-vehicle-modal').classList.remove('active');
            document.getElementById('map').classList.remove('map-blurred');
        };

        window.submitVehicle = function(e) {
            e.preventDefault();
            const brand = document.getElementById('av-brand').value;
            const model = document.getElementById('av-model').value;
            const length = parseFloat(document.getElementById('av-length').value);
            const plate = document.getElementById('av-plate').value;

            const btn = e.target.querySelector('button[type="submit"]');
            btn.textContent = 'Guardando...';
            btn.disabled = true;

            fetch('/api/v1/cars', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    brand: brand,
                    model: model,
                    length_meters: length,
                    plate: plate,
                    width_meters: 1.8
                })
            })
            .then(res => res.json())
            .then(data => {
                btn.textContent = 'Guardar vehículo';
                btn.disabled = false;
                if (data.id) {
                    window.closeAddVehicleModal();
                    loadCars();
                } else {
                    alert(data.message || "Error al guardar el vehículo");
                }
            })
            .catch(err => {
                console.error(err);
                alert("Error de conexión");
                btn.textContent = 'Guardar vehículo';
                btn.disabled = false;
            });
        };

        // 4. PINS & ICONS
        const createUserPin = () => L.divIcon({
            className: 'custom-pin',
            html: `<div class="user-dot-container"><div class="user-dot"></div></div>`,
            iconSize: [70, 70],
            iconAnchor: [35, 35]
        });

        const createParkingPin = (color, slotCount) => L.divIcon({
            className: 'custom-pin',
            html: `
                <svg width="46" height="56" viewBox="0 0 40 50" style="filter: drop-shadow(0px 4px 6px rgba(0,0,0,0.3));">
                    <path d="M20 0C8.954 0 0 8.954 0 20c0 15 20 30 20 30s20-15 20-30C40 8.954 31.046 0 20 0z" fill="${color}"/>
                    <circle cx="20" cy="18" r="11" fill="#ffffff"/>
                    <text x="20" y="22" font-family="Inter" font-size="13" font-weight="800" fill="${color}" text-anchor="middle">${slotCount}</text>
                </svg>
            `,
            iconSize: [46, 56],
            iconAnchor: [23, 56]
        });

        // GEOLOCATION
        if (navigator.geolocation) {
            navigator.geolocation.watchPosition(pos => {
                userLat = pos.coords.latitude;
                userLng = pos.coords.longitude;
                const latlng = L.latLng(userLat, userLng);
                
                if (userMarker) {
                    userMarker.setLatLng(latlng);
                } else {
                    userMarker = L.marker(latlng, { icon: createUserPin(), zIndexOffset: 1000 }).addTo(map);
                    map.setView(latlng, 15);
                    loadParkingSpots(userLat, userLng);
                }
            }, null, { enableHighAccuracy: true });
        }

        // 5. LOAD PARKING SPOTS (DYNAMIC CALCULATIONS)
        function loadParkingSpots(lat, lng) {
            markersGroup.clearLayers();
            let carId = document.getElementById('vehicle-selector').value;
            if (!carId) {
                carId = localStorage.getItem('selected_car_id') || '';
            }
            let url = `/api/v1/parking/available?lat=${lat}&lng=${lng}`;
            if (carId) url += `&car_id=${carId}`;

            fetch(url, { credentials: 'same-origin' })
                .then(res => res.json())
                .then(data => {
                    const spots = data.results || data.data || [];
                    parkingSpots = spots;

                    spots.forEach(spot => {
                        const sLat = spot.lat ?? spot.coordinates?.lat;
                        const sLng = spot.lng ?? spot.coordinates?.lon;
                        if (!sLat || !sLng) return;

                        const slots = spot.available_slots_for_this_car ?? spot.available_slots ?? 0;
                        const isAvailable = slots > 0;
                        const color = isAvailable ? '#10B981' : '#EF4444';
                        
                        const carModel = spot.car_info?.model || 'Tu auto';
                        const carLength = spot.car_info?.length_meters || 4.5;
                        const reqSpace = spot.car_info?.required_space || (carLength + 1.0);
                        const totalLen = spot.geometry_info?.total_length || 20.0;
                        const occupied = spot.geometry_info?.occupied_objects || [];
                        const gaps = spot.geometry_info?.free_gaps || [];

                        let locationName = spot.location_approximate || spot.street_name || 'Calle cercana';
                        if (!locationName || locationName.toLowerCase().startsWith('cámara') || locationName.toLowerCase().startsWith('camara')) {
                            locationName = 'Calle cercana';
                        }
                        const verifiedTime = spot.last_verified_at || 'Hace instantes';

                        const popupHtml = `
                            <div class="zone-popup-card" style="padding: 6px 4px;">
                                <div style="display: flex; align-items: flex-start; gap: 8px; margin-bottom: 10px;">
                                    <div style="background: #EEF2FF; color: #7C7CE0; width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">📍</div>
                                    <div>
                                        <div style="font-size: 11px; font-weight: 700; color: #6B7280; text-transform: uppercase; letter-spacing: 0.5px;">Ubicación aproximada</div>
                                        <div style="font-size: 14px; font-weight: 800; color: #111827; line-height: 1.2;">${locationName}</div>
                                    </div>
                                </div>
                                
                                <div class="status-badge ${isAvailable ? 'status-available' : 'status-full'}" style="width: 100%; text-align: center; padding: 10px; font-size: 13px; margin-bottom: 10px; border-radius: 8px; font-weight: 800;">
                                    ${isAvailable ? `✅ HAY LUGAR PARA TU VEHÍCULO` : `❌ SIN LUGAR PARA TU VEHÍCULO`}
                                </div>

                                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11px; color: #6B7280; border-top: 1px solid #F3F4F6; padding-top: 8px; margin-top: 4px;">
                                    <span style="display: flex; align-items: center; gap: 4px;">🕒 Última verificación:</span>
                                    <span style="font-weight: 700; color: #374151;">${verifiedTime}</span>
                                </div>
                            </div>
                        `;

                        const marker = L.marker([sLat, sLng], { icon: createParkingPin(color, slots) })
                            .bindPopup(popupHtml);
                        markersGroup.addLayer(marker);
                    });
                })
                .catch(err => console.warn('[Parking API]', err));
        }

        // NAVIGATION
        document.getElementById('btn-search').addEventListener('click', () => {
            if (!userLat || parkingSpots.length === 0) {
                TTS.speak('Buscando estacionamientos cercanos');
                return;
            }

            let nearest = null;
            let minDist = Infinity;
            
            parkingSpots.forEach(spot => {
                const sLat = spot.lat ?? spot.coordinates?.lat;
                const sLng = spot.lng ?? spot.coordinates?.lon;
                const slots = spot.available_slots_for_this_car ?? spot.available_slots ?? 0;
                if (slots > 0 && sLat) {
                    const d = Math.hypot(sLat - userLat, sLng - userLng);
                    if (d < minDist) { minDist = d; nearest = {lat: sLat, lng: sLng, name: spot.street_name || spot.name}; }
                }
            });

            if (nearest) {
                startNavigation(nearest);
            } else {
                TTS.speak('No se encontraron slots disponibles para las dimensiones de tu auto en esta zona');
                alert('No se encontraron estacionamientos con espacio suficiente para tu vehículo actual.');
            }
        });

        function startNavigation(spot) {
            isNavigating = true;
            mapAutoFollow = true;
            document.getElementById('btn-search').style.display = 'none';
            document.getElementById('nav-actions').style.display = 'flex';
            
            if (routeControl) map.removeControl(routeControl);
            
            routeControl = L.Routing.control({
                waypoints: [ L.latLng(userLat, userLng), L.latLng(spot.lat, spot.lng) ],
                router: L.Routing.osrmv1({ language: 'es', profile: 'driving' }),
                routeWhileDragging: false,
                show: false,
                createMarker: () => null,
                lineOptions: { styles: [{ color: '#7C7CE0', weight: 6 }] }
            }).addTo(map);

            TTS.speak("Navegando hacia " + spot.name);
            map.setView([userLat, userLng], 18);
        }

        document.getElementById('btn-cancel').addEventListener('click', () => {
            if (routeControl) map.removeControl(routeControl);
            isNavigating = false;
            document.getElementById('btn-search').style.display = 'flex';
            document.getElementById('nav-actions').style.display = 'none';
            TTS.speak("Navegación cancelada");
            map.setView([userLat || -34.5292, userLng || -58.5499], 15);
        });

        document.getElementById('btn-recenter').addEventListener('click', () => {
            mapAutoFollow = true;
            map.setView([userLat, userLng], 18);
            document.getElementById('btn-recenter').style.display = 'none';
        });
    });
</script>
</body>
</html>
