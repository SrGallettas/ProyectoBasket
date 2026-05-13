@extends('layouts.admin')

@section('contenido_admin')
<x-admin-header
    :titulo="__('admin.standings.index.title')"
    :subtitulo="__('admin.standings.index.subtitle')"
    :rutaNuevo="route('clasificaciones.create')"
    :textoNuevo="__('admin.standings.index.new')"
    :rutaIndex="route('clasificaciones.index')"
/>

@if(session('mensaje'))
    <div class="admin-alert-success">
        <i class="fas fa-check-circle"></i> {{ session('mensaje') }}
    </div>
@endif

@php
    // Alterna asc/desc cuando se vuelve a pulsar una cabecera.
    $nextDirection = function (string $column) use ($sort, $direction) {
        return $sort === $column && $direction === 'asc' ? 'desc' : 'asc';
    };

    // La flecha solo se enseña cuando ya hay una ordenación pedida por URL.
    $hasActiveSort = request()->has('sort');

    $sortIndicator = function (string $column) use ($sort, $direction, $hasActiveSort) {
        if (!$hasActiveSort || $sort !== $column) {
            return '';
        }

        return $direction === 'asc' ? ' ▲' : ' ▼';
    };
@endphp

<div class="pizarra-admin">
    <table class="tabla-admin">
        <thead>
            <tr>
                <th>
                    <a
                        href="{{ route('clasificaciones.index', ['search' => $search, 'sort' => 'nombre', 'direction' => $nextDirection('nombre')]) }}"
                        style="color: inherit; text-decoration: none; font: inherit;"
                    >
                        {{ __('admin.standings.fields.name') }}{!! $sortIndicator('nombre') !!}
                    </a>
                </th>
                <th>
                    <a
                        href="{{ route('clasificaciones.index', ['search' => $search, 'sort' => 'categoria', 'direction' => $nextDirection('categoria')]) }}"
                        style="color: inherit; text-decoration: none; font: inherit;"
                    >
                        {{ __('admin.standings.fields.category') }}{!! $sortIndicator('categoria') !!}
                    </a>
                </th>
                <th>
                    <a
                        href="{{ route('clasificaciones.index', ['search' => $search, 'sort' => 'temporada', 'direction' => $nextDirection('temporada')]) }}"
                        style="color: inherit; text-decoration: none; font: inherit;"
                    >
                        {{ __('admin.standings.fields.season') }}{!! $sortIndicator('temporada') !!}
                    </a>
                </th>
                <th>
                    <a
                        href="{{ route('clasificaciones.index', ['search' => $search, 'sort' => 'equipos_count', 'direction' => $nextDirection('equipos_count')]) }}"
                        style="color: inherit; text-decoration: none; font: inherit;"
                    >
                        {{ __('admin.standings.fields.teams') }}{!! $sortIndicator('equipos_count') !!}
                    </a>
                </th>
                <th>
                    <a
                        href="{{ route('clasificaciones.index', ['search' => $search, 'sort' => 'activa', 'direction' => $nextDirection('activa')]) }}"
                        style="color: inherit; text-decoration: none; font: inherit;"
                    >
                        {{ __('admin.standings.fields.status') }}{!! $sortIndicator('activa') !!}
                    </a>
                </th>
                <th>
                    <a
                        href="{{ route('clasificaciones.index', ['search' => $search, 'sort' => 'created_at', 'direction' => $nextDirection('created_at')]) }}"
                        style="color: inherit; text-decoration: none; font: inherit;"
                    >
                        {{ __('admin.standings.fields.actions') }}{!! $sortIndicator('created_at') !!}
                    </a>
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse($clasificaciones as $clasificacion)
                <tr>
                    <td><strong>{{ $clasificacion->nombre }}</strong></td>
                    <td>{{ $clasificacion->categoria ?: __('admin.standings.fields.no_category') }}</td>
                    <td>{{ $clasificacion->temporada ?: '-' }}</td>
                    <td>{{ $clasificacion->equipos_count }}</td>
                    <td>{{ $clasificacion->activa ? __('admin.standings.fields.active') : __('admin.standings.fields.inactive') }}</td>
                    <td class="admin-actions-cell">
                        <div class="admin-row-actions">
                            <a href="{{ route('clasificaciones.show', $clasificacion->id) }}" class="btn-accion admin-view-btn" title="{{ __('admin.standings.actions.view') }}">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('clasificaciones.edit', $clasificacion->id) }}" class="btn-accion editar" title="{{ __('admin.standings.actions.edit') }}">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form action="{{ route('clasificaciones.destroy', $clasificacion->id) }}" method="POST" onsubmit="return confirm('{{ __('admin.standings.actions.confirm_delete') }}')" class="admin-form-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-accion borrar" title="{{ __('admin.standings.actions.delete') }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="admin-empty-cell">
                        <i class="fas fa-info-circle"></i> {{ __('admin.standings.empty.index') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="contenedor-paginacion">
        {{ $clasificaciones->links() }}
    </div>
</div>
@endsection
