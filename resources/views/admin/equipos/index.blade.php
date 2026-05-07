@extends('layouts.admin') {{-- Carga la base (el diseño general del panel) --}}

@section('contenido_admin') {{-- Aquí empieza el contenido que se mete dentro del diseño --}}

{{-- 1. USAMOS EL COMPONENTE (Sustituye al header y al buscador viejo) --}}
<x-admin-header 
    titulo="Gestión de Equipos" 
    subtitulo="Categorías y grupos del Bellreguard CB"
    :rutaNuevo="route('equipos.create')" 
    textoNuevo="Nuevo Equipo"
    :rutaIndex="route('equipos.index')"
/>

{{-- Mensaje de éxito: Solo aparece cuando creas, editas o borras algo correctamente --}}
@if(session('mensaje'))
    <div class="admin-alert-success">
        <i class="fas fa-check-circle"></i> {{ session('mensaje') }}
    </div>
@endif

@if($errors->has('delete'))
    <div class="cuadro-errores-admin">
        <strong>No se pudo completar la acción:</strong>
        <ul>
            <li>{{ $errors->first('delete') }}</li>
        </ul>
    </div>
@endif

@php
    // Helper simple para alternar asc/desc desde las cabeceras clicables.
    $nextDirection = function (string $column) use ($sort, $direction) {
        return $sort === $column && $direction === 'asc' ? 'desc' : 'asc';
    };

    // Solo enseñamos la flecha cuando el usuario ya ha pedido una ordenación.
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
                <th>Escudo</th>
                <th>
                    <a
                        href="{{ route('equipos.index', ['search' => $search, 'sort' => 'nombre', 'direction' => $nextDirection('nombre')]) }}"
                        style="color: inherit; text-decoration: none; font: inherit;"
                    >
                        Nombre Equipo{!! $sortIndicator('nombre') !!}
                    </a>
                </th>
                <th>
                    <a
                        href="{{ route('equipos.index', ['search' => $search, 'sort' => 'categoria', 'direction' => $nextDirection('categoria')]) }}"
                        style="color: inherit; text-decoration: none; font: inherit;"
                    >
                        Categoría{!! $sortIndicator('categoria') !!}
                    </a>
                </th>
                <th>
                    <a
                        href="{{ route('equipos.index', ['search' => $search, 'sort' => 'jugadores_count', 'direction' => $nextDirection('jugadores_count')]) }}"
                        style="color: inherit; text-decoration: none; font: inherit;"
                    >
                        Nº Jugadores{!! $sortIndicator('jugadores_count') !!}
                    </a>
                </th>
                <th>
                    <a
                        href="{{ route('equipos.index', ['search' => $search, 'sort' => 'oculto', 'direction' => $nextDirection('oculto')]) }}"
                        style="color: inherit; text-decoration: none; font: inherit;"
                    >
                        Acciones{!! $sortIndicator('oculto') !!}
                    </a>
                </th>
            </tr>
        </thead>
        <tbody>
            {{-- BUCLE: Repite la fila por cada equipo que haya en la base de datos --}}
            @forelse($equipos as $equipo)
            <tr>
                <td>
                    <div class="contenedor-escudo admin-photo-frame">
                        {{-- Muestra la foto guardada en la carpeta public/escudos --}}
                        <img src="{{ $equipo->imagen_club_url }}" alt="Escudo" width="60" height="60" class="admin-img-contain admin-photo-fill">
                    </div>
                </td>
                <td><strong>{{ $equipo->nombre }}</strong></td>
                <td>{{ $equipo->categoria }}</td>
                <td>{{ $equipo->jugadores_count }}</td>
                <td class="admin-actions-cell">
                    {{-- Contenedor de los botones Editar y Borrar --}}
                    <div class="admin-row-actions">
                        
                        <a href="{{ route('equipos.show', $equipo->id) }}" class="btn-accion admin-view-btn" title="Ver detalles del equipo">
                            <i class="fas fa-eye"></i>
                        </a>

                        {{-- Botón Editar --}}
                        <a href="{{ route('equipos.edit', $equipo->id) }}" class="btn-accion editar admin-no-margin" title="Editar equipo">
                            <i class="fas fa-pen"></i>
                        </a>

                        <form action="{{ route('equipos.toggle-oculto', $equipo->id) }}" method="POST" class="admin-form-inline admin-form-inline-flex">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn-accion {{ $equipo->oculto ? 'mostrar' : 'ocultar' }} admin-no-margin" title="{{ $equipo->oculto ? 'Mostrar equipo' : 'Ocultar equipo' }}">
                                <i class="fas {{ $equipo->oculto ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                            </button>
                        </form>
                        
                        {{-- Formulario para Borrar --}}
                        <form action="{{ route('equipos.destroy', $equipo->id) }}" method="POST" onsubmit="return confirm('¿Eliminar equipo?')" class="admin-form-inline admin-form-inline-flex">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-accion borrar admin-no-margin" title="Eliminar equipo">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
        
                    </div>
                </td>
            </tr>
            @empty
            {{-- MENSAJE SI NO HAY EQUIPOS --}}
            <tr>
                <td colspan="5" class="admin-empty-cell">
                    <i class="fas fa-info-circle"></i> No se han encontrado equipos en la base de datos.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{--Esto es la paginación--}}
    <div class="contenedor-paginacion">
        {{ $equipos->links() }}
    </div>

</div>
@endsection
