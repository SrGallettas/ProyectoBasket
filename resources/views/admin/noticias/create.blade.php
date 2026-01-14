@extends('layouts.admin')

@section('contenido_admin')
<header class="header-admin" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <h2><i class="fas fa-newspaper"></i> Crear Nueva Noticia</h2>
    <a href="{{ route('noticias.index') }}" class="btn-nuevo" style="background-color: #777; text-decoration: none; color: white; padding: 10px 18px; border-radius: 6px; font-weight: 600; font-size: 0.9rem;">
        <i class="fas fa-arrow-left"></i> Volver al listado
    </a>
</header>

{{-- Bloque de errores --}}
@if ($errors->any())
    <div style="background: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <strong>¡Vaya! Algo ha ido mal:</strong>
        <ul style="margin-top: 5px; margin-bottom: 0; list-style: none; padding-left: 0;">
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
            <div class="campo" style="grid-column: span 2;">
                <label>Título de la Noticia</label>
                <input type="text" name="titulo" value="{{ old('titulo') }}" placeholder="Ej: Gran victoria del Senior A" required style="width: 100%;">
            </div>

            <div class="campo" style="grid-column: span 2;">
                <label>Subtítulo o Resumen</label>
                <textarea name="subtitulo" rows="2" placeholder="Breve resumen para la portada" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0;">{{ old('subtitulo') }}</textarea>
            </div>

            <div class="campo">
                <label>Autor</label>
                <input type="text" name="autor" value="{{ old('autor') }}" placeholder="Nombre del redactor" required>
            </div>

            <div class="campo">
                <label>Imagen de Portada</label>
                <input type="file" name="imagen_noticia" required>
            </div>

            <div class="campo" style="grid-column: span 2;">
                <label>Cuerpo de la Noticia</label>
                <textarea name="contenido" rows="8" placeholder="Escribe aquí toda la noticia..." required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0;">{{ old('contenido') }}</textarea>
            </div>
        </div>

        {{-- Botones Estilo Ficha --}}
        <div class="form-acciones-ficha" style="margin-top: 30px; display: flex; justify-content: flex-end; gap: 15px; border-top: 1px solid #eee; padding-top: 20px;">
            <a href="{{ route('noticias.index') }}" class="btn-cancelar-ficha">Cancelar</a>
            <button type="submit" class="btn-actualizar-ficha" style="background: #e60000; box-shadow: 0 4px 6px rgba(230, 0, 0, 0.2);">
                <i class="fas fa-save"></i> Publicar Noticia
            </button>
        </div>
    </form>
</div>
@endsection