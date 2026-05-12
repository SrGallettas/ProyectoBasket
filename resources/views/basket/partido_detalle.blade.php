{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

{{-- CSS reutilizado para la parte visual de estadísticas --}}
@push('styles')
<x-page-style file="estadisticas.css" />
@endpush

@section('title', 'Detalle Partido - Bellreguard CB')

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
    badge="DETALLE OFICIAL"
    title="Detalle del partido"
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
            <h3>Sin estadisticas cargadas todavia</h3>
            <p>Este partido aun no tiene estadisticas completas en el panel de administracion.</p>
            <a href="{{ route('basket.partidos') }}" class="estado-vacio-accion">Volver a partidos</a>
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
                ['label' => 'T. campo intentados',  'local' => $safe($statsLocal->fga),  'vis' => $safe($statsVisitante->fga),  'fmt' => '', 'sep' => 'Tiros de campo'],
                ['label' => 'T. campo anotados',    'local' => $safe($statsLocal->fgm),  'vis' => $safe($statsVisitante->fgm),  'fmt' => ''],
                ['label' => '% Tiros de campo',     'local' => $pctNum($statsLocal->fgm, $statsLocal->fga),  'vis' => $pctNum($statsVisitante->fgm, $statsVisitante->fga),  'fmt' => '%'],
                // ── Tiros de 2 ──
                ['label' => 'T. de 2 intentados',   'local' => $safe($statsLocal->t2a),  'vis' => $safe($statsVisitante->t2a),  'fmt' => '', 'sep' => 'Tiros de 2 puntos'],
                ['label' => 'T. de 2 anotados',     'local' => $safe($statsLocal->t2m),  'vis' => $safe($statsVisitante->t2m),  'fmt' => ''],
                ['label' => '% Tiros de 2',         'local' => $pctNum($statsLocal->t2m, $statsLocal->t2a),  'vis' => $pctNum($statsVisitante->t2m, $statsVisitante->t2a),  'fmt' => '%'],
                // ── Tiros de 3 ──
                ['label' => 'T. de 3 intentados',   'local' => $safe($statsLocal->t3a),  'vis' => $safe($statsVisitante->t3a),  'fmt' => '', 'sep' => 'Tiros de 3 puntos'],
                ['label' => 'T. de 3 anotados',     'local' => $safe($statsLocal->t3m),  'vis' => $safe($statsVisitante->t3m),  'fmt' => ''],
                ['label' => '% Tiros de 3',         'local' => $pctNum($statsLocal->t3m, $statsLocal->t3a),  'vis' => $pctNum($statsVisitante->t3m, $statsVisitante->t3a),  'fmt' => '%'],
                // ── Tiros libres ──
                ['label' => 'T. libres intentados', 'local' => $safe($statsLocal->fta),  'vis' => $safe($statsVisitante->fta),  'fmt' => '', 'sep' => 'Tiros libres'],
                ['label' => 'T. libres anotados',   'local' => $safe($statsLocal->ftm),  'vis' => $safe($statsVisitante->ftm),  'fmt' => ''],
                ['label' => '% Tiros libres',       'local' => $pctNum($statsLocal->ftm, $statsLocal->fta),  'vis' => $pctNum($statsVisitante->ftm, $statsVisitante->fta),  'fmt' => '%'],
                // ── Rebotes ──
                ['label' => 'Rebotes totales',   'local' => $rebLocal, 'vis' => $rebVis, 'fmt' => '', 'sep' => 'Rebotes'],
                ['label' => 'Reb. ofensivos',    'local' => $safe($statsLocal->reb_of),  'vis' => $safe($statsVisitante->reb_of),  'fmt' => ''],
                ['label' => 'Reb. defensivos',   'local' => $safe($statsLocal->reb_def), 'vis' => $safe($statsVisitante->reb_def), 'fmt' => ''],
                // ── Otras ──
                ['label' => 'Asistencias',    'local' => $safe($statsLocal->ast), 'vis' => $safe($statsVisitante->ast), 'fmt' => '', 'sep' => 'Otras'],
                ['label' => 'Tapones',        'local' => $safe($statsLocal->blk), 'vis' => $safe($statsVisitante->blk), 'fmt' => ''],
                ['label' => 'Recuperaciones', 'local' => $safe($statsLocal->stl), 'vis' => $safe($statsVisitante->stl), 'fmt' => ''],
                ['label' => 'Pérdidas',       'local' => $safe($statsLocal->tov), 'vis' => $safe($statsVisitante->tov), 'fmt' => ''],
                ['label' => 'Faltas',         'local' => $safe($statsLocal->pf),  'vis' => $safe($statsVisitante->pf),  'fmt' => ''],
            ];
        @endphp

        {{-- Gráfico comparativo de métricas entre local y visitante --}}
        <div class="grafico-estadisticas-wrap">
            <div class="grafico-titulo">
                <span class="grafico-titulo-equipo">{{ $partido->equipoLocal->nombre }}</span>
                <span>Estadísticas</span>
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
                        <th class="col-metrica">Metrica</th>
                        <th class="col-visitante">{{ $partido->equipoVisitante->nombre }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>{{ $safe($statsLocal->fga) }}</td><td>Tiros de campo intentados</td><td>{{ $safe($statsVisitante->fga) }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->fgm) }}</td><td>Tiros de campo anotados</td><td>{{ $safe($statsVisitante->fgm) }}</td></tr>
                    <tr><td>{{ $pct($statsLocal->fgm, $statsLocal->fga) }}</td><td>% Tiros de campo</td><td>{{ $pct($statsVisitante->fgm, $statsVisitante->fga) }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->t2a) }}</td><td>Tiros de 2 intentados</td><td>{{ $safe($statsVisitante->t2a) }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->t2m) }}</td><td>Tiros de 2 anotados</td><td>{{ $safe($statsVisitante->t2m) }}</td></tr>
                    <tr><td>{{ $pct($statsLocal->t2m, $statsLocal->t2a) }}</td><td>% Tiros de 2</td><td>{{ $pct($statsVisitante->t2m, $statsVisitante->t2a) }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->t3a) }}</td><td>Tiros de 3 intentados</td><td>{{ $safe($statsVisitante->t3a) }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->t3m) }}</td><td>Tiros de 3 anotados</td><td>{{ $safe($statsVisitante->t3m) }}</td></tr>
                    <tr><td>{{ $pct($statsLocal->t3m, $statsLocal->t3a) }}</td><td>% Tiros de 3</td><td>{{ $pct($statsVisitante->t3m, $statsVisitante->t3a) }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->fta) }}</td><td>Tiros libres intentados</td><td>{{ $safe($statsVisitante->fta) }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->ftm) }}</td><td>Tiros libres anotados</td><td>{{ $safe($statsVisitante->ftm) }}</td></tr>
                    <tr><td>{{ $pct($statsLocal->ftm, $statsLocal->fta) }}</td><td>% Tiros libres</td><td>{{ $pct($statsVisitante->ftm, $statsVisitante->fta) }}</td></tr>

                    <tr class="separador-row"><td colspan="3">Rebotes</td></tr>
                    <tr><td>{{ $safe($statsLocal->reb_of) }}</td><td>Rebotes ofensivos</td><td>{{ $safe($statsVisitante->reb_of) }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->reb_def) }}</td><td>Rebotes defensivos</td><td>{{ $safe($statsVisitante->reb_def) }}</td></tr>
                    <tr>
                        <td>{{ $safe($statsLocal->reb_of) + $safe($statsLocal->reb_def) }}</td>
                        <td>Rebotes totales</td>
                        <td>{{ $safe($statsVisitante->reb_of) + $safe($statsVisitante->reb_def) }}</td>
                    </tr>

                    <tr class="separador-row"><td colspan="3">Otras</td></tr>
                    <tr><td>{{ $safe($statsLocal->ast) }}</td><td>Asistencias</td><td>{{ $safe($statsVisitante->ast) }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->blk) }}</td><td>Tapones</td><td>{{ $safe($statsVisitante->blk) }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->tov) }}</td><td>Perdidas</td><td>{{ $safe($statsVisitante->tov) }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->stl) }}</td><td>Recuperaciones</td><td>{{ $safe($statsVisitante->stl) }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->pf) }}</td><td>Faltas</td><td>{{ $safe($statsVisitante->pf) }}</td></tr>
                    <tr><td>{{ $safe($statsLocal->tf) }}</td><td>Faltas tecnicas</td><td>{{ $safe($statsVisitante->tf) }}</td></tr>
                </tbody>
            </table>
        </div>

        {{-- Tabla final con los puntos por cuarto y prórroga --}}
        <div class="tabla-cuartos-wrap">
            <h2 class=puntos-por-parte>Puntos por partes</h2>
            <table class="tabla-cuartos">
                <thead>
                    <tr>
                        <th>Equipo</th>
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
