{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

{{-- CSS específico de la portada --}}
@push('styles')
<x-page-style file="inicio.css" />
@endpush

@use('Carbon\Carbon')

{{-- SEO y metadatos sociales de la página de inicio --}}
@section('title', __('public.home.meta.title'))
@section('meta_description', __('public.home.meta.description'))
@section('canonical', route('basket.inicio'))
@section('og_title', __('public.home.meta.og_title'))
@section('og_description', __('public.home.meta.og_description'))
@section('og_url', route('basket.inicio'))
@section('og_image', asset('img/principal-2.png'))
@section('og_image_alt', __('public.home.meta.og_image_alt'))
@section('twitter_image', asset('img/principal-2.png'))
@section('twitter_image_alt', __('public.home.meta.og_image_alt'))

{{-- Variable auxiliar para evitar conflictos con otros títulos compartidos --}}
@php $titulo = __('public.home.meta.title'); @endphp

{{-- CONTENIDO DE LA PÁGINA --}}
@section('contenido')
    {{-- Imágenes que alimentan el slider principal de portada --}}
    @php
        $heroImages = [
            ['src' => asset('img/principal-1.png'), 'alt' => __('public.home.hero.slide_alt', ['number' => 1])],
            ['src' => asset('img/principal-2.png'), 'alt' => __('public.home.hero.slide_alt', ['number' => 2])],
            ['src' => asset('img/principal-3.png'), 'alt' => __('public.home.hero.slide_alt', ['number' => 3])],
        ];
    @endphp

    {{-- HERO principal con slider de fondo y llamadas a la acción --}}
    <section class="seccion-hero">
        <div class="hero-fondo-slider" aria-hidden="true">
            @foreach ($heroImages as $index => $heroImage)
                <img
                    src="{{ $heroImage['src'] }}"
                    alt=""
                    class="hero-fondo-slide {{ $index === 0 ? 'is-active' : '' }}"
                    data-hero-bg-slide
                />
            @endforeach
            <div class="hero-fondo-overlay"></div>
        </div>

        <div class="hero-texto">
            <h1>Bellreguard CB</h1>
            <p>{{ __('public.home.hero.subtitle') }}</p>
            <div class="botones-hero">
                <a href="{{ route('basket.partidos') }}" class="boton-principal">{{ __('public.home.hero.primary_cta') }}</a>
                <a href="{{ route('basket.equipo') }}" class="boton-secundario">{{ __('public.home.hero.secondary_cta') }}</a>
            </div>
            <div class="hero-indicadores" aria-hidden="true">
                @foreach ($heroImages as $index => $heroImage)
                    <span class="hero-indicador {{ $index === 0 ? 'is-active' : '' }}" data-hero-indicator></span>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Script simple para rotar el fondo del hero --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const backgroundSlides = Array.from(document.querySelectorAll('[data-hero-bg-slide]'));
            const indicators = Array.from(document.querySelectorAll('[data-hero-indicator]'));

            if (backgroundSlides.length < 2) {
                return;
            }

            let activeIndex = 0;

            window.setInterval(function () {
                backgroundSlides[activeIndex]?.classList.remove('is-active');
                indicators[activeIndex]?.classList.remove('is-active');
                activeIndex = (activeIndex + 1) % backgroundSlides.length;
                backgroundSlides[activeIndex]?.classList.add('is-active');
                indicators[activeIndex]?.classList.add('is-active');
            }, 6000);
        });
    </script>

    {{-- CTA para captar jugadores, familias o personas interesadas en el club --}}
    <section class="inicio-club-cta">
        <div class="inicio-club-cta-card">
            <div class="inicio-club-cta-copy">
                <p class="inicio-kicker">{{ __('public.home.join.kicker') }}</p>
                <h2>{{ __('public.home.join.title') }}</h2>
                <p>{{ __('public.home.join.text') }}</p>
                <div class="inicio-club-cta-actions">
                    <a href="{{ route('basket.contacto') }}" class="boton-principal">{{ __('public.home.join.cta') }}</a>
                </div>
            </div>

            <div class="inicio-club-cta-aside">
                <div class="inicio-club-cta-pill">
                    <i class="fa-solid fa-basketball"></i>
                    <span>{{ __('public.home.join.pill') }}</span>
                </div>
                <ul class="inicio-club-cta-list">
                    <li><i class="fa-solid fa-check"></i> {{ __('public.home.join.points.categories') }}</li>
                    <li><i class="fa-solid fa-check"></i> {{ __('public.home.join.points.contact') }}</li>
                    <li><i class="fa-solid fa-check"></i> {{ __('public.home.join.points.updates') }}</li>
                </ul>
            </div>
        </div>
    </section>

    {{-- Bloque de cifras rápidas que resumen la actividad pública del club --}}
    <section class="inicio-club-stats">
        <div class="inicio-club-stats-header">
            <p class="inicio-kicker">{{ __('public.home.stats_header.kicker') }}</p>
            <h2>{{ __('public.home.stats_header.title') }}</h2>
            <p>{{ __('public.home.stats_header.text') }}</p>
        </div>

        <div class="inicio-club-stats-grid">
            @foreach ($clubStats as $stat)
                <article class="inicio-club-stat-card">
                    <strong>{{ $stat['valor'] }}</strong>
                    <h3>{{ $stat['etiqueta'] }}</h3>
                    <p>{{ $stat['texto'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Tarjetas reutilizables para próximo partido y último resultado --}}
    @php
        $cardsPartidos = collect([
            [
                'eyebrow' => __('public.home.matches.next_eyebrow'),
                'titulo' => $proximoPartido ? $proximoPartido->categoria : __('public.home.matches.next_pending_title'),
                'partido' => $proximoPartido,
                'finalizado' => false,
                'cta' => route('basket.partidos'),
                'ctaTexto' => __('public.home.matches.next_cta'),
                'vacioTitulo' => __('public.home.matches.next_empty_title'),
                'vacioTexto' => __('public.home.matches.next_empty_text'),
            ],
            [
                'eyebrow' => __('public.home.matches.last_eyebrow'),
                'titulo' => $ultimoResultado ? $ultimoResultado->categoria : __('public.home.matches.last_pending_title'),
                'partido' => $ultimoResultado,
                'finalizado' => true,
                'cta' => $ultimoResultado ? route('basket.partidos.detalle', $ultimoResultado->id) : route('basket.partidos'),
                'ctaTexto' => __('public.home.matches.last_cta'),
                'vacioTitulo' => __('public.home.matches.last_empty_title'),
                'vacioTexto' => __('public.home.matches.last_empty_text'),
            ],
        ]);
    @endphp

    {{-- Marcadores destacados de la portada --}}
    <section class="inicio-marcadores">
        <div class="inicio-marcadores-header">
            <p class="inicio-kicker">{{ __('public.home.matches.kicker') }}</p>
            <h2>{{ __('public.home.matches.title') }}</h2>
            <p>{{ __('public.home.matches.text') }}</p>
        </div>

        <div class="inicio-marcadores-grid">
            @foreach($cardsPartidos as $card)
                {{-- Datos rápidos del partido para pintar el marcador o el estado vacío --}}
                @php
                    $partidoCard = $card['partido'];
                    $statsLocal = $partidoCard?->estadisticas->firstWhere('equipo_id', $partidoCard->equipo_local_id);
                    $statsVisitante = $partidoCard?->estadisticas->firstWhere('equipo_id', $partidoCard->equipo_visitante_id);
                @endphp

                <article class="inicio-score-card">
                    <div class="inicio-score-top">
                        <span class="inicio-score-eyebrow">{{ $card['eyebrow'] }}</span>
                        <span class="inicio-score-badge {{ $card['finalizado'] ? 'is-result' : 'is-next' }}">
                            {{ $card['finalizado'] ? __('public.home.matches.badges.final') : __('public.home.matches.badges.schedule') }}
                        </span>
                    </div>

                    <h3>{{ $card['titulo'] }}</h3>

                    @if($partidoCard)
                        <div class="inicio-score-matchup">
                            <div class="inicio-score-team">
                                <img src="{{ $partidoCard->equipoLocal->imagen_club_url }}" alt="{{ $partidoCard->equipoLocal->nombre }}">
                                <span>{{ $partidoCard->equipoLocal->nombre }}</span>
                            </div>

                            <div class="inicio-score-center">
                                @if($card['finalizado'] && $statsLocal && $statsVisitante)
                                    <strong>{{ (int) $statsLocal->puntos_totales }} - {{ (int) $statsVisitante->puntos_totales }}</strong>
                                @else
                                    <strong>VS</strong>
                                @endif
                            </div>

                            <div class="inicio-score-team">
                                <img src="{{ $partidoCard->equipoVisitante->imagen_club_url }}" alt="{{ $partidoCard->equipoVisitante->nombre }}">
                                <span>{{ $partidoCard->equipoVisitante->nombre }}</span>
                            </div>
                        </div>

                        <div class="inicio-score-meta">
                            <div>
                                <i class="fa-regular fa-calendar"></i>
                                <span>{{ Carbon::parse($partidoCard->fecha_hora)->translatedFormat('d M Y') }}</span>
                            </div>
                            <div>
                                <i class="fa-regular fa-clock"></i>
                                <span>{{ Carbon::parse($partidoCard->fecha_hora)->format('H:i') }}h</span>
                            </div>
                            <div>
                                <i class="fa-solid fa-location-dot"></i>
                                <span>{{ $partidoCard->lugar }}</span>
                            </div>
                        </div>
                    @else
                        <div class="inicio-score-empty">
                            <h4>{{ $card['vacioTitulo'] }}</h4>
                            <p>{{ $card['vacioTexto'] }}</p>
                        </div>
                    @endif

                    <a href="{{ $card['cta'] }}" class="inicio-score-link">{{ $card['ctaTexto'] }}</a>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Mini clasificación de portada, solo si hay datos calculados --}}
    @if($clasificacionInicio && $clasificacionInicioFilas->isNotEmpty())
        <section class="inicio-mini-clasificacion">
            <div class="inicio-mini-clasificacion-header">
                <div>
                    <p class="inicio-kicker">{{ __('public.home.table.kicker') }}</p>
                    <h2>{{ __('public.home.table.title') }}</h2>
                    <p>
                        {{ $clasificacionInicio->nombre }}
                        @if($clasificacionInicio->temporada)
                            · {{ __('public.home.table.season') }} {{ $clasificacionInicio->temporada }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="inicio-mini-clasificacion-card">
                <table class="inicio-mini-clasificacion-table">
                    <thead>
                        <tr>
                            <th>Pos</th>
                            <th>{{ __('public.home.table.team') }}</th>
                            <th>PJ</th>
                            <th>Pts</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clasificacionInicioFilas as $fila)
                            {{-- Se resalta la fila del club para que destaque en la tabla --}}
                            @php
                                $equipoFila = $fila->equipo;
                                $esFilaClub = (bool) optional($equipoFila)->es_local
                                    || str_contains(strtolower((string) optional($equipoFila)->nombre), 'bellreguard');
                            @endphp
                            <tr class="{{ $esFilaClub ? 'is-club-row' : '' }}">
                                <td><strong>{{ $fila->posicion_home }}</strong></td>
                                <td>
                                    <div class="inicio-mini-clasificacion-team">
                                        <img src="{{ optional($equipoFila)->imagen_club_url ?? asset('img/club_14.png') }}" alt="{{ optional($equipoFila)->nombre ?? __('public.home.table.team_fallback') }}">
                                        <span>{{ optional($equipoFila)->nombre ?? __('public.home.table.team_fallback') }}</span>
                                    </div>
                                </td>
                                <td>{{ $fila->pj }}</td>
                                <td><strong>{{ $fila->pts }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="inicio-cta-wrap">
                <a href="{{ route('basket.clasificacion') }}" class="inicio-mini-clasificacion-link">{{ __('public.home.table.cta') }}</a>
            </div>
        </section>
    @endif

    {{-- Galerías destacadas para dar visibilidad a la parte visual del club --}}
    @if($galeriasDestacadas->isNotEmpty())
        <section class="inicio-galeria-destacada">
            <div class="inicio-galeria-header">
                <p class="inicio-kicker">{{ __('public.home.gallery.kicker') }}</p>
                <h2>{{ __('public.home.gallery.title') }}</h2>
                <p>{{ __('public.home.gallery.text') }}</p>
            </div>

            <div class="inicio-galeria-grid">
                @foreach($galeriasDestacadas as $index => $galeria)
                    <article class="inicio-galeria-card {{ $index === 0 ? 'is-featured' : '' }}">
                        <a href="{{ route('basket.galeria.detalle', $galeria->slug) }}" class="inicio-galeria-media">
                            @if($galeria->portada)
                                <img
                                    src="{{ $galeria->portada?->image_url ?? asset('img/club_14.png') }}"
                                    alt="{{ $galeria->titulo }}"
                                    onerror="this.onerror=null;this.src='{{ asset('img/club_14.png') }}';"
                                >
                            @else
                                <img
                                    src="{{ asset('img/club_14.png') }}"
                                    alt="{{ __('public.home.gallery.no_cover_alt') }}"
                                >
                            @endif
                            <span class="inicio-galeria-badge">{{ __('public.home.gallery.photos_count', ['count' => $galeria->imagenes_count]) }}</span>
                        </a>

                        <div class="inicio-galeria-copy">
                            <span class="inicio-galeria-type">{{ \App\Models\Galeria::translatedTypeLabel($galeria->tipo, 'public.gallery.types') }}</span>
                            <h3>{{ $galeria->titulo }}</h3>
                            <p>{{ \Illuminate\Support\Str::limit($galeria->descripcion ?: __('public.home.gallery.fallback_description'), $index === 0 ? 150 : 110) }}</p>
                            <a href="{{ route('basket.galeria.detalle', $galeria->slug) }}" class="inicio-galeria-link">{{ __('public.home.gallery.card_cta') }}</a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="inicio-cta-wrap">
                <a href="{{ route('basket.galeria') }}" class="inicio-cta-link">{{ __('public.home.gallery.cta') }}</a>
            </div>
        </section>
    @endif

    {{-- Bloque de noticias: una principal y varias secundarias --}}
    <section class="seccion-noticias">
        @php
            $noticiaPrincipal = $noticias->first();
            $noticiasSecundarias = $noticias->skip(1)->take(4);
        @endphp

        <h2>{{ __('public.home.news.title') }}</h2>
        <p class="subtitulo">{{ __('public.home.news.subtitle') }}</p>
        
        @if($noticiaPrincipal)
            <div class="noticias-destacadas-layout">
                <article class="noticia-principal-home">
                    <a href="{{ route('noticias.detalle', $noticiaPrincipal->slug) }}" class="noticia-principal-media">
                        <img src="{{ $noticiaPrincipal->imagen_noticia_url }}" alt="{{ $noticiaPrincipal->titulo }}" />
                    </a>

                    <div class="noticia-principal-copy">
                        <div class="noticia-principal-meta">
                            <span class="fecha">{{ $noticiaPrincipal->created_at->translatedFormat('d M, Y') }}</span>
                            @if($noticiaPrincipal->autor)
                                <span class="noticia-autor">{{ $noticiaPrincipal->autor }}</span>
                            @endif
                        </div>

                        <h3>{{ $noticiaPrincipal->titulo }}</h3>
                        <p>{{ Str::limit($noticiaPrincipal->subtitulo ?: strip_tags($noticiaPrincipal->contenido), 180) }}</p>
                        <a href="{{ route('noticias.detalle', $noticiaPrincipal->slug) }}" class="noticia-principal-link">{{ __('public.home.news.featured_cta') }}</a>
                    </div>
                </article>

                @if($noticiasSecundarias->isNotEmpty())
                    <div class="noticias-secundarias-grid">
                        @foreach ($noticiasSecundarias as $noticia)
                            <article class="tarjeta-noticia tarjeta-noticia-secundaria">
                                <a href="{{ route('noticias.detalle', $noticia->slug) }}" class="contenedor-img">
                                    <img src="{{ $noticia->imagen_noticia_url }}" alt="{{ $noticia->titulo }}" />
                                </a>
                                
                                <div class="info-tarjeta">
                                    <span class="fecha">{{ $noticia->created_at->translatedFormat('d M, Y') }}</span>
                                    <h3>{{ $noticia->titulo }}</h3>
                                    <p>{{ Str::limit($noticia->subtitulo ?: strip_tags($noticia->contenido), 95) }}</p>
                                    <a href="{{ route('noticias.detalle', $noticia->slug) }}">{{ __('public.home.news.more') }}</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        @else
            <div class="estado-vacio">
                <div class="estado-vacio-icono">
                    <i class="fas fa-newspaper"></i>
                </div>
                <h3>{{ __('public.home.news.empty_title') }}</h3>
                <p>{{ __('public.home.news.empty_text') }}</p>
                <a href="{{ route('basket.contacto') }}" class="estado-vacio-accion">{{ __('public.home.news.empty_cta') }}</a>
            </div>
        @endif

        @if($noticias->isNotEmpty())
            <div class="inicio-cta-wrap">
                <a href="{{ route('basket.noticias') }}" class="inicio-cta-link">{{ __('public.home.news.cta') }}</a>
            </div>
        @endif
    </section>

    {{-- Patrocinadores o colaboradores visibles en la portada --}}
    <section class="patrocinadores">
        @php
            $patrocinadoresHome = collect([
                'Patrocinador 1',
                'Patrocinador 2',
                'Patrocinador 3',
                'Patrocinador 4',
                'Patrocinador 5',
                'Patrocinador 6',
            ]);
        @endphp

        <div class="patrocinadores-header">
            <p class="inicio-kicker">{{ __('public.home.sponsors.kicker') }}</p>
            <h2>{{ __('public.home.sponsors.title') }}</h2>
            <p class="subtitulo">{{ __('public.home.sponsors.subtitle') }}</p>
        </div>

        <div class="rejilla-patrocinadores">
            @foreach ($patrocinadoresHome as $patrocinador)
                <article class="patrocinador">
                    <img src="{{ asset('img/club_14.png') }}" alt="{{ $patrocinador }}">
                </article>
            @endforeach
        </div>

        <div class="inicio-cta-wrap">
            <a href="{{ route('basket.contacto') }}" class="inicio-cta-link">{{ __('public.home.sponsors.cta') }}</a>
        </div>
    </section>

@endsection
