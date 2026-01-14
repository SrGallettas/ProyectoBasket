@extends('layouts.admin')

@section('contenido_admin')
<header class="header-admin">
    <h2>Ficha del Equipo: {{ $equipo->nombre }}</h2>
    <a href="{{ route('equipos.index') }}" class="btn-nuevo" style="background-color: #777;">
        <i class="fas fa-arrow-left"></i> Volver al listado
    </a>
</header>

<div class="pizarra-admin">
    <div style="display: flex; gap: 40px; align-items: flex-start; padding: 20px;">
        
        <div style="flex: 1; text-align: center; background: #f9f9f9; padding: 20px; border-radius: 10px; border: 1px solid #eee;">
            <img src="{{ asset('escudos/' . $equipo->imagen_club) }}" alt="Escudo" style="max-width: 250px; height: auto; border-radius: 8px;">
        </div>

        <div style="flex: 2;">
            <h3 style="margin-top: 0; color: #333; border-bottom: 2px solid #fb8500; display: inline-block;">Información General</h3>
            
            <p style="font-size: 1.2rem; margin: 15px 0;">
                <strong>Nombre:</strong> {{ $equipo->nombre }}
            </p>
            
            <p style="font-size: 1.2rem; margin: 15px 0;">
                <strong>Categoría:</strong> <span class="badge" style="background: #023e8a; color: white; padding: 5px 12px; border-radius: 15px; font-size: 1rem;">{{ $equipo->categoria }}</span>
            </p>

            <p style="font-size: 1.2rem; margin: 15px 0;">
                <strong>Fecha de Registro:</strong> {{ $equipo->created_at->format('d/m/Y') }}
            </p>

            <hr style="border: 0; border-top: 1px solid #eee; margin: 25px 0;">

            <div style="display: flex; gap: 10px;">

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
        </div>
    </div>
</div>
@endsection