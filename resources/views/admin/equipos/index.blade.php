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
    <div style="padding: 15px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 20px;">
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
                        <img src="{{ asset('escudos/' . $equipo->imagen_club) }}" alt="Escudo" width="60" height="60" style="object-fit: contain;">
                    </div>
                </td>
                <td><strong>{{ $equipo->nombre }}</strong></td>
                <td>{{ $equipo->categoria }}</td>
                <td>{{ $equipo->jugadores_count }}</td>
                <td style="padding: 25px 15px; vertical-align: middle;">
                    {{-- Contenedor de los botones Editar y Borrar --}}
                    <div style="display: flex; gap: 8px; align-items: center; justify-content: flex-start;">
                        
                        <a href="{{ route('equipos.show', $equipo->id) }}" class="btn-accion" title="Ver Detalle" style="background: #e3f2fd; color: #1976d2;">
                            <i class="fas fa-eye"></i>
                        </a>

                        {{-- Botón Editar --}}
                        <a href="{{ route('equipos.edit', $equipo->id) }}" class="btn-accion editar" title="Editar" style="margin: 0;">
                            <i class="fas fa-pen"></i>
                        </a>
                        
                        {{-- Formulario para Borrar --}}
                        <form action="{{ route('equipos.destroy', $equipo->id) }}" method="POST" onsubmit="return confirm('¿Eliminar equipo?')" style="margin: 0; display: flex; align-items: center;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-accion borrar" title="Eliminar" style="margin: 0;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
        
                    </div>
                </td>
            </tr>
            @empty
            {{-- MENSAJE SI NO HAY EQUIPOS --}}
            <tr>
                <td colspan="5" style="text-align: center; padding: 40px; color: #777;">
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