{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/paginas/clasificacion.css') }}" />
@endpush

@use('Carbon\Carbon')

{{-- TÍTULO DE LA PESTAÑA --}}
@section('title', 'Inicio - Bellreguard Club de Basket')

{{-- CONTENIDO DE LA PÁGINA --}}
@section('contenido')

<x-public-news-hero
    badge="CLASIFICACIONES OFICIALES"
    title="Tabla de Posiciones"
    subtitle="Consulta la posición de nuestros equipos en todas las categorías."
>
    <div class="icono-trofeo">
        <i class="fas fa-trophy"></i>
    </div>
</x-public-news-hero>

<section class="contenedor-clasificacion">
    @if($clasificaciones->isNotEmpty())
        <form method="GET" action="{{ route('basket.clasificacion') }}" class="selector-categorias">
            <label class="selector-field">
                <span>Competición</span>
                <select name="clasificacion_id" class="btn-cat selector-input">
                    @foreach($clasificaciones as $clasificacion)
                        <option value="{{ $clasificacion->id }}" @selected((int) optional($clasificacionSeleccionada)->id === (int) $clasificacion->id)>
                            {{ $clasificacion->nombre }}{{ $clasificacion->temporada ? ' · ' . $clasificacion->temporada : '' }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="selector-field">
                <span>Jornada</span>
                <select name="jornada" class="btn-cat selector-input">
                    <option value="general" @selected($modoGeneral ?? false)>General</option>
                    @if($jornadas->isEmpty())
                        <option value="" disabled>Sin jornadas</option>
                    @else
                        @foreach($jornadas as $jornada)
                            <option value="{{ $jornada }}" @selected(!($modoGeneral ?? false) && (int) $jornadaSeleccionada === (int) $jornada)>Jornada {{ $jornada }}</option>
                        @endforeach
                    @endif
                </select>
            </label>

            <button type="submit" class="btn-cat activo">Ver jornada</button>
        </form>

        <div class="tabla-contenedor">
            <div class="tabla-header">
                <i class="fas fa-trophy"></i>
                {{ $clasificacionSeleccionada->nombre }}
                @if($clasificacionSeleccionada->temporada)
                    - Temporada {{ $clasificacionSeleccionada->temporada }}
                @endif
                @if($modoGeneral ?? false)
                    · General
                @elseif($jornadaSeleccionada)
                    · Jornada {{ $jornadaSeleccionada }}
                @endif
                @if($jornadaClasificacionMostrada && (int) $jornadaClasificacionMostrada !== (int) $jornadaSeleccionada)
                    <small>(tabla calculada hasta jornada {{ $jornadaClasificacionMostrada }})</small>
                @endif
            </div>
            <div class="tabla-scroll">
                <table class="tabla-clasificacion">
                    <thead>
                        <tr>
                            <th>Pos</th>
                            <th>Equipo</th>
                            <th>PJ</th>
                            <th>PG</th>
                            <th>PP</th>
                            <th>PF</th>
                            <th>PC</th>
                            <th>Diff</th>
                            <th>Pts</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($filas as $idx => $fila)
                            @php
                                $nombreEquipo = optional($fila->equipo)->nombre ?? 'Equipo';
                                $escudoEquipo = optional($fila->equipo)->imagen_club
                                    ? asset('escudos/' . $fila->equipo->imagen_club)
                                    : asset('img/club_14.png');
                            @endphp
                            <tr class="{{ str_contains(strtolower($nombreEquipo), 'bellreguard') ? 'fila-destacada' : '' }}">
                                <td><strong>{{ $idx + 1 }}</strong></td>
                                <td>
                                    <div class="equipo-columna">
                                        <img src="{{ $escudoEquipo }}" alt="Escudo {{ $nombreEquipo }}" loading="lazy">
                                        <span>{{ $nombreEquipo }}</span>
                                    </div>
                                </td>
                                <td>{{ $fila->pj }}</td>
                                <td>{{ $fila->pg }}</td>
                                <td>{{ $fila->pp }}</td>
                                <td>{{ $fila->pf }}</td>
                                <td>{{ $fila->pc }}</td>
                                <td>{{ $fila->diff }}</td>
                                <td><strong>{{ $fila->pts }}</strong></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">Todavía no hay clasificación calculada para esta competición.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <section class="jornada-partidos">
            <div class="tabla-header jornada-header">
                <i class="fas fa-calendar-alt"></i>
                @if($modoGeneral ?? false)
                    Todos los partidos
                @else
                    Partidos de la jornada
                @endif
                @if(!($modoGeneral ?? false) && $jornadaSeleccionada)
                    <span class="jornada-pill">J{{ $jornadaSeleccionada }}</span>
                @endif
            </div>

            <div class="partidos-jornada-grid">
                @forelse($partidosJornada as $partido)
                    @php
                        $statsLocal = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_local_id);
                        $statsVisitante = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_visitante_id);
                        $isFinalizado = optional($statsLocal)->puntos_totales !== null && optional($statsVisitante)->puntos_totales !== null;
                        $nombreLocal = optional($partido->equipoLocal)->nombre ?? 'Local';
                        $nombreVisitante = optional($partido->equipoVisitante)->nombre ?? 'Visitante';
                        $escudoLocal = optional($partido->equipoLocal)->imagen_club
                            ? asset('escudos/' . $partido->equipoLocal->imagen_club)
                            : asset('img/club_14.png');
                        $escudoVisitante = optional($partido->equipoVisitante)->imagen_club
                            ? asset('escudos/' . $partido->equipoVisitante->imagen_club)
                            : asset('img/club_14.png');
                    @endphp

                    <article class="partido-jornada-card">
                        <div class="partido-jornada-top">
                            <span class="estado-partido {{ $isFinalizado ? 'finalizado' : 'proximo' }}">
                                {{ $isFinalizado ? 'Finalizado' : 'Próximo' }}
                            </span>
                            <span class="partido-fecha">{{ Carbon::parse($partido->fecha_hora)->format('d/m · H:i') }}</span>
                        </div>

                        <div class="partido-jornada-equipos">
                            <div class="club-mini">
                                <img src="{{ $escudoLocal }}" alt="Escudo {{ $nombreLocal }}" loading="lazy">
                                <p>{{ $nombreLocal }}</p>
                            </div>

                            @if($isFinalizado)
                                <div class="marcador-jornada">
                                    {{ (int) $statsLocal->puntos_totales }} - {{ (int) $statsVisitante->puntos_totales }}
                                </div>
                            @else
                                <div class="marcador-jornada pendiente">VS</div>
                            @endif

                            <div class="club-mini">
                                <img src="{{ $escudoVisitante }}" alt="Escudo {{ $nombreVisitante }}" loading="lazy">
                                <p>{{ $nombreVisitante }}</p>
                            </div>
                        </div>

                        <p class="partido-lugar">
                            <i class="fa-solid fa-location-dot"></i>
                            {{ $partido->lugar }}
                        </p>
                    </article>
                @empty
                    <div class="partidos-vacio">
                        <i class="fa-regular fa-calendar-xmark"></i>
                        <p>{{ ($modoGeneral ?? false) ? 'No hay partidos públicos para esta competición.' : 'No hay partidos públicos para esta jornada.' }}</p>
                    </div>
                @endforelse
            </div>
        </section>
    @else
        <div class="tabla-contenedor">
            <div class="tabla-header">
                <i class="fas fa-info-circle"></i> Sin clasificaciones disponibles
            </div>
            <div class="tabla-scroll">
                <table class="tabla-clasificacion">
                    <tbody>
                        <tr>
                            <td>El club todavía no ha publicado ninguna clasificación.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</section>

@endsection
