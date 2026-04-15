@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <x-admin-edit-header
        titulo="Ficha de Edición"
        :subtitulo="'Actualizando encuentro: <strong>' . e($partido->equipoLocal->nombre . ' vs ' . $partido->equipoVisitante->nombre) . '</strong>'"
        :backRoute="route('partidos.index')"
    />

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

            @php
                $statsLocal = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_local_id);
                $statsVisitante = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_visitante_id);
                $periodos = [1 => 'Q1', 2 => 'Q2', 3 => 'Q3', 4 => 'Q4', 5 => 'PR'];
                $camposStats = [
                    'fga' => 'Tiros de campo intentados',
                    'fgm' => 'Tiros de campo anotados',
                    't2a' => 'Tiros de 2 intentados',
                    't2m' => 'Tiros de 2 anotados',
                    't3a' => 'Tiros de 3 intentados',
                    't3m' => 'Tiros de 3 anotados',
                    'fta' => 'Tiros libres intentados',
                    'ftm' => 'Tiros libres anotados',
                    'reb_of' => 'Rebotes ofensivos',
                    'reb_def' => 'Rebotes defensivos',
                    'ast' => 'Asistencias',
                    'blk' => 'Tapones',
                    'tov' => 'Perdidas',
                    'stl' => 'Recuperaciones',
                    'pf' => 'Faltas',
                    'tf' => 'Faltas tecnicas',
                    'puntos_totales' => 'Puntos totales',
                ];

                $quarterPointsFor = function ($stat, $periodo) {
                    if (!$stat) {
                        return null;
                    }

                    return optional($stat->cuartos->first(function ($cuarto) use ($periodo, $stat) {
                        return (int) $cuarto->periodo === (int) $periodo
                            && (int) $cuarto->equipo_id === (int) $stat->equipo_id;
                    }))->puntos;
                };
            @endphp

            <div class="grid-formulario-fichas">
                
                {{-- SECCIÓN EQUIPOS (Usando tu clase seccion-personal) --}}
                <div class="seccion-form-ficha seccion-personal">
                    <h3><i class="fas fa-handshake"></i> Equipos del Encuentro</h3>
                    
                    <div class="campo-ficha">
                        <label>Equipo Local</label>
                        <select name="equipo_local_id" required class="input-ficha">
                            @foreach($equipos as $equipo)
                                {{-- Cargamos el valor de la base de datos directamente --}}
                                <option value="{{ $equipo->id }}" {{ (int) old('equipo_local_id', $partido->equipo_local_id) === (int) $equipo->id ? 'selected' : '' }}>
                                    {{ $equipo->nombre }} ({{ $equipo->categoria }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="campo-ficha">
                        <label>Equipo Visitante</label>
                        <select name="equipo_visitante_id" required class="input-ficha">
                            @foreach($equipos as $equipo)
                                <option value="{{ $equipo->id }}" {{ (int) old('equipo_visitante_id', $partido->equipo_visitante_id) === (int) $equipo->id ? 'selected' : '' }}>
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
                               value="{{ old('fecha_hora', \Carbon\Carbon::parse($partido->fecha_hora)->format('Y-m-d\TH:i')) }}" 
                               required class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                        <label>Lugar / Pabellón</label>
                        <input type="text" name="lugar" value="{{ old('lugar', $partido->lugar) }}" required class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                        <label>Categoría</label>
                        <select name="categoria" required class="input-ficha">
                            @foreach(['Senior A', 'Senior B', 'Junior', 'Cadete', 'Infantil', 'Alevín'] as $cat)
                                <option value="{{ $cat }}" {{ old('categoria', $partido->categoria) == $cat ? 'selected' : '' }}>
                                    {{ $cat }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="campo-ficha">
                        <label>Tipo de partido</label>
                        <select name="tipo_partido" class="input-ficha" data-tipo-partido>
                            <option value="liga" {{ old('tipo_partido', $partido->tipo_partido ?? 'liga') === 'liga' ? 'selected' : '' }}>Liga</option>
                            <option value="amistoso" {{ old('tipo_partido', $partido->tipo_partido) === 'amistoso' ? 'selected' : '' }}>Amistoso</option>
                        </select>
                    </div>

                    <div class="campo-ficha" data-campo-liga>
                        <label>Clasificación (liga)</label>
                        <select name="clasificacion_id" class="input-ficha">
                            <option value="" {{ old('clasificacion_id', $partido->clasificacion_id) ? '' : 'selected' }}>Selecciona clasificación...</option>
                            @foreach($clasificaciones as $clasificacion)
                                <option value="{{ $clasificacion->id }}" {{ (int) old('clasificacion_id', $partido->clasificacion_id) === (int) $clasificacion->id ? 'selected' : '' }}>
                                    {{ $clasificacion->nombre }}{{ $clasificacion->temporada ? ' · ' . $clasificacion->temporada : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="campo-ficha" data-campo-liga>
                        <label>Jornada</label>
                        <input type="number" name="jornada" min="1" class="input-ficha" value="{{ old('jornada', $partido->jornada) }}" placeholder="Ej. 3">
                    </div>

                    <div class="campo-ficha">
                        <label style="display:flex; align-items:center; gap:8px;">
                            <input type="checkbox" name="oculto" value="1" {{ old('oculto', $partido->oculto) ? 'checked' : '' }}>
                            Ocultar este partido en la web publica
                        </label>
                    </div>

                    <div class="campo-ficha" data-campo-liga>
                        <label style="display:flex; align-items:center; gap:8px;">
                            <input type="checkbox" name="oculto_en_clasificacion" value="1" {{ old('oculto_en_clasificacion', $partido->oculto_en_clasificacion) ? 'checked' : '' }}>
                            Ocultar este partido en clasificación
                        </label>
                    </div>
                </div>

            </div>

            <div class="pizarra-ficha" style="margin-top: 24px;">
                <h3 style="margin-bottom: 8px;"><i class="fas fa-chart-bar"></i> Estadisticas del partido</h3>
                <p style="margin-bottom: 20px; color: #666;">Carga datos finales de tiro, rebote y puntos para local y visitante.</p>

                <div class="grid-formulario-fichas">
                    <div class="seccion-form-ficha seccion-personal">
                        <h3><i class="fas fa-home"></i> {{ $partido->equipoLocal->nombre }} (Local)</h3>
                        @foreach($camposStats as $campo => $label)
                            <div class="campo-ficha">
                                <label>{{ $label }}</label>
                                <input type="number" min="0" step="1" name="stats[local][{{ $campo }}]"
                                       value="{{ old("stats.local.$campo", optional($statsLocal)->{$campo}) }}"
                                       class="input-ficha">
                            </div>
                        @endforeach
                    </div>

                    <div class="seccion-form-ficha seccion-deportiva">
                        <h3><i class="fas fa-plane"></i> {{ $partido->equipoVisitante->nombre }} (Visitante)</h3>
                        @foreach($camposStats as $campo => $label)
                            <div class="campo-ficha">
                                <label>{{ $label }}</label>
                                <input type="number" min="0" step="1" name="stats[visitante][{{ $campo }}]"
                                       value="{{ old("stats.visitante.$campo", optional($statsVisitante)->{$campo}) }}"
                                       class="input-ficha">
                            </div>
                        @endforeach
                    </div>
                </div>

                <h3 style="margin: 24px 0 10px;"><i class="fas fa-table"></i> Puntos por cuartos</h3>
                <div class="grid-formulario-fichas">
                    <div class="seccion-form-ficha seccion-personal">
                        <h3><i class="fas fa-home"></i> {{ $partido->equipoLocal->nombre }}</h3>
                        @foreach($periodos as $periodo => $label)
                            <div class="campo-ficha">
                                <label>{{ $label }}</label>
                                <input type="number" min="0" step="1" name="quarters[local][{{ $periodo }}]"
                                       value="{{ old("quarters.local.$periodo", $quarterPointsFor($statsLocal, $periodo)) }}"
                                       class="input-ficha">
                            </div>
                        @endforeach
                    </div>

                    <div class="seccion-form-ficha seccion-deportiva">
                        <h3><i class="fas fa-plane"></i> {{ $partido->equipoVisitante->nombre }}</h3>
                        @foreach($periodos as $periodo => $label)
                            <div class="campo-ficha">
                                <label>{{ $label }}</label>
                                <input type="number" min="0" step="1" name="quarters[visitante][{{ $periodo }}]"
                                       value="{{ old("quarters.visitante.$periodo", $quarterPointsFor($statsVisitante, $periodo)) }}"
                                       class="input-ficha">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Botones de acción con tus clases globales --}}
            <div class="form-acciones-ficha">
                <a href="{{ route('partidos.index') }}" class="btn-cancelar-ficha" title="Cancelar y volver al listado de partidos">Cancelar</a>
                <button type="submit" class="btn-actualizar-ficha" title="Actualizar partido">
                    <i class="fas fa-sync-alt"></i> ACTUALIZAR FICHA
                </button>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tipoSelect = document.querySelector('[data-tipo-partido]');
    const camposLiga = document.querySelectorAll('[data-campo-liga]');

    if (!tipoSelect || camposLiga.length === 0) {
        return;
    }

    const toggleCampos = () => {
        const esLiga = tipoSelect.value === 'liga';
        camposLiga.forEach((campo) => {
            campo.style.display = esLiga ? '' : 'none';
        });
    };

    toggleCampos();
    tipoSelect.addEventListener('change', toggleCampos);
});
</script>
@endsection
