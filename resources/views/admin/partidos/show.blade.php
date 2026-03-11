@extends('layouts.admin')

@use('Carbon\Carbon')

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <x-admin-show-header
        titulo="Detalle del Encuentro"
        :subtitulo="'ID del Partido: #' . $partido->id . ' | Categoría: ' . $partido->categoria"
        :editRoute="route('partidos.edit', $partido->id)"
        :deleteRoute="route('partidos.destroy', $partido->id)"
        :indexRoute="route('partidos.index')"
        confirmMessage="¿Seguro que deseas eliminar este partido?"
    />

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
