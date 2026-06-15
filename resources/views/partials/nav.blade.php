<nav class="barra-navegacion" data-nav>
    {{-- Datos base del menú público: enlaces, ruta activa e idiomas disponibles. --}}
    @php
        $navLinks = [
            ['label' => __('ui.nav.home'), 'href' => url('/'), 'active' => request()->routeIs('basket.inicio')],
            ['label' => __('ui.nav.team'), 'href' => route('basket.equipo'), 'active' => request()->routeIs('basket.equipo')],
            ['label' => __('ui.nav.store'), 'href' => route('basket.tienda'), 'active' => request()->routeIs('basket.tienda', 'basket.tienda.*')],
            ['label' => __('ui.nav.standings'), 'href' => route('basket.clasificacion'), 'active' => request()->routeIs('basket.clasificacion')],
            ['label' => __('ui.nav.stats'), 'href' => route('basket.estadisticas'), 'active' => request()->routeIs('basket.estadisticas')],
            ['label' => __('ui.nav.matches'), 'href' => route('basket.partidos'), 'active' => request()->routeIs('basket.partidos', 'basket.partidos.*')],
            ['label' => __('ui.nav.news'), 'href' => route('basket.noticias'), 'active' => request()->routeIs('basket.noticias', 'basket.noticias.*', 'noticias.detalle')],
            ['label' => __('ui.nav.gallery'), 'href' => route('basket.galeria'), 'active' => request()->routeIs('basket.galeria', 'basket.galeria.*')],
            ['label' => __('ui.nav.contact'), 'href' => route('basket.contacto'), 'active' => request()->routeIs('basket.contacto')],
        ];

        $currentLocale = app()->getLocale();
        $availableLocales = [
            'es' => 'ES',
            'val' => 'VAL',
        ];
    @endphp

    {{-- Logo del club: vuelve siempre a la portada. --}}
    <a href="{{ url('/') }}" class="navegacion-izquierda">
        <img src="{{ asset('img/club_14.png') }}" class="logo-navegacion" alt="{{ __('ui.club_name') }}" />
        <div class="titulo-navegacion">
            <h3>Bellreguard</h3>
            <span>{{ __('ui.club_name') }}</span>
        </div>
    </a>

    {{-- Botón hamburguesa: abre y cierra el menú en pantallas pequeñas. --}}
    <button type="button" class="boton-menu-movil" data-nav-toggle aria-label="{{ __('ui.nav.open_menu') }}" aria-expanded="false">
        <span></span>
        <span></span>
        <span></span>
    </button>

    {{-- Panel desplegable: agrupa enlaces, idioma y acciones de usuario. --}}
    <div class="panel-navegacion" data-nav-panel>
        {{-- Enlaces principales: se generan desde $navLinks y marcan la página activa. --}}
        <ul class="menu-navegacion">
            @foreach ($navLinks as $link)
                <li>
                    <a href="{{ $link['href'] }}" class="{{ $link['active'] ? 'activo' : '' }}" @if($link['active']) aria-current="page" @endif>
                        {{ $link['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>

        {{-- Acciones de la derecha: idioma, tema y sesión. --}}
        <div class="login-seccion">
            {{-- Selector de idioma: envía el cambio y vuelve a la URL actual. --}}
            <div class="selector-idioma" aria-label="{{ __('ui.language.selector_aria') }}">
                @foreach ($availableLocales as $locale => $label)
                    <form action="{{ route('locale.switch', $locale) }}" method="POST" class="selector-idioma-form">
                        @csrf
                        <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                        <button
                            type="submit"
                            class="{{ $currentLocale === $locale ? 'activo' : '' }}"
                        >
                            {{ $label }}
                        </button>
                    </form>
                @endforeach
            </div>

            {{-- Usuario visitante: puede cambiar tema e iniciar sesión. --}}
            @guest
                <button type="button" class="boton-tema-nav" id="theme-toggle" aria-label="{{ __('ui.theme.toggle_dark') }}" title="{{ __('ui.theme.toggle') }}">
                        <i class="fa-regular fa-moon"></i>
                        <span class="theme-label">{{ __('ui.theme.mode_dark') }}</span>
                    </button>
                <a href="{{ route('login') }}" class="boton-inicio-sesion">
                    {{ __('ui.auth.login') }}
                </a>
            @endguest

            {{-- Usuario autenticado: puede entrar al panel o cerrar sesión. --}}
            @auth
                <div class="auth-actions-wrap">
                    <button type="button" class="boton-tema-nav" id="theme-toggle" aria-label="{{ __('ui.theme.toggle_dark') }}" title="{{ __('ui.theme.toggle') }}">
                        <i class="fa-regular fa-moon"></i>
                        <span class="theme-label">{{ __('ui.theme.mode_dark') }}</span>
                    </button>
                    <a href="{{ route('dashboard') }}" class="boton-admin-nav">
                        {{ __('ui.auth.admin') }}
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="logout-inline-form">
                        @csrf
                        <button type="submit" class="boton-cerrar-sesion">
                            {{ __('ui.auth.logout') }}
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </div>
</nav>

<script>
    window.BellreguardPublic = {
        openMenuLabel: @json(__('ui.nav.open_menu')),
        closeMenuLabel: @json(__('ui.nav.close_menu')),
        themeModeDark: @json(__('ui.theme.mode_dark')),
        themeModeLight: @json(__('ui.theme.mode_light')),
        themeToggleDark: @json(__('ui.theme.toggle_dark')),
        themeToggleLight: @json(__('ui.theme.toggle_light')),
    };
</script>
