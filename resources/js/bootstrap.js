import axios from 'axios';

// Axios queda disponible globalmente para cualquier script que necesite hacer AJAX.
window.axios = axios;

// Cabecera habitual en Laravel para identificar peticiones hechas por JavaScript.
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
