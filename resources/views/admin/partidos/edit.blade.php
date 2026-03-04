@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    
    <header class="header-ficha">
        <div>
            {{-- Título dinámico para saber qué partido editamos --}}
            <h2>Ficha de Edición</h2>
            <p>Actualizando encuentro: <strong>{{ $partido->equipoLocal->nombre }} vs {{ $partido->equipoVisitante->nombre }}</strong></p>
        </div>
        {{-- Usamos tu clase de enlace volver --}}
        <a href="{{ route('partidos.index') }}" class="btn-volver-link">
            <i class="fas fa-chevron-left"></i> Volver al listado
        </a>
    </header>

    {{-- Bloque de errores con tu clase CSS limpia --}}
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
        {{-- Formulario hacia Update con el ID del partido y método PUT --}}
        <form action="{{ route('partidos.update', $partido->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid-formulario-fichas">
                
                {{-- SECCIÓN EQUIPOS (Usando tu clase seccion-personal) --}}
                <div class="seccion-form-ficha seccion-personal">
                    <h3><i class="fas fa-handshake"></i> Equipos del Encuentro</h3>
                    
                    <div class="campo-ficha">
                        <label>Equipo Local</label>
                        <select name="equipo_local_id" required class="input-ficha">
                            @foreach($equipos as $equipo)
                                {{-- Cargamos el valor de la base de datos directamente --}}
                                <option value="{{ $equipo->id }}" {{ $partido->equipo_local_id == $equipo->id ? 'selected' : '' }}>
                                    {{ $equipo->nombre }} ({{ $equipo->categoria }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="campo-ficha">
                        <label>Equipo Visitante</label>
                        <select name="equipo_visitante_id" required class="input-ficha">
                            @foreach($equipos as $equipo)
                                <option value="{{ $equipo->id }}" {{ $partido->equipo_visitante_id == $equipo->id ? 'selected' : '' }}>
                                    {{ $equipo->nombre }} ({{ $equipo->categoria }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- SECCIÓN DETALLES (Usando tu clase seccion-deportiva) --}}
                <div class="seccion-form-ficha seccion-deportiva">
                    <h3><i class="fas fa-info-circle"></i> Detalles del Partido</h3>

                    <div class="campo-ficha">
                        <label>Fecha y Hora</label>
                        {{-- Formateamos el valor para que el input datetime-local lo acepte --}}
                        <input type="datetime-local" name="fecha_hora" 
                               value="{{ \Carbon\Carbon::parse($partido->fecha_hora)->format('Y-m-d\TH:i') }}" 
                               required class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                        <label>Lugar / Pabellón</label>
                        <input type="text" name="lugar" value="{{ $partido->lugar }}" required class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                        <label>Categoría</label>
                        <select name="categoria" required class="input-ficha">
                            @foreach(['Senior A', 'Senior B', 'Junior', 'Cadete', 'Infantil', 'Alevín'] as $cat)
                                <option value="{{ $cat }}" {{ $partido->categoria == $cat ? 'selected' : '' }}>
                                    {{ $cat }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

            </div>

            {{-- Botones de acción con tus clases globales --}}
            <div class="form-acciones-ficha">
                <a href="{{ route('partidos.index') }}" class="btn-cancelar-ficha">Cancelar</a>
                <button type="submit" class="btn-actualizar-ficha">
                    <i class="fas fa-sync-alt"></i> ACTUALIZAR FICHA
                </button>
            </div>
        </form>
    </div>
</div>
@endsection