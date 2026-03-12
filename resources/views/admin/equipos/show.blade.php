@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <x-admin-show-header
        :titulo="'Ficha del Equipo: ' . $equipo->nombre"
        subtitulo="Perfil general del equipo y su categoría"
        :editRoute="route('equipos.edit', $equipo->id)"
        :deleteRoute="route('equipos.destroy', $equipo->id)"
        :indexRoute="route('equipos.index')"
        confirmMessage="¿Eliminar equipo?"
        backClass="btn-ficha-volver-secondary"
    />

    <div class="pizarra-ficha">
        <div class="grid-formulario-fichas">
            <div class="seccion-form-ficha ficha-col-foto">
                <div class="ficha-foto-principal ficha-foto-principal-escudo">
                    @if($equipo->imagen_club)
                        <img src="{{ asset('escudos/' . $equipo->imagen_club) }}" alt="Escudo del {{ $equipo->nombre }}">
                    @else
                        <img src="{{ asset('img/club_14.png') }}" alt="Escudo no disponible">
                    @endif
                </div>
                <p class="ficha-caption">Escudo Oficial del Club</p>
            </div>

            <div class="seccion-form-ficha ficha-col-datos ficha-col-datos-equipo">
                <h3 class="ficha-title-no-margin"><i class="fas fa-shield-alt"></i> Datos del Equipo</h3>

                <div class="campo-ficha ficha-info-row ficha-space-bottom">
                    <label>Nombre del Equipo</label>
                    <p class="ficha-nombre-principal">{{ $equipo->nombre }}</p>
                </div>

                <div class="ficha-dorsal-wrapper">
                    <div class="campo-ficha flex-1">
                        <label>Categoría</label>
                        <p class="ficha-dato-destacado">
                            <i class="fas fa-tags"></i> {{ $equipo->categoria }}
                        </p>
                    </div>
                    <div class="campo-ficha flex-2">
                        <label>Jugadores inscritos</label>
                        <div>
                            <span class="ficha-valor-azul">{{ $equipo->jugadores()->count() }}</span>
                        </div>
                    </div>
                </div>

                <div class="campo-ficha ficha-info-row">
                    <label>Identificador</label>
                    <p class="ficha-valor-azul">
                        <i class="fas fa-hashtag"></i> Equipo #{{ $equipo->id }}
                    </p>
                </div>

                <div class="campo-ficha ficha-space-top">
                    <label>Fecha de Registro</label>
                    <p class="ficha-date-text">
                        <i class="far fa-calendar-alt"></i>
                        {{ $equipo->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="form-acciones-ficha form-acciones-ficha-centered">
            <p class="ficha-meta-footer">
                Registro creado el: {{ $equipo->created_at->format('d/m/Y H:i') }} | ID Sistema: #{{ $equipo->id }}
            </p>
        </div>
    </div>
</div>
@endsection
