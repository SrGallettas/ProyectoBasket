@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <header class="header-ficha">
        <div>
            <h2>Nueva Clasificación</h2>
            <p>Crea una liga y selecciona los equipos participantes.</p>
        </div>
        <a href="{{ route('clasificaciones.index') }}" class="btn-ficha-accion btn-ficha-volver" title="Volver al listado">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
    </header>

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
        <form action="{{ route('clasificaciones.store') }}" method="POST">
            @csrf

            <div class="grid-formulario-fichas">
                <div class="seccion-form-ficha seccion-personal">
                    <h3><i class="fas fa-trophy"></i> Datos de la competición</h3>

                    <div class="campo-ficha">
                        <label>Nombre</label>
                        <input type="text" name="nombre" class="input-ficha" value="{{ old('nombre') }}" required>
                    </div>

                    <div class="campo-ficha">
                        <label>Categoría</label>
                        <input type="text" name="categoria" class="input-ficha" value="{{ old('categoria') }}" placeholder="Senior, Junior...">
                    </div>

                    <div class="campo-ficha">
                        <label>Temporada</label>
                        <input type="text" name="temporada" class="input-ficha" value="{{ old('temporada') }}" placeholder="2026/27">
                    </div>

                    <div class="campo-ficha">
                        <label style="display:flex; align-items:center; gap:8px;">
                            <input type="checkbox" name="activa" value="1" {{ old('activa') ? 'checked' : '' }}>
                            Marcar como clasificación activa
                        </label>
                    </div>
                </div>

                <div class="seccion-form-ficha seccion-deportiva">
                    <h3><i class="fas fa-users"></i> Equipos incluidos</h3>
                    <p style="margin-bottom: 14px; color: #666;">Selecciona los equipos que competirán en esta clasificación.</p>

                    <div class="campo-ficha" style="display:flex; gap:8px; flex-wrap:wrap;">
                        <button type="button" class="btn-buscar" data-filtro-tipo="todos">Todos</button>
                        <button type="button" class="btn-buscar" data-filtro-tipo="local">Solo locales</button>
                        <button type="button" class="btn-buscar" data-filtro-tipo="visitante">Solo visitantes</button>
                        <button type="button" class="btn-limpiar" data-select-all>Seleccionar todos</button>
                        <button type="button" class="btn-limpiar" data-clear-all>Limpiar</button>
                    </div>

                    <div class="campo-ficha">
                        <label>Equipos</label>
                        <div class="input-ficha" style="max-height: 380px; overflow:auto;" data-equipos-lista>
                            @foreach($equipos as $equipo)
                                <label style="display:flex; align-items:center; gap:8px; padding:6px 0;" data-equipo-item data-tipo="{{ $equipo->es_local ? 'local' : 'visitante' }}">
                                    <input type="checkbox" name="equipos[]" value="{{ $equipo->id }}" @checked(in_array($equipo->id, old('equipos', [])))>
                                    <span>
                                        {{ $equipo->nombre }} ({{ $equipo->categoria }}) · {{ $equipo->es_local ? 'Local' : 'Visitante' }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-acciones-ficha">
                <a href="{{ route('clasificaciones.index') }}" class="btn-cancelar-ficha">Cancelar</a>
                <button type="submit" class="btn-actualizar-ficha">
                    <i class="fas fa-save"></i> GUARDAR CLASIFICACIÓN
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

    const filtrar = (tipo) => {
        items.forEach((item) => {
            const mostrar = tipo === 'todos' || item.dataset.tipo === tipo;
            item.style.display = mostrar ? 'flex' : 'none';
        });
    };

    btnsFiltro.forEach((btn) => {
        btn.addEventListener('click', function () {
            filtrar(this.dataset.filtroTipo);
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
});
</script>
@endsection
