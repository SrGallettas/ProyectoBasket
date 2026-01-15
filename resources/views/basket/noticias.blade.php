@extends('layouts.app')

@section('titulo', 'Noticias - Bellreguard Basket')

@section('contenido')

{{-- HEADER DE LA SECCIÓN  --}}
<header class="news-hero">
    <div class="news-hero-overlay">
        <div class="news-hero-content">
            <span class="news-hero-badge">COMUNICADOS OFICIALES</span>
            <h1>Actualidad Granota</h1>
            <p>Sigue el día a día de nuestros equipos, crónicas de partidos y eventos del club.</p>
            <div class="news-hero-divider"></div>
        </div>
    </div>
</header>

<section class="noticias-container-rectangular">
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
                    </div>
                </div>
            </article>
        @empty
            {{-- ESTE MENSAJE APARECE SI NO HAY NOTICIAS --}}
            <div class="sin-noticias">
                <i class="fas fa-newspaper"></i>
                <p>Actualmente no hay noticias publicadas.</p>
                <span>¡Vuelve pronto para enterarte de todas las novedades!</span>
            </div>
        @endforelse
    </div>

    {{--Esto es la paginación--}}
    <div class="contenedor-paginacion">
        {{ $noticias->links() }}
    </div>
</section>

@endsection