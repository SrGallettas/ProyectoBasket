@extends('layouts.admin')

@section('contenido_admin')
<x-admin-header
    titulo="Gestión de Clasificaciones"
    subtitulo="Crea ligas por temporada y asigna equipos para generar la tabla por jornadas."
    :rutaNuevo="route('clasificaciones.create')"
    textoNuevo="Nueva Clasificación"
    :rutaIndex="route('clasificaciones.index')"
/>

@if(session('mensaje'))
    <div class="admin-alert-success">
        <i class="fas fa-check-circle"></i> {{ session('mensaje') }}
    </div>
@endif

<div class="pizarra-admin">
    <table class="tabla-admin">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Temporada</th>
                <th>Equipos</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clasificaciones as $clasificacion)
                <tr>
                    <td><strong>{{ $clasificacion->nombre }}</strong></td>
                    <td>{{ $clasificacion->categoria ?: '-' }}</td>
                    <td>{{ $clasificacion->temporada ?: '-' }}</td>
                    <td>{{ $clasificacion->equipos_count }}</td>
                    <td>{{ $clasificacion->activa ? 'Activa' : 'Inactiva' }}</td>
                    <td class="admin-actions-cell">
                        <div class="admin-row-actions">
                            <a href="{{ route('clasificaciones.show', $clasificacion->id) }}" class="btn-accion admin-view-btn" title="Ver clasificación">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('clasificaciones.edit', $clasificacion->id) }}" class="btn-accion editar" title="Editar clasificación">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form action="{{ route('clasificaciones.destroy', $clasificacion->id) }}" method="POST" onsubmit="return confirm('¿Eliminar clasificación?')" class="admin-form-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-accion borrar" title="Eliminar clasificación">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="admin-empty-cell">
                        <i class="fas fa-info-circle"></i> No hay clasificaciones creadas.
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
