@extends('layouts.admin')

@section('contenido_admin')
@php
    $adminCards = [
        [
            'titulo' => 'Usuarios',
            'texto' => 'Cuentas internas del panel, accesos y roles de gestión.',
            'ruta' => route('usuarios.index'),
            'imagen' => asset('img/usuarios.png'),
            'icono' => 'fas fa-user-shield',
            'visible' => auth()->user()?->role === \App\Models\User::ROLE_ADMIN,
        ],
        [
            'titulo' => 'Jugadores',
            'texto' => 'Altas, fichas, dorsales y asignaciones de equipo.',
            'ruta' => route('jugadores.index'),
            'imagen' => asset('img/jugadores.png'),
            'icono' => 'fas fa-users',
            'visible' => true,
        ],
        [
            'titulo' => 'Equipos',
            'texto' => 'Categorías, escudos y estructura deportiva del club.',
            'ruta' => route('equipos.index'),
            'imagen' => asset('img/equipo.png'),
            'icono' => 'fas fa-tshirt',
            'visible' => true,
        ],
        [
            'titulo' => 'Noticias',
            'texto' => 'Contenido editorial, titulares y publicaciones.',
            'ruta' => route('noticias.index'),
            'imagen' => asset('img/noticias.png'),
            'icono' => 'fas fa-newspaper',
            'visible' => true,
        ],
        [
            'titulo' => 'Merchandising',
            'texto' => 'Productos, tallas, precios e imágenes de tienda.',
            'ruta' => route('merchandising.index'),
            'imagen' => asset('img/merchan.png'),
            'icono' => 'fas fa-shopping-bag',
            'visible' => true,
        ],
        [
            'titulo' => 'Partidos',
            'texto' => 'Calendario, cruces, pabellones y categorías.',
            'ruta' => route('partidos.index'),
            'imagen' => asset('img/partidos.png'),
            'icono' => 'fas fa-calendar-alt',
            'visible' => true,
        ],
        [
            'titulo' => 'Clasificaciones',
            'texto' => 'Ligas, jornadas y tabla de posiciones por temporada.',
            'ruta' => route('clasificaciones.index'),
            'imagen' => asset('img/clasificacion.png'),
            'icono' => 'fas fa-trophy',
            'visible' => true,
        ],
        [
            'titulo' => 'Galerías',
            'texto' => 'Álbumes, portadas e imágenes del club.',
            'ruta' => route('galerias.index'),
            'imagen' => asset('img/galeria.png'),
            'icono' => 'fas fa-images',
            'visible' => true,
        ],
    ];
@endphp

<header class="dashboard-admin-hero">
    <div>
        <p class="dashboard-admin-kicker">Panel de administración</p>
        <h1>Accesos rápidos del club</h1>
        <p class="dashboard-admin-copy">Cada tarjeta puede llevar la imagen que tú quieras. Yo te dejo una base visual bonita y luego cambias esas rutas por las definitivas.</p>
    </div>

    <div class="dashboard-admin-badge">
        <i class="fas fa-basketball-ball"></i>
        <span>Bellreguard CB</span>
    </div>
</header>

<section class="dashboard-admin-grid">
    @foreach($adminCards as $card)
        @continue(!($card['visible'] ?? true))

        <a href="{{ $card['ruta'] }}" class="dashboard-admin-card">
            <div class="dashboard-admin-card-media">
                <img src="{{ $card['imagen'] }}" alt="{{ $card['titulo'] }}">
                <span class="dashboard-admin-card-icon">
                    <i class="{{ $card['icono'] }}"></i>
                </span>
            </div>

            <div class="dashboard-admin-card-body">
                <h3>{{ $card['titulo'] }}</h3>
                <p>{{ $card['texto'] }}</p>
            </div>

            <div class="dashboard-admin-card-footer">
                <span>Entrar</span>
                <i class="fas fa-arrow-right"></i>
            </div>
        </a>
    @endforeach
</section>
@endsection
