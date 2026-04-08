{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/paginas/partidos.css') }}" />
@endpush

{{-- IMPORTAMOS LA LIBRERÍA CARBON --}}
@use('Carbon\Carbon') 

{{-- TÍTULO DE LA PESTAÑA --}}
@section('title', 'Partidos - Bellreguard Club de Basket')

{{-- CONTENIDO DE LA PÁGINA --}}
@section('contenido')

<x-public-news-hero
    badge="CALENDARIO OFICIAL"
    title="Próximos Partidos"
    subtitle="Consulta el calendario de todos nuestros equipos."
/>

<section class="seccion-calendario">
    <div class="rejilla-partidos">
        @if(session('mensaje'))
            <div class="partidos-alerta-ok">
                {{ session('mensaje') }}
            </div>
        @endif
        
        {{-- Agrupamos los partidos por su categoría --}}
        @php
            $partidosPorCategoria = $partidos->groupBy('categoria');
        @endphp

        @forelse($partidosPorCategoria as $categoria => $encuentros)
            <div class="caja-equipo full-width">
                <div class="titulo-equipo">
                    {{-- Icono dinámico según la categoría --}}
                    <i class="fa-solid {{ $categoria == 'Senior A' || $categoria == 'Senior B' ? 'fa-trophy' : 'fa-star' }}"></i>
                    <div>
                        <h3>Equipo {{ $categoria }}</h3>
                        <p>Bellreguard Club de Basket</p>
                    </div>
                </div>

                <div class="rejilla-partido-doble {{ $encuentros->count() === 1 ? 'unico' : '' }}">
                    @foreach($encuentros as $partido)
                        @php
                            $statsLocal = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_local_id);
                            $statsVisitante = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_visitante_id);
                            $isFinalizado = !is_null(optional($statsLocal)->puntos_totales) && !is_null(optional($statsVisitante)->puntos_totales);
                            $puntosLocal = $isFinalizado ? (int) $statsLocal->puntos_totales : null;
                            $puntosVisitante = $isFinalizado ? (int) $statsVisitante->puntos_totales : null;
                        @endphp
                        <div class="tarjeta-partido {{ $partido->oculto ? 'tarjeta-partido-oculto' : '' }}">
                            <span class="etiqueta-estado {{ $isFinalizado ? 'finalizado' : 'proximo' }}">
                                {{ $isFinalizado ? 'Finalizado' : 'Próximo' }}
                            </span>
                            <span class="jornada">Encuentro</span>
                            
                            <div class="enfrentamiento">
                                {{-- EQUIPO LOCAL --}}
                                <div class="equipo local">
                                    <img src="{{ $partido->equipoLocal->imagen_club_url }}" alt="{{ $partido->equipoLocal->nombre }}">
                                    <p>{{ $partido->equipoLocal->nombre }}</p>
                                </div>

                                @if($isFinalizado)
                                    <span class="marcador-inline">{{ $puntosLocal }} - {{ $puntosVisitante }}</span>
                                @else
                                    <span class="vs">VS</span>
                                @endif

                                {{-- EQUIPO VISITANTE --}}
                                <div class="equipo visitante">
                                    <img src="{{ $partido->equipoVisitante->imagen_club_url }}" alt="{{ $partido->equipoVisitante->nombre }}">
                                    <p>{{ $partido->equipoVisitante->nombre }}</p>
                                </div>
                            </div>

                            <div class="info-adicional">
                                {{-- Fecha formateada en español (ej: Sábado, 23 Marzo 2026) --}}
                                <div class="dato-horario">
                                    <i class="fa-regular fa-calendar-alt"></i> 
                                    {{ Carbon::parse($partido->fecha_hora)->translatedFormat('l, d F Y') }}
                                </div>
                                
                                {{-- Hora del partido --}}
                                <div class="dato-horario">
                                    <i class="fa-regular fa-clock"></i> 
                                    {{ Carbon::parse($partido->fecha_hora)->format('H:i') }}h
                                </div>

                                {{-- Lugar del encuentro --}}
                                <div class="dato-lugar">
                                    <i class="fa-solid fa-location-dot"></i> 
                                    {{ $partido->lugar }}
                                </div>
                            </div>

                            <div class="acciones-partido">
                                <a href="{{ route('basket.partidos.detalle', $partido->id) }}" class="btn-detalle-partido">
                                    Ver detalles del partido
                                </a>
                                @if(auth()->user()?->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COACH]))
                                    <form action="{{ route('basket.partidos.toggle-oculto', $partido->id) }}" method="POST" class="form-ocultar-partido">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-toggle-oculto-partido">
                                            {{ $partido->oculto ? 'Mostrar en web' : 'Ocultar en web' }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="estado-vacio partidos-empty-card">
                <div class="estado-vacio-icono">
                    <i class="fa-regular fa-calendar-xmark"></i>
                </div>
                <h3>Calendario en actualización</h3>
                <p>Estamos cerrando los próximos encuentros. Muy pronto publicaremos nuevas fechas y horarios.</p>
                <a href="{{ route('basket.contacto') }}" class="estado-vacio-accion">Contactar con el club</a>
            </div>
        @endforelse
        
    </div>
</section>

@endsection
