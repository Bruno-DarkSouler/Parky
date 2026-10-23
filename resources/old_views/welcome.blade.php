<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Parking Finder') }} - Bienvenido</title>
        <meta name="description" content="Encuentra tu lugar de estacionamiento ideal con Parking Finder.">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('resources/css/style.css') }}?v=58">
    </head>

    <body class="welcome-body">
        <div class="slides-wrapper" id="slidesWrapper">

            <!--1-->
            <section class="slide slide-active" id="slide-0">
                <main class="welcome-container">
                    <div class="welcome-content">
                        <div class="welcome-logo">
                            <img src="{{ asset('resources/img/logo1.webp') }}" alt="Parking Finder Logo" class="welcome-logo-img">
                        </div>

                        <div class="welcome-text">
                            <h1 class="welcome-title"><span class="title-park">Parking</span><span class="title-finder">Finder</span></h1>
                            <p class="welcome-slogan">Encuentra tu lugar,<br>ahorra tiempo.</p>
                        </div>

                        <div class="welcome-action">
                            <button type="button" class="btn-empezar" id="btn-empezar" onclick="goToSlide(1)">
                                Empezar
                            </button>
                        </div>
                    </div>

                    <footer class="welcome-footer">
                        <p>&copy; Copyright {{ date('Y') }}</p>
                    </footer>
                </main>
            </section>

            <!--2-->
            <section class="slide" id="slide-1">
                <main class="w2-container">
                    <div class="w2-illustration">
                        <img src="{{ asset('resources/img/presentacion1.webp') }}" alt="Ilustración de estacionamiento" class="w2-illustration-img">
                    </div>

                    <div class="w2-content">
                        <div class="w2-text">
                            <h2 class="w2-title">Encontrá lugares rápido</h2>
                            <p class="w2-description">Vemos los espacios disponibles en tiempo real para que ahorres tiempo y vueltas</p>
                        </div>

                        <div class="w2-actions">
                            <button type="button" class="w2-btn-next" id="btn-siguiente-s2" onclick="goToSlide(2)">
                                Siguiente
                            </button>
                            <button type="button" class="w2-btn-skip" id="btn-saltar-s2" onclick="goToSlide(4)">
                                Saltar
                            </button>
                        </div>

                        <div class="w2-dots" aria-label="Página 1 de 3">
                            <span class="w2-dot active"></span>
                            <span class="w2-dot"></span>
                            <span class="w2-dot"></span>
                        </div>
                    </div>
                </main>
            </section>

            <!--3-->
            <section class="slide" id="slide-2">
                <main class="w2-container">
                    <div class="w2-illustration">
                        <img src="{{ asset('resources/img/presentacion2.webp') }}" alt="Ilustración de navegación y seguridad" class="w2-illustration-img">
                    </div>

                    <div class="w2-content">
                        <div class="w2-text">
                            <h2 class="w2-title">Evitá dar vueltas</h2>
                            <p class="w2-description">Te mostramos las mejores opciones según tráfico, distancia y disponibilidad.</p>
                        </div>

                        <div class="w2-actions">
                            <button type="button" class="w2-btn-next" id="btn-siguiente-s3" onclick="goToSlide(3)">
                                Siguiente
                            </button>
                            <button type="button" class="w2-btn-skip" id="btn-saltar-s3" onclick="goToSlide(4)">
                                Saltar
                            </button>
                        </div>

                        <div class="w2-dots" aria-label="Página 2 de 3">
                            <span class="w2-dot"></span>
                            <span class="w2-dot active"></span>
                            <span class="w2-dot"></span>
                        </div>
                    </div>
                </main>
            </section>

            <!--4-->
            <section class="slide" id="slide-3">
                <main class="w2-container">
                    <div class="w2-illustration">
                        <img src="{{ asset('resources/img/presentacion3.webp') }}" alt="Ilustración de estacionamiento seguro" class="w2-illustration-img">
                    </div>

                    <div class="w2-content">
                        <div class="w2-text">
                            <h2 class="w2-title">Estacioná seguro</h2>
                            <p class="w2-description">Elegí estacionamientos verificados con cámaras, vigilancia y buena iluminación.</p>
                        </div>

                        <div class="w2-actions">
                            <button type="button" class="w2-btn-next" id="btn-siguiente-s4" onclick="goToSlide(4)">
                                Siguiente
                            </button>
                            <button type="button" class="w2-btn-skip" id="btn-saltar-s4" onclick="goToSlide(4)">
                                Saltar
                            </button>
                        </div>

                        <div class="w2-dots" aria-label="Página 3 de 3">
                            <span class="w2-dot"></span>
                            <span class="w2-dot"></span>
                            <span class="w2-dot active"></span>
                        </div>
                    </div>
                </main>
            </section>

            <!--5-->
            <section class="slide" id="slide-4">
                <main class="w4-container">
                    <div class="w4-content">
                        <div class="w4-logo">
                            <img src="{{ asset('resources/img/logo1.webp') }}" alt="Parking Finder Logo" class="w4-logo-img">
                        </div>

                        <div class="w4-text">
                            <h2 class="w4-title"><span class="w4-title-park">Park</span><span class="w4-title-finder">Finder</span></h2>
                            <p class="w4-slogan">Encuentra tu lugar,<br>ahorra tiempo.</p>
                        </div>

                        <div class="w4-actions">
                            <a href="{{ route('login') }}" class="w4-btn-login" id="btn-iniciar-sesion">
                                Iniciar sesión
                            </a>
                            <a href="{{ route('register') }}" class="w4-btn-register" id="btn-registrarse">
                                Registrarse
                            </a>
                        </div>
                    </div>

                    <footer class="w4-footer">
                        <p>&copy; Copyright {{ date('Y') }}</p>
                    </footer>
                </main>
            </section>
        </div>

        <script>
            var currentSlide = 0;
            var totalSlides = 5;

            function goToSlide(index) {
                if (index < 0 || index >= totalSlides) return;

                var slides = document.querySelectorAll('.slide');
                var current = slides[currentSlide];
                var next = slides[index];

                current.classList.remove('slide-active');
                current.classList.add('slide-exit');

                next.classList.add('slide-active');

                setTimeout(function() {
                    current.classList.remove('slide-exit');
                }, 500);

                currentSlide = index;
            }
        </script>
    </body>
</html>
