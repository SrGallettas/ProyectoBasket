@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <header class="header-ficha">
        <div>
            <h2>Añadir Nueva Galería</h2>
            <p>Organiza un nuevo álbum de imágenes para la web del club</p>
        </div>
        <a href="{{ route('galerias.index') }}" class="btn-nuevo btn-nuevo-secondary" title="Volver al listado de galerias">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
    </header>

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
        <form action="{{ route('galerias.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="imagen_principal_nueva" value="0" data-imagen-principal-nueva>

            <div class="seccion-form-ficha seccion-personal">
                <h3><i class="fas fa-images"></i> Datos de la Galería</h3>

                <div class="campo-ficha">
                    <label>Título de la galería</label>
                    <input
                        type="text"
                        name="titulo"
                        value="{{ old('titulo') }}"
                        placeholder="Ej: Final senior contra Gandia"
                        required
                        class="input-ficha"
                    >
                </div>

                <div class="campo-ficha">
                    <label>Tipo</label>
                    <select name="tipo" required class="input-ficha input-white">
                        <option value="">Selecciona un tipo</option>
                        @foreach($tipos as $clave => $label)
                            <option value="{{ $clave }}" @selected(old('tipo') === $clave)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="campo-ficha">
                    <label>Descripción</label>
                    <textarea
                        name="descripcion"
                        rows="6"
                        class="input-ficha admin-textarea"
                        placeholder="Resumen del álbum o del evento"
                        required
                    >{{ old('descripcion') }}</textarea>
                </div>

                <div class="campo-ficha">
                    <label>Visibilidad en la web</label>
                    <div class="checkbox-wrapper checkbox-wrapper-ficha">
                        <input type="checkbox" name="oculto" value="1" {{ old('oculto') ? 'checked' : '' }}>
                        <span>Ocultar galería de la web pública</span>
                    </div>
                </div>
            </div>

            <div class="seccion-foto-ficha">
                <div class="card-imagen-horizontal">
                    <div class="preview-foto-noticia galeria-preview-vacio">
                        <div class="galeria-preview-placeholder">
                            <i class="fas fa-images"></i>
                            <span>Sin imágenes cargadas</span>
                        </div>
                    </div>

                    <div class="info-subida-noticia">
                        <h3>Imágenes de la Galería</h3>
                        <p>Puedes añadir varias tandas de imágenes y después elegir cuál será la portada principal.</p>

                        <label for="imagenes_galeria_create" class="btn-cambiar-foto">
                            <i class="fas fa-camera"></i> Seleccionar imágenes
                        </label>
                        <input type="file" name="imagenes[]" id="imagenes_galeria_create" accept="image/*" multiple required data-acumular-imagenes class="hidden-file-input">

                        <span class="nota-subida">Formatos: JPG, PNG o WEBP. Las imágenes se guardarán dentro de la carpeta de la galería.</span>
                    </div>
                </div>
            </div>

            <div class="campo-ficha galeria-upload-summary" data-upload-summary hidden>
                <label>Imágenes preparadas para subir</label>
                <p class="input-help-text">Marca cuál será la imagen principal de la galería.</p>
                <div class="galeria-admin-grid" data-upload-preview></div>
            </div>

            <div class="form-acciones-ficha">
                <a href="{{ route('galerias.index') }}" class="btn-cancelar-ficha" title="Cancelar y volver al listado de galerias">Cancelar</a>
                <button type="submit" class="btn-actualizar-ficha btn-actualizar-rojo" title="Guardar galeria">
                    <i class="fas fa-save"></i> GUARDAR GALERÍA
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
