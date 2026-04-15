{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/paginas/inicio.css') }}" />
@endpush

@use('Carbon\Carbon')

{{-- ESTO PASA PORQUE TENEMOS LA VARIABLE TITULO DE NOTICIAS, Y COMO HAY DOS VARIABLES QUE SE LLAMAN IGUAL, NOS TOCA CAMBIARLO --}}
@php $titulo = 'Inicio - Bellreguard Club de Basket'; @endphp

{{-- CONTENIDO DE LA PÁGINA --}}
@section('contenido')
    @php
        $heroImages = [
            ['src' => asset('img/principal-1.png'), 'alt' => 'Imagen principal 1 Bellreguard'],
            ['src' => asset('img/principal-2.png'), 'alt' => 'Imagen principal 2 Bellreguard'],
            ['src' => asset('img/principal-3.png'), 'alt' => 'Imagen principal 3 Bellreguard'],
        ];
    @endphp

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
            <h1>Bellreguard Club de Basket</h1>
            <p>Pasión, dedicación y excelencia en cada partido. Únete a nuestra familia basketbolística.</p>
            <div class="botones-hero">
                <a href="{{ route('basket.partidos') }}" class="boton-principal">Ver Próximos Partidos</a>
                <a href="{{ route('basket.equipo') }}" class="boton-secundario">Conocer el Equipo</a>
            </div>
            <div class="hero-indicadores" aria-hidden="true">
                @foreach ($heroImages as $index => $heroImage)
                    <span class="hero-indicador {{ $index === 0 ? 'is-active' : '' }}" data-hero-indicator></span>
                @endforeach
            </div>
        </div>
    </section>

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

    @php
        $cardsPartidos = collect([
            [
                'eyebrow' => 'Próximo partido',
                'titulo' => $proximoPartido ? $proximoPartido->categoria : 'Calendario en preparación',
                'partido' => $proximoPartido,
                'finalizado' => false,
                'cta' => route('basket.partidos'),
                'ctaTexto' => 'Ver calendario completo',
                'vacioTitulo' => 'Todavía no hay próximos partidos publicados',
                'vacioTexto' => 'En cuanto cerremos el siguiente encuentro del club, aparecerá aquí en portada.',
            ],
            [
                'eyebrow' => 'Último resultado',
                'titulo' => $ultimoResultado ? $ultimoResultado->categoria : 'Resultados pendientes',
                'partido' => $ultimoResultado,
                'finalizado' => true,
                'cta' => $ultimoResultado ? route('basket.partidos.detalle', $ultimoResultado->id) : route('basket.partidos'),
                'ctaTexto' => 'Ver estadísticas del partido',
                'vacioTitulo' => 'Aún no hay resultados cerrados',
                'vacioTexto' => 'Cuando el club registre un marcador final, lo mostraremos aquí directamente.',
            ],
        ]);
    @endphp

    <section class="inicio-marcadores">
        <div class="inicio-marcadores-header">
            <p class="inicio-kicker">Actualidad deportiva</p>
            <h2>Lo más importante de la semana</h2>
            <p>Acceso directo al siguiente compromiso del club y al último marcador registrado.</p>
        </div>

        <div class="inicio-marcadores-grid">
            @foreach($cardsPartidos as $card)
                @php
                    $partidoCard = $card['partido'];
                    $statsLocal = $partidoCard?->estadisticas->firstWhere('equipo_id', $partidoCard->equipo_local_id);
                    $statsVisitante = $partidoCard?->estadisticas->firstWhere('equipo_id', $partidoCard->equipo_visitante_id);
                @endphp

                <article class="inicio-score-card">
                    <div class="inicio-score-top">
                        <span class="inicio-score-eyebrow">{{ $card['eyebrow'] }}</span>
                        <span class="inicio-score-badge {{ $card['finalizado'] ? 'is-result' : 'is-next' }}">
                            {{ $card['finalizado'] ? 'Final' : 'Agenda' }}
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
