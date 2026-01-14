@extends('layouts.admin')

@section('contenido_admin')
<header class="header-admin" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <div>
        <h2><i class="fas fa-edit"></i> Editar Noticia</h2>
        <p style="color: #666; margin: 0;">Estás editando: <strong>{{ $noticia->titulo }}</strong></p>
    </div>
    <a href="{{ route('noticias.index') }}" class="btn-nuevo" style="background-color: #777; text-decoration: none; color: white; padding: 10px 18px; border-radius: 6px; font-weight: 600; font-size: 0.9rem;">
        <i class="fas fa-arrow-left"></i> Volver al listado
    </a>
</header>

{{-- Bloque de errores --}}
@if ($errors->any())
    <div class="alerta-error">
        <strong>¡Atención!</strong>
        <ul style="list-style: none; padding: 0; margin-top: 5px;">
            @foreach ($errors->all() as $error)
                <li><i class="fas fa-times"></i> {{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="pizarra-admin">
    <form action="{{ route('noticias.update', $noticia->id) }}" method="POST" enctype="multipart/form-data" class="formulario-admin">
        @csrf
        @method('PUT') {{-- Esto es vital para que Laravel sepa que es una edición --}}

        <div class="grid-formulario">
            
            {{-- Título --}}
            <div class="campo" style="grid-column: span 2;">
                <label>Título de la Noticia</label>
                <input type="text" name="titulo" value="{{ old('titulo', $noticia->titulo) }}" required>
            </div>

            {{-- Subtítulo --}}
            <div class="campo" style="grid-column: span 2;">
                <label>Subtítulo o Resumen</label>
                <textarea name="subtitulo" rows="2" required>{{ old('subtitulo', $noticia->subtitulo) }}</textarea>
            </div>

            {{-- Autor --}}
            <div class="campo">
                <label>Autor</label>
                <input type="text" name="autor" value="{{ old('autor', $noticia->autor) }}" required>
            </div>

            {{-- Imagen --}}
            <div class="seccion-foto-ficha">
                <div class="preview-foto">
                    @if($noticia->imagen_noticia)
                        <img src="{{ asset('noticias/' . $noticia->imagen_noticia) }}" id="img-preview" alt="Foto noticia">
                    @else
                        <img src="{{ asset('images/no-image.png') }}" id="img-preview" alt="Sin foto">
                    @endif
                </div>
    
                <div class="info-subida-ficha">
                    <label class="label-ficha">Imagen de la Noticia</label>
                    <p class="instruccion-ficha">Para que luzca bien, usa una imagen horizontal (16:9).</p>
            
                    <div class="custom-input-file">
                        <input type="file" name="imagen_noticia" id="imagen_noticia" accept="image/*" onchange="actualizarPreview(this)">
                        <label for="imagen_noticia" class="btn-subir-discreto">
                            <i class="fas fa-camera"></i> Cambiar fotografía
                        </label>
                    </div>
                    <small class="aviso-ficha">Si no seleccionas ninguna, se mantendrá la actual.</small>
                </div>
            </div>

            {{-- Contenido --}}
            <div class="campo" style="grid-column: span 2;">
                <label>Cuerpo de la Noticia</label>
                <textarea name="contenido" rows="10" required>{{ old('contenido', $noticia->contenido) }}</textarea>
            </div>
        </div>

        {{-- Botones --}}
        <div class="form-acciones-ficha">
            <a href="{{ route('noticias.index') }}" class="btn-cancelar-ficha">Cancelar</a>
            <button type="submit" class="btn-actualizar-ficha">
                <i class="fas fa-sync-alt"></i> Actualizar Noticia
            </button>
        </div>
    </form>
</div>
@endsection