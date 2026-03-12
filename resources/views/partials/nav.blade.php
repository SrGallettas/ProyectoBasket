<nav class="barra-navegacion" data-nav>

    {{-- 1. LOGO Y TEXTO --}}
    <div class="navegacion-izquierda">
        <img src="{{ asset('img/basket.jpeg') }}" class="logo-navegacion" alt="Logo Bellreguard" />
        <div class="titulo-navegacion">
            <h3>Bellreguard</h3>
            <span>Club de Basket</span>
        </div>
    </div>

    <button type="button" class="boton-menu-movil" data-nav-toggle aria-label="Abrir menu" aria-expanded="false">
        <span></span>
        <span></span>
        <span></span>
    </button>

    {{-- 2. MENÚ DE ENLACES (Centrado o distribuido) --}}
    <div class="panel-navegacion" data-nav-panel>
        <ul class="menu-navegacion">
            <li><a href="{{ url('/') }}">Inicio</a></li>
            <li><a href="{{ route('basket.equipo') }}">Equipo</a></li>
            <li><a href="{{ route('basket.tienda') }}">Tienda</a></li>
            <li><a href="{{ route('basket.clasificacion') }}">Clasificación</a></li>
            <li><a href="{{ route('basket.estadisticas') }}">Estadísticas</a></li>
            <li><a href="{{ route('basket.partidos') }}">Partidos</a></li>
            <li><a href="{{ route('basket.noticias') }}">Noticias</a></li>
            <li><a href="{{ route('basket.galeria') }}">Galería</a></li>
            <li><a href="{{ route('basket.contacto') }}">Contacto</a></li>
        </ul>

        {{-- 3. BOTÓN LOGIN / LOGOUT --}}
        <div class="login-seccion">
            @guest
                <a href="{{ route('login') }}" class="boton-inicio-sesion">
                    INICIAR SESIÓN
                </a>
            @endguest

            @auth
                <div class="auth-actions-wrap">
                    <a href="{{ route('dashboard') }}" class="boton-admin-nav">
                        ADMINISTRACIÓN
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="logout-inline-form">
                        @csrf
                        <button type="submit" class="boton-cerrar-sesion">
                            SALIR DE SESIÓN
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.documentElement.classList.add('js-nav');

    const nav = document.querySelector('[data-nav]');
    const toggle = document.querySelector('[data-nav-toggle]');
    const panel = document.querySelector('[data-nav-panel]');

    if (!nav || !toggle || !panel) {
        return;
    }

    toggle.addEventListener('click', function () {
        const abierto = nav.classList.toggle('nav-abierta');
        toggle.setAttribute('aria-label', abierto ? 'Cerrar menu' : 'Abrir menu');
        toggle.setAttribute('aria-expanded', abierto ? 'true' : 'false');
        document.body.classList.toggle('menu-movil-abierto', abierto);
    });

    panel.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', function () {
            nav.classList.remove('nav-abierta');
            toggle.setAttribute('aria-label', 'Abrir menu');
            toggle.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('menu-movil-abierto');
        });
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 1024) {
            nav.classList.remove('nav-abierta');
            toggle.setAttribute('aria-label', 'Abrir menu');
            toggle.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('menu-movil-abierto');
        }
    });
});
</script>
