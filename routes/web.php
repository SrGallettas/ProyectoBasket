<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\JugadorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BasketController;
use App\Http\Controllers\NoticiaController;
use App\Http\Controllers\MerchandisingController;

// --- 1. RUTAS PÚBLICAS (Ahora gestionadas por BasketController) ---
Route::get('/', [BasketController::class, 'inicio'])->name('basket.inicio');
Route::get('equipo', [BasketController::class, 'equipo'])->name('basket.equipo');
Route::get('tienda', [BasketController::class, 'tienda'])->name('basket.tienda');
Route::get('clasificacion', [BasketController::class, 'clasificacion'])->name('basket.clasificacion');
Route::get('estadisticas', [BasketController::class, 'estadisticas'])->name('basket.estadisticas');
Route::get('partidos', [BasketController::class, 'partidos'])->name('basket.partidos');
Route::get('galeria', [BasketController::class, 'galeria'])->name('basket.galeria');
Route::get('contacto', [BasketController::class, 'contacto'])->name('basket.contacto');
Route::get('noticia', [BasketController::class, 'noticia'])->name('basket.noticias');
Route::get('/noticias/{slug}', [BasketController::class, 'noticia_detalle'])->name('noticias.detalle');

// --- 2. AUTENTICACIÓN (Solo accesibles si NO estás logueado) ---
Route::middleware(['guest'])->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});
Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// --- 3. PANEL DE ADMINISTRACIÓN (PROTEGIDO) ---
// Todo lo que esté aquí dentro requiere estar logueado
Route::middleware(['auth'])->prefix('admin')->group(function () {

    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // RUTAS DE BÚSQUEDA (SIEMPRE antes de los resource)
    Route::get('equipos/search', [EquipoController::class, 'search'])->name('equipos.search');
    Route::get('jugadores/search', [JugadorController::class, 'search'])->name('jugadores.search');
    Route::get('noticias/search', [NoticiaController::class, 'search'])->name('noticias.search');


    // Route::resource ya crea las 7 rutas (index, create, store, show, edit, update, destroy)
    Route::resource("equipos", EquipoController::class);
    Route::resource("jugadores", JugadorController::class);
    Route::resource("noticias", NoticiaController::class);
    Route::resource("merchandising", MerchandisingController::class);
});
