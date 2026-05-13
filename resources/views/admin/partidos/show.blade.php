@extends('layouts.admin')

@use('Carbon\Carbon')

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <x-admin-show-header
        :titulo="__('admin.matches.show.title')"
        :subtitulo="__('admin.matches.show.subtitle', ['id' => $partido->id, 'category' => $partido->categoria])"
        :editRoute="route('partidos.edit', $partido->id)"
        :deleteRoute="route('partidos.destroy', $partido->id)"
        :indexRoute="route('partidos.index')"
        :confirmMessage="__('admin.matches.actions.confirm_delete_detail')"
    />

    <div class="pizarra-ficha">
        {{-- AREA DEL MARCADOR / ENFRENTAMIENTO --}}
        <div class="seccion-show-partido">
            
            <div class="marcador-visual">
                {{-- Equipo Local --}}
                <div class="equipo-detalle">
                    <div class="escudo-grande">
                        <img src="{{ $partido->equipoLocal->imagen_club_url }}" alt="{{ __('admin.teams.images.shield_alt', ['team' => $partido->equipoLocal->nombre]) }}">
                    </div>
                    <span class="nombre-equipo-gigante">{{ $partido->equipoLocal->nombre }}</span>
                    <span class="rol-equipo">{{ __('admin.matches.show.local_role') }}</span>
                </div>

                <div class="separador-vs">
                    <span class="txt-vs">VS</span>
                </div>

                {{-- Equipo Visitante --}}
                <div class="equipo-detalle">
                    <div class="escudo-grande">
                        <img src="{{ $partido->equipoVisitante->imagen_club_url }}" alt="{{ __('admin.teams.images.shield_alt', ['team' => $partido->equipoVisitante->nombre]) }}">
                    </div>
                    <span class="nombre-equipo-gigante">{{ $partido->equipoVisitante->nombre }}</span>
                    <span class="rol-equipo">{{ __('admin.matches.show.visitor_role') }}</span>
                </div>
            </div>

            <hr class="divisor-ficha">

            {{-- INFORMACIÓN LOGÍSTICA --}}
            <div class="grid-info-detalle">
                <div class="info-item">
                    <i class="fas fa-calendar-day"></i>
                    <div>
                        <label>{{ __('admin.matches.show.match_date') }}</label>
                        <p>{{ Carbon::parse($partido->fecha_hora)->translatedFormat('l, d \d\e F \d\e Y') }}</p>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <div>
                        <label>{{ __('admin.matches.show.start_time') }}</label>
                        <p>{{ Carbon::parse($partido->fecha_hora)->format('H:i') }} {{ __('admin.matches.show.hours_suffix') }}</p>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fas fa-map-marked-alt"></i>
                    <div>
                        <label>{{ __('admin.matches.show.venue') }}</label>
                        <p>{{ $partido->lugar }}</p>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fas fa-tags"></i>
                    <div>
                        <label>{{ __('admin.matches.show.competition') }}</label>
                        <p>
                            {{ $partido->categoria }}
                            @if($partido->clasificacion?->temporada)
                                - {{ __('admin.matches.show.season', ['season' => $partido->clasificacion->temporada]) }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
