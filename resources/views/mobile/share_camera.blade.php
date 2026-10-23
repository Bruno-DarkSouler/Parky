@extends('layouts.mobile')

@section('title', 'Cámara IoT - Configuración')
@section('show_bottom_nav', true)
@section('nav_menu_active', 'active')

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
    .camera-banner {
        background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
        color: white;
        padding: 32px 28px;
        border-radius: 24px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
    }

    .camera-banner::before {
        content: '';
        position: absolute;
        top: -40px;
        right: -40px;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(82, 183, 136, 0.2) 0%, rgba(0,0,0,0) 70%);
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
        font-size: 26px;
        font-weight: 800;
        margin: 0;
        letter-spacing: -0.5px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .banner-title-group p {
        font-size: 14px;
        color: #94A3B8;
        margin: 6px 0 0 0;
        max-width: 650px;
    }

    /* STEPPERS */
    .stepper-bar {
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
    }
    .step-pill {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 18px;
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        color: #64748B;
        font-weight: 600;
        font-size: 14px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    }
    .step-pill.active {
        background: #F0FDF4;
        border-color: #52B788;
        color: #166534;
    }
    .step-pill.completed {
        background: #EFF6FF;
        border-color: #3B82F6;
        color: #1E40AF;
    }
    .step-badge-num {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #E2E8F0;
        color: #475569;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 800;
        flex-shrink: 0;
    }
    .step-pill.active .step-badge-num {
        background: #52B788;
        color: #FFFFFF;
    }
    .step-pill.completed .step-badge-num {
        background: #3B82F6;
        color: #FFFFFF;
    }

    /* CARDS */
    .card-white {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 24px;
        padding: 28px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        margin-bottom: 24px;
    }

    .form-section-title {
        font-size: 16px;
        font-weight: 700;
        color: #0F172A;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .input-label-custom {
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
        display: block;
    }

    .input-field-custom {
        width: 100%;
        background: #FFFFFF;
        border: 1px solid #CBD5E1;
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 14px;
        color: #0F172A;
        transition: all 0.2s ease;
    }
    .input-field-custom:focus {
        border-color: #52B788;
        box-shadow: 0 0 0 3px rgba(82, 183, 136, 0.15);
        outline: none;
    }

    /* BUTTONS */
    .btn-green-primary {
        background: #52B788;
        color: white;
        border: none;
        border-radius: 14px;
        padding: 12px 24px;
        font-weight: 700;
        font-size: 15px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        box-shadow: 0 4px 14px rgba(82, 183, 136, 0.35);
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .btn-green-primary:hover, .btn-green-primary:active {
        background: #40916C;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(82, 183, 136, 0.45);
    }
    .btn-green-primary:disabled {
        opacity: 0.65;
        cursor: not-allowed;
        transform: none;
    }

    .btn-outline-custom {
        background: transparent;
        color: #475569;
        border: 1px solid #CBD5E1;
        border-radius: 14px;
        padding: 10px 18px;
        font-weight: 600;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .btn-outline-custom:hover {
        background: #F1F5F9;
        color: #0F172A;
        border-color: #94A3B8;
    }

    /* MAP & AUTOCOMPLETE */
    .search-wrapper {
        position: relative;
    }

    .search-input-box {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
    }

    .search-input-box .search-icon {
        position: absolute;
        left: 14px;
        width: 18px;
        height: 18px;
        color: #94A3B8;
        pointer-events: none;
        z-index: 2;
    }

    .search-input-box .search-input {
        padding-left: 42px !important;
    }

    .autocomplete-list {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1050;
        background: #FFFFFF;
        border: 1px solid #CBD5E1;
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        max-height: 220px;
        overflow-y: auto;
        margin-top: 4px;
    }

    .autocomplete-item-row {
        padding: 10px 14px;
        color: #1E293B;
        cursor: pointer;
        font-size: 13px;
        border-bottom: 1px solid #F1F5F9;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .autocomplete-item-row:last-child {
        border-bottom: none;
    }
    .autocomplete-item-row:hover {
        background: #F0FDF4;
        color: #166534;
    }

    #location_map {
        height: 260px;
        width: 100%;
        border-radius: 16px;
        border: 1px solid #E2E8F0;
        z-index: 1;
    }

    /* CANVAS & ZONES TABLE */
    #canvas_wrapper {
        position: relative;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        overflow: hidden;
        background-color: #0F172A;
        margin-bottom: 20px;
        width: 100%;
        min-height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #container {
        width: 100%;
        height: 100%;
    }

    .zones-table-wrapper {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        overflow: hidden;
        margin-top: 16px;
    }

    .zones-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
        color: #0F172A;
    }

    .zones-table th {
        background-color: #F8FAFC;
        color: #64748B;
        font-weight: 700;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 12px 16px;
        border-bottom: 1px solid #E2E8F0;
    }

    .zones-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #F1F5F9;
        vertical-align: middle;
        font-size: 14px;
    }

    .meter-input {
        background: #FFFFFF;
        border: 1px solid #CBD5E1;
        color: #0F172A;
        border-radius: 8px;
        padding: 6px 12px;
        width: 110px;
        font-weight: 700;
    }
    .meter-input:focus {
        border-color: #52B788;
        outline: none;
        box-shadow: 0 0 0 2px rgba(82, 183, 136, 0.2);
    }

    .btn-delete-zone {
        background: #FEE2E2;
        color: #EF4444;
        border: 1px solid #FCA5A5;
        border-radius: 8px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-delete-zone:hover {
        background: #EF4444;
        color: #FFFFFF;
    }

    .status-tag {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
    }
    .status-tag.libre {
        background: #D1FAE5;
        color: #065F46;
    }
    .status-tag.ocupado {
        background: #FEE2E2;
        color: #991B1B;
    }

    /* ALERTS */
    .alert-custom-error {
        background: #FEF2F2;
        border: 1px solid #FCA5A5;
        color: #991B1B;
        border-radius: 14px;
        padding: 14px 18px;
    }

    /* MOBILE RESPONSIVE ADJUSTMENTS */
    @media (max-width: 640px) {
        .camera-banner {
            padding: 20px;
            border-radius: 18px;
        }
        .banner-title-group h1 {
            font-size: 20px;
        }
        .card-white {
            padding: 18px;
            border-radius: 18px;
        }
        .stepper-bar {
            flex-direction: column;
            gap: 8px;
        }
    }
    /* CAMERAS GRID STYLES */
    .cameras-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 18px;
        margin-bottom: 28px;
    }
    @media (min-width: 768px) {
        .cameras-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .camera-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .camera-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.06);
        border-color: #CBD5E1;
    }

    .camera-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }

    .camera-info-group {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .camera-icon-badge {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        background: #F0FDF4;
        color: #166534;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .camera-card-title {
        font-size: 16px;
        font-weight: 800;
        color: #0F172A;
        margin: 0 0 2px 0;
        line-height: 1.2;
    }

    .camera-card-sub {
        font-size: 12px;
        color: #64748B;
        font-weight: 500;
    }

    .camera-details-list {
        background: #F8FAFC;
        border-radius: 12px;
        padding: 12px;
        margin: 12px 0;
        display: flex;
        flex-direction: column;
        gap: 6px;
        font-size: 13px;
        color: #334155;
    }

    .camera-detail-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .camera-card-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        padding-top: 12px;
        border-top: 1px solid #F1F5F9;
    }
</style>
@endpush

@section('content')
<div class="mobile-page">

    <!-- HEADER BANNER -->
    <div class="camera-banner">
        <div class="banner-top-bar">
            <a href="{{ route('map') }}" class="btn-back-map" title="Volver al mapa">
                <i data-feather="arrow-left"></i>
            </a>
            <span class="badge bg-white text-dark px-3 py-2 rounded-pill font-monospace fw-bold" style="font-size: 12px;" id="current_step_badge">Paso 1 de 2</span>
        </div>
        <div class="banner-title-group">
            <h1>
                <i data-feather="camera" style="width: 28px; height: 28px; color: #52B788;"></i>
                Cámara IoT
            </h1>
            <p>Conectá tu cámara de seguridad al sistema, ubicá su posición exacta en el mapa y definí las zonas de estacionamiento en tiempo real.</p>
        </div>
    </div>

    <!-- ==================== SECTION: REGISTERED CAMERAS LIST ==================== -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="fw-bold fs-5 text-dark m-0 d-flex align-items-center gap-2">
                    <i data-feather="video" class="text-success" style="width: 20px;"></i>
                    Mis Cámaras Registradas
                </h3>
            </div>
            <span class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-sm" id="cameras_count_badge">Cargando...</span>
        </div>

        <div id="registered_cameras_grid" class="cameras-grid">
            <div class="text-center py-4 bg-white rounded-4 border w-100" style="grid-column: 1 / -1;">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Cargando cámaras...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- STEP PROGRESS BAR -->
    <div class="stepper-bar">
        <div class="step-pill active" id="indicator_step1">
            <div class="step-badge-num">1</div>
            <div>Conexión DVR y Ubicación</div>
        </div>
        <div class="step-pill" id="indicator_step2">
            <div class="step-badge-num">2</div>
            <div>Definir Zonas y Metros</div>
        </div>
    </div>

    <!-- ==================== STEP 1: CONEXIÓN DVR Y UBICACIÓN ==================== -->
    <div id="view_step1" class="card-white animate-fade-in">
        
        <form id="dvr_connection_form" onsubmit="event.preventDefault();">
            
            <!-- Credentials Section -->
            <div class="form-section-title">
                <i data-feather="server" style="width: 18px; height: 18px;" class="text-success"></i>
                <span>Datos de Conexión DVR</span>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="ip_input" class="input-label-custom">IP / Dominio del DVR <span class="text-danger">*</span></label>
                    <input type="text" id="ip_input" class="input-field-custom" placeholder="Ej: 192.168.1.100 o debug" value="debug" required>
                    <div class="form-text text-muted" style="font-size: 11px;">Tip: Ingresá "debug" para probar con el simulador virtual.</div>
                </div>

                <div class="col-md-6">
                    <label for="port_input" class="input-label-custom">Puerto HTTP / ISAPI</label>
                    <input type="number" id="port_input" class="input-field-custom" placeholder="80" value="80">
                </div>

                <div class="col-md-6">
                    <label for="user_input" class="input-label-custom">Usuario</label>
                    <input type="text" id="user_input" class="input-field-custom" placeholder="admin" value="admin">
                </div>

                <div class="col-md-6">
                    <label for="password_input" class="input-label-custom">Contraseña</label>
                    <input type="password" id="password_input" class="input-field-custom" placeholder="••••••••" value="admin123">
                </div>
            </div>

            <hr class="my-4" style="border-color: #E2E8F0;">

            <!-- Location Picker Section -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div class="form-section-title m-0">
                    <i data-feather="map-pin" style="width: 18px; height: 18px;" class="text-success"></i>
                    <span>Ubicación Exacta de la Cámara</span>
                </div>
                <button type="button" id="btn_use_current_location" class="btn-outline-custom">
                    <i data-feather="navigation" style="width: 15px; height: 15px;"></i> Mi ubicación actual
                </button>
            </div>

            <!-- Address Autocomplete Input -->
            <div class="mb-3 search-wrapper">
                <label for="location_search_input" class="input-label-custom">Buscador de Dirección (con Autocompletado):</label>
                <div class="search-input-box">
                    <i data-feather="search" class="search-icon"></i>
                    <input type="text" id="location_search_input" class="input-field-custom search-input" placeholder="Buscar dirección (ej: Cerrito 3966, Rosario)..." autocomplete="off">
                </div>
                <div id="location_autocomplete_results" class="autocomplete-list d-none"></div>
            </div>

            <!-- Leaflet Map -->
            <div class="mb-3">
                <div id="location_map"></div>
            </div>

            <!-- Coordinates Inputs -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="lat_input" class="input-label-custom">Latitud</label>
                    <input type="number" step="any" id="lat_input" class="input-field-custom bg-light" value="-34.603722" readonly>
                </div>
                <div class="col-md-6">
                    <label for="lon_input" class="input-label-custom">Longitud</label>
                    <input type="number" step="any" id="lon_input" class="input-field-custom bg-light" value="-58.381592" readonly>
                </div>
            </div>

            <!-- Check Action Button -->
            <div class="d-flex justify-content-start">
                <button type="button" id="btn_check_channels" class="btn-green-primary">
                    <i data-feather="refresh-cw" style="width: 18px; height: 18px;"></i>
                    <span>Chequear canales disponibles</span>
                </button>
            </div>

        </form>

        <!-- Connection Error Banner -->
        <div id="error_alert" class="alert-custom-error mt-4 d-none">
            <div class="d-flex align-items-start gap-3">
                <i data-feather="alert-circle" style="width: 22px; height: 22px; flex-shrink: 0;" class="text-danger"></i>
                <div>
                    <strong class="d-block mb-1">Error de Conexión al DVR</strong>
                    <span id="error_alert_text">No se pudo establecer comunicación con el DVR.</span>
                </div>
            </div>
        </div>

        <!-- Channels Select Container (visible on success) -->
        <div id="channels_container" class="mt-4 p-4 rounded-4 d-none" style="background: #F0FDF4; border: 1px solid #52B788;">
            <div class="d-flex align-items-center gap-2 text-success fw-bold mb-3" style="font-size: 15px;">
                <i data-feather="check-circle" style="width: 20px; height: 20px;"></i>
                <span>Conexión establecida exitosamente con el DVR</span>
            </div>

            <div class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label for="channel_select" class="input-label-custom">Seleccioná el Canal de la Cámara:</label>
                    <select id="channel_select" class="input-field-custom" style="font-size: 15px; font-weight: 600;">
                        <!-- Populated dynamically -->
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="button" id="btn_go_to_step2" class="btn-green-primary w-100 py-2">
                        <span>Definir Zonas (Paso 2)</span>
                        <i data-feather="arrow-right" style="width: 18px; height: 18px;"></i>
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- ==================== STEP 2: DEFINIR ZONA Y METROS ==================== -->
    <div id="view_step2" class="card-white animate-fade-in d-none">
        
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1 fs-5 text-dark">Paso 2: Selección de Zona y Metros del Área</h2>
                <p class="text-muted mb-0 small">Ajustá los recuadros sobre la imagen de la cámara e indicá el largo en metros para cada área.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span id="active_dvr_badge" class="badge bg-light text-dark border px-3 py-2" style="font-size: 12px;">
                    DVR: <strong id="lbl_connected_ip">192.168.1.100</strong> | Canal: <strong id="lbl_connected_channel" class="text-success">1</strong>
                </span>
                <button type="button" id="btn_back_to_step1" class="btn-outline-custom py-1 px-3">
                    <i data-feather="arrow-left" style="width: 14px; height: 14px;"></i> Volver
                </button>
            </div>
        </div>

        <!-- Canvas Container -->
        <div id="canvas_wrapper">
            <div id="canvas_loading" class="text-center p-5 text-light">
                <div class="spinner-border text-success mb-3" role="status"></div>
                <div class="small">Obteniendo captura en tiempo real de la cámara...</div>
            </div>
            <div id="container"></div>
        </div>

        <!-- Zone Table Controls -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold m-0 fs-6 text-dark"><i data-feather="grid" class="me-2 text-success" style="width: 18px;"></i>Zonas Configuradas</h3>
            <button type="button" id="btn_new_rect" class="btn-green-primary py-2 px-3 style-sm" style="font-size: 13px;">
                <i data-feather="plus" style="width: 16px; height: 16px;"></i>
                <span>Agregar zona / espacio</span>
            </button>
        </div>

        <div class="zones-table-wrapper">
            <table id="data_section" class="zones-table">
                <!-- Populated dynamically by marcador.js -->
            </table>
        </div>

        <!-- Save Button -->
        <div class="mt-4 pt-3 border-top d-flex justify-content-end">
            <button type="button" id="btn_save_camera" class="btn-green-primary px-4 py-3">
                <i data-feather="save" style="width: 20px; height: 20px;"></i>
                <span>Guardar Configuración de Cámara</span>
            </button>
        </div>

    </div>

</div>

<!-- SUCCESS MODAL -->
<div class="modal fade" id="saveSuccessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body text-center p-4">
                <div class="p-3 rounded-circle d-inline-block mb-3" style="background: #D1FAE5; color: #059669;">
                    <i data-feather="check-circle" style="width: 48px; height: 48px;"></i>
                </div>
                <h4 class="fw-bold mb-2 text-dark">¡Configuración Guardada!</h4>
                <p class="text-muted mb-4 small" id="saveSuccessText">La cámara y sus zonas asociadas han sido almacenadas exitosamente.</p>
                <button type="button" class="btn-green-primary w-100" data-bs-dismiss="modal" onclick="window.location.reload();">Entendido</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('js/konva.js') }}"></script>
<script src="{{ asset('js/marcador.js') }}"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

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

    // LEAFLET MAP & AUTOCOMPLETE
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
            btnUseCurrentLocation.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Buscando GPS...`;

            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    const lat = pos.coords.latitude;
                    const lon = pos.coords.longitude;
                    map.setView([lat, lon], 16);
                    marker.setLatLng([lat, lon]);
                    updateCoordinates(lat, lon);
                    reverseGeocode(lat, lon);
                    btnUseCurrentLocation.disabled = false;
                    btnUseCurrentLocation.innerHTML = `<i data-feather="navigation" style="width: 15px; height: 15px;"></i> Mi ubicación actual`;
                    if (typeof feather !== 'undefined') feather.replace();
                },
                function(err) {
                    alert("No se pudo obtener su ubicación actual.");
                    btnUseCurrentLocation.disabled = false;
                    btnUseCurrentLocation.innerHTML = `<i data-feather="navigation" style="width: 15px; height: 15px;"></i> Mi ubicación actual`;
                    if (typeof feather !== 'undefined') feather.replace();
                }
            );
        }
    });

    // Reverse Geocoding
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

    // Autocomplete Search
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
                        div.className = "autocomplete-item-row";
                        div.innerHTML = `<i data-feather="map-pin" style="width: 14px; height: 14px;" class="text-success"></i> <span>${item.display_name}</span>`;
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
                    if (typeof feather !== 'undefined') feather.replace();
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

    // STEP 1: Check Available Channels
    btnCheckChannels.addEventListener("click", async function() {
        const ip = ipInput.value.trim();
        const port = portInput.value.trim() || 80;
        const user = userInput.value.trim();
        const password = passwordInput.value.trim();

        if (!ip) {
            showError("Por favor ingresá la IP o Dominio del DVR.");
            return;
        }

        hideError();
        channelsContainer.classList.add("d-none");
        
        const originalBtnContent = btnCheckChannels.innerHTML;
        btnCheckChannels.disabled = true;
        btnCheckChannels.innerHTML = `
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            <span>Verificando conexión...</span>
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
                channelSelect.innerHTML = "";
                data.channels.forEach(ch => {
                    const option = document.createElement("option");
                    const chId = ch.id || ch.inputPort || ch.channel || 1;
                    const chName = ch.name || ch.channelName || `Canal ${chId}`;
                    option.value = chId;
                    option.textContent = `Canal ${chId} - ${chName}`;
                    channelSelect.appendChild(option);
                });

                channelsContainer.classList.remove("d-none");

                currentDVRConfig = {
                    ip: ip,
                    port: port,
                    user: user,
                    password: password,
                    lat: parseFloat(latInput.value) || defaultLat,
                    lon: parseFloat(lonInput.value) || defaultLon
                };

            } else {
                const errMsg = data.message || "El DVR no responde o no se encontraron canales activos.";
                showError(errMsg);
            }

        } catch (err) {
            console.error("Error connecting to DVR:", err);
            showError("No se pudo conectar con el microservicio DVR. Verificá que el contenedor esté corriendo.");
        } finally {
            btnCheckChannels.disabled = false;
            btnCheckChannels.innerHTML = originalBtnContent;
            if (typeof feather !== 'undefined') feather.replace();
        }
    });

    // STEP 2: Transition to Step 2
    btnGoToStep2.addEventListener("click", function() {
        if (!currentDVRConfig) return;

        const selectedChannel = channelSelect.value;
        currentDVRConfig.channel = selectedChannel;
        currentDVRConfig.lat = parseFloat(latInput.value) || defaultLat;
        currentDVRConfig.lon = parseFloat(lonInput.value) || defaultLon;

        viewStep1.classList.add("d-none");
        viewStep2.classList.remove("d-none");

        indicatorStep1.classList.remove("active");
        indicatorStep1.classList.add("completed");
        indicatorStep2.classList.add("active");
        currentStepBadge.textContent = "Paso 2 de 2";

        lblConnectedIp.textContent = currentDVRConfig.ip;
        lblConnectedChannel.textContent = selectedChannel;

        canvasLoading.classList.remove("d-none");
        
        setTimeout(() => {
            canvasLoading.classList.add("d-none");
            window.initZoneMarkerApp(currentDVRConfig);
            if (typeof feather !== 'undefined') feather.replace();
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

    // Add zone space
    btnNewRect.addEventListener("click", function() {
        if (window.addNewZoneSpace) {
            window.addNewZoneSpace();
            if (typeof feather !== 'undefined') feather.replace();
        }
    });

    // Save camera
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
                loadRegisteredCameras();
            } else {
                alert("Error al guardar: " + (data.message || "No se pudo completar el registro."));
            }
        } catch (err) {
            console.error("Save error:", err);
            alert("Error de conexión al intentar guardar la configuración.");
        } finally {
            btnSaveCamera.disabled = false;
            btnSaveCamera.innerHTML = originalContent;
            if (typeof feather !== 'undefined') feather.replace();
        }
    });

    function showError(msg) {
        errorAlertText.textContent = msg;
        errorAlert.classList.remove("d-none");
        if (typeof feather !== 'undefined') feather.replace();
    }

    function hideError() {
        errorAlert.classList.add("d-none");
    }

    // ==================== REGISTERED CAMERAS API & LOGIC ====================
    let registeredCamerasCache = [];
    loadRegisteredCameras();

    async function loadRegisteredCameras() {
        const grid = document.getElementById('registered_cameras_grid');
        const badge = document.getElementById('cameras_count_badge');
        
        try {
            const res = await fetch('/api/cameras/list');
            const data = await res.json();

            if (res.ok && data.success && Array.isArray(data.cameras)) {
                const cameras = data.cameras;
                registeredCamerasCache = cameras;
                badge.textContent = `${cameras.length} ${cameras.length === 1 ? 'cámara' : 'cámaras'}`;

                if (cameras.length === 0) {
                    grid.innerHTML = `
                        <div class="text-center py-5 bg-white rounded-4 border w-100 p-4" style="grid-column: 1 / -1;">
                            <div class="d-inline-flex p-3 rounded-circle bg-light text-muted mb-3">
                                <i data-feather="video-off" style="width: 32px; height: 32px;"></i>
                            </div>
                            <h4 class="fw-bold fs-6 text-dark mb-1">¡No tenés cámaras registradas!</h4>
                            <p class="text-muted small mb-0">Usá el formulario de configuración abajo para conectar tu primer DVR e identificar lugares disponibles.</p>
                        </div>
                    `;
                    if (typeof feather !== 'undefined') feather.replace();
                    return;
                }

                grid.innerHTML = '';
                cameras.forEach(cam => {
                    const dvr = cam.dvr || {};
                    const zones = cam.camera_zones || [];
                    const totalMeters = zones.reduce((acc, z) => acc + (parseFloat(z.total_length_meters) || 0), 0);

                    const card = document.createElement('div');
                    card.className = 'camera-card';
                    card.innerHTML = `
                        <div>
                            <div class="camera-card-header">
                                <div class="camera-info-group">
                                    <div class="camera-icon-badge">
                                        <i data-feather="video" style="width: 22px; height: 22px;"></i>
                                    </div>
                                    <div>
                                        <h4 class="camera-card-title">${escapeHtml(cam.name || 'Cámara Canal ' + cam.channel)}</h4>
                                        <div class="camera-card-sub">Canal ${cam.channel} • DVR ${escapeHtml(dvr.ip || 'Local')}</div>
                                    </div>
                                </div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold" style="font-size: 11px;">Activa</span>
                            </div>

                            <div class="camera-details-list">
                                <div class="camera-detail-item">
                                    <i data-feather="server" style="width: 14px; height: 14px;" class="text-muted"></i>
                                    <span>IP / Puerto: <strong>${escapeHtml(dvr.ip || 'S/D')}:${dvr.port || 80}</strong></span>
                                </div>
                                <div class="camera-detail-item">
                                    <i data-feather="map-pin" style="width: 14px; height: 14px;" class="text-muted"></i>
                                    <span>Ubicación: <strong>${dvr.lat ? parseFloat(dvr.lat).toFixed(4) : '-'}, ${dvr.lon ? parseFloat(dvr.lon).toFixed(4) : '-'}</strong></span>
                                </div>
                                <div class="camera-detail-item">
                                    <i data-feather="grid" style="width: 14px; height: 14px;" class="text-muted"></i>
                                    <span>Zonas monitoreadas: <strong>${zones.length} ${zones.length === 1 ? 'zona' : 'zonas'} (${totalMeters.toFixed(2)}m)</strong></span>
                                </div>
                            </div>
                        </div>

                        <div class="camera-card-actions">
                            <button type="button" class="btn-outline-custom py-1 px-3" style="font-size: 12px;" onclick="editCameraById('${cam.id}')">
                                <i data-feather="edit-2" style="width: 13px; height: 13px;"></i>
                                Reconfigurar
                            </button>
                            <button type="button" class="btn-delete-zone py-1 px-3" onclick="confirmDeleteCamera('${cam.id}', '${escapeHtml(cam.name || 'Cámara Canal ' + cam.channel)}')">
                                <i data-feather="trash-2" style="width: 13px; height: 13px;"></i>
                                Eliminar
                            </button>
                        </div>
                    `;
                    grid.appendChild(card);
                });

                if (typeof feather !== 'undefined') feather.replace();

            } else {
                grid.innerHTML = `<div class="alert alert-danger text-center p-3 w-100" style="grid-column: 1 / -1;">No se pudieron cargar las cámaras.</div>`;
            }
        } catch (e) {
            console.error("Error loading registered cameras:", e);
            grid.innerHTML = `<div class="alert alert-danger text-center p-3 w-100" style="grid-column: 1 / -1;">Error al consultar la lista de cámaras.</div>`;
        }
    }

    // CONFIRM AND DELETE CAMERA
    window.confirmDeleteCamera = async function(id, name) {
        if (!confirm(`¿Estás seguro de que querés eliminar la cámara "${name}" y sus zonas de estacionamiento?`)) {
            return;
        }

        try {
            const res = await fetch(`/api/cameras/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                }
            });
            const data = await res.json();

            if (res.ok && data.success) {
                loadRegisteredCameras();
            } else {
                alert("Error al eliminar: " + (data.message || 'No se pudo completar el borrado.'));
            }
        } catch (e) {
            console.error("Error deleting camera:", e);
            alert("Error de conexión al eliminar la cámara.");
        }
    };

    // EDIT CAMERA IN WIZARD
    window.editCameraById = function(id) {
        const cam = registeredCamerasCache.find(c => c.id === id);
        if (!cam) return;

        const dvr = cam.dvr || {};
        ipInput.value = dvr.ip || 'debug';
        portInput.value = dvr.port || 80;
        userInput.value = dvr.user || 'admin';
        passwordInput.value = '';

        if (dvr.lat && dvr.lon) {
            const lat = parseFloat(dvr.lat);
            const lon = parseFloat(dvr.lon);
            map.setView([lat, lon], 16);
            marker.setLatLng([lat, lon]);
            updateCoordinates(lat, lon);
        }

        // Scroll to wizard
        document.getElementById('view_step1').scrollIntoView({ behavior: 'smooth' });

        // Auto trigger channel check
        btnCheckChannels.click();
    };

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});
</script>
@endpush
