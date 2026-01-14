@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    
    <header class="header-ficha">
        <div>
            <h2>Ficha de Edición</h2>
            <p>Actualizando a: <strong>{{ $jugador->nombre }} {{ $jugador->apellido }}</strong></p>
        </div>
        <a href="{{ route('jugadores.index') }}" class="btn-volver-link">
            <i class="fas fa-chevron-left"></i> Volver al listado
        </a>
    </header>

    @if ($errors->any())
    <div style="background: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <strong>¡Vaya! Algo ha ido mal:</strong>
        {{-- Añadimos list-style: none y quitamos el padding --}}
        <ul style="margin-top: 5px; margin-bottom: 0; list-style: none; padding-left: 0;">
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

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="campo-ficha">
                            <label>Dorsal</label>
                            <input type="number" name="dorsal" value="{{ $jugador->dorsal }}" class="input-ficha">
                        </div>
                        <div class="campo-ficha">
                            <label>Posición</label>
                            <select name="posicion" class="input-ficha" style="background: white;">
                                @foreach(['Base', 'Escolta', 'Alero', 'Ala-Pívot', 'Pívot'] as $pos)
                                    <option value="{{ $pos }}" {{ $jugador->posicion == $pos ? 'selected' : '' }}>{{ $pos }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="campo-ficha">
                        <label>Equipo Actual</label>
                        <select name="equipo_id" required class="input-ficha" style="background: white;">
                            @foreach($equipos as $equipo)
                                <option value="{{ $equipo->id }}" {{ $jugador->equipo_id == $equipo->id ? 'selected' : '' }}>
                                    {{ $equipo->nombre }} ({{ $equipo->categoria }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="seccion-foto-ficha">
                    <div style="text-align: center;">
                        <label>Imagen Actual</label>
                        <div class="preview-foto">
                            @if($jugador->imagen_jugador)
                                <img src="{{ asset('jugadores/' . $jugador->imagen_jugador) }}" alt="Foto actual">
                            @else
                                <img src="{{ asset('img/basket.jpeg') }}" alt="Foto por defecto">
                            @endif
                        </div>
                    </div>
                    
                    <div style="flex: 1;">
                        <label>Subir nueva fotografía</label>
                        <input type="file" name="imagen_jugador" class="input-ficha" style="background: white;">
                        <p style="margin: 10px 0 0; font-size: 0.8em; color: #718096;">Formatos admitidos: JPG o PNG. Se recomienda tamaño cuadrado.</p>
                    </div>
                </div>
            </div>

            <div class="form-acciones-ficha">
                <a href="{{ route('jugadores.index') }}" class="btn-cancelar-ficha">Cancelar</a>
                <button type="submit" class="btn-actualizar-ficha">
                    <i class="fas fa-sync-alt"></i> ACTUALIZAR FICHA
                </button>
            </div>
        </form>
    </div>
</div>
@endsection