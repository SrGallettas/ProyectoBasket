<nav class="barra-navegacion">

    {{-- 1. LOGO Y TEXTO --}}
    <div class="navegacion-izquierda">
        <img src="{{ asset('img/basket.jpeg') }}" class="logo-navegacion" alt="Logo Bellreguard" />
        <div class="titulo-navegacion">
            <h3>Bellreguard</h3>
            <span>Club de Basket</span>
        </div>
    </div>

    {{-- 2. MENÚ DE ENLACES (Centrado o distribuido) --}}
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
            <div style="display: flex; gap: 10px; align-items: center;">
                <a href="{{ route('dashboard') }}" class="boton-admin-nav">
                    ADMINISTRACIÓN
                </a>
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="boton-cerrar-sesion">
                        SALIR DE SESIÓN
                    </button>
                </form>
            </div>
        @endauth
    </div>

</nav>