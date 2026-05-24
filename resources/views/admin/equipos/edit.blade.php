@extends('layouts.admin')

@section('title', __('admin.teams.meta.edit_title'))

@section('contenido_admin')
@php($mostrarFotoPlantilla = old('es_local', $equipo->es_local))
<div class="contenedor-edit-jugador">
    <x-admin-edit-header
        :titulo="__('admin.teams.edit.title', ['name' => $equipo->nombre])"
        :subtitulo="__('admin.teams.edit.subtitle')"
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
        <form action="{{ route('equipos.update', $equipo->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid-formulario-fichas">
                <div class="seccion-form-ficha seccion-personal">
                    <h3><i class="fas fa-shield-alt"></i> {{ __('admin.teams.sections.data') }}</h3>

                    <div class="campo-ficha">
                        <label>{{ __('admin.teams.fields.team_name') }}</label>
                        <input
                            type="text"
                            name="nombre"
                            value="{{ old('nombre', $equipo->nombre) }}"
                            placeholder="{{ __('admin.teams.fields.team_name_placeholder') }}"
                            class="input-ficha"
                        >
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.teams.fields.category') }}</label>
                        <select name="categoria" class="input-ficha input-white">
                            @foreach($categoryOptions as $categoria)
                                <option value="{{ $categoria }}" @selected(old('categoria', $equipo->categoria) === $categoria)>{{ $categoria }}</option>
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
                                {{ old('es_local', $equipo->es_local) ? 'checked' : '' }}
                            >
                            <span>{{ __('admin.teams.fields.local_team') }}</span>
                        </div>
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.teams.fields.image_usage') }}</label>
                        <p class="input-help-text">{{ __('admin.teams.fields.image_usage_help_edit') }}</p>
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.teams.fields.visibility') }}</label>
                        <div class="checkbox-wrapper checkbox-wrapper-ficha">
                            <input type="checkbox" name="oculto" value="1" {{ old('oculto', $equipo->oculto) ? 'checked' : '' }}>
                            <span>{{ __('admin.teams.fields.hide_public') }}</span>
                        </div>
                    </div>
                </div>

                <div class="seccion-foto-ficha">
                    <div class="card-imagen-horizontal">
                        <div class="preview-foto-noticia">
                            <img
                                id="equipo-edit-preview"
                                src="{{ $equipo->imagen_club_url }}"
                                alt="{{ __('admin.teams.images.current_team_alt') }}"
                            >
                        </div>

                        <div class="info-subida-noticia">
                            <h3>{{ __('admin.teams.images.team_image') }}</h3>
                            <p>{{ __('admin.teams.images.team_image_help') }}</p>

                            <label for="imagen_club" class="btn-cambiar-foto">
                                <i class="fas fa-camera"></i> {{ __('admin.teams.images.change_image') }}
                            </label>
                            <input type="file" name="imagen_club" id="imagen_club" class="hidden-file-input" accept="image/*" data-image-input="#equipo-edit-preview">

                            <span class="nota-subida">{{ __('admin.teams.images.keep_current') }}</span>
                        </div>
                    </div>

                    <div
                        class="card-imagen-horizontal"
                        id="foto-plantilla-card"
                        @unless($mostrarFotoPlantilla) style="display: none;" @endunless
                    >
                        <div class="preview-foto-noticia {{ $equipo->foto_plantilla ? '' : 'preview-foto-noticia-vacia' }}" id="equipo-plantilla-edit-preview-wrap">
                            <img
                                id="equipo-plantilla-edit-preview"
                                src="{{ $equipo->foto_plantilla_url ?? '' }}"
                                alt="{{ __('admin.teams.images.current_template_alt') }}"
                                @if(! $equipo->foto_plantilla) style="display:none;" @endif
                            >
                            @unless($equipo->foto_plantilla)
                                <span>{{ __('admin.teams.images.template_empty') }}</span>
                            @endunless
                        </div>

                        <div class="info-subida-noticia">
                            <h3>{{ __('admin.teams.images.template_photo') }}</h3>
                            <p>{{ __('admin.teams.images.template_help_edit') }}</p>

                            <label for="foto_plantilla" class="btn-cambiar-foto">
                                <i class="fas fa-camera"></i> {{ __('admin.teams.images.change_photo') }}
                            </label>
                            <input type="file" name="foto_plantilla" id="foto_plantilla" class="hidden-file-input" accept="image/*" data-image-input="#equipo-plantilla-edit-preview">

                            <span class="nota-subida">{{ __('admin.teams.images.keep_template') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-acciones-ficha">
                <a href="{{ route('equipos.index') }}" class="btn-cancelar-ficha" title="{{ __('admin.teams.actions.cancel_back') }}">{{ __('admin.common.cancel') }}</a>
                <button type="submit" class="btn-actualizar-ficha" title="{{ __('admin.teams.actions.update') }}">
                    <i class="fas fa-sync-alt"></i> {{ __('admin.teams.edit.submit') }}
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
