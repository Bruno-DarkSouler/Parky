<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parky - Permitir ubicación</title>
    
    <link rel="icon" href="{{ asset('resources/img/logo1.webp') }}" type="image/webp">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}?v={{ time() }}">
    <style>
        .location-slide {
            opacity: 1 !important;
            visibility: visible !important;
            transform: translateX(0) !important;
            display: flex;
        }
        
        @media (min-width: 768px) {
            .location-slide {
                display: grid !important;
            }
            .location-actions {
                flex-direction: column !important;
            }
            .location-actions .btn {
                width: 100% !important;
                margin-bottom: 12px;
            }
        }
        
        .location-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 100%;
        }
        
        .onboarding-title {
            line-height: 1.2;
            margin-bottom: 16px;
        }
        
        .onboarding-desc {
            font-weight: 500;
            color: #333;
        }
    </style>
</head>
<body>

    <div class="slides-wrapper">
        <div class="slide location-slide">
            
            <div class="onboarding-img-container">
                <img src="{{ asset('resources/img/ubicacion.webp') }}" alt="Ubicación" class="onboarding-img" style="max-height: 300px;">
            </div>
            
            <div class="onboarding-text" style="align-self: center;">
                <h2 class="onboarding-title">Permitir acceso<br>a tu ubicación</h2>
                <p class="onboarding-desc">Usamos tu ubicación para mostrarte estacionamientos cercanos y mejorar tu experiencia.</p>
            </div>
            
            <div class="onboarding-actions location-actions">
                <button class="btn btn-primary" onclick="requestLocation()">Permitir ubicación</button>
                <button class="btn btn-outline" onclick="skipLocation()" style="color: #000; border: 1px solid #ccc;">ahora no</button>
            </div>
            
            <div class="dots">
                <span class="dot active"></span>
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
            
        </div>
    </div>

    <script>
        // Check permissions on load
        function checkPermission() {
            if (navigator.permissions) {
                navigator.permissions.query({ name: 'geolocation' })
                    .then(function(permissionStatus) {
                        if (permissionStatus.state === 'granted') {
                            setCookieAndRedirect();
                        }
                    });
            }
        }

        function requestLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        setCookieAndRedirect();
                    },
                    function(error) {
                        // User denied or error occurred
                        console.error("Ubicación denegada:", error);
                        // Optional: show a message or just proceed without location
                        setDeniedCookieAndRedirect();
                    }
                );
            } else {
                alert("La geolocalización no es soportada por tu navegador.");
            }
        }
        
        function setCookieAndRedirect() {
            document.cookie = "location_enabled=true; max-age=31536000; path=/";
            window.location.href = "{{ route('map') }}";
        }
        
        function setDeniedCookieAndRedirect() {
            document.cookie = "location_enabled=false; max-age=31536000; path=/";
            window.location.href = "{{ route('map') }}";
        }
        
        function skipLocation() {
            setDeniedCookieAndRedirect();
        }

        window.addEventListener('DOMContentLoaded', checkPermission);
    </script>
</body>
</html>
