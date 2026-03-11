@extends('layouts.admin')

@section('contenido_admin')
<header class="header-admin admin-header-spaced">
    <div>
        <h2><i class="fas fa-edit"></i> Editar Galería</h2>
        <p class="admin-editing-text">Estás editando: <strong>{{ $galeria->titulo }}</strong></p>
    </div>
    <a href="{{ route('galerias.index') }}" class="btn-nuevo btn-nuevo-secondary">
        <i class="fas fa-arrow-left"></i> Volver al listado
    </a>
</header>

@if ($errors->any())
    <div class="alerta-error">
        <strong>Revisa el formulario:</strong>
        <ul class="admin-error-list">
            @foreach ($errors->all() as $error)
                <li><i class="fas fa-times"></i> {{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="pizarra-admin">
    <form action="{{ route('galerias.update', $galeria->id) }}" method="POST" enctype="multipart/form-data" class="formulario-admin">
        @csrf
        @method('PUT')
        <input type="hidden" name="imagen_principal_nueva" value="" data-imagen-principal-nueva>

        <div class="grid-formulario">
            <div class="campo campo-span-2">
                <label>Título de la galería</label>
                <input type="text" name="titulo" value="{{ old('titulo', $galeria->titulo) }}" required>
            </div>

            <div class="campo">
                <label>Tipo</label>
                <select name="tipo" required>
                    @foreach($tipos as $clave => $label)
                        <option value="{{ $clave }}" @selected(old('tipo', $galeria->tipo) === $clave)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="campo">
                <label>Añadir más imágenes</label>
                <input type="file" name="imagenes[]" accept="image/*" multiple data-acumular-imagenes>
                <small class="admin-help-text">Puedes elegir archivos varias veces. Se acumularán antes de guardar.</small>
            </div>

            <div class="campo campo-span-2">
                <label>Descripción</label>
                <textarea name="descripcion" rows="5" class="admin-textarea" required>{{ old('descripcion', $galeria->descripcion) }}</textarea>
            </div>

            <div class="campo campo-span-2">
                <label>Slug actual</label>
                <input type="text" value="{{ $galeria->slug }}" readonly>
                <small class="admin-help-text">Se recalcula automáticamente a partir del título.</small>
            </div>
        </div>

        <div class="campo campo-span-2 galeria-upload-summary" data-upload-summary hidden>
            <label>Nuevas imágenes pendientes de subir</label>
            <small class="admin-help-text">Si quieres, una de estas nuevas imágenes puede pasar a ser la principal.</small>
            <div class="galeria-admin-grid" data-upload-preview></div>
        </div>

        <div class="galeria-admin-grid">
            @forelse($galeria->imagenes as $imagen)
                <label class="galeria-admin-card">
                    <img src="{{ asset('galerias/' . $imagen->archivo) }}" alt="Imagen {{ $galeria->titulo }}">
                    <span class="galeria-admin-principal">
                        <input type="radio" name="imagen_principal_existente" value="{{ $imagen->id }}" @checked($imagen->es_principal)>
                        Imagen principal
                    </span>
                    <span class="galeria-admin-remove">
                        <input type="checkbox" name="eliminar_imagenes[]" value="{{ $imagen->id }}">
                        Eliminar esta imagen
                    </span>
                </label>
            @empty
                <p class="admin-subtle-text">Esta galería todavía no tiene imágenes.</p>
            @endforelse
        </div>

        <div class="form-acciones-ficha">
            <a href="{{ route('galerias.index') }}" class="btn-cancelar-ficha">Cancelar</a>
            <button type="submit" class="btn-actualizar-ficha">
                <i class="fas fa-sync-alt"></i> Actualizar Galería
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
    const radiosExistentes = document.querySelectorAll('input[name="imagen_principal_existente"]');

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
