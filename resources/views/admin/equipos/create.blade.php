@extends('layouts.admin')

@section('contenido_admin')
<header class="header-admin">
    <h2>Crear Nuevo Equipo</h2>
    <a href="{{ route('noticias.index') }}" class="btn-nuevo" style="background-color: #777;">
            <i class="fas fa-arrow-left"></i> Volver al listado
    </a>
</header>

{{-- Bloque para mostrar errores de validación --}}

@if ($errors->any())
<div class="cuadro-errores-admin">
    <strong>¡Vaya! Algo ha ido mal:</strong>
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="pizarra-admin">
    <form action="{{ route('equipos.store') }}" method="POST" enctype="multipart/form-data" class="formulario-admin">
        @csrf
        <div class="grid-formulario">
            <div class="campo">
                <label>Nombre del Equipo</label>
                <input type="text" name="nombre" placeholder="Ej: Senior A" required>
            </div>
            <div class="campo">
                <label>Categoría</label>
                <select name="categoria" required>
                    <option value="Senior">Senior</option>
                    <option value="Junior">Junior</option>
                    <option value="Cadete">Cadete</option>
                    <option value="Infantil">Infantil</option>
                    <option value="Alevín">Alevín</option>
                </select>
            </div>

            <div class="campo campo-checkbox">
                <label>¿Es del club?</label>
                <div class="checkbox-wrapper">
                    <input type="checkbox" name="es_local" id="es_local" value="1">
                    <span>Sí, es equipo local</span>
                </div>
            </div>

            <div class="campo">
                <label>Escudo/Imagen del Club</label>
                <input type="file" name="imagen_club">
            </div>
        </div>
        <div class="form-acciones">
            <button type="submit" class="btn-guardar">Guardar Equipo</button>
        </div>
    </form>
</div>
@endsection