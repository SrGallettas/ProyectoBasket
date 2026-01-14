@extends('layouts.admin') {{-- Carga la base (el diseño general del panel) --}}

@section('contenido_admin') {{-- Aquí empieza el contenido que se mete dentro del diseño --}}
<header class="header-admin">
    <div>
        <h2>Gestión de Equipos</h2>
        <p style="color: #777;">Categorías y grupos del Bellreguard CB</p>
    </div>
    {{-- Botón para ir a la vista de crear equipo nuevo --}}
    <a href="{{ route('equipos.create') }}" class="btn-nuevo">
        <i class="fas fa-plus"></i> Nuevo Equipo
    </a>
</header>

{{-- Buscador de Equipos --}}
<div class="contenedor-buscador">
    <form action="{{ route('equipos.search') }}" method="GET" class="form-buscador">
        <div class="input-grupal">
            <input type="text" 
                   name="search" 
                   placeholder="Buscar por nombre..." 
                   value="{{ $search ?? '' }}" {{-- Mantiene el texto después de buscar --}}
                   class="input-search">
            <button type="submit" class="btn-buscar">
                <i class="fas fa-search"></i>
            </button>
        </div>
        {{-- Si hay una búsqueda activa, mostramos un botón para limpiar --}}
        @if(isset($search) && $search != '')
            <a href="{{ route('equipos.index') }}" class="btn-limpiar" title="Limpiar búsqueda">
                <i class="fas fa-times"></i>
            </a>
        @endif
    </form>
</div>


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
            @foreach($equipos as $equipo)
            <tr>
                <td>
                    <div class="contenedor-escudo">
                        {{-- Muestra la foto guardada en la carpeta public/escudos --}}
                        <img src="{{ asset('escudos/' . $equipo->imagen_club) }}" alt="Escudo" width="60" height="60" style="object-fit: contain;">
                    </div> {{-- DIV cerrado correctamente --}}
                </td>
                <td><strong>{{ $equipo->nombre }}</strong></td>
                <td>{{ $equipo->categoria }}</td>
                <td>{{ $equipo->jugadores_count }}</td>
                <td style="padding: 25px 15px; vertical-align: middle;">
                    {{-- Contenedor de los botones Editar y Borrar --}}
                    <div style="display: flex; gap: 8px; align-items: center; justify-content: flex-start;">
                        
                        <a href="{{ route('equipos.show', $equipo->id) }}" class="btn-accion" title="Ver Detalle" style="background-color: #00b4d8; color: white;">
                            <i class="fas fa-eye"></i>
                        </a>

                        {{-- Botón Editar: Pasa el ID del equipo en la URL para saber cuál editar --}}
                        <a href="{{ route('equipos.edit', $equipo->id) }}" class="btn-accion editar" title="Editar" style="margin: 0;">
                            <i class="fas fa-pen"></i>
                        </a>
                        
                        {{-- Formulario para Borrar: Es un formulario porque usa el método DELETE --}}
                        <form action="{{ route('equipos.destroy', $equipo->id) }}" method="POST" onsubmit="return confirm('¿Eliminar equipo?')" style="margin: 0; display: flex; align-items: center;">
                            @csrf {{-- Token de seguridad obligatorio --}}
                            @method('DELETE') {{-- Le dice a Laravel que use la función destroy --}}
                            <button type="submit" class="btn-accion borrar" title="Eliminar" style="margin: 0;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
        
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{--Esto es la paginación--}}
    <div class="contenedor-paginacion">
        {{ $equipos->links() }}
    </div>

</div>
@endsection