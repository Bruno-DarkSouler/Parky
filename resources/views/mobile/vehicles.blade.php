@extends('layouts.mobile')

@section('title', 'Mis Vehículos')
@section('show_bottom_nav', true)
@section('nav_vehicles_active', 'active')

@push('styles')
<style>
    .vehicles-banner {
        background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
        color: white;
        padding: 32px 28px;
        border-radius: 24px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
    }
    .vehicles-banner::before {
        content: '';
        position: absolute;
        top: -40px;
        right: -40px;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(82, 183, 136, 0.15) 0%, rgba(0,0,0,0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .banner-top-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .btn-back-map {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        text-decoration: none;
        transition: all 0.2s ease;
        backdrop-filter: blur(8px);
    }
    .btn-back-map:hover, .btn-back-map:active {
        background: rgba(255, 255, 255, 0.25);
        color: white;
    }

    .banner-title-group h1 {
        font-size: 28px;
        font-weight: 800;
        margin: 0;
        letter-spacing: -0.5px;
    }
    .banner-title-group p {
        font-size: 14px;
        color: #94A3B8;
        margin: 6px 0 0 0;
        max-width: 600px;
    }

    .btn-add-vehicle-top {
        background: #52B788;
        color: white;
        border: none;
        border-radius: 14px;
        padding: 12px 22px;
        font-weight: 700;
        font-size: 15px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        box-shadow: 0 4px 14px rgba(82, 183, 136, 0.4);
        transition: all 0.2s ease;
    }
    .btn-add-vehicle-top:hover, .btn-add-vehicle-top:active {
        background: #40916C;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(82, 183, 136, 0.5);
    }

    /* RESPONSIVE CARDS GRID */
    #vehicles-list {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
    }
    @media (min-width: 640px) {
        #vehicles-list {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (min-width: 1024px) {
        #vehicles-list {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    /* CARDS STYLING */
    .vehicle-card {
        background: #FFFFFF;
        border-radius: 20px;
        border: 1px solid #E2E8F0;
        padding: 22px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        position: relative;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
    }
    .vehicle-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
        border-color: #CBD5E1;
    }
    .vehicle-card.active-car {
        border: 2px solid #52B788;
        background: linear-gradient(180deg, #F0FDF4 0%, #FFFFFF 100%);
    }

    .card-top-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 14px;
    }

    .vehicle-info {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .vehicle-icon-badge {
        width: 50px;
        height: 50px;
        border-radius: 16px;
        background: #EEF2FF;
        color: #7C7CE0;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .vehicle-card.active-car .vehicle-icon-badge {
        background: #D1FAE5;
        color: #059669;
    }

    .vehicle-title {
        font-size: 19px;
        font-weight: 800;
        color: #0F172A;
        margin: 0;
        line-height: 1.2;
    }

    .vehicle-subtitle {
        font-size: 13px;
        color: #64748B;
        margin-top: 3px;
        font-weight: 500;
    }

    /* License Plate Badge */
    .plate-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #0F172A;
        color: #FFFFFF;
        font-family: 'Inter', monospace;
        font-weight: 800;
        font-size: 13px;
        letter-spacing: 1.5px;
        padding: 5px 12px;
        border-radius: 8px;
        border: 1.5px solid #334155;
        box-shadow: 0 2px 5px rgba(0,0,0,0.15);
        text-transform: uppercase;
    }

    /* Specs Pills */
    .specs-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin: 16px 0;
    }

    .spec-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #F1F5F9;
        color: #334155;
        font-size: 12px;
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 10px;
    }
    .spec-chip-highlight {
        background: #E0E7FF;
        color: #4338CA;
    }
    .spec-chip-active {
        background: #D1FAE5;
        color: #065F46;
    }

    .color-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        border: 1px solid rgba(0,0,0,0.2);
    }

    /* Card Actions Footer */
    .card-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 14px;
        border-top: 1px solid #F1F5F9;
        margin-top: 12px;
    }

    .btn-select-car {
        background: transparent;
        color: #64748B;
        border: 1px solid #CBD5E1;
        border-radius: 10px;
        padding: 7px 14px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .btn-select-car:hover {
        border-color: #52B788;
        color: #52B788;
        background: #F0FDF4;
    }
    .btn-select-car.is-selected {
        background: #52B788;
        color: white;
        border-color: #52B788;
    }

    .action-icons {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-icon-action {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: none;
        background: #F8FAFC;
        color: #64748B;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-icon-action:hover {
        background: #E2E8F0;
        color: #0F172A;
    }
    .btn-icon-action.delete:hover {
        background: #FEE2E2;
        color: #EF4444;
    }

    /* EMPTY STATE */
    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 24px;
        border: 2px dashed #E2E8F0;
    }
    .empty-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: #F1F5F9;
        color: #94A3B8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
    }

    /* PRESETS CHIPS IN MODAL */
    .preset-title {
        font-size: 12px;
        font-weight: 700;
        color: #64748B;
        margin-bottom: 8px;
        display: block;
    }
    .presets-container {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 18px;
    }
    .chip-preset {
        background: #F1F5F9;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 600;
        color: #334155;
        cursor: pointer;
        transition: all 0.15s;
    }
    .chip-preset:hover, .chip-preset:active {
        background: #7C7CE0;
        color: white;
        border-color: #7C7CE0;
    }

    /* MODAL STYLES */
    .modal-backdrop-custom {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(6px);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .modal-backdrop-custom.active {
        display: flex;
    }
    .modal-card {
        background: white;
        width: 100%;
        max-width: 520px;
        max-height: 90vh;
        overflow-y: auto;
        border-radius: 24px;
        padding: 28px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.25);
        animation: modalSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes modalSlideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .modal-header-custom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    .modal-title-custom {
        font-size: 22px;
        font-weight: 800;
        color: #0F172A;
        margin: 0;
    }
    .btn-close-modal {
        background: #F1F5F9;
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #64748B;
        transition: background 0.2s;
    }
    .btn-close-modal:hover {
        background: #E2E8F0;
        color: #0F172A;
    }

    /* TOAST NOTIFICATION */
    #toast-container {
        position: fixed;
        bottom: 40px;
        right: 40px;
        z-index: 10000;
        pointer-events: none;
    }
    .custom-toast {
        background: #0F172A;
        color: white;
        padding: 14px 24px;
        border-radius: 14px;
        font-size: 14px;
        font-weight: 600;
        box-shadow: 0 10px 25px rgba(0,0,0,0.25);
        display: flex;
        align-items: center;
        gap: 10px;
        animation: toastIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes toastIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* MOBILE LANDSCAPE COMPACT OVERRIDES */
    @media (max-height: 550px) {
        .vehicles-banner {
            padding: 14px 20px;
            margin-bottom: 14px;
            border-radius: 16px;
        }
        .banner-top-bar {
            margin-bottom: 6px;
        }
        .btn-back-map {
            width: 32px;
            height: 32px;
            border-radius: 8px;
        }
        .btn-add-vehicle-top {
            padding: 6px 14px;
            font-size: 13px;
            border-radius: 10px;
        }
        .banner-title-group h1 {
            font-size: 18px;
        }
        .banner-title-group p {
            font-size: 12px;
            margin-top: 2px;
        }
        .vehicle-card {
            padding: 14px;
            border-radius: 16px;
        }
        .vehicle-title {
            font-size: 16px;
        }
        .vehicle-icon-badge {
            width: 40px;
            height: 40px;
            border-radius: 12px;
        }
        .modal-card {
            max-height: 94vh;
            padding: 18px;
            border-radius: 18px;
        }
        #toast-container {
            bottom: 60px;
            right: 20px;
        }
    }
</style>
@endpush

@section('content')
<div class="mobile-page">
    
    <!-- RESPONSIVE HEADER BANNER -->
    <div class="vehicles-banner">
        <div class="banner-top-bar">
            <a href="{{ route('map') }}" class="btn-back-map" title="Volver al mapa">
                <i data-feather="arrow-left"></i>
            </a>
            <button class="btn-add-vehicle-top" onclick="openVehicleModal()">
                <i data-feather="plus" style="width: 20px; height: 20px;"></i>
                <span>Nuevo Auto</span>
            </button>
        </div>
        <div class="banner-title-group">
            <h1>Mis Vehículos</h1>
            <p>Registrá las dimensiones exactas de tus vehículos para encontrar lugares en la vía pública donde quepas con total precisión.</p>
        </div>
    </div>

    <!-- MAIN VEHICLES CONTAINER -->
    <div class="animate-fade-in">
        
        <!-- COUNTER AND STATUS -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <span class="fw-bold text-secondary small text-uppercase" style="letter-spacing: 0.5px;">Tus Autos Registrados</span>
            <span class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-sm" id="vehicle-count-badge">Cargando...</span>
        </div>

        <!-- LIST GRID CONTAINER -->
        <div id="vehicles-list">
            <div class="text-center py-5" style="grid-column: 1 / -1;">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Cargando vehículos...</span>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- ADD / EDIT VEHICLE MODAL -->
<div class="modal-backdrop-custom" id="vehicle-modal">
    <div class="modal-card">
        <div class="modal-header-custom">
            <h2 class="modal-title-custom" id="modal-title">Agregar Vehículo</h2>
            <button type="button" class="btn-close-modal" onclick="closeVehicleModal()">
                <i data-feather="x"></i>
            </button>
        </div>

        <form id="vehicle-form" onsubmit="handleFormSubmit(event)">
            <input type="hidden" id="car-id" value="">

            <div class="row g-2">
                <div class="col-6 mb-2">
                    <label class="mobile-input-label">Marca *</label>
                    <input type="text" id="car-brand" class="mobile-input-field" placeholder="Ej: Fiat" required>
                </div>
                <div class="col-6 mb-2">
                    <label class="mobile-input-label">Modelo *</label>
                    <input type="text" id="car-model" class="mobile-input-field" placeholder="Ej: 600" required>
                </div>
            </div>

            <div class="row g-2">
                <div class="col-6 mb-2">
                    <label class="mobile-input-label">Patente / Dominio *</label>
                    <input type="text" id="car-plate" class="mobile-input-field text-uppercase" placeholder="Ej: FIT-600" required style="font-family: monospace; letter-spacing: 1px;">
                </div>
                <div class="col-6 mb-2">
                    <label class="mobile-input-label">Año</label>
                    <input type="number" id="car-year" class="mobile-input-field" placeholder="Ej: 2021" min="1900" max="{{ date('Y') + 1 }}">
                </div>
            </div>

            <!-- PRESETS FOR FAST LENGTH SELECTION -->
            <div class="mt-2">
                <span class="preset-title">Medidas sugeridas por tipo de auto:</span>
                <div class="presets-container">
                    <span class="chip-preset" onclick="setLengthPreset(3.30)">🚗 Fitito / Chica (3.30m)</span>
                    <span class="chip-preset" onclick="setLengthPreset(3.90)">🚗 Compacto (3.90m)</span>
                    <span class="chip-preset" onclick="setLengthPreset(4.50)">🚘 Sedán (4.50m)</span>
                    <span class="chip-preset" onclick="setLengthPreset(4.85)">🚙 SUV (4.85m)</span>
                    <span class="chip-preset" onclick="setLengthPreset(5.35)">🛻 Pickup (5.35m)</span>
                </div>
            </div>

            <div class="row g-2">
                <div class="col-6 mb-2">
                    <label class="mobile-input-label">Largo en Metros *</label>
                    <input type="number" step="0.01" id="car-length" class="mobile-input-field" placeholder="Ej: 3.30" required min="1.00" max="10.00">
                    <small class="text-muted d-block mt-n2" style="font-size: 11px;">Clave para el cálculo de estacionamiento.</small>
                </div>
                <div class="col-6 mb-2">
                    <label class="mobile-input-label">Ancho en Metros</label>
                    <input type="number" step="0.01" id="car-width" class="mobile-input-field" placeholder="Ej: 1.80" value="1.80" min="0.50" max="3.50">
                </div>
            </div>

            <div class="mb-3">
                <label class="mobile-input-label">Color</label>
                <input type="text" id="car-color" class="mobile-input-field" placeholder="Ej: Rojo, Blanco, Negro">
            </div>

            <button type="submit" class="btn-mobile btn-mobile-primary mt-2" style="background:#52B788; border-radius:12px;" id="btn-submit-form">
                Guardar Vehículo
            </button>
        </form>
    </div>
</div>

<!-- TOAST CONTAINER -->
<div id="toast-container"></div>
@endsection

@push('scripts')
<script>
let carsCache = [];

document.addEventListener('DOMContentLoaded', () => {
    loadVehicles();
});

// Safe API call wrapper
function apiCall(endpoint, method = 'GET', data = null) {
    if (window.mobileApi && typeof window.mobileApi.request === 'function') {
        return window.mobileApi.request(endpoint, method, data);
    }
    const token = localStorage.getItem('jwt_token');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    };
    if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken;
    if (token) headers['Authorization'] = 'Bearer ' + token;

    const options = { method, headers, credentials: 'same-origin' };
    if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
        options.body = JSON.stringify(data);
    }

    return fetch('/api/v1' + endpoint, options).then(async res => {
        if (res.status === 401) {
            window.location.href = '/login';
            throw new Error('No autorizado');
        }
        const json = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(json.message || `Error ${res.status}`);
        return json;
    });
}

// Load vehicles from API
function loadVehicles() {
    apiCall('/cars', 'GET')
        .then(response => {
            const cars = Array.isArray(response) ? response : (response.data || []);
            carsCache = cars;
            renderVehiclesList(cars);
        })
        .catch(err => {
            console.error('[Vehicles API Error]', err);
            document.getElementById('vehicles-list').innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon" style="background:#FEE2E2; color:#EF4444;">
                        <i data-feather="alert-triangle"></i>
                    </div>
                    <h3 class="fw-bold fs-5 text-dark">Error al cargar vehículos</h3>
                    <p class="text-muted small">No pudimos conectar con el servidor.</p>
                    <button class="btn-mobile btn-mobile-primary mt-2 w-auto px-4" onclick="loadVehicles()" style="background:#52B788; border-radius:10px;">Reintentar</button>
                </div>
            `;
            if (typeof feather !== 'undefined') feather.replace();
        });
}

// Render list of vehicle cards
function renderVehiclesList(cars) {
    const container = document.getElementById('vehicles-list');
    const badge = document.getElementById('vehicle-count-badge');
    container.innerHTML = '';

    badge.textContent = `${cars.length} ${cars.length === 1 ? 'auto' : 'autos'}`;

    if (cars.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">
                    <i data-feather="truck" style="width:36px; height:36px;"></i>
                </div>
                <h3 class="fw-bold fs-5 text-dark mb-1">¡No tenés vehículos agregados!</h3>
                <p class="text-muted small mb-4">Registrá tu auto con su largo exacto para que el sistema evalúe si entra en los huecos disponibles en la vía pública.</p>
                <button class="btn-mobile btn-mobile-primary w-auto px-4 mx-auto" onclick="openVehicleModal()" style="background:#52B788; border-radius:12px;">
                    + Registrar mi primer auto
                </button>
            </div>
        `;
        if (typeof feather !== 'undefined') feather.replace();
        return;
    }

    const selectedCarId = localStorage.getItem('selected_car_id');

    cars.forEach(car => {
        const isSelected = (selectedCarId && selectedCarId == car.id) || (cars.length === 1 && !selectedCarId);
        if (isSelected && !selectedCarId) {
            localStorage.setItem('selected_car_id', car.id);
        }

        const brand = car.brand ? car.brand.trim() : 'Vehículo';
        const model = car.model ? car.model.trim() : '';
        const plate = car.plate ? car.plate.toUpperCase() : 'S/D';
        const year = car.year ? car.year : '';
        const lengthM = car.length_meters ? parseFloat(car.length_meters).toFixed(2) : '4.50';
        const widthM = car.width_meters ? parseFloat(car.width_meters).toFixed(2) : '1.80';
        const color = car.color ? car.color.trim() : null;

        const card = document.createElement('div');
        card.className = `vehicle-card ${isSelected ? 'active-car' : ''}`;
        card.id = `car-card-${car.id}`;

        card.innerHTML = `
            <div>
                <div class="card-top-row">
                    <div class="vehicle-info">
                        <div class="vehicle-icon-badge">
                            <i data-feather="truck" style="width:24px; height:24px;"></i>
                        </div>
                        <div>
                            <h2 class="vehicle-title">${escapeHtml(brand)} ${escapeHtml(model)}</h2>
                            <div class="vehicle-subtitle">${year ? 'Modelo ' + year : 'Registrado en la app'}</div>
                        </div>
                    </div>
                    <div class="plate-badge">${escapeHtml(plate)}</div>
                </div>

                <div class="specs-grid">
                    <span class="spec-chip spec-chip-highlight" title="Largo exacto del vehículo">
                        <i data-feather="maximize-2" style="width:12px; height:12px;"></i>
                        Largo: <strong>${lengthM} m</strong>
                    </span>
                    <span class="spec-chip" title="Ancho del vehículo">
                        <i data-feather="move" style="width:12px; height:12px;"></i>
                        Ancho: <strong>${widthM} m</strong>
                    </span>
                    ${color ? `
                    <span class="spec-chip">
                        <span class="color-dot" style="background-color: ${getColorHex(color)};"></span>
                        ${escapeHtml(color)}
                    </span>` : ''}
                    ${isSelected ? `
                    <span class="spec-chip spec-chip-active">
                        <i data-feather="check-circle" style="width:12px; height:12px;"></i> Principal
                    </span>` : ''}
                </div>
            </div>

            <div class="card-actions">
                <button class="btn-select-car ${isSelected ? 'is-selected' : ''}" onclick="selectCarAsPrimary('${car.id}')">
                    <i data-feather="${isSelected ? 'check' : 'star'}" style="width:14px; height:14px;"></i>
                    ${isSelected ? 'Auto en uso' : 'Usar para estacionar'}
                </button>
                <div class="action-icons">
                    <button class="btn-icon-action" onclick="editCar('${car.id}')" title="Editar vehículo">
                        <i data-feather="edit-2" style="width:16px; height:16px;"></i>
                    </button>
                    <button class="btn-icon-action delete" onclick="confirmDeleteCar('${car.id}', '${escapeHtml(brand + ' ' + model)}')" title="Eliminar vehículo">
                        <i data-feather="trash-2" style="width:16px; height:16px;"></i>
                    </button>
                </div>
            </div>
        `;

        container.appendChild(card);
    });

    if (typeof feather !== 'undefined') feather.replace();
}

// Select car as primary for map parking availability search
function selectCarAsPrimary(carId) {
    localStorage.setItem('selected_car_id', carId);
    showToast('✨ Auto seleccionado como principal para buscar estacionamiento');
    renderVehiclesList(carsCache);
}

// Preset button handler
function setLengthPreset(meters) {
    document.getElementById('car-length').value = meters.toFixed(2);
}

// Open Modal (Add Mode)
function openVehicleModal() {
    document.getElementById('car-id').value = '';
    document.getElementById('modal-title').textContent = 'Agregar Vehículo';
    document.getElementById('vehicle-form').reset();
    document.getElementById('car-width').value = '1.80';
    document.getElementById('vehicle-modal').classList.add('active');
}

// Close Modal
function closeVehicleModal() {
    document.getElementById('vehicle-modal').classList.remove('active');
}

// Edit Car Modal fill
function editCar(carId) {
    const car = carsCache.find(c => c.id == carId);
    if (!car) return;

    document.getElementById('car-id').value = car.id;
    document.getElementById('modal-title').textContent = 'Editar Vehículo';
    document.getElementById('car-brand').value = car.brand || '';
    document.getElementById('car-model').value = car.model || '';
    document.getElementById('car-plate').value = car.plate || '';
    document.getElementById('car-year').value = car.year || '';
    document.getElementById('car-length').value = car.length_meters || '4.50';
    document.getElementById('car-width').value = car.width_meters || '1.80';
    document.getElementById('car-color').value = car.color || '';

    document.getElementById('vehicle-modal').classList.add('active');
}

// Submit Form Handler (Add or Edit)
function handleFormSubmit(event) {
    event.preventDefault();
    
    const carId = document.getElementById('car-id').value;
    const isEdit = !!carId;

    const payload = {
        brand: document.getElementById('car-brand').value.trim(),
        model: document.getElementById('car-model').value.trim(),
        plate: document.getElementById('car-plate').value.trim().toUpperCase(),
        year: document.getElementById('car-year').value ? parseInt(document.getElementById('car-year').value) : null,
        length_meters: parseFloat(document.getElementById('car-length').value),
        width_meters: parseFloat(document.getElementById('car-width').value || '1.80'),
        color: document.getElementById('car-color').value.trim() || null
    };

    const endpoint = isEdit ? `/cars/${carId}` : '/cars';
    const method = isEdit ? 'PUT' : 'POST';

    const btnSubmit = document.getElementById('btn-submit-form');
    btnSubmit.disabled = true;
    btnSubmit.textContent = isEdit ? 'Guardando...' : 'Creando...';

    apiCall(endpoint, method, payload)
        .then(savedCar => {
            closeVehicleModal();
            showToast(isEdit ? '✅ Vehículo actualizado' : '✅ Vehículo guardado con éxito');
            
            if (!isEdit && savedCar && savedCar.id) {
                localStorage.setItem('selected_car_id', savedCar.id);
            }

            loadVehicles();
        })
        .catch(err => {
            alert('Error: ' + err.message);
        })
        .finally(() => {
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Guardar Vehículo';
        });
}

// Delete Car handler
function confirmDeleteCar(carId, carName) {
    if (confirm(`¿Estás seguro de que deseas eliminar "${carName}"?`)) {
        apiCall(`/cars/${carId}`, 'DELETE')
            .then(() => {
                showToast('🗑️ Vehículo eliminado');
                if (localStorage.getItem('selected_car_id') == carId) {
                    localStorage.removeItem('selected_car_id');
                }
                loadVehicles();
            })
            .catch(err => {
                alert('Error al eliminar: ' + err.message);
            });
    }
}

// Toast helper
function showToast(message) {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = 'custom-toast';
    toast.innerHTML = `<span>${message}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 2800);
}

// Helper color hex estimator
function getColorHex(colorName) {
    if (!colorName) return '#94A3B8';
    const str = colorName.toLowerCase();
    if (str.includes('rojo') || str.includes('red')) return '#EF4444';
    if (str.includes('azul') || str.includes('blue')) return '#3B82F6';
    if (str.includes('verde') || str.includes('green')) return '#10B981';
    if (str.includes('blanco') || str.includes('white')) return '#F8FAFC';
    if (str.includes('negro') || str.includes('black')) return '#1E293B';
    if (str.includes('gris') || str.includes('gray') || str.includes('silver')) return '#94A3B8';
    if (str.includes('amarillo') || str.includes('yellow')) return '#F59E0B';
    return '#64748B';
}

// Helper escape HTML
function escapeHtml(text) {
    if (!text) return '';
    return text.toString().replace(/[&<>"']/g, function(m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
}
</script>
@endpush
