@extends('layouts.mobile')

@section('title', 'Panel de Personas - Monitoreo de Evento')
@section('nav_personas_active', 'active')
@section('show_bottom_nav', true)

@push('styles')
<style>
    .people-panel-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 24px 16px 80px;
    }
    .metric-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        border: 1px solid #f0f2f5;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.07);
    }
    .metric-card.primary {
        background: linear-gradient(135deg, #4F46E5, #7C3AED);
        color: #ffffff;
    }
    .metric-card.primary .metric-label,
    .metric-card.primary .metric-sub {
        color: rgba(255, 255, 255, 0.85);
    }
    .metric-value {
        font-size: 2.4rem;
        font-weight: 700;
        line-height: 1.1;
        margin: 8px 0;
    }
    .metric-label {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        color: #6b7280;
    }
    .metric-sub {
        font-size: 0.8rem;
        color: #9ca3af;
    }
    .chart-container-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        border: 1px solid #f0f2f5;
        margin-bottom: 24px;
    }
    .table-container-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        border: 1px solid #f0f2f5;
    }
    .live-pulse {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #10B981;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulse 1.8s infinite;
        margin-right: 6px;
    }
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        }
        70% {
            box-shadow: 0 0 0 8px rgba(16, 185, 129, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
        }
    }
    .badge-count {
        font-size: 0.9rem;
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 20px;
    }
</style>
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="people-panel-container">
    
    <!-- Top Header & Actions -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="live-pulse"></span>
                <span class="text-success fw-semibold small text-uppercase" style="letter-spacing: 0.5px;">En vivo</span>
            </div>
            <h1 class="h3 fw-bold mb-1" style="color: #111827;">Panel de Personas</h1>
            <p class="text-muted small mb-0">Monitoreo y conteo de personas detectadas en el evento en tiempo real.</p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="input-group input-group-sm" style="width: auto;">
                <span class="input-group-text bg-white text-muted">
                    <i data-feather="clock" style="width: 14px; height: 14px;"></i>
                </span>
                <select id="auto-refresh-select" class="form-select form-select-sm">
                    <option value="0">Auto-recarga: Apagada</option>
                    <option value="5" selected>Auto-recarga: Cada 5s</option>
                    <option value="10">Auto-recarga: Cada 10s</option>
                    <option value="30">Auto-recarga: Cada 30s</option>
                </select>
            </div>

            <button id="btn-refresh" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1">
                <i data-feather="refresh-cw" id="refresh-icon" style="width: 14px; height: 14px;"></i>
                <span>Actualizar</span>
            </button>
        </div>
    </div>

    <!-- Alert Banner info -->
    <div class="alert alert-info border-0 rounded-4 shadow-sm py-2 px-3 mb-4 d-flex align-items-center gap-2 small">
        <i data-feather="info" style="width: 16px; height: 16px; flex-shrink: 0;"></i>
        <span>
            Cada medición representa la cantidad de personas detectadas en una toma fotográfica en ese instante.
        </span>
    </div>

    <!-- Metrics Grid -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="metric-card primary">
                <div class="metric-label">Última Detección</div>
                <div class="metric-value" id="val-latest">--</div>
                <div class="metric-sub" id="sub-latest">Esperando datos...</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="metric-card">
                <div class="metric-label">Suma de Registros</div>
                <div class="metric-value text-primary" id="val-sum">--</div>
                <div class="metric-sub">Total acumulado de detecciones</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="metric-card">
                <div class="metric-label">Total Mediciones</div>
                <div class="metric-value" style="color: #059669;" id="val-total-counts">--</div>
                <div class="metric-sub">Tiempos de muestreo capturados</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="metric-card">
                <div class="metric-label">Promedio de Personas</div>
                <div class="metric-value" style="color: #D97706;" id="val-avg">--</div>
                <div class="metric-sub">Por captura fotográfica</div>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="chart-container-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold mb-0" style="color: #1f2937;">Variación Temporal de Asistencia</h5>
                <span class="text-muted small">Cantidad de personas detectadas a lo largo de las mediciones</span>
            </div>
            <span class="badge bg-light text-dark fw-normal" id="chart-points-count">0 puntos</span>
        </div>
        <div style="position: relative; height: 320px; width: 100%;">
            <canvas id="peopleChart"></canvas>
        </div>
    </div>

    <!-- History Table Section -->
    <div class="table-container-card">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-0" style="color: #1f2937;">Historial de Mediciones</h5>
                <span class="text-muted small">Detalle cronológico de fecha, hora y cantidad detectada</span>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                <!-- Manual Simulation Trigger (Helper for real-time testing) -->
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#simPanel">
                    <i data-feather="plus-circle" style="width: 14px; height: 14px;"></i> Probar Envío
                </button>
            </div>
        </div>

        <!-- Simulation Box (Collapsible) -->
        <div class="collapse mb-3" id="simPanel">
            <div class="p-3 bg-light rounded-3 border">
                <h6 class="fw-bold mb-2">Simular Registro Manual a la API (POST /api/personas)</h6>
                <form id="form-simulate" class="row g-2 align-items-center">
                    <div class="col-auto">
                        <label for="sim-cantidad" class="visually-hidden">Cantidad</label>
                        <input type="number" min="0" class="form-control form-control-sm" id="sim-cantidad" placeholder="Cantidad personas (ej: 18)" required>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary">Registrar en BD</button>
                    </div>
                    <div class="col-auto">
                        <span id="sim-result" class="small text-muted"></span>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="table-history">
                <thead class="table-light">
                    <tr>
                        <th scope="col" style="width: 80px;"># ID</th>
                        <th scope="col">Fecha y Hora</th>
                        <th scope="col" class="text-center">Personas Detectadas</th>
                        <th scope="col" class="text-end">Estado</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                            Cargando datos desde la API...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    feather.replace();

    let peopleChart = null;
    let refreshIntervalId = null;

    // Elements
    const valLatest = document.getElementById('val-latest');
    const subLatest = document.getElementById('sub-latest');
    const valSum = document.getElementById('val-sum');
    const valTotalCounts = document.getElementById('val-total-counts');
    const valAvg = document.getElementById('val-avg');
    const tableBody = document.getElementById('table-body');
    const refreshBtn = document.getElementById('btn-refresh');
    const refreshIcon = document.getElementById('refresh-icon');
    const autoRefreshSelect = document.getElementById('auto-refresh-select');
    const chartPointsCount = document.getElementById('chart-points-count');
    const formSimulate = document.getElementById('form-simulate');
    const simCantidad = document.getElementById('sim-cantidad');
    const simResult = document.getElementById('sim-result');

    // Initialize Chart
    const ctx = document.getElementById('peopleChart').getContext('2d');
    peopleChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Personas Detectadas',
                data: [],
                borderColor: '#4F46E5',
                backgroundColor: 'rgba(79, 70, 229, 0.12)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#4F46E5',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1E293B',
                    titleFont: { size: 13, weight: '600' },
                    bodyFont: { size: 13 },
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function (context) {
                            return ` ${context.parsed.y} personas detectadas`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { size: 11 },
                        color: '#64748B'
                    }
                },
                y: {
                    beginAtZero: true,
                    suggestedMax: 10,
                    grid: { color: '#F1F5F9' },
                    ticks: {
                        stepSize: 1,
                        font: { size: 11 },
                        color: '#64748B'
                    }
                }
            }
        }
    });

    // Fetch data from API
    async function loadData() {
        if (refreshIcon) refreshIcon.classList.add('spin-animation');

        try {
            const res = await fetch('/api/v1/people?limit=200');
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const json = await res.json();

            renderDashboard(json);
        } catch (err) {
            console.error('Error fetching people data:', err);
        } finally {
            if (refreshIcon) {
                setTimeout(() => refreshIcon.classList.remove('spin-animation'), 500);
            }
        }
    }

    function renderDashboard(data) {
        const registros = data.registros || [];
        const suma = data.suma || 0;
        const total = data.total_mediciones || registros.length;
        const ultimo = data.ultimo_registro;

        // Metrics
        valSum.textContent = suma.toLocaleString();
        valTotalCounts.textContent = total.toLocaleString();

        if (ultimo) {
            valLatest.textContent = ultimo.cantidad;
            subLatest.textContent = `${ultimo.fecha || ''} ${ultimo.hora || ''}`;
        } else {
            valLatest.textContent = '0';
            subLatest.textContent = 'Sin mediciones';
        }

        if (registros.length > 0) {
            const avg = (suma / registros.length).toFixed(1);
            valAvg.textContent = avg;
        } else {
            valAvg.textContent = '0';
        }

        // Update Chart
        const labels = registros.map(r => r.hora || r.fecha_hora || `#${r.id}`);
        const values = registros.map(r => r.cantidad);

        peopleChart.data.labels = labels;
        peopleChart.data.datasets[0].data = values;
        peopleChart.update();

        if (chartPointsCount) {
            chartPointsCount.textContent = `${registros.length} mediciones`;
        }

        // Update Table
        if (registros.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center py-4 text-muted">
                        No hay registros de personas aún.
                    </td>
                </tr>
            `;
            return;
        }

        // Show newest first in table
        const reversed = [...registros].reverse();
        tableBody.innerHTML = reversed.map(r => {
            let badgeClass = 'bg-success';
            let badgeText = 'Normal';
            if (r.cantidad > 30) {
                badgeClass = 'bg-danger';
                badgeText = 'Afluencia Alta';
            } else if (r.cantidad > 15) {
                badgeClass = 'bg-warning text-dark';
                badgeText = 'Afluencia Media';
            }

            return `
                <tr>
                    <td class="fw-semibold text-secondary">#${r.id}</td>
                    <td>
                        <div class="fw-medium text-dark">${r.fecha_hora || '-'}</div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-2 fs-6 rounded-pill">
                            ${r.cantidad} personas
                        </span>
                    </td>
                    <td class="text-end">
                        <span class="badge ${badgeClass} rounded-pill">${badgeText}</span>
                    </td>
                </tr>
            `;
        }).join('');
    }

    // Refresh handlers
    refreshBtn.addEventListener('click', loadData);

    function setupAutoRefresh() {
        if (refreshIntervalId) clearInterval(refreshIntervalId);
        const seconds = parseInt(autoRefreshSelect.value, 10);
        if (seconds > 0) {
            refreshIntervalId = setInterval(loadData, seconds * 1000);
        }
    }

    autoRefreshSelect.addEventListener('change', setupAutoRefresh);

    // Simulation Form
    if (formSimulate) {
        formSimulate.addEventListener('submit', async (e) => {
            e.preventDefault();
            const cantidad = parseInt(simCantidad.value, 10);
            if (isNaN(cantidad) || cantidad < 0) return;

            simResult.textContent = 'Enviando...';
            simResult.className = 'small text-muted';

            try {
                const res = await fetch('/api/v1/people', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ cantidad: cantidad })
                });

                if (res.ok) {
                    simResult.textContent = '✓ Guardado correctamente';
                    simResult.className = 'small text-success';
                    simCantidad.value = '';
                    loadData();
                } else {
                    simResult.textContent = '✗ Error al guardar';
                    simResult.className = 'small text-danger';
                }
            } catch (err) {
                simResult.textContent = '✗ Error de red';
                simResult.className = 'small text-danger';
            }
        });
    }

    // Initial run
    loadData();
    setupAutoRefresh();
});
</script>
<style>
    .spin-animation {
        animation: spin 0.8s linear infinite;
    }
    @keyframes spin {
        100% { transform: rotate(360deg); }
    }
</style>
@endpush

