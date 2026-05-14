@extends('layouts.admin')

@section('title', __('admin.news.meta.create_title'))

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <x-admin-edit-header
        :titulo="__('admin.news.create.title')"
        :subtitulo="__('admin.news.create.subtitle')"
        :backRoute="route('noticias.index')"
    />

    @if ($errors->any())
    <div class="cuadro-errores-admin">
        <strong>{{ __('admin.common.form_error') }}</strong>
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
                    <h3><i class="fas fa-newspaper"></i> {{ __('admin.news.sections.publish') }}</h3>

                    <div class="campo-ficha">
                        <label>{{ __('admin.news.fields.source_language') }}</label>
                        <select name="idioma_origen" class="input-ficha">
                            <option value="es" {{ old('idioma_origen', 'es') === 'es' ? 'selected' : '' }}>{{ __('admin.news.fields.source_language_es') }}</option>
                            <option value="val" {{ old('idioma_origen') === 'val' ? 'selected' : '' }}>{{ __('admin.news.fields.source_language_val') }}</option>
                        </select>
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.news.fields.title_input') }}</label>
                        <input
                            type="text"
                            name="titulo"
                            value="{{ old('titulo') }}"
                            placeholder="{{ __('admin.news.fields.title_placeholder_input') }}"
                            class="input-ficha"
                        >
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.news.fields.subtitle_input') }}</label>
                        <textarea
                            name="subtitulo"
                            rows="3"
                            placeholder="{{ __('admin.news.fields.subtitle_placeholder_input') }}"
                            class="input-ficha admin-textarea"
                        >{{ old('subtitulo') }}</textarea>
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.news.fields.author') }}</label>
                        <input
                            type="text"
                            name="autor"
                            value="{{ old('autor') }}"
                            placeholder="{{ __('admin.news.fields.author_placeholder') }}"
                            class="input-ficha"
                        >
                    </div>
                </div>

                <div class="seccion-form-ficha seccion-deportiva">
                    <h3><i class="fas fa-pen-nib"></i> {{ __('admin.news.sections.body') }}</h3>

                    <div class="campo-ficha">
                        <label>{{ __('admin.news.fields.content_input') }}</label>
                        <textarea
                            name="contenido"
                            rows="12"
                            placeholder="{{ __('admin.news.fields.content_placeholder_input') }}"
                            class="input-ficha admin-textarea"
                        >{{ old('contenido') }}</textarea>
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.news.fields.cover_image') }}</label>
                        <p class="input-help-text">{{ __('admin.news.fields.cover_help_create') }}</p>
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.news.fields.visibility') }}</label>
                        <div class="checkbox-wrapper checkbox-wrapper-ficha">
                            <input type="checkbox" name="oculto" value="1" {{ old('oculto') ? 'checked' : '' }}>
                            <span>{{ __('admin.news.fields.hide_public') }}</span>
                        </div>
                    </div>
                </div>

                <div class="seccion-foto-ficha">
                    <div class="card-imagen-horizontal">
                        <div class="preview-foto-noticia">
                            <img id="noticia-create-preview" src="{{ asset('img/club_14.png') }}" alt="{{ __('admin.news.images.default_alt') }}">
                        </div>

                        <div class="info-subida-noticia">
                            <h3>{{ __('admin.news.images.title') }}</h3>
                            <p>{{ __('admin.news.images.help') }}</p>

                            <label for="imagen_noticia" class="btn-cambiar-foto">
                                <i class="fas fa-camera"></i> {{ __('admin.news.images.select') }}
                            </label>
                            <input type="file" name="imagen_noticia" id="imagen_noticia" class="hidden-file-input" accept="image/*" data-image-input="#noticia-create-preview">

                            <span class="nota-subida">{{ __('admin.news.images.note_create') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-acciones-ficha">
                <a href="{{ route('noticias.index') }}" class="btn-cancelar-ficha" title="{{ __('admin.news.actions.cancel_back') }}">{{ __('admin.common.cancel') }}</a>
                <button type="submit" class="btn-actualizar-ficha btn-actualizar-rojo" title="{{ __('admin.news.actions.publish') }}">
                    <i class="fas fa-save"></i> {{ __('admin.news.create.submit') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
