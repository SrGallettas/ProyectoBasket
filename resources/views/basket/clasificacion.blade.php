{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

{{-- CSS específico de la clasificación pública --}}
@push('styles')
<x-page-style file="clasificacion.css" />
@endpush

@use('Carbon\Carbon')

{{-- TÍTULO DE LA PESTAÑA --}}
@section('title', __('public.standings.meta.title'))
@section('meta_description', __('public.standings.meta.description'))

{{-- CONTENIDO DE LA PÁGINA --}}
@section('contenido')

{{-- Hero principal de clasificaciones --}}
<x-public-news-hero
    :badge="__('public.standings.hero.badge')"
    :title="__('public.standings.hero.title')"
    :subtitle="__('public.standings.hero.subtitle')"
>
    <div class="icono-trofeo">
        <i class="fas fa-trophy"></i>
    </div>
</x-public-news-hero>

{{-- Contenido principal: selector, tabla y partidos de la competición --}}
<section class="contenedor-clasificacion {{ $clasificaciones->isEmpty() ? "estado-vacio-como-partidos estado-vacio-como-partidos-clasificacion" : "" }}">
    @if($clasificaciones->isNotEmpty())
        {{-- Selector de competición y jornada --}}
        <form method="GET" action="{{ route('basket.clasificacion') }}" class="selector-categorias">
            <label class="selector-field">
                <span>{{ __('public.standings.filters.competition') }}</span>
                <select name="clasificacion_id" class="btn-cat selector-input">
                    @foreach($clasificaciones as $clasificacion)
                        <option value="{{ $clasificacion->id }}" @selected((int) optional($clasificacionSeleccionada)->id === (int) $clasificacion->id)>
                            {{ $clasificacion->nombre }}{{ $clasificacion->temporada ? ' · ' . $clasificacion->temporada : '' }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="selector-field">
                <span>{{ __('public.standings.filters.round') }}</span>
                <select name="jornada" class="btn-cat selector-input">
                    <option value="general" @selected($modoGeneral ?? false)>{{ __('public.standings.filters.general') }}</option>
                    @if($jornadas->isEmpty())
                        <option value="" disabled>{{ __('public.standings.filters.no_rounds') }}</option>
                    @else
                        @foreach($jornadas as $jornada)
                            <option value="{{ $jornada }}" @selected(!($modoGeneral ?? false) && (int) $jornadaSeleccionada === (int) $jornada)>{{ __('public.standings.filters.round_label', ['round' => $jornada]) }}</option>
                        @endforeach
                    @endif
                </select>
            </label>

            <button type="submit" class="btn-cat activo">{{ __('public.standings.filters.submit') }}</button>
        </form>

        {{-- Tabla principal de clasificación --}}
        <div class="tabla-contenedor">
            <div class="tabla-header">
                <i class="fas fa-trophy"></i>
                {{ $clasificacionSeleccionada->nombre }}
                @if($clasificacionSeleccionada->temporada)
                    - {{ __('public.standings.table.season') }} {{ $clasificacionSeleccionada->temporada }}
                @endif
                @if($modoGeneral ?? false)
                    · {{ __('public.standings.filters.general') }}
                @elseif($jornadaSeleccionada)
                    · {{ __('public.standings.filters.round_label', ['round' => $jornadaSeleccionada]) }}
                @endif
                @if($jornadaClasificacionMostrada && (int) $jornadaClasificacionMostrada !== (int) $jornadaSeleccionada)
                    <small>{{ __('public.standings.table.calculated_until', ['round' => $jornadaClasificacionMostrada]) }}</small>
                @endif
            </div>
            <div class="tabla-scroll">
                <table class="tabla-clasificacion">
                    <thead>
                        <tr>
                            <th>Pos</th>
                            <th>{{ __('public.standings.table.team') }}</th>
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
                            {{-- Datos rápidos del equipo para la fila de la tabla --}}
                            @php
                                $nombreEquipo = optional($fila->equipo)->nombre ?? __('public.standings.table.team_fallback');
                                $escudoEquipo = optional($fila->equipo)->imagen_club_url ?? asset('img/club_14.png');
                            @endphp
                            <tr class="{{ str_contains(strtolower($nombreEquipo), 'bellreguard') ? 'fila-destacada' : '' }}">
                                <td><strong>{{ $idx + 1 }}</strong></td>
                                <td>
                                    <div class="equipo-columna">
                                        <img src="{{ $escudoEquipo }}" alt="{{ __('public.standings.table.shield_alt', ['team' => $nombreEquipo]) }}" loading="lazy">
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
                                <td colspan="9">{{ __('public.standings.empty.table') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Bloque de partidos de la jornada o de toda la competición --}}
        <section class="jornada-partidos">
            <div class="tabla-header jornada-header">
                <i class="fas fa-calendar-alt"></i>
                @if($modoGeneral ?? false)
                    {{ __('public.standings.matches.all_matches') }}
                @else
                    {{ __('public.standings.matches.round_matches') }}
                @endif
                @if(!($modoGeneral ?? false) && $jornadaSeleccionada)
                    <span class="jornada-pill">J{{ $jornadaSeleccionada }}</span>
                @endif
            </div>

            {{-- En modo general se agrupan los partidos por jornada --}}
            @if(($modoGeneral ?? false) && $partidosJornada->isNotEmpty())
                @foreach($partidosJornada->groupBy(fn ($partido) => (int) ($partido->jornada ?? 0)) as $jornadaGrupo => $partidosGrupo)
                    <div class="jornada-bloque">
                        <div class="jornada-bloque-titulo">{{ __('public.standings.filters.round_label', ['round' => $jornadaGrupo]) }}</div>
                        <div class="partidos-jornada-grid">
                            @foreach($partidosGrupo as $partido)
                                {{-- Datos del partido para pintar estado, escudos y marcador --}}
                                @php
                                    $statsLocal = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_local_id);
                                    $statsVisitante = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_visitante_id);
                                    $isFinalizado = optional($statsLocal)->puntos_totales !== null && optional($statsVisitante)->puntos_totales !== null;
                                    $nombreLocal = optional($partido->equipoLocal)->nombre ?? __('public.standings.matches.local');
                                    $nombreVisitante = optional($partido->equipoVisitante)->nombre ?? __('public.standings.matches.visitor');
                                    $escudoLocal = optional($partido->equipoLocal)->imagen_club_url ?? asset('img/club_14.png');
                                    $escudoVisitante = optional($partido->equipoVisitante)->imagen_club_url ?? asset('img/club_14.png');
                                @endphp

                                <article class="partido-jornada-card">
                                    <div class="partido-jornada-top">
                                        <span class="estado-partido {{ $isFinalizado ? 'finalizado' : 'proximo' }}">
                                            {{ $isFinalizado ? __('public.matches.status.finished') : __('public.matches.status.upcoming') }}
                                        </span>
                                        <span class="partido-fecha">{{ Carbon::parse($partido->fecha_hora)->format('d/m · H:i') }}</span>
                                    </div>

                                    <div class="partido-jornada-equipos">
                                        <div class="club-mini">
                                            <img src="{{ $escudoLocal }}" alt="{{ __('public.standings.table.shield_alt', ['team' => $nombreLocal]) }}" loading="lazy">
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
                                            <img src="{{ $escudoVisitante }}" alt="{{ __('public.standings.table.shield_alt', ['team' => $nombreVisitante]) }}" loading="lazy">
                                            <p>{{ $nombreVisitante }}</p>
                                        </div>
                                    </div>

                                    <p class="partido-lugar">
                                        <i class="fa-solid fa-location-dot"></i>
                                        {{ $partido->lugar }}
                                    </p>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @elseif($partidosJornada->isNotEmpty())
                {{-- Si no es modo general, se muestran solo los partidos de la jornada seleccionada --}}
                <div class="partidos-jornada-grid">
                    @foreach($partidosJornada as $partido)
                        {{-- Datos del partido para pintar estado, escudos y marcador --}}
                        @php
                            $statsLocal = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_local_id);
                            $statsVisitante = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_visitante_id);
                            $isFinalizado = optional($statsLocal)->puntos_totales !== null && optional($statsVisitante)->puntos_totales !== null;
                            $nombreLocal = optional($partido->equipoLocal)->nombre ?? __('public.standings.matches.local');
                            $nombreVisitante = optional($partido->equipoVisitante)->nombre ?? __('public.standings.matches.visitor');
                            $escudoLocal = optional($partido->equipoLocal)->imagen_club_url ?? asset('img/club_14.png');
                            $escudoVisitante = optional($partido->equipoVisitante)->imagen_club_url ?? asset('img/club_14.png');
                        @endphp

                        <article class="partido-jornada-card">
                            <div class="partido-jornada-top">
                                <span class="estado-partido {{ $isFinalizado ? 'finalizado' : 'proximo' }}">
                                    {{ $isFinalizado ? __('public.matches.status.finished') : __('public.matches.status.upcoming') }}
                                </span>
                                <span class="partido-fecha">{{ Carbon::parse($partido->fecha_hora)->format('d/m · H:i') }}</span>
                            </div>

                            <div class="partido-jornada-equipos">
                                <div class="club-mini">
                                    <img src="{{ $escudoLocal }}" alt="{{ __('public.standings.table.shield_alt', ['team' => $nombreLocal]) }}" loading="lazy">
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
                                    <img src="{{ $escudoVisitante }}" alt="{{ __('public.standings.table.shield_alt', ['team' => $nombreVisitante]) }}" loading="lazy">
                                    <p>{{ $nombreVisitante }}</p>
                                </div>
                            </div>

                            <p class="partido-lugar">
                                <i class="fa-solid fa-location-dot"></i>
                                {{ $partido->lugar }}
                            </p>
                        </article>
                    @endforeach
                </div>
            @else
                {{-- Estado vacío cuando no hay partidos públicos --}}
                <div class="estado-vacio">
                    <div class="estado-vacio-icono">
                        <i class="fa-regular fa-calendar-xmark"></i>
                    </div>
                    <h3>{{ __('public.standings.empty.title') }}</h3>
                    <p>{{ ($modoGeneral ?? false) ? __('public.standings.matches.empty_competition') : __('public.standings.matches.empty_round') }}</p>
                </div>
            @endif
        </section>
    @else
        {{-- Estado vacío si el club aún no ha publicado clasificaciones --}}
        <div class="estado-vacio estado-vacio-merchan-gap estado-vacio-merchan-gap-clasificacion">
            <div class="estado-vacio-icono">
                <i class="fas fa-info-circle"></i>
            </div>
            <h3>{{ __('public.standings.empty.title') }}</h3>
            <p>{{ __('public.standings.empty.text') }}</p>
        </div>
    @endif
</section>

@endsection
