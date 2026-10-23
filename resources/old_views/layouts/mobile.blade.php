<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Parking Finder') }} - @yield('title', 'App')</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="{{ asset('css/mobile-app.css') }}?v={{ time() }}" rel="stylesheet">
    
    <!-- Feather Icons CDN -->
    <script src="https://unpkg.com/feather-icons"></script>
    
    @stack('styles')
</head>
<body class="mobile-app-body">
    <div class="mobile-app-root">
        
        @yield('content')

        <!-- BOTTOM NAVIGATION -->
        @if(View::hasSection('show_bottom_nav'))
        <nav class="mobile-bottom-nav">
            <a href="{{ route('map') }}" class="nav-item">
                <i data-feather="map"></i>
                <span>Mapa</span>
            </a>
            <a href="#" class="nav-item">
                <i data-feather="star"></i>
                <span>Favoritos</span>
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
