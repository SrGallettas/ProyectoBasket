<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\JugadorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BasketController;
use App\Http\Controllers\NoticiaController;
use App\Http\Controllers\MerchandisingController;
use App\Http\Controllers\PartidoController;
use App\Http\Controllers\GaleriaController;
use App\Http\Controllers\ClasificacionController;
use App\Http\Controllers\UserController;


// --- 1. RUTAS PÚBLICAS (Ahora gestionadas por BasketController) ---
Route::get('/', [BasketController::class, 'inicio'])->name('basket.inicio');
Route::get('equipo', [BasketController::class, 'equipo'])->name('basket.equipo');
Route::patch('equipo/{equipo}/oculto', [BasketController::class, 'toggleOcultoEquipo'])->name('basket.equipo.toggle-oculto')->middleware(['auth', 'role:admin,coach']);
Route::patch('jugadores/{jugador}/oculto', [BasketController::class, 'toggleOcultoJugador'])->name('basket.jugadores.toggle-oculto')->middleware(['auth', 'role:admin,coach']);
Route::get('tienda', [BasketController::class, 'tienda'])->name('basket.tienda');
Route::patch('tienda/{merchandising}/oculto', [BasketController::class, 'toggleOcultoMerchandising'])->name('basket.tienda.toggle-oculto')->middleware(['auth', 'role:admin,editor']);
Route::get('clasificacion', [BasketController::class, 'clasificacion'])->name('basket.clasificacion');
Route::get('estadisticas', [BasketController::class, 'estadisticas'])->name('basket.estadisticas');
Route::get('partidos', [BasketController::class, 'partidos'])->name('basket.partidos');
Route::get('partidos/{id}', [BasketController::class, 'partidoDetalle'])->name('basket.partidos.detalle');
Route::patch('partidos/{partido}/oculto', [BasketController::class, 'toggleOculto'])->name('basket.partidos.toggle-oculto')->middleware(['auth', 'role:admin,coach']);
Route::get('galeria', [BasketController::class, 'galeria'])->name('basket.galeria');
Route::patch('galeria/{galeria}/oculto', [BasketController::class, 'toggleOcultoGaleria'])->name('basket.galeria.toggle-oculto')->middleware(['auth', 'role:admin,editor']);
Route::get('galeria/{slug}', [BasketController::class, 'galeriaDetalle'])->name('basket.galeria.detalle');
Route::get('contacto', [BasketController::class, 'contacto'])->name('basket.contacto');
Route::get('noticia', [BasketController::class, 'noticia'])->name('basket.noticias');
Route::patch('noticia/{noticia}/oculto', [BasketController::class, 'toggleOcultoNoticia'])->name('basket.noticias.toggle-oculto')->middleware(['auth', 'role:admin,editor']);
Route::get('/noticias/{slug}', [BasketController::class, 'noticia_detalle'])->name('noticias.detalle');
Route::post('tienda/interes', [BasketController::class, 'enviarInteresMerchan'])->name('basket.tienda.interes');

// --- 2. AUTENTICACIÓN (Solo accesibles si NO estás logueado) ---
Route::middleware(['guest'])->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});
Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// --- 3. PANEL DE ADMINISTRACIÓN (PROTEGIDO) ---
// Todo lo que esté aquí dentro requiere estar logueado
Route::middleware(['auth', 'role:admin,coach,editor'])->prefix('admin')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');
});

Route::middleware(['auth', 'role:admin,coach'])->prefix('admin')->group(function () {
    Route::resource("equipos", EquipoController::class);
    Route::patch('equipos/{equipo}/oculto', [EquipoController::class, 'toggleOculto'])->name('equipos.toggle-oculto');
    Route::resource("jugadores", JugadorController::class);
    Route::patch('jugadores/{jugador}/oculto', [JugadorController::class, 'toggleOculto'])->name('jugadores.toggle-oculto');
    Route::resource('partidos', PartidoController::class);
    Route::patch('partidos/{partido}/oculto', [PartidoController::class, 'toggleOculto'])->name('partidos.toggle-oculto');
    Route::patch('partidos/{partido}/oculto-clasificacion', [PartidoController::class, 'toggleOcultoClasificacion'])->name('partidos.toggle-oculto-clasificacion');
    Route::resource('clasificaciones', ClasificacionController::class)
        ->parameters(['clasificaciones' => 'clasificacion'])
        ->only(['index', 'show']);
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::resource("usuarios", UserController::class)->except(['show']);
    Route::resource('clasificaciones', ClasificacionController::class)
        ->parameters(['clasificaciones' => 'clasificacion'])
        ->except(['index', 'show']);
    Route::post('clasificaciones/{clasificacion}/recalcular', [ClasificacionController::class, 'recalcular'])->name('clasificaciones.recalcular');
});

Route::middleware(['auth', 'role:admin,editor'])->prefix('admin')->group(function () {
    Route::resource("noticias", NoticiaController::class);
    Route::resource("merchandising", MerchandisingController::class);
    Route::patch('noticias/{noticia}/oculto', [NoticiaController::class, 'toggleOculto'])->name('noticias.toggle-oculto');
    Route::patch('merchandising/{merchandising}/oculto', [MerchandisingController::class, 'toggleOculto'])->name('merchandising.toggle-oculto');
    Route::resource('galerias', GaleriaController::class);
    Route::patch('galerias/{galeria}/oculto', [GaleriaController::class, 'toggleOculto'])->name('galerias.toggle-oculto');
});
