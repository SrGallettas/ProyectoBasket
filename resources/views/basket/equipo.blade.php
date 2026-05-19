{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

{{-- CSS específico de la página de equipos --}}
@push('styles')
<x-page-style file="equipo.css" />
@endpush

@section('title', __('public.team.meta.title'))
@section('meta_description', __('public.team.meta.description'))

@section('contenido')
{{-- Imagen de cabecera: usa la foto del equipo visible o una imagen por defecto --}}
@php
    $imagenEquipoHero = $equipoMostrar?->foto_plantilla_url ?? asset('img/equipo.png');
@endphp

{{-- Hero principal con selector rápido de categorías --}}
<section class="equipo-hero">
    <div class="equipo-hero-media">
        <img src="{{ $imagenEquipoHero }}" alt="{{ __('public.team.hero.image_alt') }}">
        <div class="equipo-hero-overlay"></div>
    </div>

    <div class="equipo-hero-content">
        <span class="equipo-hero-badge">{{ __('public.team.hero.badge') }}</span>
        <h1>{{ __('public.team.hero.title') }}</h1>
        <p>{{ __('public.team.hero.subtitle') }}</p>

        <div class="botones-categoria">
            @foreach($categorias as $categoria)
                <a href="{{ route('basket.equipo', ['categoria' => $categoria]) }}" 
                class="boton-categoria {{ $categoriaSel == $categoria ? 'activo' : '' }}">
                {{ $categoriasDisplay[$categoria] ?? $categoria }}
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- Bloque principal con resumen del equipo y plantilla publicada --}}
<section class="equipo-jugadores">
    @if(session('mensaje'))
        <div class="public-alert-ok">
            {{ session('mensaje') }}
        </div>
    @endif

    @if($equipoMostrar)
        {{-- Resumen rápido del equipo seleccionado --}}
        <div class="equipo-resumen">
            <div class="equipo-resumen-copy">
                <h2 class="equipo-titulo-categoria">{{ $equipoMostrar->nombre }}</h2>
                <p>{{ __('public.team.summary.text') }}</p>
            </div>
            <div class="equipo-resumen-stats">
                <div class="equipo-resumen-stat">
                    <strong>{{ $jugadores?->total() ?? 0 }}</strong>
                    <span>{{ __('public.team.summary.players') }}</span>
                </div>
                <div class="equipo-resumen-stat">
                    <strong>{{ $categoriaMostrar }}</strong>
                    <span>{{ __('public.team.summary.category') }}</span>
                </div>
            </div>
        </div>

        {{-- Acción rápida para ocultar el equipo desde la vista pública --}}
        @if(auth()->user()?->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COACH]))
            <div class="public-admin-actions">
                <form action="{{ route('basket.equipo.toggle-oculto', $equipoMostrar->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="public-hide-button">{{ __('public.team.actions.hide_team') }}</button>
                </form>
            </div>
        @endif
        
        {{-- Rejilla de jugadores del equipo seleccionado --}}
        <div class="rejilla-jugadores">
            @forelse($jugadores as $jugador)
                <article class="tarjeta-jugador">
                    {{-- Enlace principal para abrir la ficha pública del jugador --}}
                    <a href="{{ route('basket.jugadores.detalle', $jugador) }}" class="tarjeta-jugador-link" aria-label="{{ __('public.team.player.view_card', ['name' => trim($jugador->nombre . ' ' . ($jugador->apellido ?? ''))]) }}">
                    </a>

                    <div class="foto-jugador">
                        @if($jugador->imagen_jugador)
                            <img src="{{ $jugador->imagen_jugador_url }}" alt="{{ $jugador->nombre }}" onerror="this.onerror=null;this.src='{{ asset('img/club_14.png') }}';">
                        @else
                            <img src="{{ asset('img/club_14.png') }}" alt="{{ __('public.team.player.no_photo') }}" class="foto-defecto">
                        @endif
                        {{-- Badge del dorsal sobre la foto --}}
                        <div class="dorsal-badge">#{{ $jugador->dorsal ?? '00' }}</div>
                    </div>
            
                    <div class="info-jugador">
                        {{-- 1. Posición en pequeño y rojo --}}
                        <p class="posicion-tag">{{ $jugador->posicion_display }}</p>
                        
                        {{-- 2. Nombre del Jugador --}}
                        <h3>{{ $jugador->nombre }} {{ $jugador->apellido ?? '' }}</h3>
                        
                        {{-- 3. Separador visual --}}
                        <div class="separador"></div>
                        
                        {{-- 4. Fecha de nacimiento --}}
                        <p class="fecha-nacimiento">
                            <strong>{{ __('public.team.player.birth') }}</strong> 
                            {{ $jugador->fecha_nacimiento ? \Carbon\Carbon::parse($jugador->fecha_nacimiento)->format('d/m/Y') : __('public.team.player.not_available') }}
                        </p>

                        {{-- Acción pública solo visible para admin o staff --}}
                        @if(auth()->user()?->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COACH]))
                                <form action="{{ route('basket.jugadores.toggle-oculto', $jugador->id) }}" method="POST" class="public-card-action">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="public-hide-button">{{ __('public.team.actions.hide_player') }}</button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                {{-- Estado vacío si el equipo no tiene jugadores publicados --}}
                <div class="contenedor-mensaje-vacio full-grid-column">
                    <div class="alerta-basket">
                        <p>{!! __('public.team.empty.players', ['team' => e($equipoMostrar->nombre)]) !!}</p>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Paginación de jugadores --}}
        <div class="contenedor-paginacion">
            {{ $jugadores->links() }}
        </div>

    @else
        {{-- Estado vacío si no existe la categoría solicitada --}}
        <div class="contenedor-mensaje-vacio">
            <div class="alerta-basket">
                <p>{!! __('public.team.empty.category', ['category' => e($categoriaMostrar)]) !!}</p>
                <a href="{{ route('basket.inicio') }}" class="boton-rojo-centrado">{{ __('public.team.empty.back_home') }}</a>
            </div>
        </div>
    @endif
</section>
@endsection
