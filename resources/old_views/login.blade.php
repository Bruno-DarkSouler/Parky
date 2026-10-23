<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Parking Finder') }} - Iniciar sesión</title>
        <meta name="description" content="Inicia sesión en Parking Finder para encontrar tu lugar de estacionamiento.">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('resources/css/style.css') }}?v=43">
    </head>

    <body class="welcome-body">
        <main class="auth-container">
            <div class="auth-header">
                <a href="{{ route('welcome') }}" class="auth-back" id="btn-volver-login">
                    <svg width="10" height="16" viewBox="0 0 10 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.5 1L1.5 8L8.5 15" stroke="#1e2a38" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Volver al inicio
                </a>
            </div>

            <div class="auth-content">
                <div class="auth-text">
                    <h1 class="auth-title">Entrar a mi cuenta</h1>
                    <p class="auth-subtitle">Completa tus datos para acceder</p>
                </div>

                <form action="{{ route('api.login') }}" method="POST" class="auth-form" id="login-form">
                    @csrf
                    <div class="auth-input-group">
                        <span class="auth-input-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="4" width="20" height="16" rx="2"/>
                                <path d="M22 7l-10 7L2 7"/>
                            </svg>
                        </span>
                        <input type="email" name="email" placeholder="Correo electronico" required value="{{ old('email') }}" class="auth-input" id="input-email">
                    </div>

                    <div class="auth-input-group">
                        <span class="auth-input-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </span>
                        <input type="password" name="password" placeholder="Contraseña" required class="auth-input" id="input-password">
                    </div>

                    <div class="auth-options" style="justify-content: flex-end;">
                        <a href="#" class="auth-forgot" id="link-forgot">Olvide mi Contraseña</a>
                    </div>

                    @if ($errors->any())
                        <div class="auth-errors">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <button type="submit" class="auth-btn-submit" id="btn-iniciar-sesion">
                        Iniciar sesión
                    </button>
                </form>

                <div class="auth-separator">
                    <span>o continúa con</span>
                </div>

                <a href="{{route('auth.redirect')}}" class="auth-btn-google" id="btn-google-login">
                    <svg width="20" height="20" viewBox="0 0 48 48">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                    </svg>
                    Iniciar sesión con Google
                </a>

                <div class="auth-link">
                    <p>¿No tienes una cuenta? <a href="{{ route('register') }}" id="link-register">Registrate</a></p>
                </div>
            </div>

            <footer class="auth-footer">
                <p>&copy; Copyright {{ date('Y') }}</p>
            </footer>
        </main>
    </body>
</html>
