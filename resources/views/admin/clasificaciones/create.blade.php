@extends('layouts.admin')

@section('title', __('admin.standings.meta.create_title'))

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <header class="header-ficha">
        <div>
            <h2>{{ __('admin.standings.create.title') }}</h2>
            <p>{{ __('admin.standings.create.subtitle') }}</p>
        </div>
        <a href="{{ route('clasificaciones.index') }}" class="btn-nuevo" title="{{ __('admin.common.back_to_list') }}">
            <i class="fas fa-arrow-left"></i> {{ __('admin.common.back_to_list') }}
        </a>
    </header>

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
        <form action="{{ route('clasificaciones.store') }}" method="POST">
            @csrf

            @php
                // La categoría sale de las que ya existen en equipos para no depender de texto libre.
                $categoriasDisponibles = $equipos->pluck('categoria')->filter()->unique()->sort()->values();
            @endphp

            <div class="grid-formulario-fichas">
                <div class="seccion-form-ficha seccion-personal">
                    <h3><i class="fas fa-trophy"></i> {{ __('admin.standings.sections.competition_data') }}</h3>

                    <div class="campo-ficha">
                        <label>{{ __('admin.standings.fields.name') }}</label>
                        <input type="text" name="nombre" class="input-ficha" value="{{ old('nombre') }}">
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.standings.fields.category') }}</label>
                        <select name="categoria" class="input-ficha" data-categoria-clasificacion>
                            <option value="" {{ old('categoria') ? '' : 'selected' }}>{{ __('admin.standings.fields.no_category') }}</option>
                            @foreach($categoriasDisponibles as $categoria)
                                <option value="{{ $categoria }}" {{ old('categoria') === $categoria ? 'selected' : '' }}>
                                    {{ $categoria }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.standings.fields.season') }}</label>
                        <input type="text" name="temporada" class="input-ficha" value="{{ old('temporada') }}" placeholder="2026/27">
                    </div>

                    <div class="campo-ficha">
                        <label style="display:flex; align-items:center; gap:8px;">
                            <input type="checkbox" name="activa" value="1" {{ old('activa') ? 'checked' : '' }}>
                            {{ __('admin.standings.fields.mark_active') }}
                        </label>
                    </div>
                </div>

                <div class="seccion-form-ficha seccion-deportiva">
                    <h3><i class="fas fa-users"></i> {{ __('admin.standings.fields.included_teams') }}</h3>
                    <p style="margin-bottom: 14px; color: #666;">{{ __('admin.standings.fields.included_teams_help') }}</p>

                    <div class="clasificacion-filtros">
                        <button type="button" class="clasificacion-filtro-btn is-active" data-filtro-tipo="todos">{{ __('admin.standings.filters.all') }}</button>
                        <button type="button" class="clasificacion-filtro-btn" data-filtro-tipo="local">{{ __('admin.standings.filters.local') }}</button>
                        <button type="button" class="clasificacion-filtro-btn" data-filtro-tipo="visitante">{{ __('admin.standings.filters.visitor') }}</button>
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.standings.fields.search_team') }}</label>
                        <input type="text" class="input-ficha" placeholder="{{ __('admin.standings.fields.search_team_placeholder') }}" data-buscar-equipo>
                    </div>

                    <div class="clasificacion-acciones-masa">
                        <button type="button" class="btn-limpiar" data-select-all>{{ __('admin.standings.fields.select_all') }}</button>
                        <button type="button" class="btn-limpiar" data-clear-all>{{ __('admin.standings.fields.clear') }}</button>
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.standings.fields.teams') }}</label>
                        <div class="input-ficha" style="max-height: 380px; overflow:auto;" data-equipos-lista>
                            @foreach($equipos as $equipo)
                                <label style="display:flex; align-items:center; gap:8px; padding:6px 0;" data-equipo-item data-tipo="{{ $equipo->es_local ? 'local' : 'visitante' }}" data-categoria="{{ $equipo->categoria }}" data-nombre="{{ mb_strtolower($equipo->nombre) }}">
                                    <input type="checkbox" name="equipos[]" value="{{ $equipo->id }}" @checked(in_array($equipo->id, old('equipos', [])))>
                                    <span>
                                        {{ $equipo->nombre }} ({{ $equipo->categoria }}) · {{ $equipo->es_local ? __('admin.standings.fields.local') : __('admin.standings.fields.visitor') }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-acciones-ficha">
                <a href="{{ route('clasificaciones.index') }}" class="btn-nuevo">{{ __('admin.common.cancel') }}</a>
                <button type="submit" class="btn-actualizar-ficha">
                    <i class="fas fa-save"></i> {{ __('admin.standings.create.submit') }}
                </button>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const items = Array.from(document.querySelectorAll('[data-equipo-item]'));
    const btnsFiltro = Array.from(document.querySelectorAll('[data-filtro-tipo]'));
    const btnSelectAll = document.querySelector('[data-select-all]');
    const btnClearAll = document.querySelector('[data-clear-all]');
    const categoriaSelect = document.querySelector('[data-categoria-clasificacion]');
    const buscarInput = document.querySelector('[data-buscar-equipo]');
    let filtroTipoActivo = 'todos';

    // La lista combina tres filtros: categoría, tipo de equipo y texto buscado.
    const filtrar = () => {
        const categoriaActiva = categoriaSelect?.value || '';
        const busqueda = (buscarInput?.value || '').trim().toLowerCase();

        items.forEach((item) => {
            const coincideTipo = filtroTipoActivo === 'todos' || item.dataset.tipo === filtroTipoActivo;
            const coincideCategoria = categoriaActiva === '' || item.dataset.categoria === categoriaActiva;
            const coincideTexto = busqueda === '' || (item.dataset.nombre || '').includes(busqueda);
            const mostrar = coincideTipo && coincideCategoria && coincideTexto;
            item.style.display = mostrar ? 'flex' : 'none';
        });
    };

    btnsFiltro.forEach((btn) => {
        btn.addEventListener('click', function () {
            filtroTipoActivo = this.dataset.filtroTipo;
            btnsFiltro.forEach((item) => item.classList.remove('is-active'));
            this.classList.add('is-active');
            filtrar();
        });
    });

    btnSelectAll?.addEventListener('click', function () {
        items.forEach((item) => {
            if (item.style.display !== 'none') {
                const checkbox = item.querySelector('input[type="checkbox"]');
                if (checkbox) checkbox.checked = true;
            }
        });
    });

    btnClearAll?.addEventListener('click', function () {
        items.forEach((item) => {
            const checkbox = item.querySelector('input[type="checkbox"]');
            if (checkbox) checkbox.checked = false;
        });
    });

    categoriaSelect?.addEventListener('change', filtrar);
    buscarInput?.addEventListener('input', filtrar);
    filtrar();
});
</script>
@endsection
