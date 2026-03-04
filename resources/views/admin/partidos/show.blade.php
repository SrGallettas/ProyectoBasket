@extends('layouts.admin')

@use('Carbon\Carbon')

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    
    {{-- CABECERA DE LA FICHA --}}
    <header class="header-ficha">
        <div>
            <h2>Detalle del Encuentro</h2>
            <p>ID del Partido: #{{ $partido->id }} | Categoría: {{ $partido->categoria }}</p>
        </div>
        <div class="grupo-botones-header">
            <a href="{{ route('partidos.index') }}" class="btn-volver-link">
                <i class="fas fa-chevron-left"></i> Volver al listado
            </a>
        </div>
    </header>

    <div class="pizarra-ficha">
        {{-- AREA DEL MARCADOR / ENFRENTAMIENTO --}}
        <div class="seccion-show-partido">
            
            <div class="marcador-visual">
                {{-- Equipo Local --}}
                <div class="equipo-detalle">
                    <div class="escudo-grande">
                        <img src="{{ asset('escudos/' . $partido->equipoLocal->imagen_club) }}" alt="Escudo Local">
                    </div>
                    <span class="nombre-equipo-gigante">{{ $partido->equipoLocal->nombre }}</span>
                    <span class="rol-equipo">LOCAL</span>
                </div>

                <div class="separador-vs">
                    <span class="txt-vs">VS</span>
                </div>

                {{-- Equipo Visitante --}}
                <div class="equipo-detalle">
                    <div class="escudo-grande">
                        <img src="{{ asset('escudos/' . $partido->equipoVisitante->imagen_club) }}" alt="Escudo Visitante">
                    </div>
                    <span class="nombre-equipo-gigante">{{ $partido->equipoVisitante->nombre }}</span>
                    <span class="rol-equipo">VISITANTE</span>
                </div>
            </div>

            <hr class="divisor-ficha">

            {{-- INFORMACIÓN LOGÍSTICA --}}
            <div class="grid-info-detalle">
                <div class="info-item">
                    <i class="fas fa-calendar-day"></i>
                    <div>
                        <label>Fecha del Encuentro</label>
                        <p>{{ Carbon::parse($partido->fecha_hora)->translatedFormat('l, d \d\e F \d\e Y') }}</p>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <div>
                        <label>Hora de Inicio</label>
                        <p>{{ Carbon::parse($partido->fecha_hora)->format('H:i') }} Horas</p>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fas fa-map-marked-alt"></i>
                    <div>
                        <label>Sede / Pabellón</label>
                        <p>{{ $partido->lugar }}</p>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fas fa-tags"></i>
                    <div>
                        <label>Competición / Categoría</label>
                        <p>{{ $partido->categoria }} - Temporada 2025/2026</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection