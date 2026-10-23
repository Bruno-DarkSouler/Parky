<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Cámara y Zonas | Parky</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- Feather Icons -->
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/canvas-marker.css') }}">
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>

    <!-- Header Navigation -->
    <header class="cameras-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="header-title">
                    <i data-feather="video" class="text-success"></i>
                    <span>Configuración de Cámara</span>
                    <span class="step-badge" id="current_step_badge">Paso 1 de 2</span>
                </div>
                <div>
                    <a href="{{ route('app.share_camera') }}" class="btn btn-custom-secondary btn-sm">
                        <i data-feather="arrow-left" style="width: 16px; height: 16px;"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        
        <!-- Step Progress Indicator -->
        <div class="step-indicator-bar">
            <div class="step-item active" id="indicator_step1">
                <div class="step-num">1</div>
                <div>Conexión y Ubicación DVR</div>
            </div>
            <div class="step-item" id="indicator_step2">
                <div class="step-num">2</div>
                <div>Zona y Metros del Área</div>
            </div>
        </div>

        <!-- ==================== STEP 1: CONEXIÓN AL DVR Y UBICACIÓN ==================== -->
        <div id="view_step1" class="step-card animate-fade-in">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="p-3 rounded-circle" style="background: rgba(16, 185, 129, 0.15); color: #10B981;">
                    <i data-feather="server" style="width: 28px; height: 28px;"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-1" style="font-size: 1.35rem;">Paso 1: Datos del DVR y Ubicación Exacta</h3>
                    <p class="text-muted mb-0" style="font-size: 0.92rem;">Ingrese los datos de conexión y busque la dirección o arrastre el marcador en el mapa para ubicar la cámara.</p>
                </div>
            </div>

            <form id="dvr_connection_form" onsubmit="event.preventDefault();">
                <!-- DVR Credentials Section -->
                <h5 class="fw-bold text-light mb-3"><i data-feather="lock" class="me-2 text-success" style="width: 18px;"></i>Datos de Conexión DVR</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="ip_input" class="form-label-custom">IP / Dominio del DVR <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-muted"><i data-feather="globe" style="width: 18px;"></i></span>
                            <input type="text" id="ip_input" class="form-control form-dark-input" placeholder="Ej: 192.168.1.100 o debug" value="debug" required>
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.8rem;">Sugerencia: Ingrese "debug" para probar el simulador sin equipo físico.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="port_input" class="form-label-custom">Puerto HTTP / ISAPI</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-muted"><i data-feather="hash" style="width: 18px;"></i></span>
                            <input type="number" id="port_input" class="form-control form-dark-input" placeholder="80" value="80">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="user_input" class="form-label-custom">Usuario</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-muted"><i data-feather="user" style="width: 18px;"></i></span>
                            <input type="text" id="user_input" class="form-control form-dark-input" placeholder="admin" value="admin">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="password_input" class="form-label-custom">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-muted"><i data-feather="key" style="width: 18px;"></i></span>
                            <input type="password" id="password_input" class="form-control form-dark-input" placeholder="••••••••" value="admin123">
                        </div>
                    </div>
                </div>

                <hr class="border-secondary my-4">

                <!-- Location & Search Section -->
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h5 class="fw-bold text-light m-0"><i data-feather="map-pin" class="me-2 text-success" style="width: 18px;"></i>Ubicación Exacta de la Cámara</h5>
                    <button type="button" id="btn_use_current_location" class="btn btn-custom-secondary btn-sm">
                        <i data-feather="navigation" style="width: 16px;"></i> Usar mi ubicación actual
                    </button>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 search-container">
                        <label for="location_search_input" class="form-label-custom">Buscador de Dirección (con Autocompletado):</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-muted"><i data-feather="search" style="width: 18px;"></i></span>
                            <input type="text" id="location_search_input" class="form-control form-dark-input" placeholder="Ej. Cerrito 3966, Rosario o Av. Corrientes 1234, Buenos Aires..." autocomplete="off">
                        </div>
                        <div id="location_autocomplete_results" class="autocomplete-results d-none"></div>
                    </div>
                </div>

                <!-- Map Container -->
                <div class="mb-3">
                    <div id="location_map"></div>
                </div>

                <!-- Coordinates Inputs -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="lat_input" class="form-label-custom">Latitud</label>
                        <input type="number" step="any" id="lat_input" class="form-control form-dark-input" value="-34.603722" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="lon_input" class="form-label-custom">Longitud</label>
                        <input type="number" step="any" id="lon_input" class="form-control form-dark-input" value="-58.381592" readonly>
                    </div>
                </div>

                <!-- Check Channels Action Button -->
                <div class="mt-4 d-flex justify-content-start align-items-center gap-3">
                    <button type="button" id="btn_check_channels" class="btn btn-custom-primary">
                        <i data-feather="refresh-cw" style="width: 18px;"></i>
                        <span>Chequear canales disponibles</span>
                    </button>
                </div>
            </form>

            <!-- Error Banner -->
            <div id="error_alert" class="alert alert-custom-danger mt-4 d-none">
                <div class="d-flex align-items-start gap-3">
                    <i data-feather="alert-triangle" style="width: 22px; height: 22px; flex-shrink: 0;" class="text-danger"></i>
                    <div>
                        <strong class="d-block mb-1">Error de Conexión al DVR</strong>
                        <span id="error_alert_text">No se pudo establecer comunicación con el DVR.</span>
                    </div>
                </div>
            </div>

            <!-- Success Channel Selection Container (hidden initially) -->
            <div id="channels_container" class="mt-4 p-4 rounded-3 d-none" style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25);">
                <div class="d-flex align-items-center gap-2 text-success fw-bold mb-3" style="font-size: 1.05rem;">
                    <i data-feather="check-circle" style="width: 22px; height: 22px;"></i>
                    <span>¡Conexión exitosa al contenedor DVR_CONNECTOR!</span>
                </div>

                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label for="channel_select" class="form-label-custom">Seleccione el Canal de Cámara:</label>
                        <select id="channel_select" class="form-select form-dark-input" style="font-size: 1rem;">
                            <!-- Populated dynamically -->
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="button" id="btn_go_to_step2" class="btn btn-custom-primary w-100 py-2">
                            <span>Siguiente: Definir Zonas (Paso 2)</span>
                            <i data-feather="arrow-right" style="width: 18px;"></i>
                        </button>
                    </div>
                </div>
            </div>

        </div>


        <!-- ==================== STEP 2: DEFINIR ZONA Y METROS ==================== -->
        <div id="view_step2" class="step-card animate-fade-in d-none">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 rounded-circle" style="background: rgba(59, 130, 246, 0.15); color: #3B82F6;">
                        <i data-feather="maximize-2" style="width: 28px; height: 28px;"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-1" style="font-size: 1.35rem;">Paso 2: Selección de Zona y Metros del Área</h3>
                        <p class="text-muted mb-0" style="font-size: 0.92rem;">Ajuste los recuadros en la imagen de la cámara e indique el largo en metros para cada área.</p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span id="active_dvr_badge" class="badge bg-dark border border-secondary px-3 py-2 text-muted" style="font-weight: 500;">
                        DVR: <strong id="lbl_connected_ip" class="text-light">192.168.1.100</strong> | Canal: <strong id="lbl_connected_channel" class="text-success">1</strong>
                    </span>
                    <button type="button" id="btn_back_to_step1" class="btn btn-custom-secondary btn-sm">
                        <i data-feather="arrow-left" style="width: 16px;"></i> Volver
                    </button>
                </div>
            </div>

            <!-- Canvas Container -->
            <div id="canvas_wrapper">
                <div id="canvas_loading" class="text-center p-5 text-muted">
                    <div class="spinner-border text-success mb-3" role="status"></div>
                    <div>Obteniendo frame de cámara mediante <strong>DVR_CONNECTOR</strong>...</div>
                </div>
                <div id="container"></div>
            </div>

            <!-- Controls and Table -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold m-0"><i data-feather="grid" class="me-2 text-success"></i>Zonas Configuradas</h5>
                <button type="button" id="btn_new_rect" class="btn btn-custom-primary btn-sm">
                    <i data-feather="plus" style="width: 18px;"></i>
                    <span>Agregar espacio / zona</span>
                </button>
            </div>

            <div class="zones-table-container">
                <table id="data_section" class="zones-table">
                    <!-- Populated dynamically by marcador.js -->
                </table>
            </div>

            <!-- Save Action Button -->
            <div class="mt-4 pt-3 border-top border-secondary d-flex justify-content-end gap-3">
                <button type="button" id="btn_save_camera" class="btn btn-custom-primary btn-lg">
                    <i data-feather="save" style="width: 20px;"></i>
                    <span>Guardar Configuración de Cámara</span>
                </button>
            </div>

        </div>

    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="saveSuccessModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-light border border-secondary">
                <div class="modal-body text-center p-4">
                    <div class="p-3 rounded-circle d-inline-block mb-3" style="background: rgba(16, 185, 129, 0.2); color: #10B981;">
                        <i data-feather="check-circle" style="width: 48px; height: 48px;"></i>
                    </div>
                    <h4 class="fw-bold mb-2">¡Configuración Guardada!</h4>
                    <p class="text-muted mb-4" id="saveSuccessText">La cámara y sus zonas asociadas han sido almacenadas exitosamente.</p>
                    <button type="button" class="btn btn-custom-primary px-4" data-bs-dismiss="modal" onclick="window.location.reload();">Entendido</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/konva.js') }}"></script>
    <script src="{{ asset('js/marcador.js') }}"></script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            feather.replace();

            // Elements
            const viewStep1 = document.getElementById("view_step1");
            const viewStep2 = document.getElementById("view_step2");
            const indicatorStep1 = document.getElementById("indicator_step1");
            const indicatorStep2 = document.getElementById("indicator_step2");
            const currentStepBadge = document.getElementById("current_step_badge");

            const ipInput = document.getElementById("ip_input");
            const portInput = document.getElementById("port_input");
            const userInput = document.getElementById("user_input");
            const passwordInput = document.getElementById("password_input");

            const latInput = document.getElementById("lat_input");
            const lonInput = document.getElementById("lon_input");
            const locationSearchInput = document.getElementById("location_search_input");
            const autocompleteResults = document.getElementById("location_autocomplete_results");
            const btnUseCurrentLocation = document.getElementById("btn_use_current_location");

            const btnCheckChannels = document.getElementById("btn_check_channels");
            const errorAlert = document.getElementById("error_alert");
            const errorAlertText = document.getElementById("error_alert_text");
            const channelsContainer = document.getElementById("channels_container");
            const channelSelect = document.getElementById("channel_select");
            const btnGoToStep2 = document.getElementById("btn_go_to_step2");

            const btnBackToStep1 = document.getElementById("btn_back_to_step1");
            const lblConnectedIp = document.getElementById("lbl_connected_ip");
            const lblConnectedChannel = document.getElementById("lbl_connected_channel");
            const canvasLoading = document.getElementById("canvas_loading");
            const btnNewRect = document.getElementById("btn_new_rect");
            const btnSaveCamera = document.getElementById("btn_save_camera");

            let currentDVRConfig = null;

            // ==================== LEAFLET MAP & AUTOCOMPLETE ====================
            const defaultLat = -34.603722;
            const defaultLon = -58.381592;

            const map = L.map('location_map', {
                zoomControl: true,
                attributionControl: false
            }).setView([defaultLat, defaultLon], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(map);

            const marker = L.marker([defaultLat, defaultLon], {
                draggable: true
            }).addTo(map);

            function updateCoordinates(lat, lon) {
                latInput.value = parseFloat(lat).toFixed(6);
                lonInput.value = parseFloat(lon).toFixed(6);
            }

            marker.on('dragend', function(e) {
                const position = marker.getLatLng();
                updateCoordinates(position.lat, position.lng);
                reverseGeocode(position.lat, position.lng);
            });

            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                updateCoordinates(e.latlng.lat, e.latlng.lng);
                reverseGeocode(e.latlng.lat, e.latlng.lng);
            });

            // Geolocation button
            btnUseCurrentLocation.addEventListener("click", function() {
                if (navigator.geolocation) {
                    btnUseCurrentLocation.disabled = true;
                    btnUseCurrentLocation.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Obteniendo GPS...`;

                    navigator.geolocation.getCurrentPosition(
                        function(pos) {
                            const lat = pos.coords.latitude;
                            const lon = pos.coords.longitude;
                            map.setView([lat, lon], 16);
                            marker.setLatLng([lat, lon]);
                            updateCoordinates(lat, lon);
                            reverseGeocode(lat, lon);
                            btnUseCurrentLocation.disabled = false;
                            btnUseCurrentLocation.innerHTML = `<i data-feather="navigation" style="width: 16px;"></i> Usar mi ubicación actual`;
                            feather.replace();
                        },
                        function(err) {
                            alert("No se pudo obtener su ubicación actual.");
                            btnUseCurrentLocation.disabled = false;
                            btnUseCurrentLocation.innerHTML = `<i data-feather="navigation" style="width: 16px;"></i> Usar mi ubicación actual`;
                            feather.replace();
                        }
                    );
                }
            });

            // Reverse Geocoding Helper
            async function reverseGeocode(lat, lon) {
                try {
                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`);
                    const data = await res.json();
                    if (data && data.display_name) {
                        locationSearchInput.value = data.display_name;
                    }
                } catch (err) {
                    console.warn("Reverse geocode error:", err);
                }
            }

            // Autocomplete Search Debounce
            let searchTimeout = null;
            locationSearchInput.addEventListener("input", function() {
                const query = locationSearchInput.value.trim();
                clearTimeout(searchTimeout);

                if (query.length < 3) {
                    autocompleteResults.classList.add("d-none");
                    autocompleteResults.innerHTML = "";
                    return;
                }

                searchTimeout = setTimeout(async () => {
                    try {
                        const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&addressdetails=1`);
                        const results = await res.json();

                        autocompleteResults.innerHTML = "";
                        if (Array.isArray(results) && results.length > 0) {
                            results.forEach(item => {
                                const div = document.createElement("div");
                                div.className = "autocomplete-item";
                                div.innerHTML = `<i data-feather="map-pin" style="width: 16px; flex-shrink: 0;" class="text-success"></i> <span>${item.display_name}</span>`;
                                div.onclick = function() {
                                    const lat = parseFloat(item.lat);
                                    const lon = parseFloat(item.lon);
                                    map.setView([lat, lon], 16);
                                    marker.setLatLng([lat, lon]);
                                    updateCoordinates(lat, lon);
                                    locationSearchInput.value = item.display_name;
                                    autocompleteResults.classList.add("d-none");
                                };
                                autocompleteResults.appendChild(div);
                            });
                            autocompleteResults.classList.remove("d-none");
                            feather.replace();
                        } else {
                            autocompleteResults.classList.add("d-none");
                        }
                    } catch (e) {
                        console.warn("Autocomplete error:", e);
                    }
                }, 300);
            });

            document.addEventListener("click", function(e) {
                if (!locationSearchInput.contains(e.target) && !autocompleteResults.contains(e.target)) {
                    autocompleteResults.classList.add("d-none");
                }
            });


            // ==================== STEP 1: Check Available Channels ====================
            btnCheckChannels.addEventListener("click", async function() {
                const ip = ipInput.value.trim();
                const port = portInput.value.trim() || 80;
                const user = userInput.value.trim();
                const password = passwordInput.value.trim();

                if (!ip) {
                    showError("Por favor ingrese la IP o Dominio del DVR.");
                    return;
                }

                // Reset state
                hideError();
                channelsContainer.classList.add("d-none");
                
                const originalBtnContent = btnCheckChannels.innerHTML;
                btnCheckChannels.disabled = true;
                btnCheckChannels.innerHTML = `
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    <span>Conectando a DVR_CONNECTOR...</span>
                `;

                try {
                    const response = await fetch("/api/cameras/check-channels", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                        },
                        body: JSON.stringify({
                            ip: ip,
                            port: port,
                            user: user,
                            password: password,
                            debug: ip.toLowerCase() === 'debug'
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success && Array.isArray(data.channels) && data.channels.length > 0) {
                        // Populate channels dropdown
                        channelSelect.innerHTML = "";
                        data.channels.forEach(ch => {
                            const option = document.createElement("option");
                            const chId = ch.id || ch.inputPort || ch.channel || 1;
                            const chName = ch.name || ch.channelName || `Canal ${chId}`;
                            option.value = chId;
                            option.textContent = `Canal ${chId} - ${chName}`;
                            channelSelect.appendChild(option);
                        });

                        // Show channels dropdown container
                        channelsContainer.classList.remove("d-none");

                        // Store current valid DVR config
                        currentDVRConfig = {
                            ip: ip,
                            port: port,
                            user: user,
                            password: password,
                            lat: parseFloat(latInput.value) || defaultLat,
                            lon: parseFloat(lonInput.value) || defaultLon
                        };

                    } else {
                        const errMsg = data.message || "El DVR no responde o no tiene canales disponibles en este momento.";
                        showError(errMsg);
                    }

                } catch (err) {
                    console.error("Error connecting to DVR:", err);
                    showError("Ocurrió un error al contactar con el contenedor DVR_CONNECTOR. Asegúrese de que el servicio está en ejecución.");
                } finally {
                    btnCheckChannels.disabled = false;
                    btnCheckChannels.innerHTML = originalBtnContent;
                    feather.replace();
                }
            });

            // STEP 2: Transition to Step 2
            btnGoToStep2.addEventListener("click", function() {
                if (!currentDVRConfig) return;

                const selectedChannel = channelSelect.value;
                currentDVRConfig.channel = selectedChannel;
                currentDVRConfig.lat = parseFloat(latInput.value) || defaultLat;
                currentDVRConfig.lon = parseFloat(lonInput.value) || defaultLon;

                // Update UI state to Step 2
                viewStep1.classList.add("d-none");
                viewStep2.classList.remove("d-none");

                indicatorStep1.classList.remove("active");
                indicatorStep1.classList.add("completed");
                indicatorStep2.classList.add("active");
                currentStepBadge.textContent = "Paso 2 de 2";

                lblConnectedIp.textContent = currentDVRConfig.ip;
                lblConnectedChannel.textContent = selectedChannel;

                // Hide loading spinner after image initialization
                canvasLoading.classList.remove("d-none");
                
                setTimeout(() => {
                    canvasLoading.classList.add("d-none");
                    window.initZoneMarkerApp(currentDVRConfig);
                }, 400);
            });

            // Back to Step 1
            btnBackToStep1.addEventListener("click", function() {
                viewStep2.classList.add("d-none");
                viewStep1.classList.remove("d-none");

                indicatorStep2.classList.remove("active");
                indicatorStep1.classList.remove("completed");
                indicatorStep1.classList.add("active");
                currentStepBadge.textContent = "Paso 1 de 2";
                setTimeout(() => map.invalidateSize(), 200);
            });

            // Add new rectangle button
            btnNewRect.addEventListener("click", function() {
                if (window.addNewZoneSpace) {
                    window.addNewZoneSpace();
                }
            });

            // Save camera configuration to database
            btnSaveCamera.addEventListener("click", async function() {
                if (!currentDVRConfig) return;

                const zones = window.getZonesConfiguration ? window.getZonesConfiguration() : [];
                
                btnSaveCamera.disabled = true;
                const originalContent = btnSaveCamera.innerHTML;
                btnSaveCamera.innerHTML = `
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    <span>Guardando...</span>
                `;

                try {
                    const response = await fetch("/api/cameras/save", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                        },
                        body: JSON.stringify({
                            ip: currentDVRConfig.ip,
                            port: currentDVRConfig.port,
                            user: currentDVRConfig.user,
                            password: currentDVRConfig.password,
                            channel: currentDVRConfig.channel,
                            lat: currentDVRConfig.lat,
                            lon: currentDVRConfig.lon,
                            zones: zones
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        const modal = new bootstrap.Modal(document.getElementById('saveSuccessModal'));
                        document.getElementById('saveSuccessText').textContent = 
                            `La cámara en IP ${currentDVRConfig.ip} (Canal ${currentDVRConfig.channel}) con ubicación (${currentDVRConfig.lat.toFixed(4)}, ${currentDVRConfig.lon.toFixed(4)}) y ${data.saved_zones} zona(s) fue guardada exitosamente.`;
                        modal.show();
                    } else {
                        alert("Error al guardar: " + (data.message || "No se pudo completar el registro."));
                    }
                } catch (err) {
                    console.error("Save error:", err);
                    alert("Error de conexión al intentar guardar la configuración.");
                } finally {
                    btnSaveCamera.disabled = false;
                    btnSaveCamera.innerHTML = originalContent;
                    feather.replace();
                }
            });

            // Helper functions
            function showError(msg) {
                errorAlertText.textContent = msg;
                errorAlert.classList.remove("d-none");
            }

            function hideError() {
                errorAlert.classList.add("d-none");
            }
        });
    </script>
</body>
</html>