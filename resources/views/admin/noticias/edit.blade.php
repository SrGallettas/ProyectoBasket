@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <x-admin-edit-header
        titulo="Editar Noticia"
        subtitulo="Actualiza el contenido editorial y la imagen de portada"
        :backRoute="route('noticias.index')"
    />

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

    <div class="pizarra-ficha">
        <form action="{{ route('noticias.update', $noticia->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid-formulario-fichas">
                <div class="seccion-form-ficha seccion-personal">
                    <h3><i class="fas fa-newspaper"></i> Datos de Publicación</h3>

                    <div class="campo-ficha">
                        <label>Título de la Noticia</label>
                        <input
                            type="text"
                            name="titulo"
                            value="{{ old('titulo', $noticia->titulo) }}"
                            placeholder="Ej: Gran victoria del Senior A"
                            required
                            class="input-ficha"
                        >
                    </div>

                    <div class="campo-ficha">
                        <label>Subtítulo o Resumen</label>
                        <textarea
                            name="subtitulo"
                            rows="3"
                            placeholder="Breve resumen para la portada"
                            required
                            class="input-ficha admin-textarea"
                        >{{ old('subtitulo', $noticia->subtitulo) }}</textarea>
                    </div>

                    <div class="campo-ficha">
                        <label>Autor</label>
                        <input
                            type="text"
                            name="autor"
                            value="{{ old('autor', $noticia->autor) }}"
                            placeholder="Nombre del redactor"
                            required
                            class="input-ficha"
                        >
                    </div>
                </div>

                <div class="seccion-form-ficha seccion-deportiva">
                    <h3><i class="fas fa-pen-nib"></i> Cuerpo Editorial</h3>

                    <div class="campo-ficha">
                        <label>Contenido</label>
                        <textarea
                            name="contenido"
                            rows="12"
                            placeholder="Escribe aquí toda la noticia..."
                            required
                            class="input-ficha admin-textarea"
                        >{{ old('contenido', $noticia->contenido) }}</textarea>
                    </div>

                    <div class="campo-ficha">
                        <label>Imagen de portada</label>
                        <p class="input-help-text">Si subes una nueva imagen, sustituirá la actual. Si no, se conservará la existente.</p>
                    </div>

                    <div class="campo-ficha">
                        <label>Visibilidad en la web</label>
                        <div class="checkbox-wrapper checkbox-wrapper-ficha">
                            <input type="checkbox" name="oculto" value="1" {{ old('oculto', $noticia->oculto) ? 'checked' : '' }}>
                            <span>Ocultar noticia de la web pública</span>
                        </div>
                    </div>
                </div>

                <div class="seccion-foto-ficha">
                    <div class="card-imagen-horizontal">
                        <div class="preview-foto-noticia">
                            <img
                                id="noticia-edit-preview"
                                src="{{ $noticia->imagen_noticia_url }}"
                                alt="Imagen actual de la noticia"
                            >
                        </div>

                        <div class="info-subida-noticia">
                            <h3>Imagen de la Noticia</h3>
                            <p>Para que luzca bien, usa una imagen horizontal con buena presencia en portada.</p>

                            <label for="imagen_noticia" class="btn-cambiar-foto">
                                <i class="fas fa-camera"></i> Cambiar imagen
                            </label>
                            <input type="file" name="imagen_noticia" id="imagen_noticia" class="hidden-file-input" accept="image/*" data-image-input="#noticia-edit-preview">

                            <span class="nota-subida">Si no seleccionas ninguna, se mantendrá la actual.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-acciones-ficha">
                <a href="{{ route('noticias.index') }}" class="btn-cancelar-ficha" title="Cancelar y volver al listado de noticias">Cancelar</a>
                <button type="submit" class="btn-actualizar-ficha" title="Actualizar noticia">
                    <i class="fas fa-sync-alt"></i> ACTUALIZAR FICHA
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
