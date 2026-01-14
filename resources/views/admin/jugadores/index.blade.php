@extends('layouts.admin')

@section('contenido_admin')
<header class="header-admin">
    <div>
        <h2>Gestión de Jugadores</h2>
        <p style="color: #777;">Plantilla completa del club</p>
    </div>
    
    <a href="{{ route('jugadores.create') }}" class="btn-nuevo">
        <i class="fas fa-plus"></i> Añadir Jugador
    </a>
</header>


{{-- Buscador de Equipos --}}
<div class="contenedor-buscador">
    <form action="{{ route('jugadores.search') }}" method="GET" class="form-buscador">
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
            <a href="{{ route('jugadores.index') }}" class="btn-limpiar" title="Limpiar búsqueda">
                <i class="fas fa-times"></i>
            </a>
        @endif
    </form>
</div>

{{-- Mensaje de éxito al crear, editar o borrar --}}
@if(session('mensaje'))
    <div style="padding: 15px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 20px;">
        <i class="fas fa-check-circle"></i> {{ session('mensaje') }}
    </div>
@endif

<div class="pizarra-admin">
    <table class="tabla-admin">
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
            @foreach($jugadores as $jugador)
            <tr>
                {{-- 1. FOTO --}}
                <td>
                    <div class="contenedor-escudo" style="width: 60px; height: 60px; overflow: hidden; border: 1px solid #ddd; border-radius: 4px;">
                        @if($jugador->imagen_jugador)
                            <img src="{{ asset('jugadores/' . $jugador->imagen_jugador) }}" alt="Foto" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <img src="{{ asset('img/basket.jpeg') }}" alt="Sin foto" style="width: 100%; height: 100%; object-fit: cover;">
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
                    <span style="background: #e3f2fd; color: #0d47a1; padding: 4px 10px; border-radius: 12px; font-size: 0.85em; font-weight: bold;">
                        {{ $jugador->equipo->categoria ?? '-' }}
                    </span>
                </td>

                {{-- 6. POSICIÓN --}}
                <td>{{ $jugador->posicion }}</td>

                {{-- 4. FECHA DE NACIMIENTO --}}
                <td>
                    {{-- Usamos carbon para poner la fecha en formato día/mes/año --}}
                    {{ $jugador->fecha_nacimiento ? \Carbon\Carbon::parse($jugador->fecha_nacimiento)->format('d/m/Y') : 'No asignada' }}
                </td>
                
                {{-- 7. ACCIONES (ALINEADAS) --}}
                <td style="padding: 25px 15px; vertical-align: middle;">
                    <div style="display: flex; gap: 8px; align-items: center; justify-content: flex-start;">
                        
                        {{-- Botón Ver --}}
                        <a href="{{ route('jugadores.show', $jugador->id) }}" class="btn-accion" style="background: #e3f2fd; color: #1976d2;" title="Ver Detalles">
                            <i class="fas fa-eye"></i>
                        </a>

                        {{-- Botón Editar --}}
                        <a href="{{ route ('jugadores.edit', $jugador->id)}}" class="btn-accion editar" title="Editar" style="margin: 0;">
                            <i class="fas fa-pen"></i>
                        </a>
                        
                        {{-- Formulario Borrar --}}
                        <form action="{{ route ('jugadores.destroy', $jugador->id)}}" method="POST" onsubmit="return confirm('¿Eliminar jugador?')" style="margin: 0; display: flex; align-items: center;">
                            @csrf
                            @method('DELETE')
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
        {{ $jugadores->links() }}
    </div>

</div>
@endsection