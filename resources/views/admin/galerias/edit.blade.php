@extends('layouts.admin')

@section('title', __('admin.galleries.meta.edit_title'))

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <x-admin-edit-header
        :titulo="__('admin.galleries.edit.title')"
        :subtitulo="__('admin.galleries.edit.subtitle')"
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
        <form action="{{ route('galerias.update', $galeria->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="imagen_principal_nueva" value="" data-imagen-principal-nueva>

            <div class="seccion-form-ficha seccion-personal">
                <h3><i class="fas fa-images"></i> {{ __('admin.galleries.sections.data') }}</h3>

                <div class="campo-ficha">
                    <label>{{ __('admin.galleries.fields.title') }}</label>
                    <input type="text" name="titulo" value="{{ old('titulo', $galeria->titulo) }}" class="input-ficha">
                </div>

                <div class="campo-ficha">
                    <label>{{ __('admin.galleries.fields.type') }}</label>
                    <select name="tipo" class="input-ficha input-white">
                        @foreach($tipos as $clave => $label)
                            <option value="{{ $clave }}" @selected(old('tipo', $galeria->tipo) === $clave)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="campo-ficha">
                    <label>{{ __('admin.galleries.fields.description') }}</label>
                    <textarea name="descripcion" rows="6" class="input-ficha admin-textarea">{{ old('descripcion', $galeria->descripcion) }}</textarea>
                </div>

                <div class="campo-ficha">
                    <label>{{ __('admin.galleries.fields.current_slug') }}</label>
                    <input type="text" value="{{ $galeria->slug }}" readonly class="input-ficha">
                    <p class="input-help-text">{{ __('admin.galleries.fields.slug_help') }}</p>
                </div>

                <div class="campo-ficha">
                    <label>{{ __('admin.galleries.fields.visibility') }}</label>
                    <div class="checkbox-wrapper checkbox-wrapper-ficha">
                        <input type="checkbox" name="oculto" value="1" {{ old('oculto', $galeria->oculto) ? 'checked' : '' }}>
                        <span>{{ __('admin.galleries.fields.hide_public') }}</span>
                    </div>
                </div>
            </div>

            <div class="seccion-foto-ficha">
                <div class="card-imagen-horizontal">
                        <div class="preview-foto-noticia galeria-preview-vacio">
                            <div class="galeria-preview-placeholder">
                                <i class="fas fa-images"></i>
                                <span>{{ __('admin.galleries.images.add_new') }}</span>
                            </div>
                        </div>

                        <div class="info-subida-noticia">
                        <h3>{{ __('admin.galleries.images.title') }}</h3>
                        <p>{{ __('admin.galleries.images.help_edit') }}</p>

                        <label for="imagenes_galeria_edit" class="btn-cambiar-foto">
                            <i class="fas fa-camera"></i> {{ __('admin.galleries.images.add_images') }}
                        </label>
                        <input type="file" name="imagenes[]" id="imagenes_galeria_edit" accept="image/*" multiple data-acumular-imagenes class="hidden-file-input">

                        <span class="nota-subida">{{ __('admin.galleries.images.note_edit') }}</span>
                    </div>
                </div>
            </div>

            <div class="campo-ficha galeria-upload-summary" data-upload-summary hidden>
                <label>{{ __('admin.galleries.images.new_pending') }}</label>
                <p class="input-help-text">{{ __('admin.galleries.images.new_pending_help') }}</p>
                <div class="galeria-admin-grid" data-upload-preview></div>
            </div>

            <div class="campo-ficha">
                <label>{{ __('admin.galleries.images.current_images') }}</label>
                <p class="input-help-text">{{ __('admin.galleries.images.current_images_help') }}</p>
                <div class="galeria-admin-grid" data-existing-gallery-grid>
                    @forelse($galeria->imagenes as $imagen)
                        <div class="galeria-admin-card" data-existing-image-card>
                            <img src="{{ $imagen->image_url }}" alt="{{ __('admin.galleries.fields.image_alt', ['title' => $galeria->titulo]) }}">
                            <label class="galeria-admin-principal">
                                <input type="radio" name="imagen_principal_existente" value="{{ $imagen->id }}" @checked($imagen->es_principal)>
                                {{ __('admin.galleries.images.main_image') }}
                            </label>
                            <label class="galeria-admin-remove galeria-admin-remove-toggle">
                                <input type="checkbox" name="eliminar_imagenes[]" value="{{ $imagen->id }}">
                                <span>{{ __('admin.galleries.images.remove_image') }}</span>
                            </label>
                        </div>
                    @empty
                        <p class="admin-subtle-text">{{ __('admin.galleries.empty.no_images_yet') }}</p>
                    @endforelse
                </div>
            </div>

            <div class="form-acciones-ficha">
                <a href="{{ route('galerias.index') }}" class="btn-cancelar-ficha" title="{{ __('admin.galleries.actions.cancel_back') }}">{{ __('admin.common.cancel') }}</a>
                <button type="submit" class="btn-actualizar-ficha" title="{{ __('admin.galleries.actions.update') }}">
                    <i class="fas fa-sync-alt"></i> {{ __('admin.galleries.edit.submit') }}
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
    const radiosExistentes = document.querySelectorAll('input[name="imagen_principal_existente"]');
    const existingGrid = document.querySelector('[data-existing-gallery-grid]');

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
            const checked = Number(principalNueva.value) === index ? 'checked' : '';

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
        if (archivos.length === 1 && principalNueva.value === '') {
            principalNueva.value = '0';
            radiosExistentes.forEach((radio) => {
                radio.checked = false;
            });
        }
        actualizarInput();
    });

    preview.addEventListener('change', function (event) {
        if (event.target.name !== 'selector_principal_nueva') {
            return;
        }

        principalNueva.value = event.target.value;
        radiosExistentes.forEach((radio) => {
            radio.checked = false;
        });
    });

    radiosExistentes.forEach((radio) => {
        radio.addEventListener('change', function () {
            if (radio.checked) {
                principalNueva.value = '';
            }
        });
    });

    existingGrid?.addEventListener('click', function (event) {
        const removeLabel = event.target.closest('.galeria-admin-remove-toggle');
        if (!removeLabel) {
            return;
        }

        event.preventDefault();

        const checkbox = removeLabel.querySelector('input[name="eliminar_imagenes[]"]');
        const card = removeLabel.closest('[data-existing-image-card]');
        if (!checkbox || !card) {
            return;
        }

        checkbox.checked = true;
        card.classList.add('galeria-admin-card-hidden');

        const radioActual = card.querySelector('input[name="imagen_principal_existente"]');
        if (radioActual?.checked) {
            const radiosVisibles = Array.from(document.querySelectorAll('input[name="imagen_principal_existente"]'))
                .filter((radio) => !radio.closest('[data-existing-image-card]')?.classList.contains('galeria-admin-card-hidden'));

            if (radiosVisibles.length > 0) {
                radiosVisibles[0].checked = true;
                principalNueva.value = '';
            } else if (archivos.length > 0) {
                principalNueva.value = '0';
            } else {
                principalNueva.value = '';
            }
        }
    });

    preview.addEventListener('click', function (event) {
        if (!event.target.matches('[data-index]')) {
            return;
        }

        const index = Number(event.target.dataset.index);
        archivos = archivos.filter((_, currentIndex) => currentIndex !== index);
        const principalActual = principalNueva.value === '' ? null : Number(principalNueva.value);

        if (principalActual === index) {
            principalNueva.value = archivos.length > 0 ? '0' : '';
        } else if (principalActual !== null && principalActual > index) {
            principalNueva.value = String(principalActual - 1);
        }

        actualizarInput();
    });
});
</script>
@endsection
