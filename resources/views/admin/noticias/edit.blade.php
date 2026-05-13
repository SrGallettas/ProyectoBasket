@extends('layouts.admin')

@section('title', __('admin.news.meta.edit_title'))

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <x-admin-edit-header
        :titulo="__('admin.news.edit.title')"
        :subtitulo="__('admin.news.edit.subtitle')"
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
        <form action="{{ route('noticias.update', $noticia->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid-formulario-fichas">
                <div class="seccion-form-ficha seccion-personal">
                    <h3><i class="fas fa-newspaper"></i> {{ __('admin.news.sections.publish') }}</h3>

                    <div class="campo-ficha">
                        <label>{{ __('admin.news.fields.title') }}</label>
                        <input
                            type="text"
                            name="titulo"
                            value="{{ old('titulo', $noticia->titulo) }}"
                            placeholder="{{ __('admin.news.fields.title_placeholder') }}"
                            class="input-ficha"
                        >
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.news.fields.subtitle') }}</label>
                        <textarea
                            name="subtitulo"
                            rows="3"
                            placeholder="{{ __('admin.news.fields.subtitle_placeholder') }}"
                            class="input-ficha admin-textarea"
                        >{{ old('subtitulo', $noticia->subtitulo) }}</textarea>
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.news.fields.author') }}</label>
                        <input
                            type="text"
                            name="autor"
                            value="{{ old('autor', $noticia->autor) }}"
                            placeholder="{{ __('admin.news.fields.author_placeholder') }}"
                            class="input-ficha"
                        >
                    </div>
                </div>

                <div class="seccion-form-ficha seccion-deportiva">
                    <h3><i class="fas fa-pen-nib"></i> {{ __('admin.news.sections.body') }}</h3>

                    <div class="campo-ficha">
                        <label>{{ __('admin.news.fields.content') }}</label>
                        <textarea
                            name="contenido"
                            rows="12"
                            placeholder="{{ __('admin.news.fields.content_placeholder') }}"
                            class="input-ficha admin-textarea"
                        >{{ old('contenido', $noticia->contenido) }}</textarea>
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.news.fields.cover_image') }}</label>
                        <p class="input-help-text">{{ __('admin.news.fields.cover_help_edit') }}</p>
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.news.fields.visibility') }}</label>
                        <div class="checkbox-wrapper checkbox-wrapper-ficha">
                            <input type="checkbox" name="oculto" value="1" {{ old('oculto', $noticia->oculto) ? 'checked' : '' }}>
                            <span>{{ __('admin.news.fields.hide_public') }}</span>
                        </div>
                    </div>
                </div>

                <div class="seccion-foto-ficha">
                    <div class="card-imagen-horizontal">
                        <div class="preview-foto-noticia">
                            <img
                                id="noticia-edit-preview"
                                src="{{ $noticia->imagen_noticia_url }}"
                                alt="{{ __('admin.news.images.current_alt') }}"
                            >
                        </div>

                        <div class="info-subida-noticia">
                            <h3>{{ __('admin.news.images.title') }}</h3>
                            <p>{{ __('admin.news.images.help') }}</p>

                            <label for="imagen_noticia" class="btn-cambiar-foto">
                                <i class="fas fa-camera"></i> {{ __('admin.news.images.change') }}
                            </label>
                            <input type="file" name="imagen_noticia" id="imagen_noticia" class="hidden-file-input" accept="image/*" data-image-input="#noticia-edit-preview">

                            <span class="nota-subida">{{ __('admin.news.images.note_edit') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-acciones-ficha">
                <a href="{{ route('noticias.index') }}" class="btn-cancelar-ficha" title="{{ __('admin.news.actions.cancel_back') }}">{{ __('admin.common.cancel') }}</a>
                <button type="submit" class="btn-actualizar-ficha" title="{{ __('admin.news.actions.update') }}">
                    <i class="fas fa-sync-alt"></i> {{ __('admin.news.edit.submit') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
