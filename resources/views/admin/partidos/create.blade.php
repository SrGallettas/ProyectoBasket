@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    
    <header class="header-ficha">
        <div>
            <h2>Programar Nuevo Partido</h2>
            <p>Configura el próximo encuentro del club</p>
        </div>
        <a href="{{ route('partidos.index') }}" class="btn-ficha-accion btn-ficha-volver" title="Volver al listado de partidos">
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
                        <label>Categoría</label>
                        <select name="categoria" class="input-ficha" data-categoria-partido>
                            <option value="" disabled {{ old('categoria') ? '' : 'selected' }}>Selecciona categoría...</option>
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
                            Mostrar todos los equipos
                        </label>
                    </div>
                    
                    <div class="campo-ficha">
                        <label>Equipo Local</label>
                        <select name="equipo_local_id" class="input-ficha" data-equipo-local>
                            <option value="" disabled {{ old('equipo_local_id') ? '' : 'selected' }}>Selecciona equipo local...</option>
                            @foreach($equipos as $equipo)
                                <option value="{{ $equipo->id }}" data-categoria="{{ $equipo->categoria }}" {{ old('equipo_local_id') == $equipo->id ? 'selected' : '' }}>
                                    {{ $equipo->nombre }} ({{ $equipo->categoria }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="campo-ficha">
                        <label>Equipo Visitante</label>
                        <select name="equipo_visitante_id" class="input-ficha" data-equipo-visitante>
                            <option value="" disabled {{ old('equipo_visitante_id') ? '' : 'selected' }}>Selecciona equipo visitante...</option>
                            @foreach($equipos as $equipo)
                                <option value="{{ $equipo->id }}" data-categoria="{{ $equipo->categoria }}" {{ old('equipo_visitante_id') == $equipo->id ? 'selected' : '' }}>
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
                        <label>Tipo de partido</label>
                        <select name="tipo_partido" class="input-ficha" data-tipo-partido>
                            <option value="liga" {{ old('tipo_partido', 'liga') === 'liga' ? 'selected' : '' }}>Liga</option>
                            <option value="amistoso" {{ old('tipo_partido') === 'amistoso' ? 'selected' : '' }}>Amistoso</option>
                        </select>
                    </div>

                    <div class="campo-ficha" data-campo-liga>
                        <label>Clasificación (liga)</label>
                        <select name="clasificacion_id" class="input-ficha" data-clasificacion-partido>
                            <option value="" {{ old('clasificacion_id') ? '' : 'selected' }}>Selecciona clasificación...</option>
                            @foreach($clasificaciones as $clasificacion)
                                <option value="{{ $clasificacion->id }}" {{ (int) old('clasificacion_id') === (int) $clasificacion->id ? 'selected' : '' }}>
                                    {{ $clasificacion->nombre }}{{ $clasificacion->temporada ? ' · ' . $clasificacion->temporada : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="campo-ficha" data-campo-liga>
                        <label>Jornada</label>
                        <input type="number" name="jornada" min="1" class="input-ficha" value="{{ old('jornada') }}" placeholder="Ej. 3">
                    </div>

                    <div class="campo-ficha">
                        <label style="display:flex; align-items:center; gap:8px;">
                            <input type="checkbox" name="oculto" value="1" {{ old('oculto') ? 'checked' : '' }}>
                            Ocultar este partido en la web publica
                        </label>
                    </div>

                    <div class="campo-ficha" data-campo-liga>
                        <label style="display:flex; align-items:center; gap:8px;">
                            <input type="checkbox" name="oculto_en_clasificacion" value="1" {{ old('oculto_en_clasificacion') ? 'checked' : '' }}>
                            Ocultar este partido en clasificación
                        </label>
                    </div>

                </div>

            <div class="form-acciones-ficha">
                <a href="{{ route('partidos.index') }}" class="btn-cancelar-ficha" title="Cancelar y volver al listado de partidos">Cancelar</a>
                <button type="submit" class="btn-actualizar-ficha" title="Programar partido">
                    <i class="fas fa-save"></i> PROGRAMAR PARTIDO
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
