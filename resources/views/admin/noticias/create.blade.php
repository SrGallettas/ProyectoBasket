@extends('layouts.admin')

@section('contenido_admin')
<header class="header-admin admin-header-spaced">
    <h2><i class="fas fa-newspaper"></i> Crear Nueva Noticia</h2>
    <a href="{{ route('noticias.index') }}" class="btn-nuevo btn-nuevo-secondary">
        <i class="fas fa-arrow-left"></i> Volver al listado
    </a>
</header>

{{-- Bloque de errores --}}
@if ($errors->any())
    <div class="admin-error-box">
        <strong>¡Vaya! Algo ha ido mal:</strong>
        <ul class="admin-error-list">
            @foreach ($errors->all() as $error)
                <li><i class="fas fa-exclamation-circle"></i> {{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="pizarra-admin">
    <form action="{{ route('noticias.store') }}" method="POST" enctype="multipart/form-data" class="formulario-admin">
        @csrf
        <div class="grid-formulario">
            
            {{-- Título ocupa todo el ancho --}}
            <div class="campo campo-span-2">
                <label>Título de la Noticia</label>
                <input type="text" name="titulo" value="{{ old('titulo') }}" placeholder="Ej: Gran victoria del Senior A" required class="input-fullwidth">
            </div>

            <div class="campo campo-span-2">
                <label>Subtítulo o Resumen</label>
                <textarea name="subtitulo" rows="2" placeholder="Breve resumen para la portada" required class="admin-textarea">{{ old('subtitulo') }}</textarea>
            </div>

            <div class="campo">
                <label>Autor</label>
                <input type="text" name="autor" value="{{ old('autor') }}" placeholder="Nombre del redactor" required>
            </div>

            <div class="campo">
                <label>Imagen de Portada</label>
                <input type="file" name="imagen_noticia" required>
            </div>

            <div class="campo campo-span-2">
                <label>Cuerpo de la Noticia</label>
                <textarea name="contenido" rows="8" placeholder="Escribe aquí toda la noticia..." required class="admin-textarea">{{ old('contenido') }}</textarea>
            </div>
        </div>

        {{-- Botones Estilo Ficha --}}
        <div class="form-acciones-ficha form-acciones-ficha-top">
            <a href="{{ route('noticias.index') }}" class="btn-cancelar-ficha">Cancelar</a>
            <button type="submit" class="btn-actualizar-ficha btn-actualizar-rojo">
                <i class="fas fa-save"></i> Publicar Noticia
            </button>
        </div>
    </form>
</div>
@endsection
