@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <x-admin-show-header
        titulo="Ficha del Jugador"
        subtitulo="Perfil detallado de la plantilla"
        :editRoute="route('jugadores.edit', $jugador->id)"
        :deleteRoute="route('jugadores.destroy', $jugador->id)"
        :indexRoute="route('jugadores.index')"
        confirmMessage="¿Seguro que deseas eliminar este jugador?"
        backClass="btn-ficha-volver-secondary"
    />

    <div class="pizarra-ficha">
        <div class="grid-formulario-fichas">
            
            <div class="seccion-form-ficha ficha-col-foto">
                <div class="ficha-foto-principal">
                    @if($jugador->imagen_jugador)
                        <img src="{{ asset('jugadores/' . $jugador->imagen_jugador) }}" alt="Foto oficial">
                    @else
                        <img src="{{ asset('img/basket.jpeg') }}" alt="Sin foto">
                    @endif
                </div>
                <p class="ficha-caption">Fotografía Oficial del Club</p>
            </div>

            <div class="seccion-form-ficha ficha-col-datos ficha-col-datos-jugador">
                <h3 class="ficha-title-no-margin"><i class="fas fa-id-card"></i> Datos Técnicos</h3>
                
                <div class="campo-ficha ficha-info-row ficha-space-bottom">
                    <label>Nombre Completo</label>
                    <p class="ficha-nombre-principal">
                        {{ $jugador->nombre }} <br> {{ $jugador->apellido }}
                    </p>
                </div>

                <div class="ficha-dorsal-wrapper">
                    <div class="campo-ficha flex-1">
                        <label>Dorsal</label>
                        <div>
                            <span class="ficha-valor-azul">#{{ $jugador->dorsal ?? '00' }}</span>
                        </div>
                    </div>
                    <div class="campo-ficha flex-2">
                        <label>Posición</label>
                        <p class="ficha-dato-destacado">
                            <i class="fas fa-basketball-ball"></i> {{ $jugador->posicion }}
                        </p>
                    </div>
                </div>

                <div class="campo-ficha ficha-info-row">
                    <label>Equipo Actual</label>
                    <p class="ficha-valor-azul">
                        <i class="fas fa-shield-alt"></i> {{ $jugador->equipo->nombre ?? 'Sin equipo' }}
                    </p>
                </div>

                <div class="campo-ficha ficha-info-row">
                    <label>Categoría</label>
                    <p class="ficha-valor-azul">
                        <i class="fas fa-tags"></i> {{ $jugador->equipo->categoria ?? 'Sin Categoría' }}
                    </p>
                </div>

                <div class="campo-ficha ficha-space-top">
                    <label>Fecha de Nacimiento</label>
                    <p class="ficha-date-text">
                        <i class="far fa-calendar-alt"></i> 
                        {{ $jugador->fecha_nacimiento ? \Carbon\Carbon::parse($jugador->fecha_nacimiento)->format('d/m/Y') : 'No registrada' }}
                    </p>
                </div>
            </div>

        </div>

        <div class="form-acciones-ficha form-acciones-ficha-centered">
            <p class="ficha-meta-footer">
                Registro creado el: {{ $jugador->created_at->format('d/m/Y H:i') }} | ID Sistema: #{{ $jugador->id }}
            </p>
        </div>
    </div>
</div>
@endsection
