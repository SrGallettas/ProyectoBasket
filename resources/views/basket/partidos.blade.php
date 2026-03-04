{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

{{-- IMPORTAMOS LA LIBRERÍA CARBON --}}
@use('Carbon\Carbon') 

{{-- TÍTULO DE LA PESTAÑA --}}
@section('title', 'Inicio - Bellreguard Club de Basket')

{{-- CONTENIDO DE LA PÁGINA --}}
@section('contenido')

<section class="seccion-partidos-header">
    <div class="header-contenido">
        <div class="header-texto">
            <h1>Próximos Partidos</h1>
            <p>Consulta el calendario de todos nuestros equipos</p>
        </div>
    </div>
</section>

<section class="seccion-calendario">
    <div class="rejilla-partidos">
        
        {{-- Agrupamos los partidos por su categoría --}}
        @php
            $partidosPorCategoria = $partidos->groupBy('categoria');
        @endphp

        @forelse($partidosPorCategoria as $categoria => $encuentros)
            <div class="caja-equipo full-width">
                <div class="titulo-equipo">
                    {{-- Icono dinámico según la categoría --}}
                    <i class="fa-solid {{ $categoria == 'Senior A' || $categoria == 'Senior B' ? 'fa-trophy' : 'fa-star' }}"></i>
                    <div>
                        <h3>Equipo {{ $categoria }}</h3>
                        <p>Bellreguard Club de Basket</p>
                    </div>
                </div>

                <div class="rejilla-partido-doble">
                    @foreach($encuentros as $partido)
                        <div class="tarjeta-partido">
                            <span class="etiqueta-proximo activo">Próximo</span>
                            <span class="jornada">Encuentro</span>
                            
                            <div class="enfrentamiento">
                                {{-- EQUIPO LOCAL --}}
                                <div class="equipo local">
                                    <img src="{{ asset('escudos/' . $partido->equipoLocal->imagen_club) }}" alt="{{ $partido->equipoLocal->nombre }}">
                                    <p>{{ $partido->equipoLocal->nombre }}</p>
                                </div>

                                <span class="vs">VS</span>

                                {{-- EQUIPO VISITANTE --}}
                                <div class="equipo visitante">
                                    <img src="{{ asset('escudos/' . $partido->equipoVisitante->imagen_club) }}" alt="{{ $partido->equipoVisitante->nombre }}">
                                    <p>{{ $partido->equipoVisitante->nombre }}</p>
                                </div>
                            </div>

                            <div class="info-adicional">
                                {{-- Fecha formateada en español (ej: Sábado, 23 Marzo 2026) --}}
                                <div class="dato-horario">
                                    <i class="fa-regular fa-calendar-alt"></i> 
                                    {{ Carbon::parse($partido->fecha_hora)->translatedFormat('l, d F Y') }}
                                </div>
                                
                                {{-- Hora del partido --}}
                                <div class="dato-horario">
                                    <i class="fa-regular fa-clock"></i> 
                                    {{ Carbon::parse($partido->fecha_hora)->format('H:i') }}h
                                </div>

                                {{-- Lugar del encuentro --}}
                                <div class="dato-lugar">
                                    <i class="fa-solid fa-location-dot"></i> 
                                    {{ $partido->lugar }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="tarjeta-partido" style="grid-column: 1 / -1; text-align: center;">
                <p>No hay partidos programados actualmente.</p>
            </div>
        @endforelse
        
    </div>
</section>

@endsection