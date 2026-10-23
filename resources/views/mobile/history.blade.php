@extends('layouts.mobile')

@section('title', 'Historial')

@section('content')
<div class="mobile-page">
    <div class="mobile-page-header">
        <a href="{{ route('map') }}" class="mobile-back-button"><i data-feather="arrow-left"></i></a>
        <h2>Historial</h2>
    </div>
    <div class="container animate-fade-in p-0">
        <div class="mobile-card mb-3">
            <div class="d-flex justify-content-between">
                <span class="fw-bold">Estacionamiento Centro</span>
                <span class="fw-bold text-primary" style="color:#52B788 !important;">$450</span>
            </div>
            <p class="text-muted small mb-0 mt-1">29 Jun, 14:30 - 2 hs</p>
        </div>
        <div class="mobile-card mb-3">
            <div class="d-flex justify-content-between">
                <span class="fw-bold">Plaza Mayor</span>
                <span class="fw-bold text-primary" style="color:#52B788 !important;">$300</span>
            </div>
            <p class="text-muted small mb-0 mt-1">28 Jun, 09:15 - 1.5 hs</p>
        </div>
    </div>
</div>
@endsection
