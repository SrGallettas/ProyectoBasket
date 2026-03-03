@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    
    <header class="header-ficha">
        <div>
            <h2>Programar Nuevo Partido</h2>
            <p>Configura el próximo encuentro del club</p>
        </div>
        <a href="{{ route('partidos.index') }}" class="btn-ficha-accion btn-ficha-volver">
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
        <form action="{{ route('partidos.store') }}" method="POST">
            @csrf

            <div class="grid-formulario-fichas">
                
                {{-- SECCIÓN EQUIPOS --}}
                <div class="seccion-form-ficha seccion-personal">
                    <h3><i class="fas fa-handshake"></i> Equipos del Encuentro</h3>
                    
                    <div class="campo-ficha">
                        <label>Equipo Local</label>
                        <select name="equipo_local_id" class="input-ficha">
                            <option value="" disabled selected>Selecciona equipo local...</option>
                            @foreach($equipos as $equipo)
                                <option value="{{ $equipo->id }}" {{ old('equipo_local_id') == $equipo->id ? 'selected' : '' }}>
                                    {{ $equipo->nombre }} ({{ $equipo->categoria }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="campo-ficha">
                        <label>Equipo Visitante</label>
                        <select name="equipo_visitante_id" class="input-ficha">
                            <option value="" disabled selected>Selecciona equipo visitante...</option>
                            @foreach($equipos as $equipo)
                                <option value="{{ $equipo->id }}" {{ old('equipo_visitante_id') == $equipo->id ? 'selected' : '' }}>
                                    {{ $equipo->nombre }} ({{ $equipo->categoria }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- SECCIÓN DETALLES --}}
                <div class="seccion-form-ficha seccion-deportiva">
                    <h3><i class="fas fa-info-circle"></i> Detalles del Partido</h3>

                    <div class="campo-ficha">
                        <label>Fecha y Hora</label>
                        <input type="datetime-local" name="fecha_hora" class="input-ficha" value="{{ old('fecha_hora') }}">
                    </div>

                    <div class="campo-ficha">
                        <label>Lugar / Pabellón</label>
                        <input type="text" name="lugar" value="{{ old('lugar', 'Pabellón Municipal') }}" class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                    <label>Categoría</label>
                    <select name="categoria" class="input-ficha">
                        <option value="" disabled selected>Selecciona categoría...</option>
                        
                        {{-- Ahora usamos $nombre_categoria en todo el bucle --}}
                        @foreach(['Senior A', 'Senior B', 'Junior', 'Cadete', 'Infantil', 'Alevín'] as $nombre_categoria)
                            <option value="{{ $nombre_categoria }}" {{ old('categoria') == $nombre_categoria ? 'selected' : '' }}>
                                {{ $nombre_categoria }}
                            </option>
                        @endforeach 
                    </select>
                </div>

                </div>

                
                {{-- NOTA INFORMATIVA --}}
                <div class="seccion-foto-ficha">
                    <div class="nota-informativa-partido">
                        <i class="fas fa-clock"></i>
                        <p>El partido se creará con el marcador vacío. Podrás añadir el resultado una vez finalice el encuentro.</p>
                    </div>
                </div>
            </div>

            <div class="form-acciones-ficha">
                <a href="{{ route('partidos.index') }}" class="btn-cancelar-ficha">Cancelar</a>
                <button type="submit" class="btn-actualizar-ficha">
                    <i class="fas fa-save"></i> PROGRAMAR PARTIDO
                </button>
            </div>
        </form>
    </div>
</div>
@endsection