<?php

  use Illuminate\Support\Facades\Route;

  // Importamos los controladores.
  // Un controlador es la clase que se encarga de responder cuando el usuario entra a una ruta.
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


  // ======================================================
  // 1. RUTAS PUBLICAS
  // ======================================================
  // Son las páginas que puede ver cualquier visitante.
  // En este proyecto casi toda la parte pública la controla BasketController.

  Route::get('/', [BasketController::class, 'inicio'])->name('basket.inicio');
  // Cuando alguien entra en "/" se carga la portada de la web.

  Route::get('equipo', [BasketController::class, 'equipo'])->name('basket.equipo');
  // Muestra la página pública del equipo y los jugadores.

  Route::patch('equipo/{equipo}/oculto', [BasketController::class, 'toggleOcultoEquipo'])
      ->name('basket.equipo.toggle-oculto')
      ->middleware(['auth', 'role:admin,coach']);
  // Cambia el valor "oculto" de un equipo.
  // {equipo} será el id o modelo del equipo.
  // Solo pueden hacerlo usuarios logueados con rol admin o coach.

  Route::patch('jugadores/{jugador}/oculto', [BasketController::class,
  'toggleOcultoJugador'])
      ->name('basket.jugadores.toggle-oculto')
      ->middleware(['auth', 'role:admin,coach']);
  // Igual que la anterior, pero para jugadores.

  Route::get('tienda', [BasketController::class, 'tienda'])->name('basket.tienda');
  // Muestra la tienda o merchandising público.

  Route::patch('tienda/{merchandising}/oculto', [BasketController::class,
  'toggleOcultoMerchandising'])
      ->name('basket.tienda.toggle-oculto')
      ->middleware(['auth', 'role:admin,editor']);
  // Oculta o muestra un producto de merchandising.
  // Solo admin o editor.

  Route::get('clasificacion', [BasketController::class,
  'clasificacion'])->name('basket.clasificacion');
  // Muestra la clasificación pública.

  Route::get('estadisticas', [BasketController::class,
  'estadisticas'])->name('basket.estadisticas');
  // Muestra estadísticas públicas.

  Route::get('partidos', [BasketController::class, 'partidos'])->name('basket.partidos');
  // Muestra el listado público de partidos.

  Route::get('partidos/{id}', [BasketController::class,
  'partidoDetalle'])->name('basket.partidos.detalle');
  // Muestra el detalle de un partido concreto.
  // Aquí {id} es el identificador del partido.

  Route::patch('partidos/{partido}/oculto', [BasketController::class,
  'toggleOcultoPartido'])
      ->name('basket.partidos.toggle-oculto')
      ->middleware(['auth', 'role:admin,coach']);
  // Oculta o muestra un partido en la web pública.

  Route::get('galeria', [BasketController::class, 'galeria'])->name('basket.galeria');
  // Muestra el listado público de galerías.

  Route::patch('galeria/{galeria}/oculto', [BasketController::class,
  'toggleOcultoGaleria'])
      ->name('basket.galeria.toggle-oculto')
      ->middleware(['auth', 'role:admin,editor']);
  // Oculta o muestra una galería.

  Route::get('galeria/{slug}', [BasketController::class,
  'galeriaDetalle'])->name('basket.galeria.detalle');
  // Muestra el detalle de una galería concreta.
  // Aquí no usa id, usa slug.
  // Ejemplo: /galeria/torneo-infantil-2026

  Route::get('contacto', [BasketController::class, 'contacto'])->name('basket.contacto');
  // Muestra la página de contacto.

  Route::get('noticia', [BasketController::class, 'noticia'])->name('basket.noticias');
  // Muestra el listado público de noticias.

  Route::patch('noticia/{noticia}/oculto', [BasketController::class,
  'toggleOcultoNoticia'])
      ->name('basket.noticias.toggle-oculto')
      ->middleware(['auth', 'role:admin,editor']);
  // Oculta o muestra una noticia.

  Route::get('/noticias/{slug}', [BasketController::class,
  'noticia_detalle'])->name('noticias.detalle');
  // Muestra el detalle de una noticia usando slug.

  Route::post('tienda/interes', [BasketController::class,
  'enviarInteresMerchan'])->name('basket.tienda.interes');
  // Procesa el formulario de interés de tienda.
  // Es POST porque aquí el usuario envía datos.


  // ======================================================
  // 2. AUTENTICACION
  // ======================================================
  // Aquí está el login y logout.

  Route::middleware(['guest'])->group(function () {
      Route::get('login', [AuthController::class, 'showLogin'])->name('login');
      // Muestra el formulario de login.
      // "guest" significa: solo si NO estás logueado.

      Route::post('login', [AuthController::class, 'login']);
      // Procesa el envío del login.
  });

  Route::post('logout', [AuthController::class,
  'logout'])->name('logout')->middleware('auth');
  // Cierra sesión.
  // Solo puede hacerlo alguien autenticado.


  // ======================================================
  // 3. PANEL DE ADMINISTRACION
  // ======================================================
  // Todo lo que empieza por /admin pertenece al panel interno.
  // Además, aquí se usan middlewares para controlar acceso por roles.

  Route::middleware(['auth', 'role:admin,coach,editor'])->prefix('admin')->group(function
  () {
      Route::get('/', function () {
          return view('admin.dashboard');
      })->name('dashboard');
  });
  // Dashboard principal del panel.
  // Puede entrar cualquier usuario logueado que tenga rol admin, coach o editor.


  // ======================================================
  // 4. RUTAS DE ADMIN PARA ADMIN Y COACH
  // ======================================================

  Route::middleware(['auth', 'role:admin,coach'])->prefix('admin')->group(function () {

      Route::resource("equipos", EquipoController::class);
      // Crea automáticamente las rutas CRUD de equipos:
      // index, create, store, show, edit, update, destroy

      Route::patch('equipos/{equipo}/oculto', [EquipoController::class,
  'toggleOculto'])->name('equipos.toggle-oculto');
      // Oculta o muestra equipos desde el panel admin.

      Route::resource("jugadores", JugadorController::class);
      // CRUD de jugadores.

      Route::patch('jugadores/{jugador}/oculto', [JugadorController::class,
  'toggleOculto'])->name('jugadores.toggle-oculto');
      // Oculta o muestra jugadores desde el panel admin.

      Route::resource('partidos', PartidoController::class);
      // CRUD de partidos.

      Route::patch('partidos/{partido}/oculto', [PartidoController::class,
  'toggleOculto'])->name('partidos.toggle-oculto');
      // Oculta o muestra partidos en la web pública desde el admin.

      Route::patch('partidos/{partido}/oculto-clasificacion', [PartidoController::class,
  'toggleOcultoClasificacion'])->name('partidos.toggle-oculto-clasificacion');
      // Oculta o muestra un partido dentro de la clasificación pública.

      Route::resource('clasificaciones', ClasificacionController::class)
          ->parameters(['clasificaciones' => 'clasificacion'])
          ->only(['index', 'show']);
      // Admin y coach aquí solo pueden listar y ver clasificaciones.
      // No crear, editar ni borrar.
  });


  // ======================================================
  // 5. RUTAS DE ADMIN SOLO PARA ADMIN
  // ======================================================

  Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {

      Route::resource("usuarios", UserController::class)->except(['show']);
      // CRUD de usuarios.
      // "except(['show'])" significa que no existe la ruta para ver un usuario en detalle.

      Route::resource('clasificaciones', ClasificacionController::class)
          ->parameters(['clasificaciones' => 'clasificacion'])
          ->except(['index', 'show']);
      // Aquí el admin sí puede crear, actualizar y borrar clasificaciones.

      Route::post('clasificaciones/{clasificacion}/recalcular',
  [ClasificacionController::class, 'recalcular'])->name('clasificaciones.recalcular');
      // Recalcula la clasificación usando los resultados de partidos.
  });


  // ======================================================
  // 6. RUTAS DE ADMIN PARA ADMIN Y EDITOR
  // ======================================================

  Route::middleware(['auth', 'role:admin,editor'])->prefix('admin')->group(function () {

      Route::resource("noticias", NoticiaController::class);
      // CRUD de noticias.

      Route::resource("merchandising", MerchandisingController::class);
      // CRUD de productos de tienda.

      Route::patch('noticias/{noticia}/oculto', [NoticiaController::class,
  'toggleOculto'])->name('noticias.toggle-oculto');
      // Oculta o muestra noticias.

      Route::patch('merchandising/{merchandising}/oculto', [MerchandisingController::class,
  'toggleOculto'])->name('merchandising.toggle-oculto');
      // Oculta o muestra productos.

      Route::resource('galerias', GaleriaController::class);
      // CRUD de galerías.

      Route::patch('galerias/{galeria}/oculto', [GaleriaController::class,
  'toggleOculto'])->name('galerias.toggle-oculto');
      // Oculta o muestra galerías.
  });
