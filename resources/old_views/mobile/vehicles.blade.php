@extends('layouts.mobile')

@section('title', 'Mis Vehículos')

@section('content')
<div class="mobile-page">
    <div class="mobile-page-header">
        <a href="{{ route('app.menu') }}" class="mobile-back-button"><i data-feather="arrow-left"></i></a>
        <h2>Mis Vehículos</h2>
    </div>
    <div class="container animate-fade-in p-0">
        <div id="vehicles-container">
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
        </div>
        <button class="btn-mobile btn-mobile-outline mt-3" id="btn-add-vehicle">
            <i data-feather="plus" class="me-2"></i> Agregar Vehículo
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    function loadVehicles() {
        window.mobileApi.request('/cars', 'GET')
            .then(function(data) {
                const container = $('#vehicles-container');
                container.empty();
                
                if (data.data && data.data.length > 0) {
                    data.data.forEach(function(car) {
                        const html = `
                            <div class="mobile-card mb-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-1 fs-5 fw-bold">${car.brand || 'Vehículo'} ${car.model || ''}</h3>
                                    <span class="badge text-success bg-success bg-opacity-10">${car.plate}</span>
                                </div>
                                <i data-feather="trash-2" class="text-danger" style="cursor:pointer" onclick="deleteCar(${car.id})"></i>
                            </div>
                        `;
                        container.append(html);
                    });
                    feather.replace();
                } else {
                    container.html('<p class="text-muted text-center">No tienes vehículos registrados.</p>');
                }
            })
            .catch(function(error) {
                $('#vehicles-container').html('<p class="text-danger text-center">Error al cargar vehículos.</p>');
                console.error(error);
            });
    }

    // Llamar API al cargar
    loadVehicles();
});

// Demo function for deletion
window.deleteCar = function(id) {
    if(confirm('¿Eliminar vehículo?')) {
        window.mobileApi.request('/cars/' + id, 'DELETE')
            .then(function() {
                alert('Vehículo eliminado');
                location.reload();
            })
            .catch(function(err) {
                alert(err.message);
            });
    }
}
</script>
@endpush
