@extends('layouts.mobile')

@section('title', 'Menú Principal')
@section('show_bottom_nav', true)
@section('nav_menu_active', 'active')

@section('content')
<div class="mobile-page" style="padding: 0; background-color: var(--color-surface);">
    <!-- Header gradient area -->
    <div class="mobile-menu-header" style="background: linear-gradient(135deg, #7C7CE0, #52B788);">
        <h2 class="mobile-menu-title">Menú</h2>
        <div class="mobile-profile-avatar-container">
            <div style="width: 74px; height: 74px; border-radius: 50%; background: #7C7CE0; display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 28px;">
                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
            </div>
        </div>
        <h2 class="mobile-profile-name" style="margin-top: 8px;">{{ Auth::user()->name ?? 'Usuario' }}</h2>
        <span class="mobile-profile-email">{{ Auth::user()->email ?? 'usuario@email.com' }}</span>
    </div>

    <div class="container p-4">
        <div class="mobile-menu-card animate-fade-in">
            <a href="{{ route('app.profile') }}" class="mobile-menu-item">
                <div class="flex-center">
                    <i data-feather="user" class="mobile-menu-icon text-secondary"></i>
                    <span class="fw-medium">Mi perfil</span>
                </div>
                <i data-feather="chevron-right" class="text-muted"></i>
            </a>
            <a href="{{ route('app.vehicles') }}" class="mobile-menu-item">
                <div class="flex-center">
                    <i data-feather="truck" class="mobile-menu-icon text-secondary"></i>
                    <span class="fw-medium">Mis vehículos</span>
                </div>
                <i data-feather="chevron-right" class="text-muted"></i>
            </a>
            <a href="{{ route('app.history') }}" class="mobile-menu-item">
                <div class="flex-center">
                    <i data-feather="clock" class="mobile-menu-icon text-secondary"></i>
                    <span class="fw-medium">Historial</span>
                </div>
                <i data-feather="chevron-right" class="text-muted"></i>
            </a>
            <a href="{{ route('app.settings') }}" class="mobile-menu-item">
                <div class="flex-center">
                    <i data-feather="settings" class="mobile-menu-icon text-secondary"></i>
                    <span class="fw-medium">Configuración</span>
                </div>
                <i data-feather="chevron-right" class="text-muted"></i>
            </a>
            <a href="{{ route('app.share_camera') }}" class="mobile-menu-item">
                <div class="flex-center">
                    <i data-feather="camera" class="mobile-menu-icon text-primary"></i>
                    <span class="fw-medium">Compartir mi cámara</span>
                </div>
                <i data-feather="chevron-right" class="text-muted"></i>
            </a>
            <a href="/logout" class="mobile-menu-item" id="btn-logout-mobile">
                <div class="flex-center text-danger fw-medium">
                    <i data-feather="log-out" class="mobile-menu-icon"></i>
                    <span>Cerrar sesión</span>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
