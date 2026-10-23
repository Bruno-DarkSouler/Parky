@extends('layouts.mobile')

@section('title', 'Cámara IoT')

@section('content')
<div class="mobile-page">
    <div class="mobile-page-header">
        <a href="{{ route('app.menu') }}" class="mobile-back-button"><i data-feather="arrow-left"></i></a>
        <h2>Cámara IoT</h2>
    </div>
    <div class="container animate-fade-in text-center mt-5">
        <div class="p-4 rounded-circle d-inline-block mb-4" style="background-color: var(--color-surface);">
            <i data-feather="camera" style="width: 64px; height: 64px;" class="text-primary"></i>
        </div>
        <h3 class="fw-bold">Convierte tu cámara</h3>
        <p class="text-muted">Aporta al sistema conectando tu cámara de seguridad.</p>
        <button class="btn-mobile btn-mobile-primary mt-4 w-75">Configurar ahora</button>
    </div>
</div>
@endsection
