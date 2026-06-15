import { getCurrentTheme, toggleTheme } from '../theme';

// Guarda si el usuario dejo plegado el menu lateral en escritorio.
const SIDEBAR_STORAGE_KEY = 'admin-sidebar-collapsed';
// A partir de este ancho el panel se comporta como version movil.
const MOBILE_BREAKPOINT = 768;

// Las traducciones vienen desde Blade en window.BellreguardAdmin.
function getAdminConfig() {
    return window.BellreguardAdmin || {};
}

function isMobile() {
    return window.innerWidth <= MOBILE_BREAKPOINT;
}

// Actualiza los dos botones de tema del admin: el del sidebar y el de la cabecera movil.
function renderThemeButtons(buttons) {
    const config = getAdminConfig();
    const isDark = getCurrentTheme() === 'dark';
    // En oscuro se muestra el sol porque la accion disponible es volver a claro.
    const icon = isDark
        ? '<i class="fa-solid fa-sun"></i>'
        : '<i class="fa-solid fa-moon"></i>';
    const label = isDark ? config.themeLightLabel : config.themeDarkLabel;

    buttons.forEach((button) => {
        if (!button) {
            return;
        }

        const buttonText = button.id === 'admin-theme-toggle'
            ? `<span>${config.themeToggleText || ''}</span>`
            : '';

        button.innerHTML = `${icon}${buttonText}`;

        if (label) {
            button.setAttribute('aria-label', label);
            button.setAttribute('title', label);
        }
    });
}

// Inicializa los controles generales del panel de administracion.
export function initAdminLayout() {
    const body = document.body;
    const desktopToggle = document.getElementById('admin-sidebar-toggle');
    const mobileToggle = document.getElementById('admin-mobile-toggle');
    const desktopThemeToggle = document.getElementById('admin-theme-toggle');
    const mobileThemeToggle = document.getElementById('admin-mobile-theme-toggle');
    const mobileBackdrop = document.getElementById('admin-mobile-backdrop');
    const themeButtons = [desktopThemeToggle, mobileThemeToggle];

    if (!desktopToggle && !mobileToggle && !desktopThemeToggle && !mobileThemeToggle) {
        return;
    }

    // En movil el sidebar se abre como panel lateral encima del contenido.
    const closeMobileMenu = () => {
        body.classList.remove('admin-mobile-sidebar-open');
    };

    // En escritorio el sidebar se pliega y el estado queda recordado en localStorage.
    const toggleDesktopMenu = () => {
        body.classList.toggle('admin-sidebar-collapsed');
        localStorage.setItem(
            SIDEBAR_STORAGE_KEY,
            body.classList.contains('admin-sidebar-collapsed') ? '1' : '0',
        );
    };

    // Recupera el estado del sidebar al entrar de nuevo en escritorio.
    if (window.innerWidth > MOBILE_BREAKPOINT && localStorage.getItem(SIDEBAR_STORAGE_KEY) === '1') {
        body.classList.add('admin-sidebar-collapsed');
    }

    renderThemeButtons(themeButtons);

    desktopToggle?.addEventListener('click', () => {
        // El mismo boton abre el menu en movil y pliega el sidebar en escritorio.
        if (isMobile()) {
            body.classList.toggle('admin-mobile-sidebar-open');
            return;
        }

        toggleDesktopMenu();
    });

    mobileToggle?.addEventListener('click', () => {
        body.classList.toggle('admin-mobile-sidebar-open');
    });

    themeButtons.forEach((button) => {
        button?.addEventListener('click', () => {
            // Ambos botones de tema comparten el mismo estado visual.
            toggleTheme();
            renderThemeButtons(themeButtons);
        });
    });

    mobileBackdrop?.addEventListener('click', closeMobileMenu);

    document.querySelectorAll('.menu-admin .item-admin').forEach((item) => {
        item.addEventListener('click', () => {
            if (isMobile()) {
                closeMobileMenu();
            }
        });
    });

    window.addEventListener('resize', () => {
        // Al volver a escritorio se elimina cualquier estado abierto propio del movil.
        if (window.innerWidth > MOBILE_BREAKPOINT) {
            closeMobileMenu();
        }

        if (window.innerWidth <= MOBILE_BREAKPOINT) {
            body.classList.remove('admin-sidebar-collapsed');
        } else if (localStorage.getItem(SIDEBAR_STORAGE_KEY) === '1') {
            body.classList.add('admin-sidebar-collapsed');
        }
    });

    renderThemeButtons(themeButtons);
}
