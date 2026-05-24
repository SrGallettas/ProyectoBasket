@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <x-admin-show-header
        :titulo="__('admin.teams.show.title', ['name' => $equipo->nombre])"
        :subtitulo="__('admin.teams.show.subtitle')"
        :editRoute="route('equipos.edit', $equipo->id)"
        :deleteRoute="route('equipos.destroy', $equipo->id)"
        :indexRoute="route('equipos.index')"
        :confirmMessage="__('admin.teams.actions.confirm_delete')"
        backClass="btn-ficha-volver-secondary"
    />

    <div class="pizarra-ficha">
        <div class="grid-formulario-fichas grid-formulario-fichas-equipo-show">
            <div class="seccion-form-ficha ficha-col-datos ficha-col-datos-equipo ficha-col-datos-equipo-full ficha-col-datos-equipo-top">
                <h3 class="ficha-title-no-margin"><i class="fas fa-shield-alt"></i> {{ __('admin.teams.show.data_title') }}</h3>

                <div class="campo-ficha ficha-info-row ficha-space-bottom">
                    <label>{{ __('admin.teams.fields.team_name') }}</label>
                    <p class="ficha-nombre-principal">{{ $equipo->nombre }}</p>
                </div>

                <div class="ficha-dorsal-wrapper{{ $equipo->es_local ? '' : ' ficha-dorsal-wrapper-single' }}">
                    <div class="campo-ficha flex-1">
                        <label>{{ __('admin.teams.fields.category') }}</label>
                        <p class="ficha-dato-destacado">
                            <i class="fas fa-tags"></i> {{ $equipo->categoria }}
                        </p>
                    </div>
                    @if($equipo->es_local)
                        <div class="campo-ficha flex-2">
                            <label>{{ __('admin.teams.fields.players_count') }}</label>
                            <div>
                                <span class="ficha-valor-azul">{{ $equipo->jugadores()->count() }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="ficha-datos-grid-equipo">
                    <div class="campo-ficha ficha-info-row ficha-info-row-soft">
                        <label>{{ __('admin.teams.show.team_type') }}</label>
                        <p class="ficha-valor-azul">
                            <i class="fas fa-location-dot"></i> {{ $equipo->es_local ? __('admin.teams.show.team_type_local') : __('admin.teams.show.team_type_visitor') }}
                        </p>
                    </div>

                    <div class="campo-ficha ficha-info-row ficha-info-row-soft ficha-space-top">
                        <label>{{ __('admin.teams.show.registered_at') }}</label>
                        <p class="ficha-date-text">
                            <i class="far fa-calendar-alt"></i>
                            {{ $equipo->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="ficha-media-stack ficha-media-stack-full">
                <div class="seccion-form-ficha ficha-col-foto ficha-col-foto-full">
                    <div class="ficha-foto-principal ficha-foto-principal-escudo">
                        @if($equipo->imagen_club)
                            <img src="{{ $equipo->imagen_club_url }}" alt="{{ __('admin.teams.images.shield_alt', ['team' => $equipo->nombre]) }}">
                        @else
                            <img src="{{ asset('img/club_14.png') }}" alt="{{ __('admin.teams.images.shield_unavailable_alt') }}">
                        @endif
                    </div>
                    <p class="ficha-caption">{{ __('admin.teams.show.shield_caption') }}</p>
                </div>

                @if($equipo->es_local && $equipo->foto_plantilla)
                    <div class="seccion-form-ficha ficha-col-foto ficha-col-foto-full ficha-col-foto-plantilla">
                        <div class="ficha-foto-principal">
                            <img src="{{ $equipo->foto_plantilla_url }}" alt="{{ __('admin.teams.images.template_alt', ['team' => $equipo->nombre]) }}">
                        </div>
                        <p class="ficha-caption">{{ __('admin.teams.show.template_caption') }}</p>
                    </div>
                @endif
            </div>

        </div>

        <div class="form-acciones-ficha form-acciones-ficha-centered">
            <p class="ficha-meta-footer">
                {{ __('admin.teams.show.meta_footer', ['date' => $equipo->created_at->format('d/m/Y H:i'), 'id' => $equipo->id]) }}
            </p>
        </div>
    </div>
</div>
@endsection
