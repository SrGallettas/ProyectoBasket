// Activa la previsualizacion de imagenes en inputs con data-image-input.
export function initImagePreviews() {
    document.querySelectorAll('[data-image-input]').forEach((input) => {
        input.addEventListener('change', (event) => {
            // El atributo contiene el selector del <img> donde se mostrara la preview.
            const selector = event.currentTarget.getAttribute('data-image-input');
            const preview = selector ? document.querySelector(selector) : null;
            const file = event.currentTarget.files && event.currentTarget.files[0];

            // Si falta el destino o el archivo no es una imagen, no se intenta leer nada.
            if (!preview || !file || !file.type.startsWith('image/')) {
                return;
            }

            // FileReader convierte la imagen local en una URL temporal para el navegador.
            const reader = new FileReader();
            reader.onload = (loadEvent) => {
                if (loadEvent.target && loadEvent.target.result) {
                    preview.src = loadEvent.target.result;
                    preview.style.display = '';

                    const wrapper = preview.closest('.preview-foto-noticia');
                    wrapper?.classList.remove('preview-foto-noticia-vacia');
                    wrapper?.querySelector('span')?.remove();
                }
            };
            reader.readAsDataURL(file);
        });
    });
}
