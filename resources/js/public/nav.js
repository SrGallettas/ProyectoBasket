import { applyInitialTheme, getCurrentTheme, toggleTheme } from '../theme';

// Por encima de este ancho el menu publico deja de funcionar como panel movil.
const DESKTOP_BREAKPOINT = 1400;

// Las etiquetas traducidas se imprimen desde Blade en window.BellreguardPublic.
function getPublicConfig() {
    return window.BellreguardPublic || {};
}

// Actualiza icono, texto y aria-label del boton de tema publico.
function renderThemeButton(button) {
    if (!button) {
        return;
    }

    const config = getPublicConfig();
    const isDark = getCurrentTheme() === 'dark';
    const label = isDark ? config.themeModeLight : config.themeModeDark;
    const ariaLabel = isDark ? config.themeToggleLight : config.themeToggleDark;

    // Se muestra la accion disponible: sol para volver a claro, luna para activar oscuro.
    button.innerHTML = isDark
        ? `<i class="fa-solid fa-sun"></i><span class="theme-label">${label || ''}</span>`
        : `<i class="fa-regular fa-moon"></i><span class="theme-label">${label || ''}</span>`;

    if (ariaLabel) {
        button.setAttribute('aria-label', ariaLabel);
    }
}

// Inicializa la navegacion publica: menu movil, cierre automatico y tema.
export function initPublicNav() {
    // El tema se aplica aqui y tambien en el <head> del layout para evitar parpadeos.
    applyInitialTheme();
    // Esta clase permite al CSS distinguir navegador con JavaScript activo.
    document.documentElement.classList.add('js-nav');

    const config = getPublicConfig();
    const nav = document.querySelector('[data-nav]');
    const toggle = document.querySelector('[data-nav-toggle]');
    const panel = document.querySelector('[data-nav-panel]');
    const themeButton = document.getElementById('theme-toggle');

    renderThemeButton(themeButton);

    if (!nav || !toggle || !panel) {
        return;
    }

    toggle.addEventListener('click', () => {
        // Abre/cierra el panel movil y sincroniza atributos de accesibilidad.
        const isOpen = nav.classList.toggle('nav-abierta');
        toggle.setAttribute('aria-label', isOpen ? config.closeMenuLabel : config.openMenuLabel);
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        document.body.classList.toggle('menu-movil-abierto', isOpen);
    });

    panel.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            // Al pulsar un enlace, el menu movil se cierra para no tapar la pagina destino.
            nav.classList.remove('nav-abierta');
            toggle.setAttribute('aria-label', config.openMenuLabel || 'Abrir menu');
            toggle.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('menu-movil-abierto');
        });
    });

    window.addEventListener('resize', () => {
        // Si se vuelve a escritorio con el menu abierto, se limpia ese estado movil.
        if (window.innerWidth > DESKTOP_BREAKPOINT) {
            nav.classList.remove('nav-abierta');
            toggle.setAttribute('aria-label', config.openMenuLabel || 'Abrir menu');
            toggle.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('menu-movil-abierto');
        }
    });

    themeButton?.addEventListener('click', () => {
        // Cambia el tema compartido con el admin y redibuja el boton publico.
        toggleTheme();
        renderThemeButton(themeButton);
    });
}
