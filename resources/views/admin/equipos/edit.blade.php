@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <x-admin-edit-header
        :titulo="'Editar Equipo: ' . $equipo->nombre"
        subtitulo="Actualiza los datos del equipo y su imagen oficial"
        :backRoute="route('equipos.index')"
    />

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
        <form action="{{ route('equipos.update', $equipo->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid-formulario-fichas">
                <div class="seccion-form-ficha seccion-personal">
                    <h3><i class="fas fa-shield-alt"></i> Datos del Equipo</h3>

                    <div class="campo-ficha">
                        <label>Nombre del Equipo</label>
                        <input
                            type="text"
                            name="nombre"
                            value="{{ old('nombre', $equipo->nombre) }}"
                            placeholder="Ej: Senior A"
                            required
                            class="input-ficha"
                        >
                    </div>

                    <div class="campo-ficha">
                        <label>Categoría</label>
                        <select name="categoria" required class="input-ficha input-white">
                            <option value="" disabled {{ old('categoria', $equipo->categoria) == '' ? 'selected' : '' }}>Selecciona...</option>
                            @foreach(['Senior', 'Junior', 'Cadete', 'Infantil', 'Alevín'] as $categoria)
                                <option value="{{ $categoria }}" {{ old('categoria', $equipo->categoria) == $categoria ? 'selected' : '' }}>
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
                                {{ old('es_local', $equipo->es_local) ? 'checked' : '' }}
                            >
                            <span>Sí, es equipo local</span>
                        </div>
                    </div>

                    <div class="campo-ficha">
                        <label>Uso de la imagen</label>
                        <p class="input-help-text">Si subes una nueva imagen, sustituirá la actual del equipo.</p>
                    </div>
                </div>

                <div class="seccion-foto-ficha">
                    <div class="card-imagen-horizontal">
                        <div class="preview-foto-noticia">
                            <img
                                src="{{ $equipo->imagen_club ? asset('escudos/' . $equipo->imagen_club) : asset('img/basket.jpeg') }}"
                                alt="Imagen actual del equipo"
                            >
                        </div>

                        <div class="info-subida-noticia">
                            <h3>Imagen del Equipo</h3>
                            <p>Para que luzca bien, usa un escudo nítido o una imagen cuadrada del equipo.</p>

                            <label for="imagen_club" class="btn-cambiar-foto">
                                <i class="fas fa-camera"></i> Cambiar imagen
                            </label>
                            <input type="file" name="imagen_club" id="imagen_club" class="hidden-file-input">

                            <span class="nota-subida">Si no seleccionas ninguna, se mantendrá la actual.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-acciones-ficha">
                <a href="{{ route('equipos.index') }}" class="btn-cancelar-ficha">Cancelar</a>
                <button type="submit" class="btn-actualizar-ficha">
                    <i class="fas fa-sync-alt"></i> ACTUALIZAR FICHA
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
