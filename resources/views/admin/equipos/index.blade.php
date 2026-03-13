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

<div class="pizarra-admin">
    <table class="tabla-admin">
        <thead>
            <tr>
                <th>Escudo</th>
                <th>Nombre Equipo</th>
                <th>Categoría</th>
                <th>Nº Jugadores</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            {{-- BUCLE: Repite la fila por cada equipo que haya en la base de datos --}}
            @forelse($equipos as $equipo)
            <tr>
                <td>
                    <div class="contenedor-escudo">
                        {{-- Muestra la foto guardada en la carpeta public/escudos --}}
                        <img src="{{ asset('escudos/' . $equipo->imagen_club) }}" alt="Escudo" width="60" height="60" class="admin-img-contain">
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
                    @if($equipo->oculto)
                        <span class="badge-oculto-admin">Oculto</span>
                    @endif
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
