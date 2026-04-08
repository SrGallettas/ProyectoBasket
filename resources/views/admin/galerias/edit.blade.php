@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <x-admin-edit-header
        titulo="Editar Galería"
        subtitulo="Actualiza la información del álbum y gestiona sus imágenes"
        :backRoute="route('galerias.index')"
    />

    @if ($errors->any())
    <div class="cuadro-errores-admin">
        <strong>Revisa el formulario:</strong>
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
                <h3><i class="fas fa-images"></i> Datos de la Galería</h3>

                <div class="campo-ficha">
                    <label>Título de la galería</label>
                    <input type="text" name="titulo" value="{{ old('titulo', $galeria->titulo) }}" required class="input-ficha">
                </div>

                <div class="campo-ficha">
                    <label>Tipo</label>
                    <select name="tipo" required class="input-ficha input-white">
                        @foreach($tipos as $clave => $label)
                            <option value="{{ $clave }}" @selected(old('tipo', $galeria->tipo) === $clave)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="campo-ficha">
                    <label>Descripción</label>
                    <textarea name="descripcion" rows="6" class="input-ficha admin-textarea" required>{{ old('descripcion', $galeria->descripcion) }}</textarea>
                </div>

                <div class="campo-ficha">
                    <label>Slug actual</label>
                    <input type="text" value="{{ $galeria->slug }}" readonly class="input-ficha">
                    <p class="input-help-text">Se recalcula automáticamente a partir del título.</p>
                </div>

                <div class="campo-ficha">
                    <label>Visibilidad en la web</label>
                    <div class="checkbox-wrapper checkbox-wrapper-ficha">
                        <input type="checkbox" name="oculto" value="1" {{ old('oculto', $galeria->oculto) ? 'checked' : '' }}>
                        <span>Ocultar galería de la web pública</span>
                    </div>
                </div>
            </div>

            <div class="seccion-foto-ficha">
                <div class="card-imagen-horizontal">
                    <div class="preview-foto-noticia galeria-preview-vacio">
                        <div class="galeria-preview-placeholder">
                            <i class="fas fa-images"></i>
                            <span>Añadir nuevas imágenes</span>
                        </div>
                    </div>

                    <div class="info-subida-noticia">
                        <h3>Imágenes de la Galería</h3>
                        <p>Puedes seguir añadiendo tandas nuevas. Se acumularán antes de guardar y podrán pasar a ser la portada principal.</p>

                        <label for="imagenes_galeria_edit" class="btn-cambiar-foto">
                            <i class="fas fa-camera"></i> Añadir imágenes
                        </label>
                        <input type="file" name="imagenes[]" id="imagenes_galeria_edit" accept="image/*" multiple data-acumular-imagenes class="hidden-file-input">

                        <span class="nota-subida">Si no seleccionas nuevas imágenes, se mantendrán únicamente las actuales.</span>
                    </div>
                </div>
            </div>

            <div class="campo-ficha galeria-upload-summary" data-upload-summary hidden>
                <label>Nuevas imágenes pendientes de subir</label>
                <p class="input-help-text">Si quieres, una de estas nuevas imágenes puede pasar a ser la principal.</p>
                <div class="galeria-admin-grid" data-upload-preview></div>
            </div>

            <div class="campo-ficha">
                <label>Imágenes actuales</label>
                <p class="input-help-text">Selecciona la portada principal o marca las imágenes que quieras eliminar.</p>
                <div class="galeria-admin-grid" data-existing-gallery-grid>
                    @forelse($galeria->imagenes as $imagen)
                        <div class="galeria-admin-card" data-existing-image-card>
                            <img src="{{ $imagen->image_url }}" alt="Imagen {{ $galeria->titulo }}">
                            <label class="galeria-admin-principal">
                                <input type="radio" name="imagen_principal_existente" value="{{ $imagen->id }}" @checked($imagen->es_principal)>
                                Imagen principal
                            </label>
                            <label class="galeria-admin-remove galeria-admin-remove-toggle">
                                <input type="checkbox" name="eliminar_imagenes[]" value="{{ $imagen->id }}">
                                <span>Quitar imagen</span>
                            </label>
                        </div>
                    @empty
                        <p class="admin-subtle-text">Esta galería todavía no tiene imágenes.</p>
                    @endforelse
                </div>
            </div>

            <div class="form-acciones-ficha">
                <a href="{{ route('galerias.index') }}" class="btn-cancelar-ficha" title="Cancelar y volver al listado de galerias">Cancelar</a>
                <button type="submit" class="btn-actualizar-ficha" title="Actualizar galeria">
                    <i class="fas fa-sync-alt"></i> ACTUALIZAR FICHA
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
                            Imagen principal
                        </label>
                        <button type="button" class="galeria-upload-remove" data-index="${index}" title="Quitar imagen">Quitar</button>
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
