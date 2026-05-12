{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

{{-- CSS específico del listado de noticias --}}
@push('styles')
<x-page-style file="noticias.css" />
@endpush

@section('title', 'Noticias - Bellreguard Basket')

@section('contenido')

{{-- Hero principal del listado de noticias --}}
<x-public-news-hero
    badge="COMUNICADOS OFICIALES"
    title="Actualidad Granota"
    subtitle="Sigue el día a día de nuestros equipos, crónicas de partidos y eventos del club."
/>

{{-- Contenedor principal del listado --}}
<section class="noticias-container-rectangular">
    @if(session('mensaje'))
        <div class="public-alert-ok">
            {{ session('mensaje') }}
        </div>
    @endif

    {{-- Cabecera informativa del listado --}}
    <div class="noticias-filter-bar">
        <span><i class="fas fa-list-ul"></i> Mostrando últimas noticias del club</span>
    </div>

    {{-- Listado horizontal de noticias publicadas --}}
    <div class="noticias-list-horizontal">
        @forelse ($noticias as $noticia)
            <article class="noticia-row">
                {{-- Imagen --}}
                <div class="noticia-img-box">
                    <img src="{{ $noticia->imagen_noticia_url }}" alt="{{ $noticia->titulo }}" onerror="this.onerror=null;this.src='{{ asset('img/club_14.png') }}';">
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
                        {{-- Acción visible solo para usuarios con permisos de edición --}}
                        @if(auth()->user()?->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_EDITOR]))
                            <form action="{{ route('basket.noticias.toggle-oculto', $noticia->id) }}" method="POST" class="public-card-action">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="public-hide-button">Ocultar en web</button>
                            </form>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            {{-- Estado vacío si todavía no hay noticias visibles --}}
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

    {{-- Paginación del listado --}}
    <div class="contenedor-paginacion">
        {{ $noticias->links() }}
    </div>
</section>

@endsection
