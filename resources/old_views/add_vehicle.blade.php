<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Parking Finder') }} - Agregar Vehículo</title>
        <meta name="description" content="Registra tu vehículo en Parking Finder.">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('resources/css/style.css') }}?v=47">
    </head>

    <body class="welcome-body">
        <main class="auth-container">
            <div class="auth-header">
                <a href="{{ url()->previous() }}" class="auth-back" id="btn-volver-vehicle">
                    <svg width="10" height="16" viewBox="0 0 10 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.5 1L1.5 8L8.5 15" stroke="#1e2a38" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>

            <div class="auth-content">
                <div class="auth-text">
                    <h1 class="auth-title">Agregar Vehículo</h1>
                    <p class="auth-subtitle">Registra tu vehiculo</p>
                </div>

                <form action="{{ route('vehicle.store') }}" method="POST" class="auth-form" id="vehicle-form">
                    @csrf

                    {{-- Marca --}}
                    <div class="auth-input-group">
                        <span class="auth-input-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="1" y="3" width="15" height="13" rx="2" ry="2"/>
                                <path d="M16 8h2a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-1"/>
                                <path d="M1 16h16"/>
                                <circle cx="5.5" cy="18.5" r="2.5"/>
                                <circle cx="13.5" cy="18.5" r="2.5"/>
                            </svg>
                        </span>
                        <input type="text" name="brand" placeholder="Marca" required value="{{ old('brand') }}" class="auth-input" id="input-brand">
                    </div>

                    {{-- Modelo --}}
                    <div class="auth-input-group">
                        <span class="auth-input-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="1" y="3" width="15" height="13" rx="2" ry="2"/>
                                <path d="M16 8h2a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-1"/>
                                <path d="M1 16h16"/>
                                <circle cx="5.5" cy="18.5" r="2.5"/>
                                <circle cx="13.5" cy="18.5" r="2.5"/>
                            </svg>
                        </span>
                        <input type="text" name="model" placeholder="Modelo" required value="{{ old('model') }}" class="auth-input" id="input-model">
                    </div>

                    {{-- Año --}}
                    <div class="auth-input-group">
                        <span class="auth-input-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                        </span>
                        <input type="number" name="year" placeholder="Año" required value="{{ old('year') }}" class="auth-input" id="input-year" min="1900" max="{{ date('Y') + 1 }}">
                    </div>

                    {{-- Color --}}
                    <div class="auth-input-group">
                        <span class="auth-input-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </span>
                        <input type="text" name="color" placeholder="Color" required value="{{ old('color') }}" class="auth-input" id="input-color">
                    </div>

                    {{-- Patente --}}
                    <div class="auth-input-group">
                        <span class="auth-input-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="6" width="20" height="12" rx="2"/>
                                <line x1="6" y1="10" x2="6" y2="14"/>
                                <line x1="10" y1="10" x2="10" y2="14"/>
                                <line x1="14" y1="10" x2="14" y2="14"/>
                                <line x1="18" y1="10" x2="18" y2="14"/>
                            </svg>
                        </span>
                        <input type="text" name="plate" placeholder="Patente" required value="{{ old('plate') }}" class="auth-input" id="input-plate">
                    </div>

                    @if ($errors->any())
                        <div class="auth-errors">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <button type="submit" class="vehicle-btn-submit" id="btn-guardar-vehiculo">
                        Guardar vehículo
                    </button>
                </form>

                <a href="{{ route('welcome') }}" class="vehicle-btn-skip" id="btn-skip-vehicle">
                    ahora no
                </a>

            </div>

            <footer class="auth-footer">
                <p>&copy; Copyright {{ date('Y') }}</p>
            </footer>
        </main>
    </body>
</html>
