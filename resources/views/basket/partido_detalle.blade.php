@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/paginas/estadisticas.css') }}" />
@endpush

@section('title', 'Detalle Partido - Bellreguard Club de Basket')

@section('contenido')
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

<x-public-news-hero
    badge="DETALLE OFICIAL"
    title="Detalle del partido"
    subtitle="{{ $partido->equipoLocal->nombre }} vs {{ $partido->equipoVisitante->nombre }}"
/>

<section class="contenedor-estadisticas">
    @if(!$statsLocal || !$statsVisitante)
        <div class="estado-vacio">
            <div class="estado-vacio-icono">
                <i class="fas fa-chart-bar"></i>
            </div>
            <h3>Sin estadisticas cargadas todavia</h3>
            <p>Este partido aun no tiene estadisticas completas en el panel de administracion.</p>
            <a href="{{ route('basket.partidos') }}" class="estado-vacio-accion">Volver a partidos</a>
        </div>
    @else
        <div class="estadisticas-partido-head">
            <div class="equipo-head">
                <img src="{{ asset('escudos/' . $partido->equipoLocal->imagen_club) }}" alt="{{ $partido->equipoLocal->nombre }}">
                <span>{{ $partido->equipoLocal->nombre }}</span>
            </div>

            <div class="marcador-head">
                <strong>{{ $safe($statsLocal->puntos_totales) }} - {{ $safe($statsVisitante->puntos_totales) }}</strong>
                <small>{{ $partido->categoria }} | {{ \Carbon\Carbon::parse($partido->fecha_hora)->format('d/m/Y H:i') }}</small>
            </div>

            <div class="equipo-head">
                <img src="{{ asset('escudos/' . $partido->equipoVisitante->imagen_club) }}" alt="{{ $partido->equipoVisitante->nombre }}">
                <span>{{ $partido->equipoVisitante->nombre }}</span>
            </div>
        </div>

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

        <div class="tabla-cuartos-wrap">
            <h2>Puntos por partes</h2>
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
