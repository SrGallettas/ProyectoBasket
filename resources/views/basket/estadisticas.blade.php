{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

{{-- CSS específico de la página de estadísticas --}}
@push('styles')
<x-page-style file="estadisticas.css" />
@endpush

@section('title', __('public.stats.meta.title'))
@section('meta_description', __('public.stats.meta.description'))

@section('contenido')
{{-- Hero principal de estadísticas --}}
<x-public-news-hero
    :badge="__('public.stats.hero.badge')"
    :title="__('public.stats.hero.title')"
    :subtitle="__('public.stats.hero.subtitle')"
/>

{{-- Contenido principal con filtro y resumen de temporada --}}
<section class="contenedor-estadisticas {{ $equiposLocales->isEmpty() ? "estado-vacio-como-partidos estado-vacio-como-partidos-estadisticas" : "" }}">
    @if($equiposLocales->isNotEmpty())
        {{-- Selector del equipo local a analizar --}}
        <form method="GET" action="{{ route('basket.estadisticas') }}" class="stats-team-filter">
            <label class="stats-team-field">
                <span>{{ __('public.stats.filters.teams') }}</span>
                <select name="equipo_id" class="stats-team-select">
                    @foreach($equiposLocales as $equipo)
                        <option value="{{ $equipo->id }}" @selected((int) optional($equipoSeleccionado)->id === (int) $equipo->id)>
                            {{ $equipo->nombre }}{{ $equipo->categoria ? ' · ' . $equipo->categoria : '' }}
                        </option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="stats-team-btn">{{ __('public.stats.filters.submit') }}</button>
        </form>

        @if($equipoSeleccionado)
            {{-- Escudo del equipo actual o imagen de respaldo --}}
            @php
                $escudo = $equipoSeleccionado?->imagen_club_url ?? asset('img/club_14.png');
            @endphp

            {{-- Panel principal con resumen global del equipo --}}
            <article class="stats-team-panel">
                <header class="stats-team-head">
                    <div class="stats-team-identidad">
                        <img src="{{ $escudo }}" alt="{{ __('public.stats.shield_alt', ['team' => $equipoSeleccionado->nombre]) }}" loading="lazy">
                        <div>
                            <h3>{{ $equipoSeleccionado->nombre }}</h3>
                            <p>{{ $equipoSeleccionado->categoria ?: __('public.stats.no_category') }}</p>
                        </div>
                    </div>
                    <div class="stats-team-pill">
                        <strong>{{ (int) ($resumenTemporada['pj'] ?? 0) }}</strong>
                        <span>{{ __('public.stats.summary.league_matches') }}</span>
                    </div>
                </header>

                {{-- Resumen básico de récord de temporada --}}
                <div class="stats-record-grid">
                    <div class="stats-record-card">
                        <span>{{ __('public.stats.summary.played') }}</span>
                        <strong>{{ (int) ($resumenTemporada['pj'] ?? 0) }}</strong>
                        <small>{{ __('public.stats.summary.regular_league') }}</small>
                    </div>
                    <div class="stats-record-card">
                        <span>{{ __('public.stats.summary.wins') }}</span>
                        <strong>{{ (int) ($resumenTemporada['victorias'] ?? 0) }}</strong>
                        <small>{{ __('public.stats.summary.won_matches') }}</small>
                    </div>
                    <div class="stats-record-card">
                        <span>{{ __('public.stats.summary.losses') }}</span>
                        <strong>{{ (int) ($resumenTemporada['derrotas'] ?? 0) }}</strong>
                        <small>{{ __('public.stats.summary.lost_matches') }}</small>
                    </div>
                    <div class="stats-record-card">
                        <span>{{ __('public.stats.summary.percentage') }}</span>
                        <strong>{{ isset($resumenTemporada['porcentaje_victorias']) && $resumenTemporada['porcentaje_victorias'] !== null ? number_format($resumenTemporada['porcentaje_victorias'], 1) . '%' : '-' }}</strong>
                        <small>{{ __('public.stats.summary.win_rate') }}</small>
                    </div>
                </div>

                {{-- Cálculo visual de porcentajes de tiro --}}
                <div class="stats-shooting-grid">
                    @php
                        $pctFg = isset($resumenTemporada['porcentajes']['fg']) && $resumenTemporada['porcentajes']['fg'] !== null ? number_format($resumenTemporada['porcentajes']['fg'], 1) . '%' : '-';
                        $pctT2 = isset($resumenTemporada['porcentajes']['t2']) && $resumenTemporada['porcentajes']['t2'] !== null ? number_format($resumenTemporada['porcentajes']['t2'], 1) . '%' : '-';
                        $pctT3 = isset($resumenTemporada['porcentajes']['t3']) && $resumenTemporada['porcentajes']['t3'] !== null ? number_format($resumenTemporada['porcentajes']['t3'], 1) . '%' : '-';
                        $pctFt = isset($resumenTemporada['porcentajes']['ft']) && $resumenTemporada['porcentajes']['ft'] !== null ? number_format($resumenTemporada['porcentajes']['ft'], 1) . '%' : '-';
                    @endphp

                    {{-- Tarjetas de tiro por tipo --}}
                    <div class="stats-shot-card">
                        <h4>{{ __('public.stats.shots.field_goal') }}</h4>
                        <p>{{ __('public.stats.shots.line', ['made' => $resumenTemporada['totales']['fgm'] ?? 0, 'missed' => $resumenTemporada['totales']['fg_fallados'] ?? 0, 'attempted' => $resumenTemporada['totales']['fga'] ?? 0]) }}</p>
                        <strong>{{ $pctFg }}</strong>
                    </div>
                    <div class="stats-shot-card">
                        <h4>{{ __('public.stats.shots.two_point') }}</h4>
                        <p>{{ __('public.stats.shots.line', ['made' => $resumenTemporada['totales']['t2m'] ?? 0, 'missed' => $resumenTemporada['totales']['t2_fallados'] ?? 0, 'attempted' => $resumenTemporada['totales']['t2a'] ?? 0]) }}</p>
                        <strong>{{ $pctT2 }}</strong>
                    </div>
                    <div class="stats-shot-card">
                        <h4>{{ __('public.stats.shots.three_point') }}</h4>
                        <p>{{ __('public.stats.shots.line', ['made' => $resumenTemporada['totales']['t3m'] ?? 0, 'missed' => $resumenTemporada['totales']['t3_fallados'] ?? 0, 'attempted' => $resumenTemporada['totales']['t3a'] ?? 0]) }}</p>
                        <strong>{{ $pctT3 }}</strong>
                    </div>
                    <div class="stats-shot-card">
                        <h4>{{ __('public.stats.shots.free_throw') }}</h4>
                        <p>{{ __('public.stats.shots.line', ['made' => $resumenTemporada['totales']['ftm'] ?? 0, 'missed' => $resumenTemporada['totales']['ft_fallados'] ?? 0, 'attempted' => $resumenTemporada['totales']['fta'] ?? 0]) }}</p>
                        <strong>{{ $pctFt }}</strong>
                    </div>
                </div>

                {{-- KPIs acumulados y medias por partido --}}
                <div class="stats-kpi-grid">
                    <div class="stats-kpi"><span>{{ __('public.stats.kpis.points') }}</span><strong>{{ $resumenTemporada['totales']['pts'] ?? 0 }}</strong><small>{{ __('public.stats.average', ['value' => number_format($resumenTemporada['medias']['pts'] ?? 0, 1)]) }}</small></div>
                    <div class="stats-kpi"><span>{{ __('public.stats.kpis.rebounds') }}</span><strong>{{ $resumenTemporada['totales']['reb'] ?? 0 }}</strong><small>{{ __('public.stats.average', ['value' => number_format($resumenTemporada['medias']['reb'] ?? 0, 1)]) }}</small></div>
                    <div class="stats-kpi"><span>{{ __('public.stats.kpis.assists') }}</span><strong>{{ $resumenTemporada['totales']['ast'] ?? 0 }}</strong><small>{{ __('public.stats.average', ['value' => number_format($resumenTemporada['medias']['ast'] ?? 0, 1)]) }}</small></div>
                    <div class="stats-kpi"><span>{{ __('public.stats.kpis.steals') }}</span><strong>{{ $resumenTemporada['totales']['stl'] ?? 0 }}</strong><small>{{ __('public.stats.average', ['value' => number_format($resumenTemporada['medias']['stl'] ?? 0, 1)]) }}</small></div>
                    <div class="stats-kpi"><span>{{ __('public.stats.kpis.blocks') }}</span><strong>{{ $resumenTemporada['totales']['blk'] ?? 0 }}</strong><small>{{ __('public.stats.average', ['value' => number_format($resumenTemporada['medias']['blk'] ?? 0, 1)]) }}</small></div>
                    <div class="stats-kpi"><span>{{ __('public.stats.kpis.turnovers') }}</span><strong>{{ $resumenTemporada['totales']['tov'] ?? 0 }}</strong><small>{{ __('public.stats.average', ['value' => number_format($resumenTemporada['medias']['tov'] ?? 0, 1)]) }}</small></div>
                    <div class="stats-kpi"><span>{{ __('public.stats.kpis.fouls') }}</span><strong>{{ $resumenTemporada['totales']['pf'] ?? 0 }}</strong><small>{{ __('public.stats.average', ['value' => number_format($resumenTemporada['medias']['pf'] ?? 0, 1)]) }}</small></div>
                    <div class="stats-kpi"><span>{{ __('public.stats.kpis.tech_fouls') }}</span><strong>{{ $resumenTemporada['totales']['tf'] ?? 0 }}</strong><small>{{ __('public.stats.average', ['value' => number_format($resumenTemporada['medias']['tf'] ?? 0, 1)]) }}</small></div>
                    <div class="stats-kpi"><span>{{ __('public.stats.kpis.off_rebounds') }}</span><strong>{{ $resumenTemporada['totales']['reb_of'] ?? 0 }}</strong><small>{{ __('public.stats.average', ['value' => number_format($resumenTemporada['medias']['reb_of'] ?? 0, 1)]) }}</small></div>
                    <div class="stats-kpi"><span>{{ __('public.stats.kpis.def_rebounds') }}</span><strong>{{ $resumenTemporada['totales']['reb_def'] ?? 0 }}</strong><small>{{ __('public.stats.average', ['value' => number_format($resumenTemporada['medias']['reb_def'] ?? 0, 1)]) }}</small></div>
                    <div class="stats-kpi"><span>{{ __('public.stats.kpis.ast_to_ratio') }}</span><strong>{{ isset($resumenTemporada['ratios']['ast_per']) && $resumenTemporada['ratios']['ast_per'] !== null ? number_format($resumenTemporada['ratios']['ast_per'], 2) : '-' }}</strong><small>{{ __('public.stats.kpis.ast_to_ratio_caption') }}</small></div>
                </div>

                {{-- Reparto de puntos por cuarto y prórroga en toda la temporada --}}
                <div class="stats-quarters">
                    <h4>{{ __('public.stats.quarters.title') }}</h4>
                    <div class="stats-quarters-grid">
                        <div class="stats-quarter-item"><span>Q1</span><strong>{{ $resumenTemporada['totales']['q1'] ?? 0 }}</strong><small>{{ __('public.stats.average', ['value' => number_format($resumenTemporada['medias']['q1'] ?? 0, 1)]) }}</small></div>
                        <div class="stats-quarter-item"><span>Q2</span><strong>{{ $resumenTemporada['totales']['q2'] ?? 0 }}</strong><small>{{ __('public.stats.average', ['value' => number_format($resumenTemporada['medias']['q2'] ?? 0, 1)]) }}</small></div>
                        <div class="stats-quarter-item"><span>Q3</span><strong>{{ $resumenTemporada['totales']['q3'] ?? 0 }}</strong><small>{{ __('public.stats.average', ['value' => number_format($resumenTemporada['medias']['q3'] ?? 0, 1)]) }}</small></div>
                        <div class="stats-quarter-item"><span>Q4</span><strong>{{ $resumenTemporada['totales']['q4'] ?? 0 }}</strong><small>{{ __('public.stats.average', ['value' => number_format($resumenTemporada['medias']['q4'] ?? 0, 1)]) }}</small></div>
                        <div class="stats-quarter-item"><span>PR</span><strong>{{ $resumenTemporada['totales']['pr'] ?? 0 }}</strong><small>{{ __('public.stats.quarters.overtime_line', ['avg' => number_format($resumenTemporada['medias']['pr'] ?? 0, 1), 'count' => (int) ($resumenTemporada['partidos_con_prorroga'] ?? 0)]) }}</small></div>
                    </div>
                </div>
            </article>
        @endif
    @else
        {{-- Estado vacío si no hay equipos locales con datos públicos --}}
        <div class="estado-vacio estado-vacio-merchan-gap estado-vacio-merchan-gap-estadisticas">
            <div class="estado-vacio-icono">
                <i class="fas fa-chart-bar"></i>
            </div>
            <h3>{{ __('public.stats.empty.title') }}</h3>
            <p>{{ __('public.stats.empty.text') }}</p>
        </div>
    @endif
</section>
@endsection
