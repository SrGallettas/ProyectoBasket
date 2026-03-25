@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <x-admin-edit-header
        titulo="Ficha de Edición"
        :subtitulo="'Actualizando a: <strong>' . e($jugador->nombre . ' ' . $jugador->apellido) . '</strong>'"
        :backRoute="route('jugadores.index')"
    />

    @if ($errors->any())
    <div class="admin-error-box">
        <strong>¡Vaya! Algo ha ido mal:</strong>
        {{-- Añadimos list-style: none y quitamos el padding --}}
        <ul class="admin-error-list">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="pizarra-ficha">
        <form action="{{ route('jugadores.update', $jugador->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid-formulario-fichas">
                
                <div class="seccion-form-ficha seccion-personal">
                    <h3><i class="fas fa-user"></i> Datos Personales</h3>
                    
                    <div class="campo-ficha">
                        <label>Nombre</label>
                        <input type="text" name="nombre" value="{{ $jugador->nombre }}" required class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                        <label>Apellido</label>
                        <input type="text" name="apellido" value="{{ $jugador->apellido }}" required class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                        <label>Fecha de Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" value="{{ $jugador->fecha_nacimiento }}" class="input-ficha">
                    </div>
                </div>

                <div class="seccion-form-ficha seccion-deportiva">
                    <h3><i class="fas fa-basketball-ball"></i> Datos Deportivos</h3>

                    <div class="jugador-form-double-grid">
                        <div class="campo-ficha">
                            <label>Dorsal</label>
                            <input type="number" name="dorsal" value="{{ $jugador->dorsal }}" class="input-ficha">
                        </div>
                        <div class="campo-ficha">
                            <label>Posición</label>
                            <select name="posicion" class="input-ficha input-white">
                                @foreach(['Base', 'Escolta', 'Alero', 'Ala-Pívot', 'Pívot'] as $pos)
                                    <option value="{{ $pos }}" {{ $jugador->posicion == $pos ? 'selected' : '' }}>{{ $pos }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="campo-ficha">
                        <label>Equipo Actual</label>
                        <select name="equipo_id" required class="input-ficha input-white">
                            @foreach($equipos as $equipo)
                                <option value="{{ $equipo->id }}" {{ $jugador->equipo_id == $equipo->id ? 'selected' : '' }}>
                                    {{ $equipo->nombre }} ({{ $equipo->categoria }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="campo-ficha">
                        <label>Visibilidad en la web</label>
                        <div class="checkbox-wrapper checkbox-wrapper-ficha">
                            <input type="checkbox" name="oculto" value="1" {{ old('oculto', $jugador->oculto) ? 'checked' : '' }}>
                            <span>Ocultar jugador de la web pública</span>
                        </div>
                    </div>
                </div>

                <div class="seccion-foto-ficha">
                    <div class="card-imagen-horizontal">
                        <div class="preview-foto-noticia">
                            @if($jugador->imagen_jugador)
                                <img src="{{ asset('jugadores/' . $jugador->imagen_jugador) }}" alt="Foto actual" onerror="this.onerror=null;this.src='{{ asset('img/club_14.png') }}';">
                            @else
                                <img src="{{ asset('img/club_14.png') }}" alt="Foto por defecto">
                            @endif
                        </div>

                        <div class="info-subida-noticia">
                            <h3>Imagen del Jugador</h3>
                            <p>Para que luzca bien, usa una fotografía vertical o cuadrada.</p>

                            <label for="imagen_jugador" class="btn-cambiar-foto">
                                <i class="fas fa-camera"></i> Cambiar fotografía
                            </label>
                            <input type="file" name="imagen_jugador" id="imagen_jugador" class="hidden-file-input">

                            <span class="nota-subida">Si no seleccionas ninguna, se mantendrá la actual.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-acciones-ficha">
                <a href="{{ route('jugadores.index') }}" class="btn-cancelar-ficha" title="Cancelar y volver al listado de jugadores">Cancelar</a>
                <button type="submit" class="btn-actualizar-ficha" title="Actualizar jugador">
                    <i class="fas fa-sync-alt"></i> ACTUALIZAR FICHA
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
