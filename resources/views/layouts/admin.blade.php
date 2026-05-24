<!DOCTYPE html>
@php($htmlLocale = app()->getLocale() === 'val' ? 'ca' : str_replace('_', '-', app()->getLocale()))
<html lang="{{ $htmlLocale }}">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', __('admin.layout.title'))</title>
    <script>
        (function () {
            const key = 'bb_theme';
            const saved = localStorage.getItem(key);
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = saved || (prefersDark ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
   <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/paginas/admin.css') }}" />
    @stack('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    @php($adminUser = auth()->user())
    @php($currentAdminLocale = app()->getLocale() === 'val' ? 'val' : 'es')
    @php($nextAdminLocale = $currentAdminLocale === 'val' ? 'es' : 'val')
    <div class="contenedor-admin">
        <aside class="sidebar-admin" id="admin-sidebar">
            <div class="admin-perfil">
                <img src="{{ asset('img/club_14.png') }}" alt="{{ __('admin.layout.panel') }}">
                <div class="admin-info">
                    <h4>{{ __('admin.layout.panel') }}</h4>
                    <span>{{ $adminUser?->name }} · {{ \App\Models\User::ROLES[$adminUser?->role] ?? __('admin.layout.user') }}</span>
                </div>
            </div>

            <nav class="menu-admin">
                <a href="{{ route('dashboard') }}" class="item-admin {{ request()->routeIs('dashboard') ? 'activo' : '' }}">
                    <i class="fas fa-home"></i><span>{{ __('admin.menu.home') }}</span>
                </a>

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

                @if($adminUser?->isAdmin())
                    <a href="{{ route('usuarios.index') }}" class="item-admin {{ request()->is('admin/usuarios*') ? 'activo' : '' }}">
                        <i class="fas fa-user-shield"></i><span>{{ __('admin.menu.users') }}</span>
                    </a>
                @endif

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

                <div class="separador-admin"></div>

                <button type="button" class="item-admin admin-sidebar-menu-toggle" id="admin-sidebar-toggle" aria-label="{{ __('admin.layout.toggle_menu') }}">
                    <i class="fas fa-bars"></i><span>{{ __('admin.layout.toggle_menu_text') }}</span>
                </button>

                <button type="button" class="item-admin admin-theme-toggle" id="admin-theme-toggle" aria-label="{{ __('admin.layout.theme_dark') }}">
                    <i class="fa-solid fa-moon"></i><span>{{ __('admin.layout.theme_toggle') }}</span>
                </button>

                <form action="{{ route('locale.switch', $nextAdminLocale) }}" method="POST" class="admin-locale-form">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                    <button type="submit" class="item-admin admin-locale-toggle">
                        <i class="fas fa-language"></i>
                        <span>{{ $currentAdminLocale === 'val' ? 'Passar a castellà' : 'Cambiar a valenciano' }}</span>
                    </button>
                </form>
    
                <a href="{{ url('/') }}" class="item-admin volver-web">
                    <i class="fas fa-door-open"></i><span>{{ __('admin.layout.back_to_site') }}</span>
                </a>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="item-admin volver-web admin-logout-toggle">
                        <i class="fas fa-right-from-bracket"></i><span>{{ __('admin.layout.logout') }}</span>
                    </button>
                </form>

                
</nav>
        </aside>

        <div class="admin-mobile-backdrop" id="admin-mobile-backdrop"></div>

        <main class="contenido-admin">
            <div class="admin-mobile-header">
                <button type="button" class="admin-mobile-toggle" id="admin-mobile-toggle" aria-label="{{ __('admin.layout.open_mobile_menu') }}">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="admin-mobile-header-text">
                    <strong>{{ __('admin.layout.panel') }}</strong>
                    <span>Bellreguard CB</span>
                </div>
                <button type="button" class="admin-mobile-theme-toggle" id="admin-mobile-theme-toggle" aria-label="{{ __('admin.layout.theme_dark') }}">
                    <i class="fa-solid fa-moon"></i>
                </button>
            </div>
            @yield('contenido_admin')
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const body = document.body;
            const desktopToggle = document.getElementById('admin-sidebar-toggle');
            const mobileToggle = document.getElementById('admin-mobile-toggle');
            const desktopThemeToggle = document.getElementById('admin-theme-toggle');
            const mobileThemeToggle = document.getElementById('admin-mobile-theme-toggle');
            const mobileBackdrop = document.getElementById('admin-mobile-backdrop');
            const storageKey = 'admin-sidebar-collapsed';
            const themeStorageKey = 'bb_theme';
            const mobileBreakpoint = 768;
            const themeToggleText = @json(__('admin.layout.theme_toggle'));

            const actualizarBotonesTema = () => {
                const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
                const icono = isDark
                    ? '<i class="fa-solid fa-sun"></i>'
                    : '<i class="fa-solid fa-moon"></i>';
                const label = isDark ? @json(__('admin.layout.theme_light')) : @json(__('admin.layout.theme_dark'));

                [desktopThemeToggle, mobileThemeToggle].forEach((boton) => {
                    if (!boton) return;
                    const buttonText = boton.id === 'admin-theme-toggle' ? `<span>${themeToggleText}</span>` : '';
                    boton.innerHTML = `${icono}${buttonText}`;
                    boton.setAttribute('aria-label', label);
                    boton.setAttribute('title', label);
                });
            };

            actualizarBotonesTema();

            const toggleTema = () => {
                const temaActual = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
                const nuevoTema = temaActual === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', nuevoTema);
                localStorage.setItem(themeStorageKey, nuevoTema);
                actualizarBotonesTema();
            };

            if (window.innerWidth > mobileBreakpoint && localStorage.getItem(storageKey) === '1') {
                body.classList.add('admin-sidebar-collapsed');
            }

            const isMobile = () => window.innerWidth <= mobileBreakpoint;

            const closeMobileMenu = () => {
                body.classList.remove('admin-mobile-sidebar-open');
            };

            const toggleDesktopMenu = () => {
                body.classList.toggle('admin-sidebar-collapsed');
                localStorage.setItem(storageKey, body.classList.contains('admin-sidebar-collapsed') ? '1' : '0');
            };

            desktopToggle?.addEventListener('click', function () {
                if (isMobile()) {
                    body.classList.toggle('admin-mobile-sidebar-open');
                    return;
                }

                toggleDesktopMenu();
            });

            mobileToggle?.addEventListener('click', function () {
                body.classList.toggle('admin-mobile-sidebar-open');
            });

            desktopThemeToggle?.addEventListener('click', toggleTema);
            mobileThemeToggle?.addEventListener('click', toggleTema);

            mobileBackdrop?.addEventListener('click', closeMobileMenu);

            document.querySelectorAll('.menu-admin .item-admin').forEach((item) => {
                item.addEventListener('click', function () {
                    if (isMobile()) {
                        closeMobileMenu();
                    }
                });
            });

            window.addEventListener('resize', function () {
                if (window.innerWidth > mobileBreakpoint) {
                    closeMobileMenu();
                }

                if (window.innerWidth <= mobileBreakpoint) {
                    body.classList.remove('admin-sidebar-collapsed');
                } else if (localStorage.getItem(storageKey) === '1') {
                    body.classList.add('admin-sidebar-collapsed');
                }
            });

            document.querySelectorAll('[data-image-input]').forEach((input) => {
                input.addEventListener('change', function (event) {
                    const selector = event.currentTarget.getAttribute('data-image-input');
                    const preview = selector ? document.querySelector(selector) : null;
                    const file = event.currentTarget.files && event.currentTarget.files[0];

                    if (!preview || !file || !file.type.startsWith('image/')) {
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function (loadEvent) {
                        if (loadEvent.target && loadEvent.target.result) {
                            preview.src = loadEvent.target.result;
                        }
                    };
                    reader.readAsDataURL(file);
                });
            });

            actualizarBotonesTema();
        });
    </script>
    @stack('scripts')
</body>
</html>
