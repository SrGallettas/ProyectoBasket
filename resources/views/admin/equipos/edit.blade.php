@extends('layouts.admin') {{-- Carga el diseño del panel de administrador --}}

@section('contenido_admin')
<header class="header-admin">
    {{-- Mostramos el nombre del equipo dinámicamente en el título --}}
    <h2>Editar Equipo: {{ $equipo->nombre }}</h2>
</header>

@if ($errors->any())
    <div style="background: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <strong>¡Vaya! Algo ha ido mal:</strong>
        {{-- Añadimos list-style: none y quitamos el padding --}}
        <ul style="margin-top: 5px; margin-bottom: 0; list-style: none; padding-left: 0;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="pizarra-admin">
    {{-- IMPORTANTE: action apunta a "update" con el ID, y usamos enctype para poder subir archivos --}}
    <form action="{{ route('equipos.update', $equipo->id) }}" method="POST" enctype="multipart/form-data">
        
        @csrf {{-- Token de seguridad para evitar ataques externos --}}
        
        @method('PUT') {{-- Los navegadores no entienden "PUT", así que Laravel lo simula aquí para la actualización --}}

        <div class="grid-formulario">
            {{-- Campo para el nombre --}}
            <div class="campo">
                <label>Nombre del Equipo</label>
                {{-- El value carga el nombre actual que ya está en la base de datos --}}
                <input type="text" name="nombre" value="{{ $equipo->nombre }}" required>
            </div>

            {{-- Campo para la categoría --}}
            <div class="campo">
                <label>Categoría</label>
                <select name="categoria">
                    {{-- Usamos un ternario para marcar "selected" la opción que ya estaba guardada --}}
                    <option value="Senior" {{ $equipo->categoria == 'Senior' ? 'selected' : '' }}>Senior</option>
                    <option value="Junior" {{ $equipo->categoria == 'Junior' ? 'selected' : '' }}>Junior</option>
                    <option value="Cadete" {{ $equipo->categoria == 'Cadete' ? 'selected' : '' }}>Cadete</option>
                </select>
            </div>

            {{-- Campo para la imagen --}}
            <div class="campo">
                <label>Escudo Actual</label>
                <div style="display: flex; align-items: center; gap: 15px;">
                    {{-- Mostramos la imagen que tiene el equipo ahora mismo --}}
                    <img src="{{ asset('escudos/' . $equipo->imagen_club) }}" alt="Escudo" width="60" height="60" style="object-fit: contain;">
                    
                    {{-- Input para subir una foto nueva si el usuario quiere cambiarla --}}
                    <input type="file" name="imagen_club" id="imagen_club">
                </div>
            </div>
        </div>

        {{-- Botones de acción --}}
        <div class="form-acciones">
            {{-- Enlace para volver atrás sin guardar nada --}}
            <a href="{{ route('equipos.index') }}" style="margin-right: 20px; color: #777; text-decoration: none;">Cancelar</a>
            
            {{-- El botón submit envía el formulario al método "update" del controlador --}}
            <button type="submit" class="btn-guardar">Guardar Cambios</button>
        </div>
    </form>
</div>
@endsection