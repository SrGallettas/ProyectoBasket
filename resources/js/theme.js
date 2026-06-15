// Clave compartida por la web publica y el admin para recordar el tema elegido.
const THEME_STORAGE_KEY = 'bb_theme';

// Aplica el tema inicial usando primero localStorage y, si no existe, la preferencia del sistema.
export function applyInitialTheme() {
    const savedTheme = localStorage.getItem(THEME_STORAGE_KEY);
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const theme = savedTheme || (prefersDark ? 'dark' : 'light');

    // El CSS lee este atributo para activar los estilos claros u oscuros.
    document.documentElement.setAttribute('data-theme', theme);
}

// Devuelve siempre un valor controlado aunque el atributo no exista todavia.
export function getCurrentTheme() {
    return document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
}

// Alterna el tema, lo guarda en el navegador y devuelve el nuevo valor.
export function toggleTheme() {
    const nextTheme = getCurrentTheme() === 'dark' ? 'light' : 'dark';

    document.documentElement.setAttribute('data-theme', nextTheme);
    localStorage.setItem(THEME_STORAGE_KEY, nextTheme);

    return nextTheme;
}
