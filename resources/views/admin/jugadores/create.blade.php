@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    
    <header class="header-ficha">
        <div>
            <h2>Añadir Nuevo Jugador</h2>
            <p>Introduce los datos para inscribir al jugador en el club</p>
        </div>
        <a href="{{ route('jugadores.index') }}" class="btn-nuevo" style="background-color: #777;">
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

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="campo-ficha">
                            <label>Dorsal</label>
                            <input type="number" name="dorsal" value="{{ old('dorsal') }}" placeholder="00" class="input-ficha">
                        </div>
                        <div class="campo-ficha">
                            <label>Posición</label>
                            <select name="posicion" class="input-ficha" style="background: white;">
                                <option value="" disabled {{ old('posicion') == '' ? 'selected' : '' }}>Selecciona...</option>
                                @foreach(['Base', 'Escolta', 'Alero', 'Ala-Pívot', 'Pívot'] as $pos)
                                    <option value="{{ $pos }}" {{ old('posicion') == $pos ? 'selected' : '' }}>{{ $pos }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="campo-ficha">
                        <label>Asignar Equipo</label>
                        <select name="equipo_id" required class="input-ficha" style="background: white;">
                            <option value="" disabled {{ old('equipo_id') == '' ? 'selected' : '' }}>Selecciona un equipo</option>
                            @foreach($equipos as $equipo)
                                <option value="{{ $equipo->id }}" {{ old('equipo_id') == $equipo->id ? 'selected' : '' }}>
                                    {{ $equipo->nombre }} ({{ $equipo->categoria }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="seccion-foto-ficha">
                    <div style="text-align: center;">
                        <label>Imagen</label>
                        <div class="preview-foto">
                            <img src="{{ asset('img/basket.jpeg') }}" alt="Imagen por defecto">
                        </div>
                    </div>
                    
                    <div style="flex: 1;">
                        <label>Subir fotografía del jugador</label>
                        <input type="file" name="imagen_jugador" class="input-ficha" style="background: white;">
                        <p style="margin: 10px 0 0; font-size: 0.8em; color: #718096;">Formatos: JPG o PNG. La foto se guardará en public/jugadores.</p>
                    </div>
                </div>
            </div>

            <div class="form-acciones-ficha">
                <a href="{{ route('jugadores.index') }}" class="btn-cancelar-ficha">Cancelar</a>
                <button type="submit" class="btn-actualizar-ficha" style="background: #023e8a; box-shadow: 0 4px 6px rgba(2, 62, 138, 0.3);">
                    <i class="fas fa-save"></i> REGISTRAR JUGADOR
                </button>
            </div>
        </form>
    </div>
</div>
@endsection