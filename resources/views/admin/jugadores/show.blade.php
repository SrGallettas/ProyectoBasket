@extends('layouts.admin')

@section('title', __('admin.players.meta.show_title'))

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <x-admin-show-header
        :titulo="__('admin.players.show.title')"
        :subtitulo="__('admin.players.show.subtitle')"
        :editRoute="route('jugadores.edit', $jugador->id)"
        :deleteRoute="route('jugadores.destroy', $jugador->id)"
        :indexRoute="route('jugadores.index')"
        :confirmMessage="__('admin.players.actions.confirm_delete_detail')"
        backClass="btn-ficha-volver-secondary"
    />

    <div class="pizarra-ficha">
        <div class="grid-formulario-fichas">
            
            <div class="seccion-form-ficha ficha-col-foto">
                <div class="ficha-foto-principal">
                    @if($jugador->imagen_jugador)
                        <img src="{{ $jugador->imagen_jugador_url }}" alt="{{ __('admin.players.images.official_alt') }}" onerror="this.onerror=null;this.src='{{ asset('img/club_14.png') }}';">
                    @else
                        <img src="{{ asset('img/club_14.png') }}" alt="{{ __('admin.players.fields.no_photo') }}">
                    @endif
                </div>
                <p class="ficha-caption">{{ __('admin.players.show.photo_caption') }}</p>
            </div>

            <div class="seccion-form-ficha ficha-col-datos ficha-col-datos-jugador">
                <h3 class="ficha-title-no-margin"><i class="fas fa-id-card"></i> {{ __('admin.players.show.tech_data') }}</h3>
                
                <div class="campo-ficha ficha-info-row ficha-space-bottom">
                    <label>{{ __('admin.players.show.full_name') }}</label>
                    <p class="ficha-nombre-principal">
                        {{ $jugador->nombre }} <br> {{ $jugador->apellido }}
                    </p>
                </div>

                <div class="ficha-dorsal-wrapper">
                    <div class="campo-ficha flex-1">
                        <label>{{ __('admin.players.fields.number') }}</label>
                        <div>
                            <span class="ficha-valor-azul">#{{ $jugador->dorsal ?? '00' }}</span>
                        </div>
                    </div>
                    <div class="campo-ficha flex-2">
                        <label>{{ __('admin.players.fields.position') }}</label>
                        <p class="ficha-dato-destacado">
                            <i class="fas fa-basketball-ball"></i> {{ $jugador->posicion }}
                        </p>
                    </div>
                </div>

                <div class="campo-ficha ficha-info-row">
                    <label>{{ __('admin.players.fields.current_team') }}</label>
                    <p class="ficha-valor-azul">
                        <i class="fas fa-shield-alt"></i> {{ $jugador->equipo->nombre ?? __('admin.players.fields.no_team') }}
                    </p>
                </div>

                <div class="campo-ficha ficha-info-row">
                    <label>{{ __('admin.players.fields.category') }}</label>
                    <p class="ficha-valor-azul">
                        <i class="fas fa-tags"></i> {{ $jugador->equipo->categoria ?? __('admin.players.fields.no_category') }}
                    </p>
                </div>

                <div class="campo-ficha ficha-space-top">
                    <label>{{ __('admin.players.fields.birth_date') }}</label>
                    <p class="ficha-date-text">
                        <i class="far fa-calendar-alt"></i> 
                        {{ $jugador->fecha_nacimiento ? \Carbon\Carbon::parse($jugador->fecha_nacimiento)->format('d/m/Y') : __('admin.players.fields.not_registered') }}
                    </p>
                </div>
            </div>

        </div>

        <div class="form-acciones-ficha form-acciones-ficha-centered">
            <p class="ficha-meta-footer">
                {{ __('admin.players.show.meta_footer', ['date' => $jugador->created_at->format('d/m/Y H:i'), 'id' => $jugador->id]) }}
            </p>
        </div>
    </div>
</div>
@endsection
