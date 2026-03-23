@extends('layouts.admin')

@use('Carbon\Carbon') 

@section('contenido_admin')

{{-- Invocamos el componente con tus datos --}}
<x-admin-header 
    titulo="Gestión de Jugadores" 
    subtitulo="Plantilla completa del club"
    :rutaNuevo="route('jugadores.create')" 
    textoNuevo="Añadir Jugador"
    :rutaIndex="route('jugadores.index')"
/>

{{-- Mensaje de éxito --}}
@if(session('mensaje'))
    <div class="admin-alert-success">
        <i class="fas fa-check-circle"></i> {{ session('mensaje') }}
    </div>
@endif

<div class="pizarra-admin">
    <table class="tabla-admin">
        {{-- Tu tabla se queda EXACTAMENTE IGUAL que la tenías --}}
        <thead>
            <tr>
                <th>Foto</th>
                <th>Dorsal</th>
                <th>Nombre</th>
                <th>Apellidos</th>
                <th>Club</th>
                <th>Categoría</th>
                <th>Posición</th>
                <th>Fecha Nacimiento</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jugadores as $jugador)
            <tr>
                {{-- 1. FOTO --}}
                <td>
                    <div class="contenedor-escudo admin-photo-frame">
                        @if($jugador->imagen_jugador)
                            <img src="{{ asset('jugadores/' . $jugador->imagen_jugador) }}" alt="Foto" class="admin-photo-fill">
                        @else
                            <img src="{{ asset('img/basket.jpeg') }}" alt="Sin foto" class="admin-photo-fill">
                        @endif
                    </div>
                </td>
                {{-- 2. DORSAL --}}
                <td>
                    <span class="dorsal-badge">#{{ $jugador->dorsal }}</span>
                </td>            
                {{-- 3. NOMBRE --}}
                <td><strong>{{ $jugador->nombre }}</strong></td>

                {{-- 3.APELLIDO --}}
                <td><strong>{{ $jugador->apellido }}</strong></td>
                
                {{-- 4. CLUB --}}
                <td>
                    {{ $jugador->equipo->nombre ?? 'Sin Equipo' }}
                </td>

                {{-- 5. CATEGORÍA (Sacada del modelo Equipo) --}}
                <td>
                    <span class="admin-badge-light">
                        {{ $jugador->equipo->categoria ?? '-' }}
                    </span>
                </td>

                {{-- 6. POSICIÓN --}}
                <td>{{ $jugador->posicion }}</td>

                {{-- 4. FECHA DE NACIMIENTO --}}
                <td>
                    {{-- Usamos carbon para poner la fecha en formato día/mes/año --}}
                    {{ $jugador->fecha_nacimiento ? Carbon::parse($jugador->fecha_nacimiento)->format('d/m/Y') : 'No asignada' }}
                </td>
                
                {{-- 7. ACCIONES (ALINEADAS) --}}
                <td class="admin-actions-cell">
                    <div class="admin-row-actions">
                        
                        {{-- Botón Ver --}}
                        <a href="{{ route('jugadores.show', $jugador->id) }}" class="btn-accion admin-view-btn" title="Ver detalles del jugador">
                            <i class="fas fa-eye"></i>
                        </a>

                        {{-- Botón Editar --}}
                        <a href="{{ route ('jugadores.edit', $jugador->id)}}" class="btn-accion editar admin-no-margin" title="Editar jugador">
                            <i class="fas fa-pen"></i>
                        </a>

                        <form action="{{ route('jugadores.toggle-oculto', $jugador->id) }}" method="POST" class="admin-form-inline admin-form-inline-flex">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn-accion {{ $jugador->oculto ? 'mostrar' : 'ocultar' }} admin-no-margin" title="{{ $jugador->oculto ? 'Mostrar jugador' : 'Ocultar jugador' }}">
                                <i class="fas {{ $jugador->oculto ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                            </button>
                        </form>
                        
                        {{-- Formulario Borrar --}}
                        <form action="{{ route ('jugadores.destroy', $jugador->id)}}" method="POST" onsubmit="return confirm('¿Eliminar jugador?')" class="admin-form-inline admin-form-inline-flex">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-accion borrar admin-no-margin" title="Eliminar jugador">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>

                    </div>
                </td>
            </tr>
            @empty
            {{-- MENSAJE SI NO HAY JUGADORES --}}
            <tr>
                <td colspan="9" class="admin-empty-cell">
                    <i class="fas fa-info-circle"></i> No se han encontrado jugadores en la base de datos.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{--Esto es la paginación--}}
    <div class="contenedor-paginacion">
        {{ $jugadores->links() }}
    </div>

</div>
@endsection
