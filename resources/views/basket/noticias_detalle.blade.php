{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

{{-- CSS específico del detalle de noticia --}}
@push('styles')
<x-page-style file="noticias.css" />
@endpush

@section('title', $noticia->titulo . ' - Bellreguard Basket')
@section('meta_description', $noticia->subtitulo ?: \Illuminate\Support\Str::limit(strip_tags($noticia->contenido), 160))

@section('contenido')
{{-- Artículo completo de la noticia seleccionada --}}
<article class="articulo-noticia">
    {{-- Cabecera con categoría, fecha, título y autor --}}
    <header class="articulo-header">
        <div class="articulo-meta">
            <span class="categoria-badge">{{ __('public.news_detail.category_badge') }}</span>
            <span class="fecha-top">{{ $noticia->created_at->translatedFormat('d M, Y') }}</span>
        </div>
        
        <h1 class="titulo-noticia">{{ $noticia->titulo }}</h1>
        <p class="articulo-subtitulo">{{ $noticia->subtitulo }}</p>
        
        <div class="autor-box">
            <div class="autor-avatar">
                {{ substr($noticia->autor, 0, 1) }}
            </div>
            <div class="autor-detalles">
                <span class="nombre-autor">{{ $noticia->autor }}</span>
                <span class="rol-autor">{{ __('public.news_detail.author_role') }}</span>
            </div>
        </div>
    </header>

    {{-- Imagen principal de la noticia --}}
    <div class="articulo-imagen-wrap">
        <img src="{{ $noticia->imagen_noticia_url }}" alt="{{ $noticia->titulo }}" onerror="this.onerror=null;this.src='{{ asset('img/club_14.png') }}';">
    </div>

    {{-- Cuerpo completo con saltos de línea respetados --}}
    <div class="articulo-cuerpo">
        <div class="texto-contenido">
            {!! nl2br(e($noticia->contenido)) !!}
        </div>
    </div>

    {{-- Pie con opciones de compartir y navegación de vuelta --}}
    <footer class="articulo-footer">
        <div class="caja-compartir">
            <h3>{{ __('public.news_detail.share.title') }}</h3>
            <p>{{ __('public.news_detail.share.text') }}</p>
            <div class="social-flex">
                <a href="#" class="social-link fb"><i class="fab fa-facebook-f"></i> Facebook</a>
                <a href="#" class="social-link tw"><i class="fab fa-twitter"></i> Twitter</a>
                <a href="#" class="social-link wa"><i class="fab fa-whatsapp"></i> WhatsApp</a>
            </div>
        </div>

        <div class="navegacion-final">
            <a href="{{ route('basket.noticias') }}" class="btn-regresar-lista">
                <i class="fas fa-arrow-left"></i> {{ __('public.news_detail.back') }}
            </a>
        </div>
    </footer>
</article>
@endsection
