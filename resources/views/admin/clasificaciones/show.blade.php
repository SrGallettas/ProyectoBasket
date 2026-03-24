@extends('layouts.admin')

@use('Carbon\Carbon')

@section('contenido_admin')
<x-admin-show-header
    :titulo="$clasificacion->nombre"
    :subtitulo="'Categoría: ' . ($clasificacion->categoria ?: 'Sin categoría') . ' · Temporada: ' . ($clasificacion->temporada ?: 'Sin temporada')"
    :editRoute="route('clasificaciones.edit', $clasificacion->id)"
    :deleteRoute="route('clasificaciones.destroy', $clasificacion->id)"
    :indexRoute="route('clasificaciones.index')"
    confirmMessage="¿Seguro que deseas eliminar esta clasificación?"
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
            <i class="fas fa-sync-alt"></i> Recalcular clasificación con partidos de liga
        </button>
    </form>
</div>

<div class="pizarra-admin">
    <form action="{{ route('clasificaciones.show', $clasificacion->id) }}" method="GET" class="form-buscador" style="margin-bottom: 20px;">
        <div class="input-grupal">
            <select name="jornada" class="input-search">
                @if($jornadas->isEmpty())
                    <option value="">Sin jornadas calculadas</option>
                @else
                    @foreach($jornadas as $jornada)
                        <option value="{{ $jornada }}" @selected((int) $jornadaSeleccionada === (int) $jornada)>Jornada {{ $jornada }}</option>
                    @endforeach
                @endif
            </select>
            <button type="submit" class="btn-buscar" title="Filtrar jornada">
                <i class="fas fa-filter"></i>
            </button>
        </div>
    </form>

    <table class="tabla-admin">
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
                        <i class="fas fa-info-circle"></i> No hay tabla calculada para esta jornada.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="pizarra-admin" style="margin-top: 20px;">
    <h3 style="margin-bottom: 14px;">Partidos de la jornada</h3>
    <table class="tabla-admin">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Local</th>
                <th>Visitante</th>
                <th>Resultado</th>
                <th>Lugar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($partidos as $partido)
                @php
                    $statsLocal = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_local_id);
                    $statsVisitante = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_visitante_id);
                    $marcador = optional($statsLocal)->puntos_totales !== null && optional($statsVisitante)->puntos_totales !== null
                        ? ((int) $statsLocal->puntos_totales . ' - ' . (int) $statsVisitante->puntos_totales)
                        : 'Pendiente';
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
                        <i class="fas fa-info-circle"></i> No hay partidos para este filtro.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
