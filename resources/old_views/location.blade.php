<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Parking Finder') }} - Permitir ubicación</title>
        <meta name="description" content="Permite el acceso a tu ubicación para encontrar estacionamientos cercanos.">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('resources/css/style.css') }}?v=46">
    </head>

    <body class="welcome-body">
        <main class="w5-container">
            <div class="w5-illustration">
                <img src="{{ asset('resources/img/ubicacion.webp') }}" alt="Ilustración de ubicación" class="w5-illustration-img">
            </div>

            <div class="w5-content">
                <div class="w5-text">
                    <h1 class="w5-title">Permitir acceso<br>a tu ubicación</h1>
                    <p class="w5-description">Usamos tu ubicación para mostrarte estacionamientos cercanos y mejorar tu experiencia.</p>
                </div>

                <div id="error-message" style="display: none; color: #dc3545; margin-bottom: 15px; text-align: center; font-size: 14px; font-weight: 500; padding: 10px; border-radius: 8px; background-color: #f8d7da; border: 1px solid #f5c6cb;">
                    <p>El acceso a la ubicación fue denegado. Es un requisito obligatorio para usar la aplicación. Por favor, habilítalo en la configuración de tu navegador.</p>
                </div>

                <div class="w5-actions">
                    <button type="button" class="w5-btn-allow" id="btn-permitir-ubicacion" onclick="requestLocation()">
                        Permitir ubicación
                    </button>
                </div>
            </div>
        </main>

        <script>

            function checkPermission() {
                navigator.permissions.query({ name: 'geolocation' })
                    .then(function(permissionStatus) {
                        if (permissionStatus.state === 'granted') {
                            document.cookie = "location=true; path=/; max-age=31536000";
                            window.location.href = "{{ route('welcome') }}";
                        } else if (permissionStatus.state === 'prompt') {
                            document.cookie = "location=false; path=/; max-age=31536000";
                            document.getElementById('error-message').style.display = 'none';
                        } else if (permissionStatus.state === 'denied') {
                            document.cookie = "location=false; path=/; max-age=31536000";
                            document.getElementById('error-message').style.display = 'block';
                        }

                        permissionStatus.onchange = function() {
                            checkPermission();
                        };
                    });
            }
  
  
            function requestLocation() {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        document.cookie = "location=true; path=/; max-age=31536000";
                        window.location.href = "{{ route('welcome') }}";
                    },
                    function(error) {
                        document.cookie = "location=false; path=/; max-age=31536000";
                        if (error.code === error.PERMISSION_DENIED) {
                            document.getElementById('error-message').style.display = 'block';
                        }
                    }
                );
            }

            // on load
            window.addEventListener('DOMContentLoaded', function() {
                if (navigator.geolocation) {
                    checkPermission();
                } else {
                    document.getElementById('error-message').style.display = 'block';
                }
            });

        </script>
    </body>
</html>
