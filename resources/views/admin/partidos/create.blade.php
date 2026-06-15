@extends('layouts.admin')

@section('title', __('admin.matches.meta.create_title'))

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    
    <header class="header-ficha">
        <div>
            <h2>{{ __('admin.matches.create.title') }}</h2>
            <p>{{ __('admin.matches.create.subtitle') }}</p>
        </div>
        <a href="{{ route('partidos.index') }}" class="btn-ficha-accion btn-ficha-volver" title="{{ __('admin.matches.actions.cancel_back') }}">
            <i class="fas fa-arrow-left"></i> {{ __('admin.common.back_to_list') }}
        </a>
    </header>

    {{-- Bloque de errores (Estilo idéntico al de Merchandising) --}}
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
        <form action="{{ route('partidos.store') }}" method="POST">
            @csrf

            <div class="grid-formulario-fichas">
                
                {{-- SECCIÓN EQUIPOS --}}
                <div class="seccion-form-ficha seccion-personal">
                    <h3><i class="fas fa-handshake"></i> {{ __('admin.matches.sections.teams') }}</h3>

                    <div class="campo-ficha">
                        <label>{{ __('admin.matches.fields.category') }}</label>
                        <select name="categoria" class="input-ficha" data-categoria-partido>
                            <option value="" disabled {{ old('categoria') ? '' : 'selected' }}>{{ __('admin.matches.fields.select_category') }}</option>
                            @foreach(['Senior', 'Senior B', 'Junior', 'Cadete', 'Infantil', 'Alevín'] as $nombre_categoria)
                                <option value="{{ $nombre_categoria }}" {{ old('categoria') == $nombre_categoria ? 'selected' : '' }}>
                                    {{ $nombre_categoria }}
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
                            <option value="" disabled {{ old('equipo_local_id') ? '' : 'selected' }}>{{ __('admin.matches.fields.select_local_team') }}</option>
                            @foreach($equipos as $equipo)
                                <option value="{{ $equipo->id }}" data-categoria="{{ $equipo->categoria }}" {{ old('equipo_local_id') == $equipo->id ? 'selected' : '' }}>
                                    {{ $equipo->nombre }} ({{ $equipo->categoria }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.matches.fields.visitor_team') }}</label>
                        <select name="equipo_visitante_id" class="input-ficha" data-equipo-visitante>
                            <option value="" disabled {{ old('equipo_visitante_id') ? '' : 'selected' }}>{{ __('admin.matches.fields.select_visitor_team') }}</option>
                            @foreach($equipos as $equipo)
                                <option value="{{ $equipo->id }}" data-categoria="{{ $equipo->categoria }}" {{ old('equipo_visitante_id') == $equipo->id ? 'selected' : '' }}>
                                    {{ $equipo->nombre }} ({{ $equipo->categoria }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- SECCIÓN RESULTADO OPCIONAL --}}
                <div class="seccion-form-ficha seccion-deportiva">
                    <h3><i class="fas fa-basketball-ball"></i> {{ __('admin.matches.sections.result') }}</h3>
                    <p style="margin-bottom: 16px; color: #666;">{{ __('admin.matches.fields.result_help') }}</p>

                    <div class="campo-ficha">
                        <label>{{ __('admin.matches.fields.points_local') }}</label>
                        <input type="number" min="0" step="1" name="puntos_local" value="{{ old('puntos_local') }}" class="input-ficha" placeholder="{{ __('admin.matches.fields.points_placeholder') }}">
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.matches.fields.points_visitante') }}</label>
                        <input type="number" min="0" step="1" name="puntos_visitante" value="{{ old('puntos_visitante') }}" class="input-ficha" placeholder="{{ __('admin.matches.fields.points_placeholder') }}">
                    </div>
                </div>

                {{-- SECCIÓN DETALLES --}}
                <div class="seccion-form-ficha seccion-deportiva">
                    <h3><i class="fas fa-info-circle"></i> {{ __('admin.matches.sections.details') }}</h3>

                    <div class="campo-ficha">
                        <label>{{ __('admin.matches.fields.date_time') }}</label>
                        <input type="datetime-local" name="fecha_hora" class="input-ficha" value="{{ old('fecha_hora') }}">
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.matches.fields.venue') }}</label>
                        <input type="text" name="lugar" value="{{ old('lugar', 'Pabellón Municipal') }}" class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.matches.fields.type') }}</label>
                        <select name="tipo_partido" class="input-ficha" data-tipo-partido>
                            <option value="liga" {{ old('tipo_partido', 'liga') === 'liga' ? 'selected' : '' }}>{{ __('admin.matches.types.league') }}</option>
                            <option value="amistoso" {{ old('tipo_partido') === 'amistoso' ? 'selected' : '' }}>{{ __('admin.matches.types.friendly') }}</option>
                        </select>
                    </div>

                    <div class="campo-ficha" data-campo-liga>
                        <label>{{ __('admin.matches.fields.standing') }}</label>
                        <select name="clasificacion_id" class="input-ficha" data-clasificacion-partido>
                            <option value="" {{ old('clasificacion_id') ? '' : 'selected' }}>{{ __('admin.matches.fields.select_standing') }}</option>
                            @foreach($clasificaciones as $clasificacion)
                                <option value="{{ $clasificacion->id }}" {{ (int) old('clasificacion_id') === (int) $clasificacion->id ? 'selected' : '' }}>
                                    {{ $clasificacion->nombre }}{{ $clasificacion->temporada ? ' · ' . $clasificacion->temporada : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="campo-ficha" data-campo-liga>
                        <label>{{ __('admin.matches.fields.round') }}</label>
                        <input type="number" name="jornada" min="1" class="input-ficha" value="{{ old('jornada') }}" placeholder="{{ __('admin.matches.fields.round_placeholder') }}">
                    </div>

                    <div class="campo-ficha">
                        <label style="display:flex; align-items:center; gap:8px;">
                            <input type="checkbox" name="oculto" value="1" {{ old('oculto') ? 'checked' : '' }}>
                            {{ __('admin.matches.fields.hide_public') }}
                        </label>
                    </div>

                    <div class="campo-ficha" data-campo-liga>
                        <label style="display:flex; align-items:center; gap:8px;">
                            <input type="checkbox" name="oculto_en_clasificacion" value="1" {{ old('oculto_en_clasificacion') ? 'checked' : '' }}>
                            {{ __('admin.matches.fields.hide_standings') }}
                        </label>
                    </div>

                </div>
            </div>

            <div class="form-acciones-ficha">
                <a href="{{ route('partidos.index') }}" class="btn-nuevo" title="{{ __('admin.matches.actions.cancel_back') }}">{{ __('admin.common.cancel') }}</a>
                <button type="submit" class="btn-actualizar-ficha btn-actualizar-azul" title="{{ __('admin.matches.index.new') }}">
                    <i class="fas fa-save"></i> {{ __('admin.matches.create.submit') }}
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

    // Filtra los equipos del formulario para que solo salgan
    // los de la categoría elegida en ese momento.
    const filtrarEquiposPorCategoria = (select, categoriaActiva, mostrarTodos = false) => {
        if (!select) {
            return;
        }

        Array.from(select.options).forEach((option, index) => {
            if (index === 0) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            const coincideCategoria = mostrarTodos || option.dataset.categoria === categoriaActiva;
            option.hidden = !coincideCategoria;
            option.disabled = !coincideCategoria;
        });

        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption && selectedOption.dataset.categoria && selectedOption.dataset.categoria !== categoriaActiva) {
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
