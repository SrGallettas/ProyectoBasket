{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

{{-- CSS específico de la portada --}}
@push('styles')
<link rel="stylesheet" href="{{ asset('css/paginas/inicio.css') }}" />
@endpush

@use('Carbon\Carbon')

{{-- SEO y metadatos sociales de la página de inicio --}}
@section('title', 'Inicio - Bellreguard Club de Basket')
@section('meta_description', 'Descubre el Bellreguard Club de Basket: próximos partidos, último resultado, noticias del club y toda la actualidad del baloncesto en Bellreguard.')
@section('canonical', route('basket.inicio'))
@section('og_title', 'Bellreguard Club de Basket | Partidos, noticias y actualidad')
@section('og_description', 'Consulta los próximos partidos, el último resultado y las noticias del Bellreguard Club de Basket desde la portada oficial del club.')
@section('og_url', route('basket.inicio'))
@section('og_image', asset('img/principal-2.png'))
@section('og_image_alt', 'Jugadores y ambiente del Bellreguard Club de Basket')
@section('twitter_image', asset('img/principal-2.png'))
@section('twitter_image_alt', 'Jugadores y ambiente del Bellreguard Club de Basket')

{{-- Variable auxiliar para evitar conflictos con otros títulos compartidos --}}
@php $titulo = 'Inicio - Bellreguard Club de Basket'; @endphp

{{-- CONTENIDO DE LA PÁGINA --}}
@section('contenido')
    {{-- Imágenes que alimentan el slider principal de portada --}}
    @php
        $heroImages = [
            ['src' => asset('img/principal-1.png'), 'alt' => 'Imagen principal 1 Bellreguard'],
            ['src' => asset('img/principal-2.png'), 'alt' => 'Imagen principal 2 Bellreguard'],
            ['src' => asset('img/principal-3.png'), 'alt' => 'Imagen principal 3 Bellreguard'],
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
                <p class="inicio-kicker">Únete al club</p>
                <h2>¿Quieres jugar con nosotros esta temporada?</h2>
                <p>
                    Si buscas equipo, quieres traer a tu hijo o simplemente conocer mejor el club,
                    te ayudamos a encontrar la categoría adecuada y resolver cualquier duda.
                </p>
                <div class="inicio-club-cta-actions">
                    <a href="{{ route('basket.contacto') }}" class="boton-principal">Contactar con el club</a>
                </div>
            </div>

            <div class="inicio-club-cta-aside">
                <div class="inicio-club-cta-pill">
                    <i class="fa-solid fa-basketball"></i>
                    <span>Pruebas, cantera y equipos del club</span>
                </div>
                <ul class="inicio-club-cta-list">
                    <li><i class="fa-solid fa-check"></i> Información clara sobre categorías y equipos</li>
                    <li><i class="fa-solid fa-check"></i> Contacto directo con el club</li>
                    <li><i class="fa-solid fa-check"></i> Acceso rápido a próximos partidos y actualidad</li>
                </ul>
            </div>
        </div>
    </section>

    {{-- Bloque de cifras rápidas que resumen la actividad pública del club --}}
    <section class="inicio-club-stats">
        <div class="inicio-club-stats-header">
            <p class="inicio-kicker">Sobre el club</p>
            <h2>Un proyecto deportivo con actividad real</h2>
            <p>Estas cifras salen del contenido público que ya tiene el club en la web y ayudan a mostrar que la portada está viva.</p>
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

    {{-- Marcadores destacados de la portada --}}
    <section class="inicio-marcadores">
        <div class="inicio-marcadores-header">
            <p class="inicio-kicker">Actualidad deportiva</p>
            <h2>Lo más importante de la semana</h2>
            <p>Acceso directo al siguiente compromiso del club y al último marcador registrado.</p>
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

    {{-- Mini clasificación de portada, solo si hay datos calculados --}}
    @if($clasificacionInicio && $clasificacionInicioFilas->isNotEmpty())
        <section class="inicio-mini-clasificacion">
            <div class="inicio-mini-clasificacion-header">
                <div>
                    <p class="inicio-kicker">Clasificación</p>
                    <h2>Así va la competición</h2>
                    <p>
                        {{ $clasificacionInicio->nombre }}
                        @if($clasificacionInicio->temporada)
                            · Temporada {{ $clasificacionInicio->temporada }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="inicio-mini-clasificacion-card">
                <table class="inicio-mini-clasificacion-table">
                    <thead>
                        <tr>
                            <th>Pos</th>
                            <th>Equipo</th>
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
                                        <img src="{{ optional($equipoFila)->imagen_club_url ?? asset('img/club_14.png') }}" alt="{{ optional($equipoFila)->nombre ?? 'Equipo' }}">
                                        <span>{{ optional($equipoFila)->nombre ?? 'Equipo' }}</span>
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
                <a href="{{ route('basket.clasificacion') }}" class="inicio-mini-clasificacion-link">Ver clasificación completa</a>
            </div>
        </section>
    @endif

    {{-- Galerías destacadas para dar visibilidad a la parte visual del club --}}
    @if($galeriasDestacadas->isNotEmpty())
        <section class="inicio-galeria-destacada">
            <div class="inicio-galeria-header">
                <p class="inicio-kicker">Vida del club</p>
                <h2>Imágenes que enseñan el ambiente Bellreguard</h2>
                <p>Una selección de álbumes recientes para que la portada también muestre lo que pasa dentro y fuera de la pista.</p>
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
                                    alt="Galería sin portada"
                                >
                            @endif
                            <span class="inicio-galeria-badge">{{ $galeria->imagenes_count }} fotos</span>
                        </a>

                        <div class="inicio-galeria-copy">
                            <span class="inicio-galeria-type">{{ \App\Models\Galeria::TIPOS[$galeria->tipo] ?? ucfirst($galeria->tipo) }}</span>
                            <h3>{{ $galeria->titulo }}</h3>
                            <p>{{ \Illuminate\Support\Str::limit($galeria->descripcion ?: 'Revive este álbum destacado del Bellreguard Club de Basket.', $index === 0 ? 150 : 110) }}</p>
                            <a href="{{ route('basket.galeria.detalle', $galeria->slug) }}" class="inicio-galeria-link">Ver álbum</a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="inicio-cta-wrap">
                <a href="{{ route('basket.galeria') }}" class="inicio-cta-link">Ver toda la galería</a>
            </div>
        </section>
    @endif

    {{-- Bloque de noticias: una principal y varias secundarias --}}
    <section class="seccion-noticias">
        @php
            $noticiaPrincipal = $noticias->first();
            $noticiasSecundarias = $noticias->skip(1)->take(4);
        @endphp

        <h2>Últimas Noticias</h2>
        <p class="subtitulo">Mantente al día con todas las novedades del club</p>
        
        @if($noticiaPrincipal)
            <div class="noticias-destacadas-layout">
                <article class="noticia-principal-home">
                    <a href="{{ route('noticias.detalle', $noticiaPrincipal->slug) }}" class="noticia-principal-media">
                        <img src="{{ $noticiaPrincipal->imagen_noticia_url }}" alt="{{ $noticiaPrincipal->titulo }}" />
                    </a>

                    <div class="noticia-principal-copy">
                        <div class="noticia-principal-meta">
                            <span class="fecha">{{ $noticiaPrincipal->created_at->format('d M, Y') }}</span>
                            @if($noticiaPrincipal->autor)
                                <span class="noticia-autor">{{ $noticiaPrincipal->autor }}</span>
                            @endif
                        </div>

                        <h3>{{ $noticiaPrincipal->titulo }}</h3>
                        <p>{{ Str::limit($noticiaPrincipal->subtitulo ?: strip_tags($noticiaPrincipal->contenido), 180) }}</p>
                        <a href="{{ route('noticias.detalle', $noticiaPrincipal->slug) }}" class="noticia-principal-link">Leer noticia destacada</a>
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
                                    <span class="fecha">{{ $noticia->created_at->format('d M, Y') }}</span>
                                    <h3>{{ $noticia->titulo }}</h3>
                                    <p>{{ Str::limit($noticia->subtitulo ?: strip_tags($noticia->contenido), 95) }}</p>
                                    <a href="{{ route('noticias.detalle', $noticia->slug) }}">Leer más →</a>
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
                <h3>Sin noticias publicadas por ahora</h3>
                <p>En cuanto tengamos novedades del club, las veras aqui en primer plano.</p>
                <a href="{{ route('basket.contacto') }}" class="estado-vacio-accion">Contactar con el club</a>
            </div>
        @endif

        @if($noticias->isNotEmpty())
            <div class="inicio-cta-wrap">
                <a href="{{ route('basket.noticias') }}" class="inicio-cta-link">Ver todas las noticias</a>
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
            <p class="inicio-kicker">Red de apoyo</p>
            <h2>Nuestros Patrocinadores</h2>
            <p class="subtitulo">Empresas y colaboradores que ayudan a sostener el baloncesto del club y su actividad durante la temporada.</p>
        </div>

        <div class="rejilla-patrocinadores">
            @foreach ($patrocinadoresHome as $patrocinador)
                <article class="patrocinador">
                    <img src="{{ asset('img/club_14.png') }}" alt="{{ $patrocinador }}">
                </article>
            @endforeach
        </div>

        <div class="inicio-cta-wrap">
            <a href="{{ route('basket.contacto') }}" class="inicio-cta-link">Quiero colaborar con el club</a>
        </div>
    </section>

@endsection
