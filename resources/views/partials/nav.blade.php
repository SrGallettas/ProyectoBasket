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
        <li><a href="{{ route('basket.merchandising') }}">Tienda</a></li>
        <li><a href="{{ route('basket.clasificacion') }}">Clasificación</a></li>
        <li><a href="{{ route('basket.estadisticas') }}">Estadísticas</a></li>
        <li><a href="{{ route('basket.partidos') }}">Partidos</a></li>
        <li><a href="{{ route('basket.noticias') }}">Noticias</a></li>
        <li><a href="{{ route('basket.galeria') }}">Galería</a></li>
        <li><a href="{{ route('basket.contacto') }}">Contacto</a></li>
    </ul>

    {{-- 3. BOTÓN LOGIN --}}
    <div class="login-seccion">
        <a href="{{ route('login') }}" class="boton-inicio-sesion">
            INICIAR SESIÓN
        </a>
    </div>

</nav>