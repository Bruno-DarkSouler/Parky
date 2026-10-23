<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parky - Bienvenido</title>
    
    <link rel="icon" href="{{ asset('resources/img/logo1.webp') }}" type="image/webp">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}?v={{ time() }}">
</head>
<body>

    <div class="slides-wrapper">
        
        <!-- Slide 0: Splash -->
        <div class="slide slide-active" id="slide-0">
            <div class="splash-content">
                <img src="{{ asset('resources/img/logo1.webp') }}" alt="Logo" class="logo-img">
                <h1 class="logo-text"><span class="park">Par</span><span class="finder">ky</span></h1>
                <p class="slogan">Encuentra tu lugar,<br>ahorra tiempo.</p>
            </div>
            
            <div class="splash-actions">
                <button class="btn btn-pink" onclick="goToSlide(1)">Empezar</button>
            </div>
            
            <div class="footer">&copy; Copyright {{ date('Y') }}</div>
        </div>

        <!-- Slide 1: Onboarding 1 -->
        <div class="slide" id="slide-1">
            <div class="onboarding-img-container">
                <img src="{{ asset('resources/img/presentacion1.webp') }}" alt="Encontrá lugares" class="onboarding-img">
            </div>
            
            <div class="onboarding-text">
                <h2 class="onboarding-title">Encontrá lugares rápido</h2>
                <p class="onboarding-desc">Vemos los espacios disponibles en tiempo real para que ahorres tiempo y vueltas</p>
            </div>
            
            <div class="onboarding-actions">
                <button class="btn btn-primary" onclick="goToSlide(2)">Siguiente</button>
                <button class="btn btn-outline" onclick="goToSlide(4)">Saltar</button>
            </div>
            
            <div class="dots">
                <span class="dot active"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </div>

        <!-- Slide 2: Onboarding 2 -->
        <div class="slide" id="slide-2">
            <div class="onboarding-img-container">
                <img src="{{ asset('resources/img/presentacion2.webp') }}" alt="Evitá dar vueltas" class="onboarding-img">
            </div>
            
            <div class="onboarding-text">
                <h2 class="onboarding-title">Evitá dar vueltas</h2>
                <p class="onboarding-desc">Te mostramos las mejores opciones según tráfico, distancia y disponibilidad.</p>
            </div>
            
            <div class="onboarding-actions">
                <button class="btn btn-primary" onclick="goToSlide(3)">Siguiente</button>
                <button class="btn btn-outline" onclick="goToSlide(4)">Saltar</button>
            </div>
            
            <div class="dots">
                <span class="dot"></span>
                <span class="dot active"></span>
                <span class="dot"></span>
            </div>
        </div>

        <!-- Slide 3: Onboarding 3 -->
        <div class="slide" id="slide-3">
            <div class="onboarding-img-container">
                <!-- Using presentacion3.webp placeholder. User can upload correct camera one if needed -->
                <img src="{{ asset('resources/img/presentacion3.webp') }}" alt="Colaborá con tu cámara" class="onboarding-img">
            </div>
            
            <div class="onboarding-text">
                <h2 class="onboarding-title">Colaborá con tu cámara</h2>
                <p class="onboarding-desc">Comparti la vista de tu cámara y ayudá a que todos encuentren lugares más facil.</p>
            </div>
            
            <div class="onboarding-actions">
                <button class="btn btn-primary" onclick="goToSlide(4)">Siguiente</button>
                <button class="btn btn-outline" onclick="goToSlide(4)">Saltar</button>
            </div>
            
            <div class="dots">
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot active"></span>
            </div>
        </div>

        <!-- Slide 4: Login / Register Actions -->
        <div class="slide" id="slide-4">
            <div class="splash-content">
                <img src="{{ asset('resources/img/logo1.webp') }}" alt="Logo" class="logo-img">
                <h1 class="logo-text"><span class="park">Par</span><span class="finder">ky</span></h1>
                <p class="slogan">Encuentra tu lugar,<br>ahorra tiempo.</p>
            </div>
            
            <div class="splash-actions" style="display:flex; flex-direction:column; gap:16px;">
                <a href="{{ route('login') }}" class="btn btn-primary" style="text-decoration:none; display:flex; align-items:center; justify-content:center;">Iniciar sesión</a>
                <a href="{{ route('register') }}" class="btn btn-outline-purple" style="text-decoration:none; display:flex; align-items:center; justify-content:center;">Registrarse</a>
            </div>
            
            <div class="footer">&copy; Copyright {{ date('Y') }}</div>
        </div>

    </div>

    <script>
        let currentSlide = 0;
        const totalSlides = 5;

        function goToSlide(index) {
            if (index < 0 || index >= totalSlides) return;

            const slides = document.querySelectorAll('.slide');
            const current = slides[currentSlide];
            const next = slides[index];

            current.classList.remove('slide-active');
            current.classList.add('slide-exit');

            next.classList.remove('slide-exit');
            next.classList.add('slide-active');

            setTimeout(() => {
                current.classList.remove('slide-exit');
            }, 400);

            currentSlide = index;
        }
    </script>
</body>
</html>
