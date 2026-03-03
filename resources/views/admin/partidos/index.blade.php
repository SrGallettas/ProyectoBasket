@extends('layouts.admin')

@use('Carbon\Carbon') 

@section('contenido_admin')

<x-admin-header 
    titulo="Calendario de Partidos" 
    subtitulo="Próximos encuentros y programación del club"
    :rutaNuevo="route('partidos.create')" 
    textoNuevo="Programar Partido"
    :rutaIndex="route('partidos.index')"
/>

@if(session('mensaje'))
    <div class="alerta-exito">
        <i class="fas fa-check-circle"></i> {{ session('mensaje') }}
    </div>
@endif

<div class="pizarra-admin">
    <table class="tabla-admin">
        <thead>
            <tr>
                <th>Fecha y Hora</th>
                <th>Categoría</th>
                <th class="text-right">Local</th>
                <th class="text-center">Encuentro</th>
                <th class="text-left">Visitante</th>
                <th>Lugar</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($partidos as $partido)
            <tr>
                {{-- 1. FECHA Y HORA --}}
                <td>
                    <span class="fecha-txt">{{ Carbon::parse($partido->fecha_hora)->format('d/m/Y') }}</span><br>
                    <small class="hora-txt">{{ Carbon::parse($partido->fecha_hora)->format('H:i') }}h</small>
                </td>

                {{-- 2. CATEGORÍA --}}
                <td>
                    <span class="badge-categoria-inline">{{ $partido->categoria }}</span>
                </td>

                {{-- 3. EQUIPO LOCAL (Alineado a la derecha) --}}
                <td class="text-right">
                    <div class="col-equipo-horizontal-reverse">
                        <span class="nombre-equipo">{{ $partido->equipoLocal->nombre }}</span>
                        <img src="{{ asset('escudos/' . $partido->equipoLocal->imagen_club) }}" class="escudo-tabla">
                    </div>
                </td>

                {{-- 4. VS (Siempre VS fijo) --}}
                <td class="text-center">
                    <span class="badge-vs">VS</span>
                </td>

                {{-- 5. EQUIPO VISITANTE (Alineado a la izquierda) --}}
                <td class="text-left">
                    <div class="col-equipo-horizontal">
                        <img src="{{ asset('escudos/' . $partido->equipoVisitante->imagen_club) }}" class="escudo-tabla">
                        <span class="nombre-equipo">{{ $partido->equipoVisitante->nombre }}</span>
                    </div>
                </td>
                
                {{-- 6. LUGAR --}}
                <td>
                    <span class="lugar-txt">
                        <i class="fas fa-map-marker-alt"></i> {{ $partido->lugar }}
                    </span>
                </td>
                
                {{-- 7. ACCIONES --}}
                <td class="col-acciones text-center">
                    <div class="grupo-acciones justify-center">
                        <a href="{{ route('partidos.edit', $partido->id) }}" class="btn-accion editar" title="Editar">
                            <i class="fas fa-pen"></i>
                        </a>
                        
                        <form action="{{ route('partidos.destroy', $partido->id) }}" method="POST" onsubmit="return confirm('¿Eliminar partido?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-accion borrar" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="tabla-vacia">
                    <i class="fas fa-calendar-times"></i> No hay partidos programados.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="contenedor-paginacion">
        {{ $partidos->links() }}
    </div>
</div>
@endsection