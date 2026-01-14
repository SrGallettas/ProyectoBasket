{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

{{-- TÍTULO DE LA PESTAÑA --}}
@section('title', 'Inicio - Bellreguard Club de Basket')

{{-- CONTENIDO DE LA PÁGINA --}}
@section('contenido')

    <section class="seccion-hero">
        <div class="hero-texto">
            <h1>Bellreguard Club de Basket</h1>
            <p>Pasión, dedicación y excelencia en cada partido. Únete a nuestra familia basketbolística.</p>
            <div class="botones-hero">
                <button class="boton-principal">Ver Próximos Partidos</button>
                <button class="boton-secundario">Conocer el Equipo</button>
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
            @for ($i = 1; $i <= 4; $i++)
            <div class="tarjeta-noticia">
                <img src="{{ asset('img/intro.jpeg') }}" />
                <div class="info-tarjeta">
                    <span class="fecha">15 Noviembre 2024</span>
                    <h3>Victoria Contundente en Casa</h3>
                    <p>El equipo logró una importante victoria por 85–72 ante el equipo visitante.</p>
                    <a href="#">Leer más →</a>
                </div>
            </div>
            @endfor
        </div>
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