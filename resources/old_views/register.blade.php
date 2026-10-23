<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Parking Finder') }} - Crear cuenta</title>
        <meta name="description" content="Regístrate en Parking Finder para encontrar tu lugar de estacionamiento.">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('resources/css/style.css') }}?v=43">
    </head>

    <body class="welcome-body">
        <main class="auth-container">
            <div class="auth-header">
                <a href="{{ route('welcome') }}" class="auth-back" id="btn-volver-register">
                    <svg width="10" height="16" viewBox="0 0 10 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.5 1L1.5 8L8.5 15" stroke="#1e2a38" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Volver al inicio
                </a>
            </div>

            <div class="auth-content">
                <div class="auth-text">
                    <h1 class="auth-title">Crear cuenta</h1>
                    <p class="auth-subtitle">Completa tus datos para comenzar</p>
                </div>

                <form action="{{ route('api.register') }}" method="POST" class="auth-form" id="register-form">
                    @csrf
                    <div class="auth-input-group">
                        <span class="auth-input-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </span>
                        <input type="text" name="name" placeholder="Nombre completo" required value="{{ old('name') }}" class="auth-input" id="input-name">
                    </div>

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
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                        </span>
                        <input type="tel" name="phone" placeholder="Teléfono" value="{{ old('phone') }}" class="auth-input" id="input-phone">
                    </div>

                    <div class="auth-input-group">
                        <span class="auth-input-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </span>
                        <input type="password" name="password" placeholder="contraseña" required class="auth-input" id="input-password">
                    </div>

                    <div class="auth-input-group">
                        <span class="auth-input-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </span>
                        <input type="password" name="password_confirmation" placeholder="Confirmar Contraseña" required class="auth-input" id="input-password-confirm">
                    </div>

                    @if ($errors->any())
                        <div class="auth-errors">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <button type="submit" class="auth-btn-submit" id="btn-registrarse">
                        Registrarse
                    </button>
                </form>

                <div class="auth-link">
                    <p>Ya tienes cuenta? <a href="{{ route('login') }}" id="link-login">Inicia sesión</a></p>
                </div>
            </div>

            <footer class="auth-footer">
                <p>&copy; Copyright {{ date('Y') }}</p>
            </footer>
        </main>
    </body>
</html>
