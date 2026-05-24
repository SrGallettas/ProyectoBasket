{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

{{-- CSS específico de la página de equipos --}}
@push('styles')
<x-page-style file="equipo.css" />
@endpush

{{-- TÍTULO DE LA PESTAÑA --}}
@section('title', $jugador->nombre . ' ' . ($jugador->apellido ?? '') . ' - Bellreguard CB')
@section('meta_description', __('public.player.meta.description', ['name' => trim($jugador->nombre . ' ' . ($jugador->apellido ?? ''))]))

{{-- CONTENIDO DE LA PÁGINA --}}
@section('contenido')

{{-- Hero principal de la ficha del jugador --}}
<section class="equipo-hero equipo-hero-jugador">
    <div class="equipo-hero-content">
        <h1>{{ $jugador->nombre }} {{ $jugador->apellido ?? '' }}</h1>
        <p>{{ __('public.player.hero.subtitle', ['team' => $jugador->equipo->nombre, 'position' => $jugador->posicion_display, 'number' => $jugador->dorsal ?? '00']) }}</p>

        <div class="botones-categoria">
            <a href="{{ route('basket.equipo', ['categoria' => $jugador->equipo->categoria]) }}" class="boton-categoria activo">
                {{ __('public.player.hero.back_to_team') }}
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
                <div class="jugador-header">
                    <div>
                        <p class="posicion-tag">{{ $jugador->posicion_display }}</p>
                        <h2>{{ $jugador->nombre }} {{ $jugador->apellido ?? '' }}</h2>
                    </div>

                    <div class="jugador-dorsal-grande">#{{ $jugador->dorsal ?? '00' }}</div>
                </div>

                <p class="jugador-copy">
                    {{ __('public.player.copy') }}
                </p>

                <div class="jugador-meta-grid">
                    <div class="jugador-meta-item">
                        <span>{{ __('public.player.meta.team') }}</span>
                        <strong>{{ $jugador->equipo->nombre }}</strong>
                    </div>

                    <div class="jugador-meta-item">
                        <span>{{ __('public.player.meta.category') }}</span>
                        <strong>{{ $categoriaMostrar }}</strong>
                    </div>

                    <div class="jugador-meta-item">
                        <span>{{ __('public.player.meta.birth') }}</span>
                        <strong>
                            {{ $jugador->fecha_nacimiento ? \Carbon\Carbon::parse($jugador->fecha_nacimiento)->format('d/m/Y') : __('public.player.meta.not_available') }}
                        </strong>
                    </div>
                </div>
            </article>

            {{-- Navegación rápida a otros jugadores del mismo equipo --}}
            @if($companeros->isNotEmpty())
                <aside class="jugador-companeros">
                    <h3>{{ __('public.player.teammates.title') }}</h3>
                    <p>{{ __('public.player.teammates.text') }}</p>

                    <div class="jugador-companeros-grid">
                        @foreach($companeros as $companero)
                            <a href="{{ route('basket.jugadores.detalle', $companero) }}" class="jugador-companero-link">
                                <img src="{{ $companero->imagen_jugador_url }}" alt="{{ $companero->nombre }} {{ $companero->apellido ?? '' }}">

                                <div>
                                    <strong>{{ $companero->nombre }} {{ $companero->apellido ?? '' }}</strong>
                                    <span>{{ $companero->posicion_display }}</span>
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
