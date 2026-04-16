@extends('layouts.admin')

@section('contenido_admin')
@php($mostrarFotoPlantilla = old('es_local'))
<div class="contenedor-edit-jugador">
    <header class="header-ficha">
        <div>
            <h2>Añadir Nuevo Equipo</h2>
            <p>Introduce los datos para registrar un nuevo equipo del club</p>
        </div>
        <a href="{{ route('equipos.index') }}" class="btn-nuevo btn-nuevo-secondary" title="Volver al listado de equipos">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
    </header>

    @if ($errors->any())
    <div class="cuadro-errores-admin">
        <strong>¡Vaya! Algo ha ido mal:</strong>
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
                    <h3><i class="fas fa-shield-alt"></i> Datos del Equipo</h3>

                    <div class="campo-ficha">
                        <label>Nombre del Equipo</label>
                        <input
                            type="text"
                            name="nombre"
                            value="{{ old('nombre') }}"
                            placeholder="Ej: Senior A"
                            required
                            class="input-ficha"
                        >
                    </div>

                    <div class="campo-ficha">
                        <label>Categoría</label>
                        <select name="categoria" required class="input-ficha input-white">
                            <option value="" disabled {{ old('categoria') == '' ? 'selected' : '' }}>Selecciona...</option>
                            @foreach($categoryOptions as $categoria)
                                <option value="{{ $categoria }}" {{ old('categoria') == $categoria ? 'selected' : '' }}>
                                    {{ $categoria }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="seccion-form-ficha seccion-deportiva">
                    <h3><i class="fas fa-clipboard-list"></i> Configuración</h3>

                    <div class="campo-ficha">
                        <label>Estado del Equipo</label>
                        <div class="checkbox-wrapper checkbox-wrapper-ficha">
                            <input
                                type="checkbox"
                                name="es_local"
                                id="es_local"
                                value="1"
                                {{ old('es_local') ? 'checked' : '' }}
                            >
                            <span>Sí, es equipo local</span>
                        </div>
                    </div>

                    <div class="campo-ficha">
                        <label>Uso de la imagen</label>
                        <p class="input-help-text">Puedes subir un escudo propio o dejar la imagen por defecto del formulario por ahora.</p>
                    </div>

                    <div class="campo-ficha">
                        <label>Visibilidad en la web</label>
                        <div class="checkbox-wrapper checkbox-wrapper-ficha">
                            <input type="checkbox" name="oculto" value="1" {{ old('oculto') ? 'checked' : '' }}>
                            <span>Ocultar equipo de la web pública</span>
                        </div>
                    </div>
                </div>

                <div class="seccion-foto-ficha">
                    <div class="card-imagen-horizontal">
                        <div class="preview-foto-noticia">
                            <img id="equipo-create-preview" src="{{ asset('img/basket.jpeg') }}" alt="Imagen por defecto">
                        </div>

                        <div class="info-subida-noticia">
                            <h3>Imagen del Equipo</h3>
                            <p>Para que luzca bien, usa un escudo nítido o una imagen cuadrada del equipo.</p>

                            <label for="imagen_club" class="btn-cambiar-foto">
                                <i class="fas fa-camera"></i> Seleccionar imagen
                            </label>
                            <input type="file" name="imagen_club" id="imagen_club" class="hidden-file-input" accept="image/*" data-image-input="#equipo-create-preview">

                            <span class="nota-subida">Formatos: JPG o PNG. La imagen se guardará en public/escudos.</span>
                        </div>
                    </div>

                    <div
                        class="card-imagen-horizontal"
                        id="foto-plantilla-card"
                        @unless($mostrarFotoPlantilla) style="display: none;" @endunless
                    >
                        <div class="preview-foto-noticia">
                            <img id="equipo-plantilla-create-preview" src="{{ asset('img/equipo.png') }}" alt="Foto de plantilla por defecto">
                        </div>

                        <div class="info-subida-noticia">
                            <h3>Foto de Plantilla</h3>
                            <p>Esta imagen se usará en la cabecera pública de la página Equipo para esta categoría.</p>

                            <label for="foto_plantilla" class="btn-cambiar-foto">
                                <i class="fas fa-camera"></i> Seleccionar foto
                            </label>
                            <input type="file" name="foto_plantilla" id="foto_plantilla" class="hidden-file-input" accept="image/*" data-image-input="#equipo-plantilla-create-preview">

                            <span class="nota-subida">Formatos: JPG, PNG o WEBP. Si no subes nada se usará la imagen general por defecto.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-acciones-ficha">
                <a href="{{ route('equipos.index') }}" class="btn-cancelar-ficha" title="Cancelar y volver al listado de equipos">Cancelar</a>
                <button type="submit" class="btn-actualizar-ficha btn-actualizar-azul" title="Registrar equipo">
                    <i class="fas fa-save"></i> REGISTRAR EQUIPO
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
