# Bellreguard Basket

Aplicacion web del club Bellreguard CB construida con Laravel 12. Incluye zona publica, panel de administracion con roles y gestion de contenido para equipo, partidos, clasificaciones, noticias, galeria y merchandising.

## Requisitos

- PHP 8.2+
- Composer
- Node.js 20+
- SQLite, MySQL o MariaDB

## Puesta en marcha

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
```

Para desarrollo:

```bash
composer run dev
```

Para tests:

```bash
php artisan test
```

## Despliegue

Hay un script de despliegue en `tools/deploy_production.sh` pensado para subir la aplicacion a un VPS ya preparado con Laravel y Nginx.

Ejemplo:

```bash
DEPLOY_HOST=217.154.179.212 ./tools/deploy_production.sh
```

Variables utiles:

- `DEPLOY_HOST`: host o IP del servidor.
- `DEPLOY_USER`: usuario SSH. Por defecto `root`.
- `DEPLOY_APP_DIR`: ruta remota de la app. Por defecto `/var/www/ProyectoBasket/BellreguardBasket`.
- `DEPLOY_SSH_OPTS`: opciones extra para `ssh` y `scp`.
- `DEPLOY_SKIP_BUILD=1`: omite `npm run build` si ya has generado los assets.

El script:

- genera el build de frontend
- empaqueta el proyecto sin `.env` ni la base SQLite local
- sube el paquete por `scp`
- conserva una copia del `.env` remoto
- ejecuta `composer install`, `php artisan migrate --force` y recachea Laravel
- recrea `public/storage` apuntando a `storage/app/public`

## Estructura del proyecto

- `app/Http/Controllers`: controladores de admin, autenticacion y zona publica.
- `app/Services/Basket`: logica de dominio de portada, tienda y orden de categorias.
- `app/Services/Partidos`: sincronizacion, validacion y tablero de estadisticas de partidos.
- `resources/views/admin`: panel interno por secciones.
- `resources/views/basket`: vistas publicas.
- `public/css`: hojas de estilo publicas cargadas desde Blade.
- `tests/Feature`: cobertura funcional de permisos, vistas publicas y flujos de negocio.

## Roles

- `admin`: acceso total al panel.
- `coach`: gestion deportiva de equipos, jugadores, partidos y clasificaciones.
- `editor`: gestion de noticias, galerias y merchandising.

## Assets

El proyecto mantiene los estilos CSS como assets estaticos en `public/css` y las plantillas Blade los cargan directamente con `asset()`. Vite se conserva unicamente para JavaScript de `resources/js/app.js` y para futuras ampliaciones del frontend.

## Areas funcionales

- Portada publica con resumen de actividad del club.
- Calendario, resultados, clasificaciones y estadisticas.
- Fichas de equipo y jugadores.
- Noticias y galerias publicas.
- Tienda de merchandising con cesta en sesion y envio por email.
- Panel de administracion con control por roles.
