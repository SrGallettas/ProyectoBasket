@extends('layouts.admin')

@section('title', __('admin.players.meta.edit_title'))

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <x-admin-edit-header
        :titulo="__('admin.players.edit.title')"
        :subtitulo="__('admin.players.edit.subtitle', ['name' => e($jugador->nombre . ' ' . $jugador->apellido)])"
        :backRoute="route('jugadores.index')"
    />

    @if ($errors->any())
    <div class="admin-error-box">
        <strong>{{ __('admin.common.form_error') }}</strong>
        {{-- Añadimos list-style: none y quitamos el padding --}}
        <ul class="admin-error-list">
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
                    <h3><i class="fas fa-user"></i> {{ __('admin.players.sections.personal') }}</h3>
                    
                    <div class="campo-ficha">
                        <label>{{ __('admin.players.fields.name') }}</label>
                        <input type="text" name="nombre" value="{{ $jugador->nombre }}" class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.players.fields.surname') }}</label>
                        <input type="text" name="apellido" value="{{ $jugador->apellido }}" class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.players.fields.birth_date') }}</label>
                        <input type="date" name="fecha_nacimiento" value="{{ $jugador->fecha_nacimiento }}" class="input-ficha">
                    </div>
                </div>

                <div class="seccion-form-ficha seccion-deportiva">
                    <h3><i class="fas fa-basketball-ball"></i> {{ __('admin.players.sections.sports') }}</h3>

                    <div class="jugador-form-double-grid">
                        <div class="campo-ficha">
                            <label>{{ __('admin.players.fields.number') }}</label>
                            <input type="number" name="dorsal" value="{{ $jugador->dorsal }}" class="input-ficha">
                        </div>
                        <div class="campo-ficha">
                            <label>{{ __('admin.players.fields.position') }}</label>
                            <select name="posicion" class="input-ficha input-white">
                                @foreach(\App\Models\Jugador::positionOptions() as $pos)
                                    <option value="{{ $pos }}" {{ $jugador->posicion == $pos ? 'selected' : '' }}>{{ \App\Models\Jugador::displayPositionLabel($pos) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.players.fields.current_team') }}</label>
                        <select name="equipo_id" class="input-ficha input-white">
                            @foreach($equipos as $equipo)
                                <option value="{{ $equipo->id }}" {{ $jugador->equipo_id == $equipo->id ? 'selected' : '' }}>
                                    {{ $equipo->nombre }} ({{ $equipo->categoria }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.players.fields.visibility') }}</label>
                        <div class="checkbox-wrapper checkbox-wrapper-ficha">
                            <input type="checkbox" name="oculto" value="1" {{ old('oculto', $jugador->oculto) ? 'checked' : '' }}>
                            <span>{{ __('admin.players.fields.hide_public') }}</span>
                        </div>
                    </div>
                </div>

                <div class="seccion-foto-ficha">
                    <div class="card-imagen-horizontal">
                        <div class="preview-foto-noticia">
                            @if($jugador->imagen_jugador)
                                <img id="jugador-edit-preview" src="{{ $jugador->imagen_jugador_url }}" alt="{{ __('admin.players.images.current_alt') }}" onerror="this.onerror=null;this.src='{{ asset('img/club_14.png') }}';">
                            @else
                                <img id="jugador-edit-preview" src="{{ asset('img/club_14.png') }}" alt="{{ __('admin.players.images.default_alt') }}">
                            @endif
                        </div>

                        <div class="info-subida-noticia">
                            <h3>{{ __('admin.players.images.title') }}</h3>
                            <p>{{ __('admin.players.images.help') }}</p>

                            <label for="imagen_jugador" class="btn-cambiar-foto">
                                <i class="fas fa-camera"></i> {{ __('admin.players.images.change') }}
                            </label>
                            <input type="file" name="imagen_jugador" id="imagen_jugador" class="hidden-file-input" accept="image/*" data-image-input="#jugador-edit-preview">

                            <span class="nota-subida">{{ __('admin.players.images.note_edit') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-acciones-ficha">
                <a href="{{ route('jugadores.index') }}" class="btn-cancelar-ficha" title="{{ __('admin.players.actions.cancel_back') }}">{{ __('admin.common.cancel') }}</a>
                <button type="submit" class="btn-actualizar-ficha" title="{{ __('admin.players.actions.update') }}">
                    <i class="fas fa-sync-alt"></i> {{ __('admin.players.edit.submit') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
