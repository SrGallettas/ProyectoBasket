{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

{{-- CSS reutilizado para la parte visual de estadísticas --}}
@push('styles')
<x-page-style file="estadisticas.css" />
@endpush

@section('title', __('public.match_detail.meta.title'))
@section('meta_description', __('public.match_detail.meta.description', ['local' => $partido->equipoLocal->nombre, 'visitor' => $partido->equipoVisitante->nombre]))

@section('contenido')
{{-- Helpers locales para limpiar nulos, calcular porcentajes y leer cuartos --}}
@php
    $safe = fn ($value) => is_null($value) ? 0 : (int) $value;

    $pct = function ($made, $attempted) use ($safe) {
        $made = $safe($made);
        $attempted = $safe($attempted);

        if ($attempted === 0) {
            return '0%';
        }

        return number_format(($made / $attempted) * 100, 2) . '%';
    };

    $quarterValue = function ($stat, $periodo) use ($safe) {
        if (!$stat) {
            return 0;
        }

        $cuarto = $stat->cuartos->first(function ($item) use ($stat, $periodo) {
            return (int) $item->equipo_id === (int) $stat->equipo_id && (int) $item->periodo === (int) $periodo;
        });

        return $safe(optional($cuarto)->puntos);
    };
@endphp

{{-- Hero principal del detalle del partido --}}
<x-public-news-hero
    :badge="__('public.match_detail.hero.badge')"
    :title="__('public.match_detail.hero.title')"
    subtitle="{{ $partido->equipoLocal->nombre }} vs {{ $partido->equipoVisitante->nombre }}"
/>

{{-- Contenido principal del partido: marcador, gráfico, tabla y cuartos --}}
<section class="contenedor-estadisticas">
    @if(!$statsLocal || !$statsVisitante)
        {{-- Estado vacío si el partido aún no tiene estadísticas completas --}}
        <div class="estado-vacio">
            <div class="estado-vacio-icono">
                <i class="fas fa-chart-bar"></i>
            </div>
            <h3>{{ __('public.match_detail.empty.title') }}</h3>
            <p>{{ __('public.match_detail.empty.text') }}</p>
            <a href="{{ route('basket.partidos') }}" class="estado-vacio-accion">{{ __('public.match_detail.empty.cta') }}</a>
        </div>
    @else
        {{-- Cabecera con escudos, marcador final y fecha del encuentro --}}
        <div class="estadisticas-partido-head">
            <div class="equipo-head">
                <img src="{{ $partido->equipoLocal->imagen_club_url }}" alt="{{ $partido->equipoLocal->nombre }}" onerror="this.onerror=null;this.src='{{ asset('img/club_14.png') }}';">
                <span>{{ $partido->equipoLocal->nombre }}</span>
            </div>

            <div class="marcador-head">
                <strong>{{ $safe($statsLocal->puntos_totales) }} - {{ $safe($statsVisitante->puntos_totales) }}</strong>
                <small>{{ $partido->categoria }} | {{ \Carbon\Carbon::parse($partido->fecha_hora)->format('d/m/Y H:i') }}</small>
            </div>

            <div class="equipo-head">
                <img src="{{ $partido->equipoVisitante->imagen_club_url }}" alt="{{ $partido->equipoVisitante->nombre }}" onerror="this.onerror=null;this.src='{{ asset('img/club_14.png') }}';">
                <span>{{ $partido->equipoVisitante->nombre }}</span>
            </div>
        </div>

        {{-- Preparación de datos para el gráfico comparativo tipo Flashscore --}}
        @php
            $barWidth = function ($a, $b) {
                $total = $a + $b;
                if ($total === 0) return [50, 50];
                return [round(($a / $total) * 100), round(($b / $total) * 100)];
            };

            $pctNum = function ($made, $attempted) use ($safe) {
                $made = $safe($made);
                $attempted = $safe($attempted);
                return $attempted === 0 ? 0 : round(($made / $attempted) * 100, 1);
            };

            $rebLocal = $safe($statsLocal->reb_of) + $safe($statsLocal->reb_def);
            $rebVis   = $safe($statsVisitante->reb_of) + $safe($statsVisitante->reb_def);

            $grafStats = [
                // ── Tiros de campo ──
                ['label' => __('public.match_detail.stats.fga'),  'local' => $safe($statsLocal->fga),  'vis' => $safe($statsVisitante->fga),  'fmt' => '', 'sep' => __('public.match_detail.sections.field_goals')],
                ['label' => __('public.match_detail.stats.fgm'),    'local' => $safe($statsLocal->fgm),  'vis' => $safe($statsVisitante->fgm),  'fmt' => ''],
                ['label' => __('public.match_detail.stats.fg_pct'),     'local' => $pctNum($statsLocal->fgm, $statsLocal->fga),  'vis' => $pctNum($statsVisitante->fgm, $statsVisitante->fga),  'fmt' => '%'],
                // ── Tiros de 2 ──
                ['label' => __('public.match_detail.stats.t2a'),   'local' => $safe($statsLocal->t2a),  'vis' => $safe($statsVisitante->t2a),  'fmt' => '', 'sep' => __('public.match_detail.sections.two_points')],
                ['label' => __('public.match_detail.stats.t2m'),     'local' => $safe($statsLocal->t2m),  'vis' => $safe($statsVisitante->t2m),  'fmt' => ''],
                ['label' => __('public.match_detail.stats.t2_pct'),         'local' => $pctNum($statsLocal->t2m, $statsLocal->t2a),  'vis' => $pctNum($statsVisitante->t2m, $statsVisitante->t2a),  'fmt' => '%'],
                // ── Tiros de 3 ──
                ['label' => __('public.match_detail.stats.t3a'),   'local' => $safe($statsLocal->t3a),  'vis' => $safe($statsVisitante->t3a),  'fmt' => '', 'sep' => __('public.match_detail.sections.three_points')],
                ['label' => __('public.match_detail.stats.t3m'),     'local' => $safe($statsLocal->t3m),  'vis' => $safe($statsVisitante->t3m),  'fmt' => ''],
                ['label' => __('public.match_detail.stats.t3_pct'),         'local' => $pctNum($statsLocal->t3m, $statsLocal->t3a),  'vis' => $pctNum($statsVisitante->t3m, $statsVisitante->t3a),  'fmt' => '%'],
                // ── Tiros libres ──
                ['label' => __('public.match_detail.stats.fta'), 'local' => $safe($statsLocal->fta),  'vis' => $safe($statsVisitante->fta),  'fmt' => '', 'sep' => __('public.match_detail.sections.free_throws')],
                ['label' => __('public.match_detail.stats.ftm'),   'local' => $safe($statsLocal->ftm),  'vis' => $safe($statsVisitante->ftm),  'fmt' => ''],
                ['label' => __('public.match_detail.stats.ft_pct'),       'local' => $pctNum($statsLocal->ftm, $statsLocal->fta),  'vis' => $pctNum($statsVisitante->ftm, $statsVisitante->fta),  'fmt' => '%'],
                // ── Rebotes ──
                ['label' => __('public.match_detail.stats.reb_total'),   'local' => $rebLocal, 'vis' => $rebVis, 'fmt' => '', 'sep' => __('public.match_detail.sections.rebounds')],
                ['label' => __('public.match_detail.stats.reb_off'),    'local' => $safe($statsLocal->reb_of),  'vis' => $safe($statsVisitante->reb_of),  'fmt' => ''],
                ['label' => __('public.match_detail.stats.reb_def'),   'local' => $safe($statsLocal->reb_def), 'vis' => $safe($statsVisitante->reb_def), 'fmt' => ''],
                // ── Otras ──
                ['label' => __('public.match_detail.stats.ast'),    'local' => $safe($statsLocal->ast), 'vis' => $safe($statsVisitante->ast), 'fmt' => '', 'sep' => __('public.match_detail.sections.other')],
                ['label' => __('public.match_detail.stats.blk'),        'local' => $safe($statsLocal->blk), 'vis' => $safe($statsVisitante->blk), 'fmt' => ''],
                ['label' => __('public.match_detail.stats.stl'), 'local' => $safe($statsLocal->stl), 'vis' => $safe($statsVisitante->stl), 'fmt' => ''],
                ['label' => __('public.match_detail.stats.tov'),       'local' => $safe($statsLocal->tov), 'vis' => $safe($statsVisitante->tov), 'fmt' => ''],
                ['label' => __('public.match_detail.stats.pf'),         'local' => $safe($statsLocal->pf),  'vis' => $safe($statsVisitante->pf),  'fmt' => ''],
            ];
        @endphp

        {{-- Gráfico comparativo de métricas entre local y visitante --}}
        <div class="grafico-estadisticas-wrap">
            <div class="grafico-titulo">
                <span class="grafico-titulo-equipo">{{ $partido->equipoLocal->nombre }}</span>
                <span>{{ __('public.match_detail.hero.stats_center') }}</span>
                <span class="grafico-titulo-equipo">{{ $partido->equipoVisitante->nombre }}</span>
            </div>

            @foreach ($grafStats as $stat)
                @php
                    [$wL, $wV] = $barWidth($stat['local'], $stat['vis']);
                    $isWinnerLocal = $stat['local'] > $stat['vis'];
                    $isWinnerVis   = $stat['vis'] > $stat['local'];
                    $displayLocal = $stat['local'] . $stat['fmt'];
                    $displayVis   = $stat['vis'] . $stat['fmt'];
                @endphp

                {{-- Separador visual entre bloques de estadísticas --}}
                @if (!empty($stat['sep']))
                    <div class="grafico-separador">{{ $stat['sep'] }}</div>
                @endif

                <div class="grafico-fila">
                    {{-- Valor local --}}
                    <div class="grafico-valor grafico-valor-local {{ $isWinnerLocal ? 'grafico-ganador' : '' }}">
                        {{ $displayLocal }}
                    </div>

                    {{-- Barra local (crece hacia la derecha, pegada al label) --}}
                    <div class="grafico-barra-container-local">
                        <div class="grafico-barra grafico-barra-local" style="width: {{ $wL }}%"></div>
                    </div>

                    {{-- Etiqueta central --}}
                    <div class="grafico-label">{{ $stat['label'] }}</div>

                    {{-- Barra visitante (crece hacia la izquierda, pegada al label) --}}
                    <div class="grafico-barra-container-visitante">
                        <div class="grafico-barra grafico-barra-visitante" style="width: {{ $wV }}%"></div>
                    </div>

                    {{-- Valor visitante --}}
                    <div class="grafico-valor grafico-valor-visitante {{ $isWinnerVis ? 'grafico-ganador' : '' }}">
                        {{ $displayVis }}
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Tabla comparativa completa de métricas del partido --}}
        <div class="tabla-estadisticas-wrap">
            <table class="tabla-estadisticas-comparada">
                <thead>
                    <tr>
                        <th class="col-local">{{ $partido->equipoLocal->nombre }}</th>
                        <th class="col-metrica">{{ __('public.match_detail.table.metric') }}</th>
                        <th class="col-visitante">{{ $partido->equipoVisitante->nombre }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>{{ $safe($statsLocal->fga) }}</td><td>{{ __('public.match_detail.stats.fga') }}</td><td>{{ $safe($statsVisitante->fga) }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->fgm) }}</td><td>{{ __('public.match_detail.stats.fgm') }}</td><td>{{ $safe($statsVisitante->fgm) }}</td></tr>
                    <tr><td>{{ $pct($statsLocal->fgm, $statsLocal->fga) }}</td><td>{{ __('public.match_detail.stats.fg_pct') }}</td><td>{{ $pct($statsVisitante->fgm, $statsVisitante->fga) }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->t2a) }}</td><td>{{ __('public.match_detail.stats.t2a') }}</td><td>{{ $safe($statsVisitante->t2a) }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->t2m) }}</td><td>{{ __('public.match_detail.stats.t2m') }}</td><td>{{ $safe($statsVisitante->t2m) }}</td></tr>
                    <tr><td>{{ $pct($statsLocal->t2m, $statsLocal->t2a) }}</td><td>{{ __('public.match_detail.stats.t2_pct') }}</td><td>{{ $pct($statsVisitante->t2m, $statsVisitante->t2a) }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->t3a) }}</td><td>{{ __('public.match_detail.stats.t3a') }}</td><td>{{ $safe($statsVisitante->t3a) }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->t3m) }}</td><td>{{ __('public.match_detail.stats.t3m') }}</td><td>{{ $safe($statsVisitante->t3m) }}</td></tr>
                    <tr><td>{{ $pct($statsLocal->t3m, $statsLocal->t3a) }}</td><td>{{ __('public.match_detail.stats.t3_pct') }}</td><td>{{ $pct($statsVisitante->t3m, $statsVisitante->t3a) }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->fta) }}</td><td>{{ __('public.match_detail.stats.fta') }}</td><td>{{ $safe($statsVisitante->fta) }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->ftm) }}</td><td>{{ __('public.match_detail.stats.ftm') }}</td><td>{{ $safe($statsVisitante->ftm) }}</td></tr>
                    <tr><td>{{ $pct($statsLocal->ftm, $statsLocal->fta) }}</td><td>{{ __('public.match_detail.stats.ft_pct') }}</td><td>{{ $pct($statsVisitante->ftm, $statsVisitante->fta) }}</td></tr>

                    <tr class="separador-row"><td colspan="3">{{ __('public.match_detail.sections.rebounds') }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->reb_of) }}</td><td>{{ __('public.match_detail.stats.reb_off') }}</td><td>{{ $safe($statsVisitante->reb_of) }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->reb_def) }}</td><td>{{ __('public.match_detail.stats.reb_def') }}</td><td>{{ $safe($statsVisitante->reb_def) }}</td></tr>
                    <tr>
                        <td>{{ $safe($statsLocal->reb_of) + $safe($statsLocal->reb_def) }}</td>
                        <td>{{ __('public.match_detail.stats.reb_total') }}</td>
                        <td>{{ $safe($statsVisitante->reb_of) + $safe($statsVisitante->reb_def) }}</td>
                    </tr>

                    <tr class="separador-row"><td colspan="3">{{ __('public.match_detail.sections.other') }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->ast) }}</td><td>{{ __('public.match_detail.stats.ast') }}</td><td>{{ $safe($statsVisitante->ast) }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->blk) }}</td><td>{{ __('public.match_detail.stats.blk') }}</td><td>{{ $safe($statsVisitante->blk) }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->tov) }}</td><td>{{ __('public.match_detail.stats.tov') }}</td><td>{{ $safe($statsVisitante->tov) }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->stl) }}</td><td>{{ __('public.match_detail.stats.stl') }}</td><td>{{ $safe($statsVisitante->stl) }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->pf) }}</td><td>{{ __('public.match_detail.stats.pf') }}</td><td>{{ $safe($statsVisitante->pf) }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->tf) }}</td><td>{{ __('public.match_detail.stats.tf') }}</td><td>{{ $safe($statsVisitante->tf) }}</td></tr>
                </tbody>
            </table>
        </div>

        {{-- Tabla final con los puntos por cuarto y prórroga --}}
        <div class="tabla-cuartos-wrap">
            <h2 class=puntos-por-parte>{{ __('public.match_detail.quarters.title') }}</h2>
            <table class="tabla-cuartos">
                <thead>
                    <tr>
                        <th>{{ __('public.match_detail.table.team') }}</th>
                        <th>Q1</th>
                        <th>Q2</th>
                        <th>Q3</th>
                        <th>Q4</th>
                        <th>PR</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $partido->equipoLocal->nombre }}</td>
                        <td>{{ $quarterValue($statsLocal, 1) }}</td>
                        <td>{{ $quarterValue($statsLocal, 2) }}</td>
                        <td>{{ $quarterValue($statsLocal, 3) }}</td>
                        <td>{{ $quarterValue($statsLocal, 4) }}</td>
                        <td>{{ $quarterValue($statsLocal, 5) }}</td>
                        <td>{{ $safe($statsLocal->puntos_totales) }}</td>
                    </tr>
                    <tr>
                        <td>{{ $partido->equipoVisitante->nombre }}</td>
                        <td>{{ $quarterValue($statsVisitante, 1) }}</td>
                        <td>{{ $quarterValue($statsVisitante, 2) }}</td>
                        <td>{{ $quarterValue($statsVisitante, 3) }}</td>
                        <td>{{ $quarterValue($statsVisitante, 4) }}</td>
                        <td>{{ $quarterValue($statsVisitante, 5) }}</td>
                        <td>{{ $safe($statsVisitante->puntos_totales) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection
