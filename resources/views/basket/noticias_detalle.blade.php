@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/paginas/noticias.css') }}" />
@endpush

@section('title', $noticia->titulo . ' - Bellreguard Basket')

@section('contenido')
<article class="articulo-noticia">
    {{-- Cabecera --}}
    <header class="articulo-header">
        <div class="articulo-meta">
            <span class="categoria-badge">ACTUALIDAD</span>
            <span class="fecha-top">{{ $noticia->created_at->format('d M, Y') }}</span>
        </div>
        
        <h1 class="titulo-noticia">{{ $noticia->titulo }}</h1>
        <p class="articulo-subtitulo">{{ $noticia->subtitulo }}</p>
        
        <div class="autor-box">
            <div class="autor-avatar">
                {{ substr($noticia->autor, 0, 1) }}
            </div>
            <div class="autor-detalles">
                <span class="nombre-autor">{{ $noticia->autor }}</span>
                <span class="rol-autor">Redactor Bellreguard Basket</span>
            </div>
        </div>
    </header>

    {{-- Contenedor de Imagen con Efecto --}}
    <div class="articulo-imagen-wrap">
        <img src="{{ asset('noticias/' . $noticia->imagen_noticia) }}" alt="{{ $noticia->titulo }}" onerror="this.onerror=null;this.src='{{ asset('img/club_14.png') }}';">
    </div>

    {{-- Cuerpo del Artículo --}}
    <div class="articulo-cuerpo">
        <div class="texto-contenido">
            {!! nl2br(e($noticia->contenido)) !!}
        </div>
    </div>

    {{-- Footer --}}
    <footer class="articulo-footer">
        <div class="caja-compartir">
            <h3>¿Te ha gustado esta noticia?</h3>
            <p>Ayúdanos a crecer compartiendo la actualidad del club.</p>
            <div class="social-flex">
                <a href="#" class="social-link fb"><i class="fab fa-facebook-f"></i> Facebook</a>
                <a href="#" class="social-link tw"><i class="fab fa-twitter"></i> Twitter</a>
                <a href="#" class="social-link wa"><i class="fab fa-whatsapp"></i> WhatsApp</a>
            </div>
        </div>

        <div class="navegacion-final">
            <a href="{{ route('noticias.index') }}" class="btn-regresar-lista">
                <i class="fas fa-arrow-left"></i> Volver a todas las noticias
            </a>
        </div>
    </footer>
</article>
@endsection
