{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/paginas/inicio.css') }}" />
@endpush

{{-- ESTO PASA PORQUE TENEMOS LA VARIABLE TITULO DE NOTICIAS, Y COMO HAY DOS VARIABLES QUE SE LLAMAN IGUAL, NOS TOCA CAMBIARLO --}}
@php $titulo = 'Inicio - Bellreguard Club de Basket'; @endphp

{{-- CONTENIDO DE LA PÁGINA --}}
@section('contenido')

    <section class="seccion-hero">
        <div class="hero-texto">
            <h1>Bellreguard Club de Basket</h1>
            <p>Pasión, dedicación y excelencia en cada partido. Únete a nuestra familia basketbolística.</p>
            <div class="botones-hero">
                <a href="{{ route('basket.partidos') }}" class="boton-principal">Ver Próximos Partidos</a>
                <a href="{{ route('basket.equipo') }}" class="boton-secundario">Conocer el Equipo</a>
            </div>
        </div>
        <div class="hero-imagen">
            <img src="{{ asset('img/basket.png') }}" alt="Equipo celebrando" />
        </div>
    </section>

    <section class="seccion-noticias">
        <h2>Últimas Noticias</h2>
        <p class="subtitulo">Mantente al día con todas las novedades del club</p>
        
        <div class="rejilla-noticias">
            @forelse ($noticias as $noticia)
                <div class="tarjeta-noticia">
                    <div class="contenedor-img">
                        <img src="{{ $noticia->imagen_noticia_url }}" alt="{{ $noticia->titulo }}" />
                    </div>
                    
                    <div class="info-tarjeta">
                        <span class="fecha">{{ $noticia->created_at->format('d M, Y') }}</span>
                        <h3>{{ $noticia->titulo }}</h3>
                        <p>{{ Str::limit($noticia->subtitulo, 80) }}</p>
                        <a href="{{ route('noticias.detalle', $noticia->slug) }}">Leer más →</a>
                    </div>
                </div>
            @empty
                <div class="estado-vacio">
                    <div class="estado-vacio-icono">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <h3>Sin noticias publicadas por ahora</h3>
                    <p>En cuanto tengamos novedades del club, las veras aqui en primer plano.</p>
                    <a href="{{ route('basket.contacto') }}" class="estado-vacio-accion">Contactar con el club</a>
                </div>
            @endforelse
        </div>

        @if($noticias->isNotEmpty())
            <div class="inicio-cta-wrap">
                <a href="{{ route('basket.noticias') }}" class="inicio-cta-link">Ver todas las noticias</a>
            </div>
        @endif
    </section>


    <section class="patrocinadores">
        <h2>Nuestros Patrocinadores</h2>
        <div class="rejilla-patrocinadores">
            {{-- Bucle de Blade para mostrar 6 patrocinadores --}}
            @for ($i = 1; $i <= 6; $i++)
                <div class="patrocinador">
                    <img src="{{ asset('img/logo_nike.png') }}" alt="Patrocinador {{ $i }}">
                </div>
            @endfor
        </div>
    </section>

@endsection
