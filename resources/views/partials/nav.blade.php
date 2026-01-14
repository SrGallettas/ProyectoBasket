<nav class="barra-navegacion">

    {{-- PARTE IZQUIERDA --}}
    <div class="navegacion-izquierda">
        <img src="{{ asset('img/basket.jpeg') }}" class="logo-navegacion" alt="Logo Bellreguard" />
        <div class="titulo-navegacion">
            <h3>Bellreguard</h3>
            <span>Club de Basket</span>
        </div>
    </div>

    {{-- CONTENEDOR DEL MENÚ MÓVIL --}}
    <div class="contenedor-menu-movil">

        {{-- BOTÓN MENÚ MÓVIL --}}
        <button class="boton-menu-movil" id="botonMenu" aria-label="Abrir menú">
            ☰
        </button>

        {{-- MENÚ DESPLEGABLE --}}
        <ul class="menu-navegacion" id="menuNavegacion">
            <li><a href="{{ url('/') }}">Inicio</a></li>
            <li><a href="{{ route('basket.equipo') }}">Equipo</a></li>
            <li><a href="{{ route('basket.merchandising') }}">Merchandising</a></li>
            <li><a href="{{ route('basket.clasificacion') }}">Clasificación</a></li>
            <li><a href="{{ route('basket.estadisticas') }}">Estadísticas</a></li>
            <li><a href="{{ route('basket.partidos') }}">Partidos</a></li>
            <li><a href="{{ route('basket.galeria') }}">Galería</a></li>
            <li><a href="{{ route('basket.contacto') }}">Contacto</a></li>

            {{-- BOTÓN LOGIN EN MÓVIL --}}
            <li class="login-movil">
                <a href="{{ route('login') }}" class="boton-inicio-sesion">
                    Iniciar Sesión
                </a>
            </li>
        </ul>

    </div>

    {{-- BOTÓN LOGIN DESKTOP --}}
    <div class="login-desktop">
        <a href="{{ route('login') }}" style="text-decoration: none;">
            <button class="boton-inicio-sesion">Iniciar Sesión</button>
        </a>
    </div>

</nav>