// Carga la configuracion base de Laravel para JavaScript.
import './bootstrap';
import { initAdminLayout } from './admin/layout';
import { initImagePreviews } from './admin/image-preview';
import { initPublicNav } from './public/nav';

// Punto de entrada de Vite: desde aqui se conectan los modulos JS del proyecto.
document.addEventListener('DOMContentLoaded', () => {
    // Navegacion publica: menu movil y boton de tema de la web publica.
    initPublicNav();

    // Panel admin: sidebar, cabecera movil, boton de tema y cierre del menu movil.
    initAdminLayout();

    // Formularios admin: previsualizacion local de imagenes antes de subirlas.
    initImagePreviews();
});
