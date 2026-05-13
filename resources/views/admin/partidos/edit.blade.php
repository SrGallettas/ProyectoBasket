@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <x-admin-edit-header
        :titulo="__('admin.matches.edit.title')"
        :subtitulo="__('admin.matches.edit.subtitle', ['match' => e($partido->equipoLocal->nombre . ' vs ' . $partido->equipoVisitante->nombre)])"
        :backRoute="route('partidos.index')"
    />

    {{-- Bloque de errores con tu clase CSS limpia --}}
    @if ($errors->any())
    <div class="cuadro-errores-admin">
        <strong>{{ __('admin.common.form_error') }}</strong>
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
                    'fga' => __('admin.matches.stats.fga'),
                    'fgm' => __('admin.matches.stats.fgm'),
                    't2a' => __('admin.matches.stats.t2a'),
                    't2m' => __('admin.matches.stats.t2m'),
                    't3a' => __('admin.matches.stats.t3a'),
                    't3m' => __('admin.matches.stats.t3m'),
                    'fta' => __('admin.matches.stats.fta'),
                    'ftm' => __('admin.matches.stats.ftm'),
                    'reb_of' => __('admin.matches.stats.reb_of'),
                    'reb_def' => __('admin.matches.stats.reb_def'),
                    'ast' => __('admin.matches.stats.ast'),
                    'blk' => __('admin.matches.stats.blk'),
                    'tov' => __('admin.matches.stats.tov'),
                    'stl' => __('admin.matches.stats.stl'),
                    'pf' => __('admin.matches.stats.pf'),
                    'tf' => __('admin.matches.stats.tf'),
                    'puntos_totales' => __('admin.matches.stats.puntos_totales'),
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
                    <h3><i class="fas fa-handshake"></i> {{ __('admin.matches.sections.teams') }}</h3>

                    <div class="campo-ficha">
                        <label>{{ __('admin.matches.fields.category') }}</label>
                        <select name="categoria" class="input-ficha" data-categoria-partido>
                            @foreach(['Senior', 'Senior B', 'Junior', 'Cadete', 'Infantil', 'Alevín'] as $cat)
                                <option value="{{ $cat }}" {{ old('categoria', $partido->categoria) == $cat ? 'selected' : '' }}>
                                    {{ $cat }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="campo-ficha">
                        <label style="display:flex; align-items:center; gap:8px;">
                            <input type="checkbox" data-mostrar-todos-equipos>
                            {{ __('admin.matches.fields.show_all_teams') }}
                        </label>
                    </div>
                    
                    <div class="campo-ficha">
                        <label>{{ __('admin.matches.fields.local_team') }}</label>
                        <select name="equipo_local_id" class="input-ficha" data-equipo-local>
                            <option value="" disabled {{ old('equipo_local_id', $partido->equipo_local_id) ? '' : 'selected' }}>{{ __('admin.matches.fields.select_local_team') }}</option>
                            @foreach($equipos as $equipo)
                                {{-- Cargamos el valor de la base de datos directamente --}}
                                <option value="{{ $equipo->id }}" data-categoria="{{ $equipo->categoria }}" {{ (int) old('equipo_local_id', $partido->equipo_local_id) === (int) $equipo->id ? 'selected' : '' }}>
                                    {{ $equipo->nombre }} ({{ $equipo->categoria }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.matches.fields.visitor_team') }}</label>
                        <select name="equipo_visitante_id" class="input-ficha" data-equipo-visitante>
                            <option value="" disabled {{ old('equipo_visitante_id', $partido->equipo_visitante_id) ? '' : 'selected' }}>{{ __('admin.matches.fields.select_visitor_team') }}</option>
                            @foreach($equipos as $equipo)
                                <option value="{{ $equipo->id }}" data-categoria="{{ $equipo->categoria }}" {{ (int) old('equipo_visitante_id', $partido->equipo_visitante_id) === (int) $equipo->id ? 'selected' : '' }}>
                                    {{ $equipo->nombre }} ({{ $equipo->categoria }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- SECCIÓN DETALLES (Usando tu clase seccion-deportiva) --}}
                <div class="seccion-form-ficha seccion-deportiva">
                    <h3><i class="fas fa-info-circle"></i> {{ __('admin.matches.sections.details') }}</h3>

                    <div class="campo-ficha">
                        <label>{{ __('admin.matches.fields.date_time') }}</label>
                        {{-- Formateamos el valor para que el input datetime-local lo acepte --}}
                        <input type="datetime-local" name="fecha_hora" 
                               value="{{ old('fecha_hora', \Carbon\Carbon::parse($partido->fecha_hora)->format('Y-m-d\TH:i')) }}" 
                               class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.matches.fields.venue') }}</label>
                        <input type="text" name="lugar" value="{{ old('lugar', $partido->lugar) }}" class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.matches.fields.type') }}</label>
                        <select name="tipo_partido" class="input-ficha" data-tipo-partido>
                            <option value="liga" {{ old('tipo_partido', $partido->tipo_partido ?? 'liga') === 'liga' ? 'selected' : '' }}>{{ __('admin.matches.types.league') }}</option>
                            <option value="amistoso" {{ old('tipo_partido', $partido->tipo_partido) === 'amistoso' ? 'selected' : '' }}>{{ __('admin.matches.types.friendly') }}</option>
                        </select>
                    </div>

                    <div class="campo-ficha" data-campo-liga>
                        <label>{{ __('admin.matches.fields.standing') }}</label>
                        <select name="clasificacion_id" class="input-ficha" data-clasificacion-partido>
                            <option value="" {{ old('clasificacion_id', $partido->clasificacion_id) ? '' : 'selected' }}>{{ __('admin.matches.fields.select_standing') }}</option>
                            @foreach($clasificaciones as $clasificacion)
                                <option value="{{ $clasificacion->id }}" {{ (int) old('clasificacion_id', $partido->clasificacion_id) === (int) $clasificacion->id ? 'selected' : '' }}>
                                    {{ $clasificacion->nombre }}{{ $clasificacion->temporada ? ' · ' . $clasificacion->temporada : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="campo-ficha" data-campo-liga>
                        <label>{{ __('admin.matches.fields.round') }}</label>
                        <input type="number" name="jornada" min="1" class="input-ficha" value="{{ old('jornada', $partido->jornada) }}" placeholder="{{ __('admin.matches.fields.round_placeholder') }}">
                    </div>

                    <div class="campo-ficha">
                        <label style="display:flex; align-items:center; gap:8px;">
                            <input type="checkbox" name="oculto" value="1" {{ old('oculto', $partido->oculto) ? 'checked' : '' }}>
                            {{ __('admin.matches.fields.hide_public') }}
                        </label>
                    </div>

                    <div class="campo-ficha" data-campo-liga>
                        <label style="display:flex; align-items:center; gap:8px;">
                            <input type="checkbox" name="oculto_en_clasificacion" value="1" {{ old('oculto_en_clasificacion', $partido->oculto_en_clasificacion) ? 'checked' : '' }}>
                            {{ __('admin.matches.fields.hide_standings') }}
                        </label>
                    </div>
                </div>

            </div>

            <div class="pizarra-ficha" style="margin-top: 24px;">
                <h3 style="margin-bottom: 8px;"><i class="fas fa-chart-bar"></i> {{ __('admin.matches.sections.stats') }}</h3>
                <p style="margin-bottom: 20px; color: #666;">{{ __('admin.matches.edit.stats_help') }}</p>

                <div class="grid-formulario-fichas">
                    <div class="seccion-form-ficha seccion-personal">
                        <h3><i class="fas fa-home"></i> {{ $partido->equipoLocal->nombre }} ({{ __('admin.matches.fields.local') }})</h3>
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
                        <h3><i class="fas fa-plane"></i> {{ $partido->equipoVisitante->nombre }} ({{ __('admin.matches.fields.visitor') }})</h3>
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

                <h3 style="margin: 24px 0 10px;"><i class="fas fa-table"></i> {{ __('admin.matches.sections.quarters') }}</h3>
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
                <a href="{{ route('partidos.index') }}" class="btn-cancelar-ficha" title="{{ __('admin.matches.actions.cancel_back') }}">{{ __('admin.common.cancel') }}</a>
                <button type="submit" class="btn-actualizar-ficha" title="{{ __('admin.matches.actions.edit') }}">
                    <i class="fas fa-sync-alt"></i> {{ __('admin.matches.edit.submit') }}
                </button>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tipoSelect = document.querySelector('[data-tipo-partido]');
    const camposLiga = document.querySelectorAll('[data-campo-liga]');
    const categoriaSelect = document.querySelector('[data-categoria-partido]');
    const equipoLocalSelect = document.querySelector('[data-equipo-local]');
    const equipoVisitanteSelect = document.querySelector('[data-equipo-visitante]');
    const clasificacionSelect = document.querySelector('[data-clasificacion-partido]');
    const mostrarTodosEquiposCheckbox = document.querySelector('[data-mostrar-todos-equipos]');

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

    // Filtra local y visitante para que solo salgan equipos
    // de la categoría seleccionada en el formulario.
    const filtrarEquiposPorCategoria = (select, categoriaActiva, mostrarTodos = false) => {
        if (!select) {
            return;
        }

        Array.from(select.options).forEach((option) => {
            if (!option.dataset.categoria) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            const coincideCategoria = mostrarTodos || option.dataset.categoria === categoriaActiva;
            option.hidden = !coincideCategoria;
            option.disabled = !coincideCategoria;
        });

        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption && selectedOption.dataset.categoria !== categoriaActiva) {
            if (!mostrarTodos) {
                select.selectedIndex = 0;
            }
        }
    };

    const syncEquiposByCategoria = () => {
        const categoriaActiva = categoriaSelect?.value || '';
        const mostrarTodos = !!mostrarTodosEquiposCheckbox?.checked;

        filtrarEquiposPorCategoria(equipoLocalSelect, categoriaActiva, mostrarTodos);
        filtrarEquiposPorCategoria(equipoVisitanteSelect, categoriaActiva, mostrarTodos);
    };

    syncEquiposByCategoria();
    categoriaSelect?.addEventListener('change', syncEquiposByCategoria);
    mostrarTodosEquiposCheckbox?.addEventListener('change', syncEquiposByCategoria);
});
</script>
@endsection
