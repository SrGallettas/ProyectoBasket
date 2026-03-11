@extends('layouts.admin')

@section('contenido_admin')
<x-admin-show-header
    :titulo="'Ficha del Equipo: ' . $equipo->nombre"
    subtitulo="Información general y categoría"
    :editRoute="route('equipos.edit', $equipo->id)"
    :deleteRoute="route('equipos.destroy', $equipo->id)"
    :indexRoute="route('equipos.index')"
    confirmMessage="¿Eliminar equipo?"
    backText="VOLVER"
/>

<div class="pizarra-admin">
    <div class="equipo-show-layout">
        
        <div class="equipo-show-card">
            <img src="{{ asset('escudos/' . $equipo->imagen_club) }}" alt="Escudo" class="equipo-show-img">
        </div>

        <div class="equipo-show-info">
            <h3 class="equipo-show-title">Información General</h3>
            
            <p class="equipo-show-text">
                <strong>Nombre:</strong> {{ $equipo->nombre }}
            </p>
            
            <p class="equipo-show-text">
                <strong>Categoría:</strong> <span class="badge equipo-show-badge">{{ $equipo->categoria }}</span>
            </p>

            <p class="equipo-show-text">
                <strong>Fecha de Registro:</strong> {{ $equipo->created_at->format('d/m/Y') }}
            </p>

            <hr class="equipo-show-separator">

            <div class="equipo-show-actions">

                {{-- Botón Editar: Pasa el ID del equipo en la URL para saber cuál editar --}}
                <a href="{{ route('equipos.edit', $equipo->id) }}" class="btn-accion editar admin-no-margin" title="Editar">
                    <i class="fas fa-pen"></i>
                </a>
                        
                {{-- Formulario para Borrar: Es un formulario porque usa el método DELETE --}}
                <form action="{{ route('equipos.destroy', $equipo->id) }}" method="POST" onsubmit="return confirm('¿Eliminar equipo?')" class="admin-form-inline admin-form-inline-flex">
                    @csrf {{-- Token de seguridad obligatorio --}}
                    @method('DELETE') {{-- Le dice a Laravel que use la función destroy --}}
                    <button type="submit" class="btn-accion borrar admin-no-margin" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
