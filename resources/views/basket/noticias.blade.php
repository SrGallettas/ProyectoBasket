@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/paginas/noticias.css') }}" />
@endpush

@section('titulo', 'Noticias - Bellreguard Basket')

@section('contenido')

{{-- HEADER DE LA SECCIÓN  --}}
<x-public-news-hero
    badge="COMUNICADOS OFICIALES"
    title="Actualidad Granota"
    subtitle="Sigue el día a día de nuestros equipos, crónicas de partidos y eventos del club."
/>

<section class="noticias-container-rectangular">
    @if(session('mensaje'))
        <div class="public-alert-ok">
            {{ session('mensaje') }}
        </div>
    @endif

    {{-- BARRA DE FILTRO --}}
    <div class="noticias-filter-bar">
        <span><i class="fas fa-list-ul"></i> Mostrando últimas noticias del club</span>
    </div>

    <div class="noticias-list-horizontal">
        @forelse ($noticias as $noticia)
            <article class="noticia-row">
                {{-- Imagen --}}
                <div class="noticia-img-box">
                    <img src="{{ asset('noticias/' . $noticia->imagen_noticia) }}" alt="{{ $noticia->titulo }}">
                    <div class="noticia-category-tag">BASKET</div>
                </div>
                
                <div class="noticia-info-box">
                    {{-- 1. (Fecha y Autor  --}}
                    <div class="noticia-meta-top">
                        <span class="meta-item"><i class="far fa-calendar-alt"></i> {{ $noticia->created_at->format('d/m/Y') }}</span>
                        <span class="meta-item"><i class="far fa-user"></i> {{ $noticia->autor }}</span>
                    </div>
                    
                    {{-- 2. Título --}}
                    <h2 class="noticia-title-bold">{{ $noticia->titulo }}</h2>
                    
                    {{-- 3. Subtítulo --}}
                    <p class="noticia-subtitle-line">{{ $noticia->subtitulo }}</p>
                    
                    {{-- 4. Botón de acción al final --}}
                    <div class="noticia-action">
                        <a href="{{ route('noticias.detalle', $noticia->slug) }}" class="btn-leer-noticia">
                            LEER CRÓNICA COMPLETA <i class="fas fa-chevron-right"></i>
                        </a>
                        @auth
                            <form action="{{ route('basket.noticias.toggle-oculto', $noticia->id) }}" method="POST" class="public-card-action">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="public-hide-button">Ocultar en web</button>
                            </form>
                        @endauth
                    </div>
                </div>
            </article>
        @empty
            <div class="estado-vacio">
                <div class="estado-vacio-icono">
                    <i class="fas fa-newspaper"></i>
                </div>
                <h3>Sin noticias publicadas por ahora</h3>
                <p>Estamos preparando nuevas publicaciones con cronicas, novedades del club y agenda deportiva.</p>
                <a href="{{ route('basket.inicio') }}" class="estado-vacio-accion">Volver al inicio</a>
            </div>
        @endforelse
    </div>

    {{--Esto es la paginación--}}
    <div class="contenedor-paginacion">
        {{ $noticias->links() }}
    </div>
</section>

@endsection
