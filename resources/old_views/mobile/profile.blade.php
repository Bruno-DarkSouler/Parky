@extends('layouts.mobile')

@section('title', 'Mi Perfil')

@section('content')
<div class="mobile-page">
    <div class="mobile-page-header">
        <a href="{{ route('app.menu') }}" class="mobile-back-button"><i data-feather="arrow-left"></i></a>
        <h2>Mi Perfil</h2>
    </div>
    <div class="container animate-fade-in p-0">
        <p class="text-muted mb-4">Configuración de tu cuenta.</p>
        
        <form id="form-profile">
            <label class="mobile-input-label">Nombre Completo</label>
            <input type="text" class="mobile-input-field" value="{{ Auth::user()->name ?? '' }}" required>
            
            <label class="mobile-input-label">Correo Electrónico</label>
            <input type="email" class="mobile-input-field" value="{{ Auth::user()->email ?? '' }}" readonly>
            
            <button type="submit" class="btn-mobile btn-mobile-primary mt-3">Guardar Cambios</button>
        </form>
    </div>
</div>
@endsection
