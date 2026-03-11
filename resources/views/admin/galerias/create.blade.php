@extends('layouts.admin')

@section('contenido_admin')
<header class="header-admin admin-header-spaced">
    <h2><i class="fas fa-images"></i> Crear Nueva Galería</h2>
    <a href="{{ route('galerias.index') }}" class="btn-nuevo btn-nuevo-secondary">
        <i class="fas fa-arrow-left"></i> Volver al listado
    </a>
</header>

@if ($errors->any())
    <div class="admin-error-box">
        <strong>Revisa el formulario:</strong>
        <ul class="admin-error-list">
            @foreach ($errors->all() as $error)
                <li><i class="fas fa-exclamation-circle"></i> {{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="pizarra-admin">
    <form action="{{ route('galerias.store') }}" method="POST" enctype="multipart/form-data" class="formulario-admin">
        @csrf
        <input type="hidden" name="imagen_principal_nueva" value="0" data-imagen-principal-nueva>
        <div class="grid-formulario">
            <div class="campo campo-span-2">
                <label>Título de la galería</label>
                <input type="text" name="titulo" value="{{ old('titulo') }}" placeholder="Ej: Final senior contra Gandia" required>
            </div>

            <div class="campo">
                <label>Tipo</label>
                <select name="tipo" required>
                    <option value="">Selecciona un tipo</option>
                    @foreach($tipos as $clave => $label)
                        <option value="{{ $clave }}" @selected(old('tipo') === $clave)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="campo">
                <label>Imágenes</label>
                <input type="file" name="imagenes[]" accept="image/*" multiple required data-acumular-imagenes>
                <small class="admin-help-text">Puedes seleccionar varias tandas. Las imágenes se irán acumulando hasta guardar.</small>
            </div>

            <div class="campo campo-span-2">
                <label>Descripción</label>
                <textarea name="descripcion" rows="5" class="admin-textarea" placeholder="Resumen del álbum o del evento" required>{{ old('descripcion') }}</textarea>
            </div>
        </div>

        <div class="campo campo-span-2 galeria-upload-summary" data-upload-summary hidden>
            <label>Imágenes preparadas para subir</label>
            <small class="admin-help-text">Marca cuál será la imagen principal de la galería.</small>
            <div class="galeria-admin-grid" data-upload-preview></div>
        </div>

        <div class="form-acciones-ficha form-acciones-ficha-top">
            <a href="{{ route('galerias.index') }}" class="btn-cancelar-ficha">Cancelar</a>
            <button type="submit" class="btn-actualizar-ficha btn-actualizar-rojo">
                <i class="fas fa-save"></i> Guardar Galería
            </button>
        </div>
    </form>
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
                        <button type="button" class="galeria-upload-remove" data-index="${index}">Quitar</button>
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
