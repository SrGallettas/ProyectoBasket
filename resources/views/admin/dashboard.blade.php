@extends('layouts.admin')

@section('contenido_admin')
@section('title', __('admin.dashboard.meta.title'))
@php
    $adminUser = auth()->user();
    $adminCards = [
        [
            'titulo' => __('admin.dashboard.cards.users.title'),
            'texto' => __('admin.dashboard.cards.users.text'),
            'ruta' => route('usuarios.index'),
            'imagen' => asset('img/usuarios.png'),
            'icono' => 'fas fa-user-shield',
            'visible' => $adminUser?->role === \App\Models\User::ROLE_ADMIN,
        ],
        [
            'titulo' => __('admin.dashboard.cards.players.title'),
            'texto' => __('admin.dashboard.cards.players.text'),
            'ruta' => route('jugadores.index'),
            'imagen' => asset('img/jugadores.png'),
            'icono' => 'fas fa-users',
            'visible' => $adminUser?->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COACH]),
        ],
        [
            'titulo' => __('admin.dashboard.cards.teams.title'),
            'texto' => __('admin.dashboard.cards.teams.text'),
            'ruta' => route('equipos.index'),
            'imagen' => asset('img/equipo.png'),
            'icono' => 'fas fa-tshirt',
            'visible' => $adminUser?->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COACH]),
        ],
        [
            'titulo' => __('admin.dashboard.cards.news.title'),
            'texto' => __('admin.dashboard.cards.news.text'),
            'ruta' => route('noticias.index'),
            'imagen' => asset('img/noticias.png'),
            'icono' => 'fas fa-newspaper',
            'visible' => $adminUser?->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_EDITOR]),
        ],
        [
            'titulo' => __('admin.dashboard.cards.store.title'),
            'texto' => __('admin.dashboard.cards.store.text'),
            'ruta' => route('merchandising.index'),
            'imagen' => asset('img/merchan.png'),
            'icono' => 'fas fa-shopping-bag',
            'visible' => $adminUser?->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_EDITOR]),
        ],
        [
            'titulo' => __('admin.dashboard.cards.matches.title'),
            'texto' => __('admin.dashboard.cards.matches.text'),
            'ruta' => route('partidos.index'),
            'imagen' => asset('img/partidos.png'),
            'icono' => 'fas fa-calendar-alt',
            'visible' => $adminUser?->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COACH]),
        ],
        [
            'titulo' => __('admin.dashboard.cards.standings.title'),
            'texto' => __('admin.dashboard.cards.standings.text'),
            'ruta' => route('clasificaciones.index'),
            'imagen' => asset('img/clasificacion.png'),
            'icono' => 'fas fa-trophy',
            'visible' => $adminUser?->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COACH]),
        ],
        [
            'titulo' => __('admin.dashboard.cards.galleries.title'),
            'texto' => __('admin.dashboard.cards.galleries.text'),
            'ruta' => route('galerias.index'),
            'imagen' => asset('img/galeria.png'),
            'icono' => 'fas fa-images',
            'visible' => $adminUser?->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_EDITOR]),
        ],
    ];
@endphp

<header class="dashboard-admin-hero">
    <div>
        <p class="dashboard-admin-kicker">{{ __('admin.dashboard.kicker') }}</p>
        <h1>{{ __('admin.dashboard.title') }}</h1>
        <p class="dashboard-admin-copy">{{ __('admin.dashboard.copy') }}</p>
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
                <span>{{ __('admin.dashboard.enter') }}</span>
                <i class="fas fa-arrow-right"></i>
            </div>
        </a>
    @endforeach
</section>
@endsection
