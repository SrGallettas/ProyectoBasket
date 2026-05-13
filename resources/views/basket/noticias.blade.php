{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

{{-- CSS específico del listado de noticias --}}
@push('styles')
<x-page-style file="noticias.css" />
@endpush

@section('title', __('public.news.meta.title'))
@section('meta_description', __('public.news.meta.description'))

@section('contenido')

{{-- Hero principal del listado de noticias --}}
<x-public-news-hero
    :badge="__('public.news.hero.badge')"
    :title="__('public.news.hero.title')"
    :subtitle="__('public.news.hero.subtitle')"
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
        <span><i class="fas fa-list-ul"></i> {{ __('public.news.listing_bar') }}</span>
    </div>

    {{-- Listado horizontal de noticias publicadas --}}
    <div class="noticias-list-horizontal">
        @forelse ($noticias as $noticia)
            <article class="noticia-row">
                {{-- Imagen --}}
                <div class="noticia-img-box">
                    <img src="{{ $noticia->imagen_noticia_url }}" alt="{{ $noticia->titulo }}" onerror="this.onerror=null;this.src='{{ asset('img/club_14.png') }}';">
                    <div class="noticia-category-tag">{{ __('public.news.category_tag') }}</div>
                </div>
                
                <div class="noticia-info-box">
                    {{-- 1. (Fecha y Autor  --}}
                    <div class="noticia-meta-top">
                        <span class="meta-item"><i class="far fa-calendar-alt"></i> {{ $noticia->created_at->translatedFormat('d/m/Y') }}</span>
                        <span class="meta-item"><i class="far fa-user"></i> {{ $noticia->autor }}</span>
                    </div>
                    
                    {{-- 2. Título --}}
                    <h2 class="noticia-title-bold">{{ $noticia->titulo }}</h2>
                    
                    {{-- 3. Subtítulo --}}
                    <p class="noticia-subtitle-line">{{ $noticia->subtitulo }}</p>
                    
                    {{-- 4. Botón de acción al final --}}
                    <div class="noticia-action">
                        <a href="{{ route('noticias.detalle', $noticia->slug) }}" class="btn-leer-noticia">
                            {{ __('public.news.read_cta') }} <i class="fas fa-chevron-right"></i>
                        </a>
                        {{-- Acción visible solo para usuarios con permisos de edición --}}
                        @if(auth()->user()?->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_EDITOR]))
                            <form action="{{ route('basket.noticias.toggle-oculto', $noticia->id) }}" method="POST" class="public-card-action">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="public-hide-button">{{ __('public.news.hide') }}</button>
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
                <h3>{{ __('public.news.empty_title') }}</h3>
                <p>{{ __('public.news.empty_text') }}</p>
                <a href="{{ route('basket.inicio') }}" class="estado-vacio-accion">{{ __('public.news.empty_cta') }}</a>
            </div>
        @endforelse
    </div>

    {{-- Paginación del listado --}}
    <div class="contenedor-paginacion">
        {{ $noticias->links() }}
    </div>
</section>

@endsection
