@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    
    <header class="header-ficha">
        <div>
            <h2>Añadir Nuevo Jugador</h2>
            <p>Introduce los datos para inscribir al jugador en el club</p>
        </div>
        <a href="{{ route('jugadores.index') }}" class="btn-nuevo btn-nuevo-secondary" title="Volver al listado de jugadores">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
    </header>

     {{-- Bloque de errores (Estilo idéntico al de Merchandising) --}}
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
        <form action="{{ route('jugadores.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid-formulario-fichas">
                
                <div class="seccion-form-ficha seccion-personal">
                    <h3><i class="fas fa-user-plus"></i> Datos Personales</h3>
                    
                    <div class="campo-ficha">
                        <label>Nombre</label>
                        <input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Nombre del jugador" required class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                        <label>Apellido</label>
                        <input type="text" name="apellido" value="{{ old('apellido') }}" placeholder="Apellidos" required class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                        <label>Fecha de Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" class="input-ficha">
                    </div>
                </div>

                <div class="seccion-form-ficha seccion-deportiva">
                    <h3><i class="fas fa-basketball-ball"></i> Datos Deportivos</h3>

                    <div class="jugador-form-double-grid">
                        <div class="campo-ficha">
                            <label>Dorsal</label>
                            <input type="number" name="dorsal" value="{{ old('dorsal') }}" placeholder="00" class="input-ficha">
                        </div>
                        <div class="campo-ficha">
                            <label>Posición</label>
                            <select name="posicion" class="input-ficha input-white">
                                <option value="" disabled {{ old('posicion') == '' ? 'selected' : '' }}>Selecciona...</option>
                                @foreach(['Base', 'Escolta', 'Alero', 'Ala-Pívot', 'Pívot'] as $pos)
                                    <option value="{{ $pos }}" {{ old('posicion') == $pos ? 'selected' : '' }}>{{ $pos }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="campo-ficha">
                        <label>Asignar Equipo</label>
                        <select name="equipo_id" required class="input-ficha input-white">
                            <option value="" disabled {{ old('equipo_id') == '' ? 'selected' : '' }}>Selecciona un equipo</option>
                            @foreach($equipos as $equipo)
                                <option value="{{ $equipo->id }}" {{ old('equipo_id') == $equipo->id ? 'selected' : '' }}>
                                    {{ $equipo->nombre }} ({{ $equipo->categoria }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="campo-ficha">
                        <label>Visibilidad en la web</label>
                        <div class="checkbox-wrapper checkbox-wrapper-ficha">
                            <input type="checkbox" name="oculto" value="1" {{ old('oculto') ? 'checked' : '' }}>
                            <span>Ocultar jugador de la web pública</span>
                        </div>
                    </div>
                </div>

                <div class="seccion-foto-ficha">
                    <div class="card-imagen-horizontal">
                        <div class="preview-foto-noticia">
                            <img src="{{ asset('img/club_14.png') }}" alt="Imagen por defecto">
                        </div>

                        <div class="info-subida-noticia">
                            <h3>Imagen del Jugador</h3>
                            <p>Para que luzca bien, usa una fotografía vertical o cuadrada.</p>

                            <label for="imagen_jugador" class="btn-cambiar-foto">
                                <i class="fas fa-camera"></i> Seleccionar fotografía
                            </label>
                            <input type="file" name="imagen_jugador" id="imagen_jugador" class="hidden-file-input">

                            <span class="nota-subida">Formatos: JPG o PNG. La foto se guardará en public/jugadores.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-acciones-ficha">
                <a href="{{ route('jugadores.index') }}" class="btn-cancelar-ficha" title="Cancelar y volver al listado de jugadores">Cancelar</a>
                <button type="submit" class="btn-actualizar-ficha btn-actualizar-azul" title="Registrar jugador">
                    <i class="fas fa-save"></i> REGISTRAR JUGADOR
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
