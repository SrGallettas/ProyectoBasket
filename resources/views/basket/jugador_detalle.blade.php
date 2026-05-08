{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

{{-- CSS específico de la página de equipos --}}
@push('styles')
<link rel="stylesheet" href="{{ asset('css/paginas/equipo.css') }}" />
@endpush

{{-- TÍTULO DE LA PESTAÑA --}}
@section('title', $jugador->nombre . ' ' . ($jugador->apellido ?? '') . ' - Bellreguard CB')

{{-- CONTENIDO DE LA PÁGINA --}}
@section('contenido')

{{-- Hero principal de la ficha del jugador --}}
<section class="equipo-hero">
    <div class="equipo-hero-media">
        <img src="{{ $jugador->imagen_jugador_url }}" alt="{{ $jugador->nombre }} {{ $jugador->apellido ?? '' }}">
        <div class="equipo-hero-overlay"></div>
    </div>

    <div class="equipo-hero-content">
        <span class="equipo-hero-badge">FICHA DE JUGADOR</span>
        <h1>{{ $jugador->nombre }} {{ $jugador->apellido ?? '' }}</h1>
        <p>{{ $jugador->equipo->nombre }} · {{ $jugador->posicion }} · Dorsal #{{ $jugador->dorsal ?? '00' }}</p>

        <div class="botones-categoria">
            <a href="{{ route('basket.equipo', ['categoria' => $jugador->equipo->categoria]) }}" class="boton-categoria activo">
                Volver al equipo
            </a>
        </div>
    </div>
</section>

{{-- Contenido principal de la ficha pública --}}
<section class="jugador-detalle">
    <div class="jugador-layout">
        {{-- Foto principal del jugador --}}
        <aside class="jugador-media-card">
            <img src="{{ $jugador->imagen_jugador_url }}" alt="{{ $jugador->nombre }} {{ $jugador->apellido ?? '' }}">
        </aside>

        <div class="jugador-info-stack">
            {{-- Datos principales del jugador --}}
            <article class="jugador-info-card">
                <span class="jugador-kicker">Plantilla oficial</span>

                <div class="jugador-header">
                    <div>
                        <p class="posicion-tag">{{ $jugador->posicion }}</p>
                        <h2>{{ $jugador->nombre }} {{ $jugador->apellido ?? '' }}</h2>
                    </div>

                    <div class="jugador-dorsal-grande">#{{ $jugador->dorsal ?? '00' }}</div>
                </div>

                <p class="jugador-copy">
                    Perfil público del jugador dentro de la plantilla del Bellreguard CB. Aquí puedes consultar su equipo actual, posición y los datos básicos publicados por el club.
                </p>

                <div class="jugador-meta-grid">
                    <div class="jugador-meta-item">
                        <span>Equipo</span>
                        <strong>{{ $jugador->equipo->nombre }}</strong>
                    </div>

                    <div class="jugador-meta-item">
                        <span>Categoría</span>
                        <strong>{{ $jugador->equipo->categoria }}</strong>
                    </div>

                    <div class="jugador-meta-item">
                        <span>Nacimiento</span>
                        <strong>
                            {{ $jugador->fecha_nacimiento ? \Carbon\Carbon::parse($jugador->fecha_nacimiento)->format('d/m/Y') : 'No disponible' }}
                        </strong>
                    </div>
                </div>
            </article>

            {{-- Navegación rápida a otros jugadores del mismo equipo --}}
            @if($companeros->isNotEmpty())
                <aside class="jugador-companeros">
                    <h3>Más jugadores del equipo</h3>
                    <p>Explora otros perfiles publicados de la misma categoría.</p>

                    <div class="jugador-companeros-grid">
                        @foreach($companeros as $companero)
                            <a href="{{ route('basket.jugadores.detalle', $companero) }}" class="jugador-companero-link">
                                <img src="{{ $companero->imagen_jugador_url }}" alt="{{ $companero->nombre }} {{ $companero->apellido ?? '' }}">

                                <div>
                                    <strong>{{ $companero->nombre }} {{ $companero->apellido ?? '' }}</strong>
                                    <span>{{ $companero->posicion }}</span>
                                </div>

                                <em>#{{ $companero->dorsal ?? '00' }}</em>
                            </a>
                        @endforeach
                    </div>
                </aside>
            @endif
        </div>
    </div>
</section>

@endsection
