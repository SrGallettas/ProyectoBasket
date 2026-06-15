@extends('layouts.admin')

@section('title', __('admin.standings.meta.show_title'))

@use('Carbon\Carbon')

@section('contenido_admin')
<x-admin-show-header
    :titulo="$clasificacion->nombre"
    :subtitulo="__('admin.standings.show.subtitle', ['category' => $clasificacion->categoria ?: __('admin.standings.show.no_category'), 'season' => $clasificacion->temporada ?: __('admin.standings.show.no_season')])"
    :editRoute="route('clasificaciones.edit', $clasificacion->id)"
    :deleteRoute="route('clasificaciones.destroy', $clasificacion->id)"
    :indexRoute="route('clasificaciones.index')"
    :confirmMessage="__('admin.standings.actions.confirm_delete_detail')"
/>

@if(session('mensaje'))
    <div class="admin-alert-success">
        <i class="fas fa-check-circle"></i> {{ session('mensaje') }}
    </div>
@endif

<div class="pizarra-admin" style="margin-bottom: 20px;">
    <form action="{{ route('clasificaciones.recalcular', $clasificacion->id) }}" method="POST" class="admin-form-inline">
        @csrf
        <button type="submit" class="btn-nuevo">
            <i class="fas fa-sync-alt"></i> {{ __('admin.standings.show.recalculate') }}
        </button>
    </form>
</div>

<div class="pizarra-admin">
    <form action="{{ route('clasificaciones.show', $clasificacion->id) }}" method="GET" class="form-buscador" style="margin-bottom: 20px;">
        <div class="input-grupal">
            <select name="jornada" class="input-search">
                @if($jornadas->isEmpty())
                    <option value="">{{ __('admin.standings.filters.no_rounds') }}</option>
                @else
                    @foreach($jornadas as $jornada)
                        <option value="{{ $jornada }}" @selected((int) $jornadaSeleccionada === (int) $jornada)>{{ __('admin.standings.filters.round', ['round' => $jornada]) }}</option>
                    @endforeach
                @endif
            </select>
            <button type="submit" class="btn-buscar" title="{{ __('admin.standings.filters.filter_round') }}">
                <i class="fas fa-filter"></i>
            </button>
        </div>
    </form>

    <table class="tabla-admin">
        <thead>
            <tr>
                <th>{{ __('admin.standings.fields.position') }}</th>
                <th>{{ __('admin.standings.fields.team') }}</th>
                <th>{{ __('admin.standings.fields.games_played') }}</th>
                <th>{{ __('admin.standings.fields.won') }}</th>
                <th>{{ __('admin.standings.fields.lost') }}</th>
                <th>{{ __('admin.standings.fields.points_for') }}</th>
                <th>{{ __('admin.standings.fields.points_against') }}</th>
                <th>{{ __('admin.standings.fields.diff') }}</th>
                <th>{{ __('admin.standings.fields.points') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($filas as $idx => $fila)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td><strong>{{ $fila->equipo->nombre }}</strong></td>
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
                    <td colspan="9" class="admin-empty-cell">
                        <i class="fas fa-info-circle"></i> {{ __('admin.standings.empty.table') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="pizarra-admin" style="margin-top: 20px;">
    <h3 style="margin-bottom: 14px;">{{ __('admin.standings.show.round_matches') }}</h3>
    <table class="tabla-admin">
        <thead>
            <tr>
                <th>{{ __('admin.standings.fields.date') }}</th>
                <th>{{ __('admin.standings.fields.local') }}</th>
                <th>{{ __('admin.standings.fields.visitor') }}</th>
                <th>{{ __('admin.standings.fields.result') }}</th>
                <th>{{ __('admin.standings.fields.place') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($partidos as $partido)
                @php
                    $statsLocal = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_local_id);
                    $statsVisitante = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_visitante_id);
                    $marcador = optional($statsLocal)->puntos_totales !== null && optional($statsVisitante)->puntos_totales !== null
                        ? ((int) $statsLocal->puntos_totales . ' - ' . (int) $statsVisitante->puntos_totales)
                        : __('admin.standings.show.pending');
                @endphp
                <tr>
                    <td>{{ Carbon::parse($partido->fecha_hora)->format('d/m/Y H:i') }}</td>
                    <td>{{ $partido->equipoLocal->nombre }}</td>
                    <td>{{ $partido->equipoVisitante->nombre }}</td>
                    <td>{{ $marcador }}</td>
                    <td>{{ $partido->lugar }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="admin-empty-cell">
                        <i class="fas fa-info-circle"></i> {{ __('admin.standings.empty.matches') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
