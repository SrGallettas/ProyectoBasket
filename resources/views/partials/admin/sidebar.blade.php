{{-- Menú lateral principal del panel de administración. --}}
<aside class="sidebar-admin" id="admin-sidebar">
    {{-- Perfil superior: escudo, nombre del panel, usuario y rol actual. --}}
    <div class="admin-perfil">
        <img src="{{ asset('img/club_14.png') }}" alt="{{ __('admin.layout.panel') }}">
        <div class="admin-info">
            <h4>{{ __('admin.layout.panel') }}</h4>
            <span>{{ $adminUser?->name }} · {{ \App\Models\User::ROLES[$adminUser?->role] ?? __('admin.layout.user') }}</span>
        </div>
    </div>

    {{-- Navegación interna del panel. --}}
    <nav class="menu-admin">
        {{-- Inicio del panel: visible para cualquier usuario autenticado. --}}
        <a href="{{ route('dashboard') }}" class="item-admin {{ request()->routeIs('dashboard') ? 'activo' : '' }}">
            <i class="fas fa-home"></i><span>{{ __('admin.menu.home') }}</span>
        </a>

        {{-- Gestión deportiva: visible para admin y coach. --}}
        @if($adminUser?->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_COACH]))
            <a href="{{ route('equipos.index') }}" class="item-admin {{ request()->is('admin/equipos*') ? 'activo' : '' }}">
                <i class="fas fa-tshirt"></i><span>{{ __('admin.menu.teams') }}</span>
            </a>

            <a href="{{ route('jugadores.index') }}" class="item-admin {{ request()->is('admin/jugadores*') ? 'activo' : '' }}">
                <i class="fas fa-users"></i><span>{{ __('admin.menu.players') }}</span>
            </a>

            <a href="{{ route('partidos.index') }}" class="item-admin {{ request()->is('admin/partidos*') ? 'activo' : '' }}">
                <i class="fas fa-calendar-alt"></i><span>{{ __('admin.menu.matches') }}</span>
            </a>

            <a href="{{ route('clasificaciones.index') }}" class="item-admin {{ request()->is('admin/clasificaciones*') ? 'activo' : '' }}">
                <i class="fas fa-trophy"></i><span>{{ __('admin.menu.standings') }}</span>
            </a>
        @endif

        {{-- Usuarios: solo visible para administradores. --}}
        @if($adminUser?->isAdmin())
            <a href="{{ route('usuarios.index') }}" class="item-admin {{ request()->is('admin/usuarios*') ? 'activo' : '' }}">
                <i class="fas fa-user-shield"></i><span>{{ __('admin.menu.users') }}</span>
            </a>
        @endif

        {{-- Contenido editorial y tienda: visible para admin y editor. --}}
        @if($adminUser?->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_EDITOR]))
            <a href="{{ route('noticias.index') }}" class="item-admin {{ request()->is('admin/noticias*') ? 'activo' : '' }}">
                <i class="fas fa-newspaper"></i><span>{{ __('admin.menu.news') }}</span>
            </a>

            <a href="{{ route('merchandising.index') }}" class="item-admin {{ request()->is('admin/merchandising*') ? 'activo' : '' }}">
                <i class="fas fa-shopping-bag"></i><span>{{ __('admin.menu.store') }}</span>
            </a>

            <a href="{{ route('galerias.index') }}" class="item-admin {{ request()->is('admin/galerias*') ? 'activo' : '' }}">
                <i class="fas fa-images"></i><span>{{ __('admin.menu.galleries') }}</span>
            </a>
        @endif

        {{-- Separador entre módulos de gestión y acciones generales. --}}
        <div class="separador-admin"></div>

        {{-- Botón para plegar/desplegar el sidebar en escritorio. --}}
        <button type="button" class="item-admin admin-sidebar-menu-toggle" id="admin-sidebar-toggle" aria-label="{{ __('admin.layout.toggle_menu') }}">
            <i class="fas fa-bars"></i><span>{{ __('admin.layout.toggle_menu_text') }}</span>
        </button>

        {{-- Botón de tema del admin: cambia entre modo claro y oscuro. --}}
        <button type="button" class="item-admin admin-theme-toggle" id="admin-theme-toggle" aria-label="{{ __('admin.layout.theme_dark') }}">
            <i class="fa-solid fa-moon"></i><span>{{ __('admin.layout.theme_toggle') }}</span>
        </button>

        {{-- Cambio de idioma: alterna entre castellano y valenciano manteniendo la URL actual. --}}
        <form action="{{ route('locale.switch', $nextAdminLocale) }}" method="POST" class="admin-locale-form">
            @csrf
            <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
            <button type="submit" class="item-admin admin-locale-toggle">
                <i class="fas fa-language"></i>
                <span>{{ $currentAdminLocale === 'val' ? 'Passar a castellà' : 'Cambiar a valenciano' }}</span>
            </button>
        </form>

        {{-- Enlace para salir del panel y volver a la web pública. --}}
        <a href="{{ url('/') }}" class="item-admin volver-web">
            <i class="fas fa-door-open"></i><span>{{ __('admin.layout.back_to_site') }}</span>
        </a>

        {{-- Cierre de sesión del usuario autenticado. --}}
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="item-admin volver-web admin-logout-toggle">
                <i class="fas fa-right-from-bracket"></i><span>{{ __('admin.layout.logout') }}</span>
            </button>
        </form>
    </nav>
</aside>
