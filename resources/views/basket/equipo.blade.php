{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

{{-- CSS específico de la página de equipos --}}
@push('styles')
<x-page-style file="equipo.css" />
@endpush

@section('contenido')
{{-- Imagen de cabecera: usa la foto del equipo visible o una imagen por defecto --}}
@php
    $imagenEquipoHero = $equipoMostrar?->foto_plantilla_url ?? asset('img/equipo.png');
@endphp

{{-- Hero principal con selector rápido de categorías --}}
<section class="equipo-hero">
    <div class="equipo-hero-media">
        <img src="{{ $imagenEquipoHero }}" alt="Plantilla y equipos del Bellreguard Club de Basket">
        <div class="equipo-hero-overlay"></div>
    </div>

    <div class="equipo-hero-content">
        <span class="equipo-hero-badge">PLANTILLA OFICIAL</span>
        <h1>Cantera y equipos del Bellreguard CB</h1>
        <p>Descubre las categorías del club y consulta la plantilla disponible de cada equipo local.</p>

        <div class="botones-categoria">
            @foreach($categorias as $categoria)
                <a href="{{ route('basket.equipo', ['categoria' => $categoria]) }}" 
                class="boton-categoria {{ $categoriaSel == $categoria ? 'activo' : '' }}">
                {{ $categoria }}
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
                <p>Consulta la plantilla disponible de esta categoría y revisa los jugadores publicados actualmente en la web del club.</p>
            </div>
            <div class="equipo-resumen-stats">
                <div class="equipo-resumen-stat">
                    <strong>{{ $jugadores?->total() ?? 0 }}</strong>
                    <span>Jugadores</span>
                </div>
                <div class="equipo-resumen-stat">
                    <strong>{{ $equipoMostrar->categoria }}</strong>
                    <span>Categoría</span>
                </div>
            </div>
        </div>

        {{-- Acción rápida para ocultar el equipo desde la vista pública --}}
        @if(auth()->user()?->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COACH]))
            <div class="public-admin-actions">
                <form action="{{ route('basket.equipo.toggle-oculto', $equipoMostrar->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="public-hide-button">Ocultar equipo en web</button>
                </form>
            </div>
        @endif
        
        {{-- Rejilla de jugadores del equipo seleccionado --}}
        <div class="rejilla-jugadores">
            @forelse($jugadores as $jugador)
                <article class="tarjeta-jugador">
                    {{-- Enlace principal para abrir la ficha pública del jugador --}}
                    <a href="{{ route('basket.jugadores.detalle', $jugador) }}" class="tarjeta-jugador-link" aria-label="Ver ficha de {{ $jugador->nombre }} {{ $jugador->apellido ?? '' }}">
                    </a>

                    <div class="foto-jugador">
                        @if($jugador->imagen_jugador)
                            <img src="{{ $jugador->imagen_jugador_url }}" alt="{{ $jugador->nombre }}" onerror="this.onerror=null;this.src='{{ asset('img/club_14.png') }}';">
                        @else
                            <img src="{{ asset('img/club_14.png') }}" alt="Sin foto" class="foto-defecto">
                        @endif
                        {{-- Badge del dorsal sobre la foto --}}
                        <div class="dorsal-badge">#{{ $jugador->dorsal ?? '00' }}</div>
                    </div>
            
                    <div class="info-jugador">
                        {{-- 1. Posición en pequeño y rojo --}}
                        <p class="posicion-tag">{{ $jugador->posicion }}</p>
                        
                        {{-- 2. Nombre del Jugador --}}
                        <h3>{{ $jugador->nombre }} {{ $jugador->apellido ?? '' }}</h3>
                        
                        {{-- 3. Separador visual --}}
                        <div class="separador"></div>
                        
                        {{-- 4. Fecha de nacimiento --}}
                        <p class="fecha-nacimiento">
                            <strong>Nacimiento:</strong> 
                            {{ $jugador->fecha_nacimiento ? \Carbon\Carbon::parse($jugador->fecha_nacimiento)->format('d/m/Y') : 'N/A' }}
                        </p>

                        {{-- Acción pública solo visible para admin o staff --}}
                        @if(auth()->user()?->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COACH]))
                            <form action="{{ route('basket.jugadores.toggle-oculto', $jugador->id) }}" method="POST" class="public-card-action">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="public-hide-button">Ocultar jugador en web</button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                {{-- Estado vacío si el equipo no tiene jugadores publicados --}}
                <div class="contenedor-mensaje-vacio full-grid-column">
                    <div class="alerta-basket">
                        <p>Todavía no hay jugadores inscritos en el equipo <strong>{{ $equipoMostrar->nombre }}</strong>.</p>
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
                <p>No se ha encontrado el equipo de la categoría <strong>{{ $categoriaSel }}</strong>.</p>
                <a href="{{ route('basket.inicio') }}" class="boton-rojo-centrado">Volver al inicio</a>
            </div>
        </div>
    @endif
</section>
@endsection
