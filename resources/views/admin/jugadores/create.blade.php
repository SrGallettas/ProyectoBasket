@extends('layouts.admin')

@section('title', __('admin.players.meta.create_title'))

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <x-admin-edit-header
        :titulo="__('admin.players.create.title')"
        :subtitulo="__('admin.players.create.subtitle')"
        :backRoute="route('jugadores.index')"
    />

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
        <form action="{{ route('jugadores.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid-formulario-fichas">
                
                <div class="seccion-form-ficha seccion-personal">
                    <h3><i class="fas fa-user-plus"></i> {{ __('admin.players.sections.personal') }}</h3>
                    
                    <div class="campo-ficha">
                        <label>{{ __('admin.players.fields.name') }}</label>
                        <input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="{{ __('admin.players.fields.name_placeholder') }}" class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.players.fields.surname') }}</label>
                        <input type="text" name="apellido" value="{{ old('apellido') }}" placeholder="{{ __('admin.players.fields.surname_placeholder') }}" class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.players.fields.birth_date') }}</label>
                        <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" class="input-ficha">
                    </div>
                </div>

                <div class="seccion-form-ficha seccion-deportiva">
                    <h3><i class="fas fa-basketball-ball"></i> {{ __('admin.players.sections.sports') }}</h3>

                    <div class="jugador-form-double-grid">
                        <div class="campo-ficha">
                            <label>{{ __('admin.players.fields.number') }}</label>
                            <input type="number" name="dorsal" value="{{ old('dorsal') }}" placeholder="00" class="input-ficha">
                        </div>
                        <div class="campo-ficha">
                            <label>{{ __('admin.players.fields.position') }}</label>
                            <select name="posicion" class="input-ficha input-white">
                                <option value="" disabled {{ old('posicion') == '' ? 'selected' : '' }}>{{ __('admin.common.select') }}</option>
                                @foreach(['Base', 'Escolta', 'Alero', 'Ala-Pívot', 'Pívot'] as $pos)
                                    <option value="{{ $pos }}" {{ old('posicion') == $pos ? 'selected' : '' }}>{{ $pos }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.players.fields.assign_team') }}</label>
                        <select name="equipo_id" class="input-ficha input-white">
                            <option value="" disabled {{ old('equipo_id') == '' ? 'selected' : '' }}>{{ __('admin.players.fields.select_team') }}</option>
                            @foreach($equipos as $equipo)
                                <option value="{{ $equipo->id }}" {{ old('equipo_id') == $equipo->id ? 'selected' : '' }}>
                                    {{ $equipo->nombre }} ({{ $equipo->categoria }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.players.fields.visibility') }}</label>
                        <div class="checkbox-wrapper checkbox-wrapper-ficha">
                            <input type="checkbox" name="oculto" value="1" {{ old('oculto') ? 'checked' : '' }}>
                            <span>{{ __('admin.players.fields.hide_public') }}</span>
                        </div>
                    </div>
                </div>

                <div class="seccion-foto-ficha">
                    <div class="card-imagen-horizontal">
                        <div class="preview-foto-noticia">
                            <img id="jugador-create-preview" src="{{ asset('img/club_14.png') }}" alt="{{ __('admin.players.images.default_alt') }}">
                        </div>

                        <div class="info-subida-noticia">
                            <h3>{{ __('admin.players.images.title') }}</h3>
                            <p>{{ __('admin.players.images.help') }}</p>

                            <label for="imagen_jugador" class="btn-cambiar-foto">
                                <i class="fas fa-camera"></i> {{ __('admin.players.images.select') }}
                            </label>
                            <input type="file" name="imagen_jugador" id="imagen_jugador" class="hidden-file-input" accept="image/*" data-image-input="#jugador-create-preview">

                            <span class="nota-subida">{{ __('admin.players.images.note_create') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-acciones-ficha">
                <a href="{{ route('jugadores.index') }}" class="btn-cancelar-ficha" title="{{ __('admin.players.actions.cancel_back') }}">{{ __('admin.common.cancel') }}</a>
                <button type="submit" class="btn-actualizar-ficha btn-actualizar-azul" title="{{ __('admin.players.actions.register') }}">
                    <i class="fas fa-save"></i> {{ __('admin.players.create.submit') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
