/**
 * ParkingFinder Map JS
 * Handles: map, vehicles, parking spots, navigation, voice guidance, offline cache
 */

// ============================================================
//  JWT API Helper
// ============================================================
const ParkingAPI = {
    _token: null,

    getToken() {
        if (!this._token) this._token = localStorage.getItem('jwt_token');
        return this._token;
    },

    setToken(token) {
        this._token = token;
        if (token) localStorage.setItem('jwt_token', token);
        else localStorage.removeItem('jwt_token');
    },

    async request(endpoint, method = 'GET', body = null) {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };
        const token = this.getToken();
        if (token) headers['Authorization'] = 'Bearer ' + token;

        const opts = { method, headers, credentials: 'same-origin' };
        if (body) opts.body = JSON.stringify(body);

        const res = await fetch('/api/v1' + endpoint, opts);

        if (res.status === 401) {
            this.setToken(null);
            window.location.href = '/login';
            return null;
        }

        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || 'Error ' + res.status);
        }

        return res.json();
    },

    async getCars() {
        return this.request('/cars');
    },

    async getParkingAvailable(lat, lng) {
        let url = '/parking/available';
        if (lat && lng) url += `?lat=${lat}&lng=${lng}`;
        return this.request(url);
    }
};

// ============================================================
//  Voice Synthesis (offline capable — uses OS voices)
// ============================================================
const ParkingVoice = {
    _synth: window.speechSynthesis,
    _voice: null,
    _queue: [],
    _speaking: false,

    init() {
        const loadVoices = () => {
            const voices = this._synth.getVoices();
            // Prefer a Spanish voice
            this._voice = voices.find(v => v.lang.startsWith('es')) || voices[0] || null;
        };
        loadVoices();
        if (speechSynthesis.onvoiceschanged !== undefined) {
            speechSynthesis.onvoiceschanged = loadVoices;
        }
    },

    speak(text) {
        if (!text) return;
        this._synth.cancel(); // Cancel any ongoing speech
        const utter = new SpeechSynthesisUtterance(text);
        utter.lang = 'es-AR';
        utter.rate = 1.0;
        utter.pitch = 1.0;
        if (this._voice) utter.voice = this._voice;
        this._synth.speak(utter);
    },

    stop() {
        this._synth.cancel();
    }
};

// ============================================================
//  Map Module
// ============================================================
const ParkingMap = {
    map: null,
    userMarker: null,
    userCircle: null,
    userLat: null,
    userLng: null,
    mapViewSet: false,
    parkingSpots: [],
    parkingMarkers: [],
    routeControl: null,
    isNavigating: false,
    autoFollow: true,
    hasAutoDownloaded: false,

    init() {
        this.map = L.map('map', {
            zoomControl: false,
            attributionControl: false
        });

        // Tile layer with offline cache support via service worker
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            subdomains: ['a', 'b', 'c']
        }).addTo(this.map);

        setTimeout(() => this.map.invalidateSize(), 200);
        this._bindEvents();
    },

    _bindEvents() {
        this.map.on('dragstart', () => {
            if (this.isNavigating && this.autoFollow) {
                this.autoFollow = false;
                document.getElementById('btn-recenter')?.style && (document.getElementById('btn-recenter').style.display = 'flex');
            }
        });
    },

    setUserLocation(lat, lng, accuracy) {
        this.userLat = lat;
        this.userLng = lng;
        const latlng = L.latLng(lat, lng);

        const carIcon = L.divIcon({
            className: 'user-car-marker',
            html: `<div class="car-marker-inner">
                     <div style="width:18px;height:18px;background:#4285F4;border:3px solid #fff;border-radius:50%;box-shadow:0 2px 5px rgba(0,0,0,0.3);"></div>
                   </div>`,
            iconSize: [44, 44],
            iconAnchor: [22, 22]
        });

        if (this.userMarker) {
            this.userMarker.setLatLng(latlng);
            this.userCircle.setLatLng(latlng).setRadius(accuracy);
        } else {
            this.userMarker = L.marker(latlng, { icon: carIcon }).addTo(this.map);
            this.userCircle = L.circle(latlng, {
                radius: accuracy,
                color: '#4285F4',
                fillColor: '#4285F4',
                fillOpacity: 0.12,
                weight: 1
            }).addTo(this.map);
        }

        if (!this.mapViewSet) {
            this.mapViewSet = true;
            this.map.setView(latlng, 16);
            this.autoDownloadTiles(lat, lng);
            // Load parking spots now that we have user location
            this.loadParkingSpots(lat, lng);
        }

        if (this.isNavigating && this.autoFollow) {
            this.map.setView(latlng, 18);
        }

        // Update route start if navigating
        if (this.routeControl) {
            const wps = this.routeControl.getWaypoints();
            if (wps.length >= 2) {
                wps[0].latLng = latlng;
                this.routeControl.setWaypoints(wps);
            }
        }
    },

    createParkingIcon(status) {
        const colors = { available: '#52B788', full: '#F94144', limited: '#F9C74F' };
        const color = colors[status] || colors.available;
        return L.divIcon({
            className: 'parking-marker',
            html: `<svg viewBox="0 0 32 42" width="32" height="42" style="filter: drop-shadow(0px 4px 6px rgba(0,0,0,0.25));">
                     <path d="M16 0 C7.163 0 0 7.163 0 16 C0 28 16 42 16 42 C16 42 32 28 32 16 C32 7.163 24.837 0 16 0 Z" fill="${color}"/>
                     <circle cx="16" cy="15" r="9" fill="#FFFFFF"/>
                     <text x="16" y="20" font-family="'Poppins', sans-serif" font-size="14" font-weight="bold" fill="${color}" text-anchor="middle">P</text>
                   </svg>`,
            iconSize: [32, 42],
            iconAnchor: [16, 42],
            popupAnchor: [0, -42]
        });
    },

    async loadParkingSpots(lat, lng) {
        try {
            const data = await ParkingAPI.getParkingAvailable(lat, lng);
            // Support both {data: [...]} and [...] responses
            const spots = Array.isArray(data) ? data : (data?.data || data?.spots || data?.results || []);
            this.parkingSpots = spots;
            this._renderParkingMarkers(spots);
        } catch (e) {
            console.warn('No se pudieron cargar los estacionamientos:', e.message);
            // Fallback to demo spots so the map isn't empty
            this._renderParkingMarkers([]);
        }
    },

    _renderParkingMarkers(spots) {
        // Remove old markers
        this.parkingMarkers.forEach(m => this.map.removeLayer(m));
        this.parkingMarkers = [];

        spots.forEach(spot => {
            const lat = spot.latitude ?? spot.lat ?? spot.coordinates?.lat;
            const lng = spot.longitude ?? spot.lng ?? spot.coordinates?.lon ?? spot.coordinates?.lng;
            if (!lat || !lng) return;

            const status = spot.status || (spot.available_slots > 0 || spot.available_slots_for_this_car > 0 ? 'available' : 'full');
            const marker = L.marker([lat, lng], { icon: this.createParkingIcon(status) }).addTo(this.map);
            marker.parkingData = { ...spot, lat, lng, status };
            this.parkingMarkers.push(marker);

            const slots = spot.available_slots ?? spot.available_slots_for_this_car ?? spot.spots ?? '?';
            const statusText = status === 'available' ? `Disponible — ${slots} lugares` : 'Completo';
            const statusClass = status === 'available' ? 'popup-available' : 'popup-full';

            const el = document.createElement('div');
            el.className = 'parking-popup';
            el.innerHTML = `<strong>${spot.name || spot.street_name || spot.street || 'Estacionamiento'}</strong>
                            <span class="${statusClass}">${statusText}</span>`;

            if (status === 'available') {
                const btn = document.createElement('button');
                btn.className = 'btn-navegar';
                btn.textContent = 'Navegar aquí';
                btn.onclick = () => this.startNavigation(marker.parkingData);
                el.appendChild(btn);
            }

            marker.bindPopup(el);
        });
    },

    startNavigation(spot) {
        if (!this.userLat || !this.userLng) {
            alert('Esperando ubicación...');
            return;
        }

        this.map.closePopup();
        if (this.routeControl) this.map.removeControl(this.routeControl);

        this.isNavigating = true;
        this.autoFollow = true;

        document.getElementById('search-btn-wrapper').style.display = 'none';
        document.getElementById('nav-actions-wrapper').style.display = 'flex';
        document.getElementById('btn-recenter').style.display = 'none';

        ParkingVoice.speak('Iniciando navegación. Te guiaremos paso a paso.');

        this.routeControl = L.Routing.control({
            waypoints: [
                L.latLng(this.userLat, this.userLng),
                L.latLng(spot.lat, spot.lng)
            ],
            router: L.Routing.osrmv1({ language: 'es', profile: 'driving' }),
            routeWhileDragging: false,
            show: false,
            createMarker: () => null,
            lineOptions: { styles: [{ color: '#4285F4', opacity: 0.85, weight: 6 }] }
        }).addTo(this.map);

        let firstRoute = true;
        let lastInstruction = '';
        this.routeControl.on('routesfound', (e) => {
            const route = e.routes[0];
            const summary = route.summary;

            if (firstRoute) {
                firstRoute = false;
                const dist = summary.totalDistance > 1000
                    ? (summary.totalDistance / 1000).toFixed(1) + ' kilómetros'
                    : Math.round(summary.totalDistance) + ' metros';
                const name = spot.name || spot.street_name || spot.street || 'tu destino';
                ParkingVoice.speak(`En ${dist}, llegarás a ${name}. ${route.instructions[0]?.text || ''}`);
                this.map.setView([this.userLat, this.userLng], 18);
            } else {
                const instr = route.instructions[0];
                if (instr && instr.text !== lastInstruction) {
                    lastInstruction = instr.text;
                    ParkingVoice.speak(instr.text);
                }
            }
        });
    },

    cancelNavigation() {
        if (this.routeControl) {
            this.map.removeControl(this.routeControl);
            this.routeControl = null;
        }
        this.isNavigating = false;
        this.autoFollow = false;
        document.getElementById('nav-actions-wrapper').style.display = 'none';
        document.getElementById('search-btn-wrapper').style.display = 'flex';
        ParkingVoice.speak('Navegación cancelada.');
        if (this.userLat) this.map.setView([this.userLat, this.userLng], 15);
    },

    findNearestAndNavigate() {
        if (!this.userLat || !this.userLng) return;
        if (!this.parkingSpots.length) {
            alert('No se encontraron estacionamientos.');
            return;
        }

        let nearest = null;
        let minDist = Infinity;

        this.parkingSpots.forEach(spot => {
            const lat = spot.latitude ?? spot.lat ?? spot.coordinates?.lat;
            const lng = spot.longitude ?? spot.lng ?? spot.coordinates?.lon ?? spot.coordinates?.lng;
            const status = spot.status || (spot.available_slots > 0 || spot.available_slots_for_this_car > 0 ? 'available' : 'full');
            if (status === 'available' && lat && lng) {
                const d = Math.hypot(lat - this.userLat, lng - this.userLng);
                if (d < minDist) { minDist = d; nearest = { ...spot, lat, lng, status }; }
            }
        });

        if (nearest) {
            this.startNavigation(nearest);
        } else {
            alert('No hay estacionamientos disponibles en este momento.');
        }
    },

    // ============================================================
    //  Auto-download map tiles for offline use
    // ============================================================
    autoDownloadTiles(lat, lng) {
        if (this.hasAutoDownloaded || !('caches' in window)) return;
        this.hasAutoDownloaded = true;

        const toast = document.getElementById('offline-toast');
        const toastText = document.getElementById('offline-toast-text');
        toast.classList.add('show');
        toastText.textContent = 'Preparando mapa sin conexión...';

        function lon2tile(lon, z) { return Math.floor((lon + 180) / 360 * Math.pow(2, z)); }
        function lat2tile(latD, z) { return Math.floor((1 - Math.log(Math.tan(latD * Math.PI / 180) + 1 / Math.cos(latD * Math.PI / 180)) / Math.PI) / 2 * Math.pow(2, z)); }

        const offset = 0.025; // ~2.5km
        const urls = [];
        for (let z = 13; z <= 17; z++) {
            const xMin = lon2tile(lng - offset, z), xMax = lon2tile(lng + offset, z);
            const yMin = lat2tile(lat + offset, z), yMax = lat2tile(lat - offset, z);
            for (let x = xMin; x <= xMax; x++) {
                for (let y = yMin; y <= yMax; y++) {
                    ['a', 'b', 'c'].forEach(s => urls.push(`https://${s}.tile.openstreetmap.org/${z}/${x}/${y}.png`));
                }
            }
        }
        if (urls.length > 600) urls.length = 600;

        caches.open('parking-tiles-v1').then(cache => {
            let done = 0;
            const total = urls.length;
            const batchSize = 8;
            let idx = 0;

            const downloadBatch = () => {
                const batch = urls.slice(idx, idx + batchSize);
                if (!batch.length) {
                    toastText.textContent = `Mapa listo (${total} tiles)`;
                    setTimeout(() => toast.classList.remove('show'), 2500);
                    return;
                }
                idx += batchSize;
                Promise.all(batch.map(url =>
                    cache.match(url).then(existing => {
                        if (existing) { done++; return; }
                        return fetch(url).then(r => { if (r.ok) cache.put(url, r.clone()); done++; }).catch(() => done++);
                    })
                )).then(() => {
                    toastText.textContent = `Descargando mapa: ${Math.round(done / total * 100)}%`;
                    requestAnimationFrame(downloadBatch);
                });
            };

            downloadBatch();
        }).catch(() => toast.classList.remove('show'));
    }
};

// ============================================================
//  Vehicle Selector
// ============================================================
const VehicleSelector = {
    async load() {
        const sel = document.getElementById('vehicle-selector');
        if (!sel) return;

        try {
            const data = await ParkingAPI.getCars();
            const cars = Array.isArray(data) ? data : (data?.data || []);

            if (!cars.length) {
                sel.innerHTML = '<option value="">Sin vehículos</option>';
                return;
            }

            sel.innerHTML = '<option value="">Mis autos</option>';
            cars.forEach(car => {
                const opt = document.createElement('option');
                opt.value = car.id;
                opt.textContent = `${car.brand} ${car.model} — ${car.plate}`;
                sel.appendChild(opt);
            });
        } catch (e) {
            console.warn('No se pudieron cargar vehículos:', e.message);
            sel.innerHTML = '<option value="">Sin vehículos</option>';
        }
    }
};

// ============================================================
//  Resource Widget
// ============================================================
const ResourceWidget = {
    collapsed: false,

    init() {
        this.update();
        setInterval(() => this.update(), 10000);
    },

    formatBytes(bytes) {
        if (!bytes) return '0 B';
        const k = 1024, s = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return (bytes / Math.pow(k, i)).toFixed(1) + ' ' + s[i];
    },

    async update() {
        const widget = document.getElementById('resource-widget');
        if (!widget) return;

        let tileCount = 0, totalEntries = 0;
        if ('caches' in window) {
            const names = await caches.keys();
            for (const name of names) {
                const c = await caches.open(name);
                const keys = await c.keys();
                totalEntries += keys.length;
                keys.forEach(r => { if (r.url.includes('openstreetmap')) tileCount++; });
            }
        }

        let storageUsed = 0;
        if (navigator.storage?.estimate) {
            const est = await navigator.storage.estimate();
            storageUsed = est.usage || 0;
        }

        const isOnline = navigator.onLine;
        const conn = navigator.connection || {};
        const netType = conn.effectiveType ? conn.effectiveType.toUpperCase() + (conn.downlink ? ` · ${conn.downlink}Mbps` : '') : (isOnline ? 'OK' : '—');

        if (this.collapsed) {
            widget.innerHTML = `<div class="rw-collapsed-content" id="rw-expand">
                <span class="rw-dot ${isOnline ? 'on' : 'off'}"></span>
                <span>${this.formatBytes(storageUsed)}</span>
                <span style="color:#4b5563">·</span>
                <span>${tileCount} tiles</span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
            </div>`;
            document.getElementById('rw-expand')?.addEventListener('click', () => { this.collapsed = false; widget.classList.remove('collapsed'); this.update(); });
            return;
        }

        widget.innerHTML = `
            <div class="rw-header">
                <span class="rw-title">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#4abe8a" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Recursos
                </span>
                <button class="rw-toggle" id="rw-collapse">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
                </button>
            </div>
            <div class="rw-body">
                <div class="rw-row"><span class="rw-label"><span class="rw-dot ${isOnline ? 'on' : 'off'}"></span>${isOnline ? 'En línea' : 'Offline'}</span><span class="rw-value">${netType}</span></div>
                <div class="rw-divider"></div>
                <div class="rw-row"><span class="rw-label">Tiles mapa</span><span class="rw-value">${tileCount.toLocaleString()}</span></div>
                <div class="rw-row"><span class="rw-label">Almacenamiento</span><span class="rw-value">${this.formatBytes(storageUsed)}</span></div>
                <div class="rw-row"><span class="rw-label">Caché total</span><span class="rw-value">${totalEntries.toLocaleString()}</span></div>
            </div>`;

        document.getElementById('rw-collapse')?.addEventListener('click', () => {
            this.collapsed = true;
            widget.classList.add('collapsed');
            this.update();
        });
    }
};

// ============================================================
//  Boot
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    ParkingVoice.init();
    ParkingMap.init();
    VehicleSelector.load();
    ResourceWidget.init();

    // Geolocation
    if (navigator.geolocation) {
        ParkingMap.map.setView([-34.53, -58.55], 14); // temp default
        navigator.geolocation.getCurrentPosition(
            pos => ParkingMap.setUserLocation(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy),
            () => ParkingMap.setUserLocation(-34.53, -58.55, 100),
            { enableHighAccuracy: true, timeout: 10000 }
        );
        navigator.geolocation.watchPosition(
            pos => ParkingMap.setUserLocation(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy),
            () => {},
            { enableHighAccuracy: true }
        );
    } else {
        ParkingMap.setUserLocation(-34.53, -58.55, 100);
    }

    // Buttons
    document.getElementById('btn-buscar-estacionamiento')?.addEventListener('click', () => {
        ParkingMap.findNearestAndNavigate();
    });

    document.getElementById('btn-cancel-nav')?.addEventListener('click', () => {
        ParkingMap.cancelNavigation();
    });

    document.getElementById('btn-recenter')?.addEventListener('click', function () {
        ParkingMap.autoFollow = true;
        if (ParkingMap.userLat) ParkingMap.map.setView([ParkingMap.userLat, ParkingMap.userLng], 18);
        this.style.display = 'none';
    });

    // Sidebar nav-mapa button
    document.getElementById('nav-mapa')?.addEventListener('click', () => {
        document.getElementById('sidebar')?.classList.remove('open');
        document.getElementById('sidebar-overlay')?.classList.remove('visible');
        document.body.style.overflow = '';
    });

    // Service Worker
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    }
});
