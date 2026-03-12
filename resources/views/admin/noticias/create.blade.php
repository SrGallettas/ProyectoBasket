@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <header class="header-ficha">
        <div>
            <h2>Añadir Nueva Noticia</h2>
            <p>Introduce el contenido editorial para publicarlo en la web del club</p>
        </div>
        <a href="{{ route('noticias.index') }}" class="btn-nuevo btn-nuevo-secondary">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
    </header>

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
        <form action="{{ route('noticias.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid-formulario-fichas">
                <div class="seccion-form-ficha seccion-personal">
                    <h3><i class="fas fa-newspaper"></i> Datos de Publicación</h3>

                    <div class="campo-ficha">
                        <label>Título de la Noticia</label>
                        <input
                            type="text"
                            name="titulo"
                            value="{{ old('titulo') }}"
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
                        >{{ old('subtitulo') }}</textarea>
                    </div>

                    <div class="campo-ficha">
                        <label>Autor</label>
                        <input
                            type="text"
                            name="autor"
                            value="{{ old('autor') }}"
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
                        >{{ old('contenido') }}</textarea>
                    </div>

                    <div class="campo-ficha">
                        <label>Imagen de portada</label>
                        <p class="input-help-text">Usa preferiblemente una imagen horizontal. Si no subes una personalizada, seguirá la imagen por defecto configurada en el formulario.</p>
                    </div>
                </div>

                <div class="seccion-foto-ficha">
                    <div class="card-imagen-horizontal">
                        <div class="preview-foto-noticia">
                            <img src="{{ asset('img/basket.jpeg') }}" alt="Imagen por defecto de la noticia">
                        </div>

                        <div class="info-subida-noticia">
                            <h3>Imagen de la Noticia</h3>
                            <p>Para que luzca bien, usa una imagen horizontal con buena presencia en portada.</p>

                            <label for="imagen_noticia" class="btn-cambiar-foto">
                                <i class="fas fa-camera"></i> Seleccionar imagen
                            </label>
                            <input type="file" name="imagen_noticia" id="imagen_noticia" class="hidden-file-input" required>

                            <span class="nota-subida">Formatos: JPG, PNG o WEBP. La imagen se guardará en public/noticias.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-acciones-ficha">
                <a href="{{ route('noticias.index') }}" class="btn-cancelar-ficha">Cancelar</a>
                <button type="submit" class="btn-actualizar-ficha btn-actualizar-rojo">
                    <i class="fas fa-save"></i> PUBLICAR NOTICIA
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
