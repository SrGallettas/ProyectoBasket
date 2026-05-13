{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

{{-- CSS específico del calendario público --}}
@push('styles')
<x-page-style file="partidos.css" />
@endpush

{{-- IMPORTAMOS LA LIBRERÍA CARBON --}}
@use('Carbon\Carbon') 

{{-- TÍTULO DE LA PESTAÑA --}}
@section('title', __('public.matches.meta.title'))
@section('meta_description', __('public.matches.meta.description'))

{{-- CONTENIDO DE LA PÁGINA --}}
@section('contenido')

{{-- Hero principal del calendario --}}
<x-public-news-hero
    :badge="__('public.matches.hero.badge')"
    :title="__('public.matches.hero.title')"
    :subtitle="__('public.matches.hero.subtitle')"
/>

{{-- Rejilla principal de partidos agrupados por categoría --}}
<section class="seccion-calendario">
    <div class="rejilla-partidos">
        @if(session('mensaje'))
            <div class="partidos-alerta-ok">
                {{ session('mensaje') }}
            </div>
        @endif
        
        {{-- Agrupamos los partidos por categoría para mostrarlos en bloques --}}
        @php
            // Orden manual de categorías: primero los equipos más mayores.
            $ordenCategorias = ['Senior', 'Senior B', 'Senior C', 'Senior D', 'Junior', 'Junior A', 'Junior B', 'Cadete', 'Cadete A', 'Cadete B', 'Infantil', 'Infantil A', 'Infantil B', 'Alevín', 'Alevin', 'Benjamín', 'Benjamin', 'Prebenjamín', 'Prebenjamin'];

            $partidosPorCategoria = $partidos
                ->groupBy('categoria')
                ->sortBy(function ($encuentros, $categoria) use ($ordenCategorias) {
                    $indice = array_search((string) $categoria, $ordenCategorias, true);

                    return $indice !== false ? $indice : count($ordenCategorias);
                });
        @endphp

        @forelse($partidosPorCategoria as $categoria => $encuentros)
            {{-- Bloque visual de una categoría concreta --}}
            <div class="caja-equipo full-width">
                <div class="titulo-equipo">
                    {{-- Icono dinámico según la categoría --}}
                    <i class="fa-solid {{ $categoria == 'Senior' || $categoria == 'Senior B' ? 'fa-trophy' : 'fa-star' }}"></i>
                    <div>
                        <h3>Equipo {{ $categoria }}</h3>
                        <p>Bellreguard CB</p>
                    </div>
                </div>

                <div class="rejilla-partido-doble {{ $encuentros->count() === 1 ? 'unico' : '' }}">
                    @foreach($encuentros as $partido)
                        {{-- Datos del partido para calcular si ya hay marcador final --}}
                        @php
                            $statsLocal = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_local_id);
                            $statsVisitante = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_visitante_id);
                            $isFinalizado = !is_null(optional($statsLocal)->puntos_totales) && !is_null(optional($statsVisitante)->puntos_totales);
                            $puntosLocal = $isFinalizado ? (int) $statsLocal->puntos_totales : null;
                            $puntosVisitante = $isFinalizado ? (int) $statsVisitante->puntos_totales : null;
                        @endphp
                        <div class="tarjeta-partido {{ $partido->oculto ? 'tarjeta-partido-oculto' : '' }}">
                            <span class="etiqueta-estado {{ $isFinalizado ? 'finalizado' : 'proximo' }}">
                                {{ $isFinalizado ? __('public.matches.status.finished') : __('public.matches.status.upcoming') }}
                            </span>
                            <span class="jornada">{{ __('public.matches.match_label') }}</span>
                            
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
                                    {{ __('public.matches.detail_cta') }}
                                </a>
                                {{-- Acción de visibilidad solo para usuarios con permisos --}}
                                @if(auth()->user()?->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COACH]))
                                    <form action="{{ route('basket.partidos.toggle-oculto', $partido->id) }}" method="POST" class="form-ocultar-partido">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-toggle-oculto-partido">
                                            {{ $partido->oculto ? __('public.matches.actions.show') : __('public.matches.actions.hide') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            {{-- Estado vacío si todavía no hay calendario publicado --}}
            <div class="estado-vacio partidos-empty-card">
                <div class="estado-vacio-icono">
                    <i class="fa-regular fa-calendar-xmark"></i>
                </div>
                <h3>{{ __('public.matches.empty.title') }}</h3>
                <p>{{ __('public.matches.empty.text') }}</p>
                <a href="{{ route('basket.contacto') }}" class="estado-vacio-accion">{{ __('public.matches.empty.cta') }}</a>
            </div>
        @endforelse
        
    </div>
</section>

@if($partidos->hasPages())
    <section class="seccion-calendario">
        {{ $partidos->links() }}
    </section>
@endif

@endsection
