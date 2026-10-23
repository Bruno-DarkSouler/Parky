@extends('layouts.mobile')

@section('title', 'Configuración')

@section('content')
<div class="mobile-page">
    <div class="mobile-page-header">
        <a href="{{ route('map') }}" class="mobile-back-button"><i data-feather="arrow-left"></i></a>
        <h2>Configuración</h2>
    </div>
    <div class="container animate-fade-in p-0">
        <div class="mobile-menu-card">
            <div class="mobile-menu-item" style="cursor: default;">
                <span class="fw-medium">Notificaciones Push</span>
                <div class="form-check form-switch m-0">
                    <input class="form-check-input" type="checkbox" checked>
                </div>
            </div>
            <div class="mobile-menu-item" style="cursor: default;">
                <span class="fw-medium">Modo Oscuro</span>
                <div class="form-check form-switch m-0">
                    <input class="form-check-input" type="checkbox" id="theme-toggle">
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
