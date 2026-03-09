{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/paginas/galeria.css') }}" />
@endpush

{{-- TÍTULO DE LA PESTAÑA --}}
@section('title', 'Inicio - Bellreguard Club de Basket')

{{-- CONTENIDO DE LA PÁGINA --}}
@section('contenido')

<section class="seccion-galeria-header">
    <div class="header-contenido">
        <h1>Galería Bellreguard CB</h1>
        <p>Revive los mejores momentos de nuestro equipo</p>
        <div class="stats-galeria">
            <span><i class="fas fa-camera"></i> 500+ Fotos</span>
            <span><i class="fas fa-calendar-alt"></i> Temporada 2023-24</span>
        </div>
    </div>
</section>

<section class="contenedor-galeria">
    <div class="filtros-galeria">
        <button class="btn-filtro activo">Todos</button>
        <button class="btn-filtro"><i class="fas fa-basketball-ball"></i> Partidos</button>
        <button class="btn-filtro"><i class="fas fa-dumbbell"></i> Entrenamientos</button>
        <button class="btn-filtro"><i class="fas fa-users"></i> Equipo</button>
        <button class="btn-filtro"><i class="fas fa-star"></i> Eventos</button>
    </div>

    <div class="rejilla-albumes">
        <div class="tarjeta-album">
            <div class="imagen-album">
                <img src="img/final.jpeg" alt="Final Liga Regional">
                <span class="badge-fotos">25 fotos</span>
            </div>
            <div class="info-album">
                <h3>Final Liga Regional</h3>
                <p>Victoria histórica contra Valencia BC. Momentos épicos del partido más importante de la temporada.</p>
                <div class="footer-album">
                    <span class="fecha-album">15 Marzo 2024</span>
                    <a href="#" class="btn-ver-album">Ver álbum</a>
                </div>
            </div>
        </div>

        <div class="tarjeta-album">
            <div class="imagen-album">
                <img src="img/final.jpeg" alt="Entrenamiento Intensivo">
                <span class="badge-fotos">15 fotos</span>
            </div>
            <div class="info-album">
                <h3>Entrenamiento Intensivo</h3>
                <p>Sesión de preparación para la final. Ejercicios tácticos y físicos en el pabellón municipal.</p>
                <div class="footer-album">
                    <span class="fecha-album">10 Marzo 2024</span>
                    <a href="#" class="btn-ver-album">Ver álbum</a>
                </div>
            </div>
        </div>

        <div class="tarjeta-album">
            <div class="imagen-album">
                <img src="img/final.jpeg" alt="Celebración Campeones">
                <span class="badge-fotos">32 fotos</span>
            </div>
            <div class="info-album">
                <h3>Celebración Campeones</h3>
                <p>La celebración del título de liga regional. Momentos únicos con aficionados y jugadores.</p>
                <div class="footer-album">
                    <span class="fecha-album">20 Marzo 2024</span>
                    <a href="#" class="btn-ver-album">Ver álbum</a>
                </div>
            </div>
        </div>
    </div>

    <div class="contenedor-cargar-mas">
        <button class="btn-cargar-mas"><i class="fas fa-plus"></i> Cargar más álbumes</button>
    </div>
</section>

@endsection
