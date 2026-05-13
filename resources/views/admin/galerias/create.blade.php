@extends('layouts.admin')

@section('title', __('admin.galleries.meta.create_title'))

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <x-admin-edit-header
        :titulo="__('admin.galleries.create.title')"
        :subtitulo="__('admin.galleries.create.subtitle')"
        :backRoute="route('galerias.index')"
    />

    @if ($errors->any())
    <div class="cuadro-errores-admin">
        <strong>{{ __('admin.galleries.form_review') }}</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="pizarra-ficha">
        <form action="{{ route('galerias.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="imagen_principal_nueva" value="0" data-imagen-principal-nueva>

            <div class="seccion-form-ficha seccion-personal">
                <h3><i class="fas fa-images"></i> {{ __('admin.galleries.sections.data') }}</h3>

                <div class="campo-ficha">
                    <label>{{ __('admin.galleries.fields.title') }}</label>
                    <input
                        type="text"
                        name="titulo"
                        value="{{ old('titulo') }}"
                        placeholder="{{ __('admin.galleries.fields.title_placeholder') }}"
                        class="input-ficha"
                    >
                </div>

                <div class="campo-ficha">
                    <label>{{ __('admin.galleries.fields.type') }}</label>
                    <select name="tipo" class="input-ficha input-white">
                        <option value="">{{ __('admin.galleries.fields.select_type') }}</option>
                        @foreach($tipos as $clave => $label)
                            <option value="{{ $clave }}" @selected(old('tipo') === $clave)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="campo-ficha">
                    <label>{{ __('admin.galleries.fields.description') }}</label>
                    <textarea
                        name="descripcion"
                        rows="6"
                        class="input-ficha admin-textarea"
                        placeholder="{{ __('admin.galleries.fields.description_placeholder') }}"
                    >{{ old('descripcion') }}</textarea>
                </div>

                <div class="campo-ficha">
                    <label>{{ __('admin.galleries.fields.visibility') }}</label>
                    <div class="checkbox-wrapper checkbox-wrapper-ficha">
                        <input type="checkbox" name="oculto" value="1" {{ old('oculto') ? 'checked' : '' }}>
                        <span>{{ __('admin.galleries.fields.hide_public') }}</span>
                    </div>
                </div>
            </div>

            <div class="seccion-foto-ficha">
                <div class="card-imagen-horizontal">
                        <div class="preview-foto-noticia galeria-preview-vacio">
                            <div class="galeria-preview-placeholder">
                                <i class="fas fa-images"></i>
                                <span>{{ __('admin.galleries.images.none_loaded') }}</span>
                            </div>
                        </div>

                        <div class="info-subida-noticia">
                        <h3>{{ __('admin.galleries.images.title') }}</h3>
                        <p>{{ __('admin.galleries.images.help_create') }}</p>

                        <label for="imagenes_galeria_create" class="btn-cambiar-foto">
                            <i class="fas fa-camera"></i> {{ __('admin.galleries.images.select') }}
                        </label>
                        <input type="file" name="imagenes[]" id="imagenes_galeria_create" accept="image/*" multiple data-acumular-imagenes class="hidden-file-input">

                        <span class="nota-subida">{{ __('admin.galleries.images.note_create') }}</span>
                    </div>
                </div>
            </div>

            <div class="campo-ficha galeria-upload-summary" data-upload-summary hidden>
                <label>{{ __('admin.galleries.images.pending') }}</label>
                <p class="input-help-text">{{ __('admin.galleries.images.pending_help') }}</p>
                <div class="galeria-admin-grid" data-upload-preview></div>
            </div>

            <div class="form-acciones-ficha">
                <a href="{{ route('galerias.index') }}" class="btn-cancelar-ficha" title="{{ __('admin.galleries.actions.cancel_back') }}">{{ __('admin.common.cancel') }}</a>
                <button type="submit" class="btn-actualizar-ficha btn-actualizar-rojo" title="{{ __('admin.galleries.actions.save') }}">
                    <i class="fas fa-save"></i> {{ __('admin.galleries.create.submit') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.querySelector('[data-acumular-imagenes]');
    const preview = document.querySelector('[data-upload-preview]');
    const summary = document.querySelector('[data-upload-summary]');
    const principalNueva = document.querySelector('[data-imagen-principal-nueva]');

    if (!input || !preview || !summary) {
        return;
    }

    let archivos = [];

    const actualizarInput = () => {
        const dataTransfer = new DataTransfer();
        archivos.forEach((archivo) => dataTransfer.items.add(archivo));
        input.files = dataTransfer.files;
        summary.hidden = archivos.length === 0;
        renderizarPreview();
    };

    const renderizarPreview = () => {
        preview.innerHTML = '';

        archivos.forEach((archivo, index) => {
            const reader = new FileReader();
            const checked = Number(principalNueva.value || 0) === index ? 'checked' : '';

            reader.onload = function (event) {
                const item = document.createElement('div');
                item.className = 'galeria-upload-item';
                item.innerHTML = `
                    <img src="${event.target.result}" alt="${archivo.name}">
                    <div class="galeria-upload-item-body">
                        <strong>${archivo.name}</strong>
                        <label class="galeria-upload-principal">
                            <input type="radio" name="selector_principal_nueva" value="${index}" ${checked}>
                            ${@json(__('admin.galleries.images.main_image'))}
                        </label>
                        <button type="button" class="galeria-upload-remove" data-index="${index}" title="${@json(__('admin.galleries.images.remove_image'))}">${@json(__('admin.galleries.images.remove'))}</button>
                    </div>
                `;
                preview.appendChild(item);
            };

            reader.readAsDataURL(archivo);
        });
    };

    input.addEventListener('change', function () {
        archivos = archivos.concat(Array.from(input.files));
        if (archivos.length === 1) {
            principalNueva.value = '0';
        }
        actualizarInput();
    });

    preview.addEventListener('change', function (event) {
        if (event.target.name !== 'selector_principal_nueva') {
            return;
        }

        principalNueva.value = event.target.value;
    });

    preview.addEventListener('click', function (event) {
        if (!event.target.matches('[data-index]')) {
            return;
        }

        const index = Number(event.target.dataset.index);
        archivos = archivos.filter((_, currentIndex) => currentIndex !== index);
        const principalActual = Number(principalNueva.value || 0);

        if (principalActual === index) {
            principalNueva.value = '0';
        } else if (principalActual > index) {
            principalNueva.value = String(principalActual - 1);
        }

        actualizarInput();
    });
});
</script>
@endsection
