@extends('layouts.admin')

{{-- Importamos Carbon para el formateo de fechas --}}
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
    {{-- Añadimos una clase única a la tabla para no romper otras del sitio --}}
    <table class="tabla-admin tabla-partidos-ajustada">
        <thead>
            <tr>
                <th class="col-fecha">Fecha y Hora</th>
                <th class="col-categoria">Categoría</th>
                <th class="text-right col-equipo" style="text-align: right; padding-right: 28px;">Local</th>
                <th class="text-center col-vs">Encuentro</th>
                <th class="text-left col-equipo">Visitante</th>
                <th class="text-center col-lugar">Lugar</th>
                <th class="text-center col-acciones">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($partidos as $partido)
            <tr>
                {{-- 1. FECHA: Con negrita para que resalte --}}
                <td class="col-fecha">
                    <div class="celda-fecha">
                        <span class="txt-dia">{{ Carbon::parse($partido->fecha_hora)->format('d/m/Y') }}</span>
                        <span class="txt-hora">{{ Carbon::parse($partido->fecha_hora)->format('H:i') }}h</span>
                    </div>
                </td>

                {{-- 2. CATEGORÍA: Con un fondo suave --}}
                <td class="col-categoria">
                    <span class="badge-categoria-pildora">{{ $partido->categoria }}</span>
                    @if($partido->oculto)
                        <span class="badge-oculto-admin">Oculto</span>
                    @endif
                </td>

                {{-- 3. LOCAL: Alineado a la derecha para que pegue al VS --}}
                <td class="text-right col-equipo">
                    <div class="contenedor-equipo-tabla reverse">
                        <span class="nombre-equipo">{{ $partido->equipoLocal->nombre }}</span>
                        <img src="{{ asset('escudos/' . $partido->equipoLocal->imagen_club) }}" class="escudo-tabla-ajustado">
                    </div>
                </td>

                {{-- 4. VS: Pequeño y discreto --}}
                <td class="text-center col-vs">
                    <span class="vs-decorativo">VS</span>
                </td>

                {{-- 5. VISITANTE: Alineado a la izquierda --}}
                <td class="text-left col-equipo">
                    <div class="contenedor-equipo-tabla">
                        <img src="{{ asset('escudos/' . $partido->equipoVisitante->imagen_club) }}" class="escudo-tabla-ajustado">
                        <span class="nombre-equipo">{{ $partido->equipoVisitante->nombre }}</span>
                    </div>
                </td>
                
                {{-- 6. LUGAR --}}
                <td class="col-lugar">
                    <div class="txt-lugar">
                        <i class="fas fa-map-marker-alt"></i> {{ $partido->lugar }}
                    </div>
                </td>
                
                {{-- 7. ACCIONES --}}
                <td class="text-center col-acciones">
                    <div class="flex-acciones">
                        
                        {{-- Botón Ver --}}
                        <a href="{{ route('partidos.show', $partido->id) }}" class="btn-accion" title="Ver detalles del partido">
                            <i class="fas fa-eye"></i>
                        </a>

                        <a href="{{ route('partidos.edit', $partido->id) }}" class="btn-accion editar" title="Editar partido">
                            <i class="fas fa-pen"></i>
                        </a>

                        <form action="{{ route('partidos.toggle-oculto', $partido->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn-accion {{ $partido->oculto ? 'mostrar' : 'ocultar' }}" title="{{ $partido->oculto ? 'Mostrar partido' : 'Ocultar partido' }}">
                                <i class="fas {{ $partido->oculto ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                            </button>
                        </form>

                        <form action="{{ route('partidos.destroy', $partido->id) }}" method="POST" onsubmit="return confirm('¿Eliminar partido?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-accion borrar" title="Eliminar partido">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="tabla-vacia">No hay partidos programados.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="contenedor-paginacion">
        {{ $partidos->links() }}
    </div>
</div>
@endsection
