{{-- Cabecera visible en móvil dentro del panel de administración. --}}
<div class="admin-mobile-header">
    {{-- Botón hamburguesa: abre el menú lateral admin en pantallas pequeñas. --}}
    <button type="button" class="admin-mobile-toggle" id="admin-mobile-toggle" aria-label="{{ __('admin.layout.open_mobile_menu') }}">
        <i class="fas fa-bars"></i>
    </button>

    {{-- Título central de la cabecera móvil. --}}
    <div class="admin-mobile-header-text">
        <strong>{{ __('admin.layout.panel') }}</strong>
        <span>Bellreguard CB</span>
    </div>

    {{-- Botón de tema: cambia entre modo claro y oscuro desde móvil. --}}
    <button type="button" class="admin-mobile-theme-toggle" id="admin-mobile-theme-toggle" aria-label="{{ __('admin.layout.theme_dark') }}">
        <i class="fa-solid fa-moon"></i>
    </button>
</div>
