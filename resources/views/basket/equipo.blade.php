@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/paginas/equipo.css') }}" />
@endpush

@section('contenido')
<x-public-news-hero
    badge="PLANTILLA OFICIAL"
    title="Nuestro Equipo"
    subtitle="Conoce a los jugadores y categorías del Bellreguard Club de Basket."
/>

<section class="navegacion-categorias">
    <div class="botones-categoria">
        @foreach($equiposLocales as $eq)
            <a href="{{ route('basket.equipo', ['categoria' => $eq->categoria]) }}" 
               class="boton-categoria {{ $categoriaSel == $eq->categoria ? 'activo' : '' }}">
               {{ $eq->categoria }}
            </a>
        @endforeach
    </div>
</section>

<section class="equipo-jugadores">
    @if(session('mensaje'))
        <div class="public-alert-ok">
            {{ session('mensaje') }}
        </div>
    @endif

    @if($equipoMostrar)
        {{-- Título centrado --}}
        <h2 class="equipo-titulo-categoria">
            Equipo {{ $equipoMostrar->categoria }} - {{ $equipoMostrar->nombre }}
        </h2>
        @auth
            <div class="public-admin-actions">
                <form action="{{ route('basket.equipo.toggle-oculto', $equipoMostrar->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="public-hide-button">Ocultar equipo en web</button>
                </form>
            </div>
        @endauth
        
        <div class="rejilla-jugadores">
            @forelse($equipoMostrar->jugadores as $jugador)
                <div class="tarjeta-jugador">
                    <div class="foto-jugador">
                        @if($jugador->imagen_jugador)
                            <img src="{{ asset('jugadores/' . $jugador->imagen_jugador) }}" alt="{{ $jugador->nombre }}">
                        @else
                            <img src="{{ asset('img/basket.jpeg') }}" alt="Sin foto" class="foto-defecto">
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

                        @auth
                            <form action="{{ route('basket.jugadores.toggle-oculto', $jugador->id) }}" method="POST" class="public-card-action">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="public-hide-button">Ocultar jugador en web</button>
                            </form>
                        @endauth
                    </div>
                </div>
            @empty
                <div class="contenedor-mensaje-vacio full-grid-column">
                    <div class="alerta-basket">
                        <p>Todavía no hay jugadores inscritos en el equipo <strong>{{ $equipoMostrar->nombre }}</strong>.</p>
                    </div>
                </div>
            @endforelse
        </div>        
    @else
        <div class="contenedor-mensaje-vacio">
            <div class="alerta-basket">
                <p>No se ha encontrado el equipo de la categoría <strong>{{ $categoriaSel }}</strong>.</p>
                <a href="{{ route('basket.inicio') }}" class="boton-rojo-centrado">Volver al inicio</a>
            </div>
        </div>
    @endif
</section>
@endsection
