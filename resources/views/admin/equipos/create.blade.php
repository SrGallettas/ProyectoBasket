@extends('layouts.admin')

@section('title', __('admin.teams.meta.create_title'))

@section('contenido_admin')
@php($mostrarFotoPlantilla = old('es_local'))
<div class="contenedor-edit-jugador">
    <x-admin-edit-header
        :titulo="__('admin.teams.create.title')"
        :subtitulo="__('admin.teams.create.subtitle')"
        :backRoute="route('equipos.index')"
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
        <form action="{{ route('equipos.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid-formulario-fichas">
                <div class="seccion-form-ficha seccion-personal">
                    <h3><i class="fas fa-shield-alt"></i> {{ __('admin.teams.sections.data') }}</h3>

                    <div class="campo-ficha">
                        <label>{{ __('admin.teams.fields.team_name') }}</label>
                        <input
                            type="text"
                            name="nombre"
                            value="{{ old('nombre') }}"
                            placeholder="{{ __('admin.teams.fields.team_name_placeholder') }}"
                            class="input-ficha"
                        >
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.teams.fields.category') }}</label>
                        <select name="categoria" class="input-ficha input-white">
                            <option value="" disabled {{ old('categoria') === null || old('categoria') === '' ? 'selected' : '' }}>{{ __('admin.common.select') }}</option>
                            @foreach($categoryOptions as $categoria)
                                <option value="{{ $categoria }}" @selected(old('categoria') === $categoria)>{{ $categoria }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="seccion-form-ficha seccion-deportiva">
                    <h3><i class="fas fa-clipboard-list"></i> {{ __('admin.teams.sections.config') }}</h3>

                    <div class="campo-ficha">
                        <label>{{ __('admin.teams.fields.team_status') }}</label>
                        <div class="checkbox-wrapper checkbox-wrapper-ficha">
                            <input
                                type="checkbox"
                                name="es_local"
                                id="es_local"
                                value="1"
                                {{ old('es_local') ? 'checked' : '' }}
                            >
                            <span>{{ __('admin.teams.fields.local_team') }}</span>
                        </div>
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.teams.fields.image_usage') }}</label>
                        <p class="input-help-text">{{ __('admin.teams.fields.image_usage_help_create') }}</p>
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.teams.fields.visibility') }}</label>
                        <div class="checkbox-wrapper checkbox-wrapper-ficha">
                            <input type="checkbox" name="oculto" value="1" {{ old('oculto') ? 'checked' : '' }}>
                            <span>{{ __('admin.teams.fields.hide_public') }}</span>
                        </div>
                    </div>
                </div>

                <div class="seccion-foto-ficha">
                    <div class="card-imagen-horizontal">
                        <div class="preview-foto-noticia">
                            <img id="equipo-create-preview" src="{{ asset('img/club_14.png') }}" alt="{{ __('admin.teams.images.default_alt') }}">
                        </div>

                        <div class="info-subida-noticia">
                            <h3>{{ __('admin.teams.images.team_image') }}</h3>
                            <p>{{ __('admin.teams.images.team_image_help') }}</p>

                            <label for="imagen_club" class="btn-cambiar-foto">
                                <i class="fas fa-camera"></i> {{ __('admin.teams.images.select_image') }}
                            </label>
                            <input type="file" name="imagen_club" id="imagen_club" class="hidden-file-input" accept="image/*" data-image-input="#equipo-create-preview">

                            <span class="nota-subida">{{ __('admin.teams.images.team_note_create') }}</span>
                        </div>
                    </div>

                    <div
                        class="card-imagen-horizontal"
                        id="foto-plantilla-card"
                        @unless($mostrarFotoPlantilla) style="display: none;" @endunless
                    >
                        <div class="preview-foto-noticia preview-foto-noticia-vacia" id="equipo-plantilla-create-preview-wrap">
                            <img id="equipo-plantilla-create-preview" src="" alt="{{ __('admin.teams.images.template_default_alt') }}" style="display:none;">
                            <span>{{ __('admin.teams.images.template_empty') }}</span>
                        </div>

                        <div class="info-subida-noticia">
                            <h3>{{ __('admin.teams.images.template_photo') }}</h3>
                            <p>{{ __('admin.teams.images.template_help_create') }}</p>

                            <label for="foto_plantilla" class="btn-cambiar-foto">
                                <i class="fas fa-camera"></i> {{ __('admin.teams.images.select_photo') }}
                            </label>
                            <input type="file" name="foto_plantilla" id="foto_plantilla" class="hidden-file-input" accept="image/*" data-image-input="#equipo-plantilla-create-preview">

                            <span class="nota-subida">{{ __('admin.teams.images.template_note_create') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-acciones-ficha">
                <a href="{{ route('equipos.index') }}" class="btn-nuevo" title="{{ __('admin.teams.actions.cancel_back') }}">{{ __('admin.common.cancel') }}</a>
                <button type="submit" class="btn-actualizar-ficha btn-actualizar-azul" title="{{ __('admin.teams.actions.register') }}">
                    <i class="fas fa-save"></i> {{ __('admin.teams.create.submit') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const localCheckbox = document.getElementById('es_local');
    const plantillaCard = document.getElementById('foto-plantilla-card');
    const plantillaInput = document.getElementById('foto_plantilla');

    if (!localCheckbox || !plantillaCard) {
        return;
    }

    const togglePlantillaCard = () => {
        const isLocal = localCheckbox.checked;
        plantillaCard.style.display = isLocal ? '' : 'none';

        if (!isLocal && plantillaInput) {
            plantillaInput.value = '';
        }
    };

    localCheckbox.addEventListener('change', togglePlantillaCard);
    togglePlantillaCard();
});
</script>
@endpush
