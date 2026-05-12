<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClasificacionController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\GaleriaController;
use App\Http\Controllers\JugadorController;
use App\Http\Controllers\MerchandisingController;
use App\Http\Controllers\NoticiaController;
use App\Http\Controllers\PartidoAnotacionController;
use App\Http\Controllers\PartidoController;
use App\Http\Controllers\Public\ClasificacionController as PublicClasificacionController;
use App\Http\Controllers\Public\EquipoController as PublicEquipoController;
use App\Http\Controllers\Public\GaleriaController as PublicGaleriaController;
use App\Http\Controllers\Public\HomeController as PublicHomeController;
use App\Http\Controllers\Public\NoticiaController as PublicNoticiaController;
use App\Http\Controllers\Public\PartidoController as PublicPartidoController;
use App\Http\Controllers\Public\TiendaController as PublicTiendaController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/

Route::get('/', [PublicHomeController::class, 'inicio'])->name('basket.inicio');
Route::get('contacto', [PublicHomeController::class, 'contacto'])->name('basket.contacto');

Route::get('equipo', [PublicEquipoController::class, 'equipo'])->name('basket.equipo');
Route::get('jugadores/{jugador}', [PublicEquipoController::class, 'jugadorDetalle'])
    ->name('basket.jugadores.detalle');
Route::patch('equipo/{equipo}/oculto', [PublicEquipoController::class, 'toggleOcultoEquipo'])
    ->name('basket.equipo.toggle-oculto')
    ->middleware(['auth', 'role:admin,coach']);
Route::patch('jugadores/{jugador}/oculto', [PublicEquipoController::class, 'toggleOcultoJugador'])
    ->name('basket.jugadores.toggle-oculto')
    ->middleware(['auth', 'role:admin,coach']);

Route::get('partidos', [PublicPartidoController::class, 'partidos'])->name('basket.partidos');
Route::get('partidos/{id}', [PublicPartidoController::class, 'partidoDetalle'])
    ->name('basket.partidos.detalle');
Route::patch('partidos/{partido}/oculto', [PublicPartidoController::class, 'toggleOcultoPartido'])
    ->name('basket.partidos.toggle-oculto')
    ->middleware(['auth', 'role:admin,coach']);

Route::get('clasificacion', [PublicClasificacionController::class, 'clasificacion'])
    ->name('basket.clasificacion');
Route::get('estadisticas', [PublicClasificacionController::class, 'estadisticas'])
    ->name('basket.estadisticas');

Route::get('galeria', [PublicGaleriaController::class, 'galeria'])->name('basket.galeria');
Route::get('galeria/{slug}', [PublicGaleriaController::class, 'galeriaDetalle'])
    ->name('basket.galeria.detalle');
Route::patch('galeria/{galeria}/oculto', [PublicGaleriaController::class, 'toggleOcultoGaleria'])
    ->name('basket.galeria.toggle-oculto')
    ->middleware(['auth', 'role:admin,editor']);

Route::get('noticia', [PublicNoticiaController::class, 'noticia'])->name('basket.noticias');
Route::get('noticias/{slug}', [PublicNoticiaController::class, 'noticia_detalle'])
    ->name('noticias.detalle');
Route::patch('noticia/{noticia}/oculto', [PublicNoticiaController::class, 'toggleOcultoNoticia'])
    ->name('basket.noticias.toggle-oculto')
    ->middleware(['auth', 'role:admin,editor']);

Route::get('tienda', [PublicTiendaController::class, 'tienda'])->name('basket.tienda');
Route::post('tienda/cesta', [PublicTiendaController::class, 'anadirProductoCesta'])
    ->name('basket.tienda.cesta.add');
Route::patch('tienda/cesta/{itemKey}', [PublicTiendaController::class, 'actualizarProductoCesta'])
    ->name('basket.tienda.cesta.update');
Route::delete('tienda/cesta/{itemKey}', [PublicTiendaController::class, 'eliminarProductoCesta'])
    ->name('basket.tienda.cesta.remove');
Route::get('tienda/pedido', [PublicTiendaController::class, 'mostrarFormularioPedido'])
    ->name('basket.tienda.pedido');
Route::post('tienda/pedido', [PublicTiendaController::class, 'enviarPedidoMerchan'])
    ->name('basket.tienda.pedido.enviar');
Route::patch('tienda/{merchandising}/oculto', [PublicTiendaController::class, 'toggleOcultoMerchandising'])
    ->name('basket.tienda.toggle-oculto')
    ->middleware(['auth', 'role:admin,editor']);

/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

Route::post('logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

/*
|--------------------------------------------------------------------------
| Dashboard admin compartido
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,coach,editor'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/', function () {
            return view('admin.dashboard');
        })->name('dashboard');
    });

/*
|--------------------------------------------------------------------------
| Admin y coach
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,coach'])
    ->prefix('admin')
    ->group(function () {
        Route::middleware('role:admin')->group(function () {
            Route::resource('clasificaciones', ClasificacionController::class)
                ->parameters(['clasificaciones' => 'clasificacion'])
                ->except(['index', 'show']);

            Route::post('clasificaciones/{clasificacion}/recalcular', [ClasificacionController::class, 'recalcular'])
                ->name('clasificaciones.recalcular');
        });

        Route::resource('equipos', EquipoController::class);
        Route::patch('equipos/{equipo}/oculto', [EquipoController::class, 'toggleOculto'])
            ->name('equipos.toggle-oculto');

        Route::resource('jugadores', JugadorController::class);
        Route::patch('jugadores/{jugador}/oculto', [JugadorController::class, 'toggleOculto'])
            ->name('jugadores.toggle-oculto');

        Route::resource('partidos', PartidoController::class);
        Route::get('partidos/{partido}/anotacion', [PartidoAnotacionController::class, 'anotacion'])
            ->name('partidos.anotacion');
        Route::post('partidos/{partido}/anotacion/periodo', [PartidoAnotacionController::class, 'cambiarPeriodoAnotacion'])
            ->name('partidos.anotacion.periodo');
        Route::post('partidos/{partido}/anotacion/accion', [PartidoAnotacionController::class, 'registrarAccionAnotacion'])
            ->name('partidos.anotacion.accion');
        Route::post('partidos/{partido}/anotacion/deshacer', [PartidoAnotacionController::class, 'deshacerAccionAnotacion'])
            ->name('partidos.anotacion.deshacer');
        Route::post('partidos/{partido}/anotacion/confirmar', [PartidoAnotacionController::class, 'confirmarAnotacion'])
            ->name('partidos.anotacion.confirmar');
        Route::patch('partidos/{partido}/oculto', [PartidoController::class, 'toggleOculto'])
            ->name('partidos.toggle-oculto');
        Route::patch('partidos/{partido}/oculto-clasificacion', [PartidoController::class, 'toggleOcultoClasificacion'])
            ->name('partidos.toggle-oculto-clasificacion');

        Route::resource('clasificaciones', ClasificacionController::class)
            ->parameters(['clasificaciones' => 'clasificacion'])
            ->only(['index', 'show']);
    });

/*
|--------------------------------------------------------------------------
| Solo admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->group(function () {
        Route::resource('usuarios', UserController::class)->except(['show']);
    });

/*
|--------------------------------------------------------------------------
| Admin y editor
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,editor'])
    ->prefix('admin')
    ->group(function () {
        Route::resource('noticias', NoticiaController::class);
        Route::patch('noticias/{noticia}/oculto', [NoticiaController::class, 'toggleOculto'])
            ->name('noticias.toggle-oculto');

        Route::resource('merchandising', MerchandisingController::class);
        Route::patch('merchandising/{merchandising}/oculto', [MerchandisingController::class, 'toggleOculto'])
            ->name('merchandising.toggle-oculto');

        Route::resource('galerias', GaleriaController::class);
        Route::patch('galerias/{galeria}/oculto', [GaleriaController::class, 'toggleOculto'])
            ->name('galerias.toggle-oculto');
    });
