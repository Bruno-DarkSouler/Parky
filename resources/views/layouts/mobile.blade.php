<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Parky') }} - @yield('title', 'App')</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="{{ asset('css/mobile-app.css') }}?v={{ time() }}" rel="stylesheet">
    
    <!-- Feather Icons CDN -->
    <script src="https://unpkg.com/feather-icons"></script>

    <style>
        body {
            font-family: 'Inter', 'Poppins', sans-serif;
            background-color: #F8FAFC;
        }
    </style>
    
    @stack('styles')
</head>
<body class="mobile-app-body">
    <div class="mobile-app-root">
        
        <!-- DESKTOP TOP NAVBAR -->
        <header class="desktop-navbar">
            <a href="{{ route('map') }}" class="desktop-logo">
                <span class="park">Par</span><span class="finder">ky</span>
            </a>
            
            <nav class="desktop-nav-links">
                <a href="{{ route('map') }}" class="desktop-nav-link @yield('nav_map_active')">
                    <i data-feather="map"></i>
                    <span>Mapa</span>
                </a>
                <a href="{{ route('app.vehicles') }}" class="desktop-nav-link @yield('nav_vehicles_active')">
                    <i data-feather="truck"></i>
                    <span>Mis Vehículos</span>
                </a>
                <a href="{{ route('app.history') }}" class="desktop-nav-link @yield('nav_history_active')">
                    <i data-feather="clock"></i>
                    <span>Historial</span>
                </a>
                <a href="{{ route('app.settings') }}" class="desktop-nav-link @yield('nav_settings_active')">
                    <i data-feather="settings"></i>
                    <span>Configuración</span>
                </a>
            </nav>

            <div class="d-flex align-items-center gap-3">
                <div class="desktop-user-pill">
                    <div class="desktop-avatar">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <span class="desktop-username">{{ Auth::user()->name ?? 'Usuario' }}</span>
                </div>
                <a href="/logout" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold">
                    Cerrar sesión
                </a>
            </div>
        </header>
        
        @yield('content')

        <!-- BOTTOM NAVIGATION (MOBILE ONLY) -->
        @if(View::hasSection('show_bottom_nav'))
        <nav class="mobile-bottom-nav">
            <a href="{{ route('map') }}" class="nav-item">
                <i data-feather="map"></i>
                <span>Mapa</span>
            </a>
            <a href="{{ route('app.vehicles') }}" class="nav-item @yield('nav_vehicles_active')">
                <i data-feather="truck"></i>
                <span>Vehículos</span>
            </a>
            <a href="#" id="nav-menu" class="nav-item @yield('nav_menu_active')">
                <i data-feather="menu"></i>
                <span>Menú</span>
            </a>
        </nav>
        @endif

    </div>

    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- App Scripts -->
    <script src="{{ asset('js/mobile-api.js') }}?v={{ time() }}"></script>
    
    @stack('scripts')
    
    @include('partials.sidebar')
</body>
</html>
