@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/paginas/estadisticas.css') }}" />
@endpush

@section('title', 'Estadísticas - Bellreguard Club de Basket')

@section('contenido')
<x-public-news-hero
    badge="ESTADISTICAS OFICIALES"
    title="Estadisticas Bellreguard"
    subtitle="Resumen de temporada por equipo local en partidos de liga."
/>

<section class="contenedor-estadisticas">
    @if($equiposLocales->isNotEmpty())
        <form method="GET" action="{{ route('basket.estadisticas') }}" class="stats-team-filter">
            <label class="stats-team-field">
                <span>Equipos</span>
                <select name="equipo_id" class="stats-team-select">
                    @foreach($equiposLocales as $equipo)
                        <option value="{{ $equipo->id }}" @selected((int) optional($equipoSeleccionado)->id === (int) $equipo->id)>
                            {{ $equipo->nombre }}{{ $equipo->categoria ? ' · ' . $equipo->categoria : '' }}
                        </option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="stats-team-btn">Ver estadísticas</button>
        </form>

        @if($equipoSeleccionado)
            @php
                $escudo = $equipoSeleccionado?->imagen_club_url ?? asset('img/club_14.png');
            @endphp
            <article class="stats-team-panel">
                <header class="stats-team-head">
                    <div class="stats-team-identidad">
                        <img src="{{ $escudo }}" alt="Escudo {{ $equipoSeleccionado->nombre }}" loading="lazy">
                        <div>
                            <h3>{{ $equipoSeleccionado->nombre }}</h3>
                            <p>{{ $equipoSeleccionado->categoria ?: 'Sin categoría' }}</p>
                        </div>
                    </div>
                    <div class="stats-team-pill">
                        <strong>{{ (int) ($resumenTemporada['pj'] ?? 0) }}</strong>
                        <span>Partidos de liga</span>
                    </div>
                </header>

                <div class="stats-shooting-grid">
                    @php
                        $pctFg = isset($resumenTemporada['porcentajes']['fg']) && $resumenTemporada['porcentajes']['fg'] !== null ? number_format($resumenTemporada['porcentajes']['fg'], 1) . '%' : '-';
                        $pctT2 = isset($resumenTemporada['porcentajes']['t2']) && $resumenTemporada['porcentajes']['t2'] !== null ? number_format($resumenTemporada['porcentajes']['t2'], 1) . '%' : '-';
                        $pctT3 = isset($resumenTemporada['porcentajes']['t3']) && $resumenTemporada['porcentajes']['t3'] !== null ? number_format($resumenTemporada['porcentajes']['t3'], 1) . '%' : '-';
                        $pctFt = isset($resumenTemporada['porcentajes']['ft']) && $resumenTemporada['porcentajes']['ft'] !== null ? number_format($resumenTemporada['porcentajes']['ft'], 1) . '%' : '-';
                    @endphp

                    <div class="stats-shot-card">
                        <h4>Tiro de campo</h4>
                        <p>{{ $resumenTemporada['totales']['fgm'] ?? 0 }} anotados · {{ $resumenTemporada['totales']['fg_fallados'] ?? 0 }} fallados · {{ $resumenTemporada['totales']['fga'] ?? 0 }} intentados</p>
                        <strong>{{ $pctFg }}</strong>
                    </div>
                    <div class="stats-shot-card">
                        <h4>Tiro de 2</h4>
                        <p>{{ $resumenTemporada['totales']['t2m'] ?? 0 }} anotados · {{ $resumenTemporada['totales']['t2_fallados'] ?? 0 }} fallados · {{ $resumenTemporada['totales']['t2a'] ?? 0 }} intentados</p>
                        <strong>{{ $pctT2 }}</strong>
                    </div>
                    <div class="stats-shot-card">
                        <h4>Tiro de 3</h4>
                        <p>{{ $resumenTemporada['totales']['t3m'] ?? 0 }} anotados · {{ $resumenTemporada['totales']['t3_fallados'] ?? 0 }} fallados · {{ $resumenTemporada['totales']['t3a'] ?? 0 }} intentados</p>
                        <strong>{{ $pctT3 }}</strong>
                    </div>
                    <div class="stats-shot-card">
                        <h4>Tiro libre</h4>
                        <p>{{ $resumenTemporada['totales']['ftm'] ?? 0 }} anotados · {{ $resumenTemporada['totales']['ft_fallados'] ?? 0 }} fallados · {{ $resumenTemporada['totales']['fta'] ?? 0 }} intentados</p>
                        <strong>{{ $pctFt }}</strong>
                    </div>
                </div>

                <div class="stats-kpi-grid">
                    <div class="stats-kpi"><span>Puntos</span><strong>{{ $resumenTemporada['totales']['pts'] ?? 0 }}</strong><small>Media {{ number_format($resumenTemporada['medias']['pts'] ?? 0, 1) }}</small></div>
                    <div class="stats-kpi"><span>Rebotes</span><strong>{{ $resumenTemporada['totales']['reb'] ?? 0 }}</strong><small>Media {{ number_format($resumenTemporada['medias']['reb'] ?? 0, 1) }}</small></div>
                    <div class="stats-kpi"><span>Asistencias</span><strong>{{ $resumenTemporada['totales']['ast'] ?? 0 }}</strong><small>Media {{ number_format($resumenTemporada['medias']['ast'] ?? 0, 1) }}</small></div>
                    <div class="stats-kpi"><span>Recuperaciones</span><strong>{{ $resumenTemporada['totales']['stl'] ?? 0 }}</strong><small>Media {{ number_format($resumenTemporada['medias']['stl'] ?? 0, 1) }}</small></div>
                    <div class="stats-kpi"><span>Tapones</span><strong>{{ $resumenTemporada['totales']['blk'] ?? 0 }}</strong><small>Media {{ number_format($resumenTemporada['medias']['blk'] ?? 0, 1) }}</small></div>
                    <div class="stats-kpi"><span>Pérdidas</span><strong>{{ $resumenTemporada['totales']['tov'] ?? 0 }}</strong><small>Media {{ number_format($resumenTemporada['medias']['tov'] ?? 0, 1) }}</small></div>
                    <div class="stats-kpi"><span>Faltas</span><strong>{{ $resumenTemporada['totales']['pf'] ?? 0 }}</strong><small>Media {{ number_format($resumenTemporada['medias']['pf'] ?? 0, 1) }}</small></div>
                    <div class="stats-kpi"><span>Faltas técnicas</span><strong>{{ $resumenTemporada['totales']['tf'] ?? 0 }}</strong><small>Media {{ number_format($resumenTemporada['medias']['tf'] ?? 0, 1) }}</small></div>
                    <div class="stats-kpi"><span>Reb. ofensivos</span><strong>{{ $resumenTemporada['totales']['reb_of'] ?? 0 }}</strong><small>Media {{ number_format($resumenTemporada['medias']['reb_of'] ?? 0, 1) }}</small></div>
                    <div class="stats-kpi"><span>Reb. defensivos</span><strong>{{ $resumenTemporada['totales']['reb_def'] ?? 0 }}</strong><small>Media {{ number_format($resumenTemporada['medias']['reb_def'] ?? 0, 1) }}</small></div>
                    <div class="stats-kpi"><span>Ratio AST/PER</span><strong>{{ isset($resumenTemporada['ratios']['ast_per']) && $resumenTemporada['ratios']['ast_per'] !== null ? number_format($resumenTemporada['ratios']['ast_per'], 2) : '-' }}</strong><small>Asistencias por pérdida</small></div>
                </div>

                <div class="stats-quarters">
                    <h4>Puntos por partes de la temporada</h4>
                    <div class="stats-quarters-grid">
                        <div class="stats-quarter-item"><span>Q1</span><strong>{{ $resumenTemporada['totales']['q1'] ?? 0 }}</strong><small>Media {{ number_format($resumenTemporada['medias']['q1'] ?? 0, 1) }}</small></div>
                        <div class="stats-quarter-item"><span>Q2</span><strong>{{ $resumenTemporada['totales']['q2'] ?? 0 }}</strong><small>Media {{ number_format($resumenTemporada['medias']['q2'] ?? 0, 1) }}</small></div>
                        <div class="stats-quarter-item"><span>Q3</span><strong>{{ $resumenTemporada['totales']['q3'] ?? 0 }}</strong><small>Media {{ number_format($resumenTemporada['medias']['q3'] ?? 0, 1) }}</small></div>
                        <div class="stats-quarter-item"><span>Q4</span><strong>{{ $resumenTemporada['totales']['q4'] ?? 0 }}</strong><small>Media {{ number_format($resumenTemporada['medias']['q4'] ?? 0, 1) }}</small></div>
                        <div class="stats-quarter-item"><span>PR</span><strong>{{ $resumenTemporada['totales']['pr'] ?? 0 }}</strong><small>Media {{ number_format($resumenTemporada['medias']['pr'] ?? 0, 1) }}</small></div>
                    </div>
                </div>
            </article>
        @endif
    @else
        <div class="estado-vacio">
            <div class="estado-vacio-icono">
                <i class="fas fa-chart-bar"></i>
            </div>
            <h3>No hay equipos locales disponibles</h3>
            <p>Crea equipos locales y registra estadísticas en partidos de liga para ver el resumen de temporada.</p>
        </div>
    @endif
</section>
@endsection
