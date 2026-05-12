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

@php
    // Alterna asc/desc cuando el usuario pulsa varias veces una cabecera.
    $nextDirection = function (string $column) use ($sort, $direction) {
        return $sort === $column && $direction === 'asc' ? 'desc' : 'asc';
    };

    // Solo pintamos la flecha si ya hay una ordenación activa en la URL.
    $hasActiveSort = request()->has('sort');

    $sortIndicator = function (string $column) use ($sort, $direction, $hasActiveSort) {
        if (!$hasActiveSort || $sort !== $column) {
            return '';
        }

        return $direction === 'asc' ? ' ▲' : ' ▼';
    };
@endphp

<div class="pizarra-admin">
    {{-- Añadimos una clase única a la tabla para no romper otras del sitio --}}
    <table class="tabla-admin tabla-partidos-ajustada">
        <thead>
            <tr>
                <th class="col-fecha">
                    <a
                        href="{{ route('partidos.index', ['search' => $search, 'sort' => 'fecha_hora', 'direction' => $nextDirection('fecha_hora')]) }}"
                        style="color: inherit; text-decoration: none; font: inherit;"
                    >
                        Fecha y Hora{!! $sortIndicator('fecha_hora') !!}
                    </a>
                </th>
                <th class="col-categoria">
                    <a
                        href="{{ route('partidos.index', ['search' => $search, 'sort' => 'categoria', 'direction' => $nextDirection('categoria')]) }}"
                        style="color: inherit; text-decoration: none; font: inherit;"
                    >
                        Categoría{!! $sortIndicator('categoria') !!}
                    </a>
                </th>
                <th class="text-right col-equipo" style="text-align: right; padding-right: 28px;">
                    <a
                        href="{{ route('partidos.index', ['search' => $search, 'sort' => 'equipo_local', 'direction' => $nextDirection('equipo_local')]) }}"
                        style="color: inherit; text-decoration: none; font: inherit;"
                    >
                        Local{!! $sortIndicator('equipo_local') !!}
                    </a>
                </th>
                <th class="text-center col-vs">Encuentro</th>
                <th class="text-left col-equipo">
                    <a
                        href="{{ route('partidos.index', ['search' => $search, 'sort' => 'equipo_visitante', 'direction' => $nextDirection('equipo_visitante')]) }}"
                        style="color: inherit; text-decoration: none; font: inherit;"
                    >
                        Visitante{!! $sortIndicator('equipo_visitante') !!}
                    </a>
                </th>
                <th class="text-center col-lugar">
                    <a
                        href="{{ route('partidos.index', ['search' => $search, 'sort' => 'lugar', 'direction' => $nextDirection('lugar')]) }}"
                        style="color: inherit; text-decoration: none; font: inherit;"
                    >
                        Lugar{!! $sortIndicator('lugar') !!}
                    </a>
                </th>
                <th class="text-center col-acciones">
                    <a
                        href="{{ route('partidos.index', ['search' => $search, 'sort' => 'oculto', 'direction' => $nextDirection('oculto')]) }}"
                        style="color: inherit; text-decoration: none; font: inherit;"
                    >
                        Acciones{!! $sortIndicator('oculto') !!}
                    </a>
                </th>
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
                    <div class="admin-subtle-text" style="margin-top: 6px;">
                        {{ ucfirst($partido->tipo_partido ?? 'liga') }}
                        @if($partido->tipo_partido === 'liga' && $partido->jornada)
                            · J{{ $partido->jornada }}
                        @endif
                        @if($partido->tipo_partido === 'liga' && $partido->clasificacion)
                            · {{ $partido->clasificacion->nombre }}
                        @endif
                    </div>
                </td>

                {{-- 3. LOCAL: Alineado a la derecha para que pegue al VS --}}
                <td class="text-right col-equipo">
                    <div class="contenedor-equipo-tabla reverse">
                        <span class="nombre-equipo">{{ $partido->equipoLocal->nombre }}</span>
                        <span class="admin-shield-frame">
                            <img src="{{ $partido->equipoLocal->imagen_club_url }}" class="escudo-tabla-ajustado" alt="Escudo {{ $partido->equipoLocal->nombre }}">
                        </span>
                    </div>
                </td>

                {{-- 4. VS: Pequeño y discreto --}}
                <td class="text-center col-vs">
                    <span class="vs-decorativo">VS</span>
                </td>

                {{-- 5. VISITANTE: Alineado a la izquierda --}}
                <td class="text-left col-equipo">
                    <div class="contenedor-equipo-tabla">
                        <span class="admin-shield-frame">
                            <img src="{{ $partido->equipoVisitante->imagen_club_url }}" class="escudo-tabla-ajustado" alt="Escudo {{ $partido->equipoVisitante->nombre }}">
                        </span>
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

                        <a href="{{ route('partidos.anotacion', $partido->id) }}" class="btn-accion apuntar" title="Apuntar estadísticas">
                            <i class="fas fa-basketball"></i>
                        </a>

                        <form action="{{ route('partidos.toggle-oculto', $partido->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn-accion {{ $partido->oculto ? 'mostrar' : 'ocultar' }}" title="{{ $partido->oculto ? 'Mostrar en Partidos' : 'Ocultar de Partidos' }}">
                                <i class="fas {{ $partido->oculto ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                            </button>
                        </form>

                        @if($partido->tipo_partido === 'liga' && $partido->clasificacion_id)
                            <form action="{{ route('partidos.toggle-oculto-clasificacion', $partido->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button
                                    type="submit"
                                    class="btn-accion {{ $partido->oculto_en_clasificacion ? 'mostrar' : 'ocultar' }}"
                                    title="{{ $partido->oculto_en_clasificacion ? 'Mostrar en Clasificación' : 'Ocultar de Clasificación' }}"
                                >
                                    <i class="fas fa-trophy"></i>
                                </button>
                            </form>
                        @endif

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
