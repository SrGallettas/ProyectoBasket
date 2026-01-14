@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    
    <header class="header-ficha">
        <div>
            <h2>Ficha del Jugador</h2>
            <p>Perfil detallado de la plantilla</p>
        </div>
        <div class="acciones-header-ficha">
            
            <a href="{{ route('jugadores.edit', $jugador->id) }}" class="btn-ficha-accion btn-ficha-edit">
                <i class="fas fa-edit"></i>&nbsp;EDITAR
            </a>

            <form action="{{ route('jugadores.destroy', $jugador->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este jugador?')" style="margin: 0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-ficha-accion btn-ficha-delete">
                    <i class="fas fa-trash-alt"></i>&nbsp;ELIMINAR
                </button>
            </form>

            <a href="{{ route('jugadores.index') }}" class="btn-ficha-accion btn-ficha-volver" style="background-color: #777;">
                <i class="fas fa-arrow-left"></i> Volver al listado
            </a>
        </div>
    </header>

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
                <p style="margin-top: 15px; color: #888; font-style: italic;">Fotografía Oficial del Club</p>
            </div>

            <div class="seccion-form-ficha ficha-col-datos">
                <h3 style="margin-top: 0;"><i class="fas fa-id-card"></i> Datos Técnicos</h3>
                
                <div class="campo-ficha ficha-info-row" style="margin-bottom: 25px;">
                    <label>Nombre Completo</label>
                    <p class="ficha-nombre-principal">
                        {{ $jugador->nombre }} <br> {{ $jugador->apellido }}
                    </p>
                </div>

                <div class="ficha-dorsal-wrapper">
                    <div class="campo-ficha" style="flex: 1;">
                        <label>Dorsal</label>
                        <div>
                            <span class="ficha-valor-azul">#{{ $jugador->dorsal ?? '00' }}</span>
                        </div>
                    </div>
                    <div class="campo-ficha" style="flex: 2;">
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

                <div class="campo-ficha" style="margin-top: 20px;">
                    <label>Fecha de Nacimiento</label>
                    <p style="margin: 0; font-size: 1.1rem; color: #4a5568;">
                        <i class="far fa-calendar-alt"></i> 
                        {{ $jugador->fecha_nacimiento ? \Carbon\Carbon::parse($jugador->fecha_nacimiento)->format('d/m/Y') : 'No registrada' }}
                    </p>
                </div>
            </div>

        </div>

        <div class="form-acciones-ficha" style="justify-content: center; border-top: 1px solid #eee; margin-top: 40px; padding-top: 20px;">
            <p style="color: #bbb; font-size: 0.8rem; margin: 0;">
                Registro creado el: {{ $jugador->created_at->format('d/m/Y H:i') }} | ID Sistema: #{{ $jugador->id }}
            </p>
        </div>
    </div>
</div>
@endsection