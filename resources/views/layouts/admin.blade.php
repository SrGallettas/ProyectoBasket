<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin - Bellreguard CB</title>
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
    <div class="contenedor-admin">
        <aside class="sidebar-admin" id="admin-sidebar">
            <div class="admin-perfil">
                <img src="{{ asset('img/basket.jpeg') }}" alt="Logo Admin">
                <div class="admin-info">
                    <h4>Panel Admin</h4>
                    <span>{{ $adminUser?->name }} · {{ \App\Models\User::ROLES[$adminUser?->role] ?? 'Usuario' }}</span>
                </div>
            </div>

            <nav class="menu-admin">
                <a href="{{ route('dashboard') }}" class="item-admin {{ request()->routeIs('dashboard') ? 'activo' : '' }}">
                    <i class="fas fa-home"></i><span>Inicio</span>
                </a>

                <a href="{{ route('jugadores.index') }}" class="item-admin {{ request()->is('admin/jugadores*') ? 'activo' : '' }}">
                    <i class="fas fa-users"></i><span>Jugadores</span>
                </a>

                @if($adminUser?->isAdmin())
                    <a href="{{ route('usuarios.index') }}" class="item-admin {{ request()->is('admin/usuarios*') ? 'activo' : '' }}">
                        <i class="fas fa-user-shield"></i><span>Usuarios</span>
                    </a>

                    <a href="{{ route('equipos.index') }}" class="item-admin {{ request()->is('admin/equipos*') ? 'activo' : '' }}">
                        <i class="fas fa-tshirt"></i><span>Equipos</span>
                    </a>
                @endif

                @if($adminUser?->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_EDITOR]))
                    <a href="{{ route('noticias.index') }}" class="item-admin {{ request()->is('admin/noticias*') ? 'activo' : '' }}">
                        <i class="fas fa-newspaper"></i><span>Noticias</span>
                    </a>

                    <a href="{{ route('merchandising.index') }}" class="item-admin {{ request()->is('admin/merchandising*') ? 'activo' : '' }}">
                        <i class="fas fa-shopping-bag"></i><span>Merchandising</span>
                    </a>
                @endif

                <a href="{{ route('partidos.index') }}" class="item-admin {{ request()->is('admin/partidos*') ? 'activo' : '' }}">
                    <i class="fas fa-calendar-alt"></i><span>Partidos</span>
                </a>

                <a href="{{ route('clasificaciones.index') }}" class="item-admin {{ request()->is('admin/clasificaciones*') ? 'activo' : '' }}">
                    <i class="fas fa-trophy"></i><span>Clasificaciones</span>
                </a>

                @if($adminUser?->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_EDITOR]))
                    <a href="{{ route('galerias.index') }}" class="item-admin {{ request()->is('admin/galerias*') ? 'activo' : '' }}">
                        <i class="fas fa-images"></i><span>Galerías</span>
                    </a>
                @endif

                <div class="separador-admin"></div>

                <button type="button" class="item-admin admin-sidebar-menu-toggle" id="admin-sidebar-toggle" aria-label="Desplegar u ocultar menú">
                    <i class="fas fa-bars"></i><span>Abrir o cerrar menú</span>
                </button>

                <button type="button" class="item-admin admin-theme-toggle" id="admin-theme-toggle" aria-label="Cambiar modo oscuro">
                    <i class="fa-regular fa-moon"></i><span>Cambiar tema</span>
                </button>
    
                <a href="{{ url('/') }}" class="item-admin volver-web">
                    <i class="fas fa-door-open"></i><span>Volver a la Web</span>
                </a>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="item-admin volver-web" style="width:100%; border:none; text-align:left;">
                        <i class="fas fa-right-from-bracket"></i><span>Cerrar sesión</span>
                    </button>
                </form>

                
</nav>
        </aside>

        <div class="admin-mobile-backdrop" id="admin-mobile-backdrop"></div>

        <main class="contenido-admin">
            <div class="admin-mobile-header">
                <button type="button" class="admin-mobile-toggle" id="admin-mobile-toggle" aria-label="Abrir menu de administracion">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="admin-mobile-header-text">
                    <strong>Panel Admin</strong>
                    <span>Bellreguard CB</span>
                </div>
                <button type="button" class="admin-mobile-theme-toggle" id="admin-mobile-theme-toggle" aria-label="Cambiar modo oscuro">
                    <i class="fa-regular fa-moon"></i>
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

            const actualizarBotonesTema = () => {
                const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
                const icono = isDark ? 'fa-sun' : 'fa-moon';
                const label = isDark ? 'Activar modo claro' : 'Activar modo oscuro';

                [desktopThemeToggle, mobileThemeToggle].forEach((boton) => {
                    if (!boton) return;
                    boton.innerHTML = `<i class="fa-regular ${icono}"></i>${boton.id === 'admin-theme-toggle' ? '<span>Cambiar tema</span>' : ''}`;
                    boton.setAttribute('aria-label', label);
                    boton.setAttribute('title', label);
                });
            };

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

            document.querySelectorAll('.menu-admin a').forEach((link) => {
                link.addEventListener('click', function () {
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
</body>
</html>
