<?php

namespace App\Http\Controllers;

use App\Mail\MerchandisingPedidoMail;
use App\Models\Equipo;
use App\Models\Noticia;
use App\Models\Merchandising;
use App\Models\Partido;
use App\Models\Galeria;
use App\Models\Jugador;
use App\Models\Clasificacion;
use App\Models\ClasificacionFila;
use App\Models\PartidoCuarto;
use App\Models\PartidoEstadistica;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class BasketController extends Controller
{
    /*
     * PORTADA PRINCIPAL
     *
     * Este método prepara todos los datos que necesita la pagina de inicio.
     * No solo carga una tabla: mezcla información de varios modelos.
     *
     * Qué construye:
     * - próximo partido destacado
     * - último resultado destacado
     * - cifras generales del club
     * - galerías para enseñar en portada
     * - una mini clasificación
     * - últimas noticias
     *
     * Idea importante:
     * el controlador hace la lógica de negocio y deja la vista casi lista para pintar.
     */
    public function inicio()
    {
        $ahora = Carbon::now();

        // Primero se buscan los partidos públicos del club.
        // "oculto = false" significa que sí se pueden enseñar en la web.
        // Además solo interesan partidos donde intervenga un equipo local del Bellreguard.
        $partidosClub = Partido::with(['equipoLocal', 'equipoVisitante', 'estadisticas'])
            ->where('oculto', false)
            ->where(function ($query) {
                $query->whereHas('equipoLocal', function ($teamQuery) {
                    $teamQuery->where('es_local', true);
                })->orWhereHas('equipoVisitante', function ($teamQuery) {
                    $teamQuery->where('es_local', true);
                });
            })
            ->orderBy('fecha_hora', 'asc')
            ->get();

        // La portada prioriza al Senior como equipo principal del club.
        $partidosSenior = $partidosClub->filter(fn (Partido $partido) => $this->matchIncludesPreferredLocalTeam(
            $partido,
            ['CB Bellreguard Senior'],
            ['Senior']
        ));

        // Si el Senior no tiene datos útiles, se cae al Senior B.
        $partidosSeniorB = $partidosClub->filter(fn (Partido $partido) => $this->matchIncludesPreferredLocalTeam(
            $partido,
            ['CB Bellreguard Senior B'],
            ['Senior B']
        ));

        // Esta función local busca el siguiente partido pendiente de jugar o sin resultado final.
        // Para considerarlo "pendiente", al menos uno de los dos equipos debe seguir sin puntos_totales.
        $resolverProximoPartido = function ($partidos) use ($ahora) {
            return $partidos
                ->filter(function (Partido $partido) use ($ahora) {
                    $statsLocal = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_local_id);
                    $statsVisitante = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_visitante_id);
                    $sinResultadoFinal = optional($statsLocal)->puntos_totales === null
                        || optional($statsVisitante)->puntos_totales === null;

                    return $sinResultadoFinal
                        && Carbon::parse($partido->fecha_hora)->greaterThanOrEqualTo($ahora);
                })
                ->sortBy('fecha_hora')
                ->first();
        };

        // Esta otra función local hace lo contrario:
        // busca el último partido que sí esté cerrado y tenga marcador definitivo.
        $resolverUltimoResultado = function ($partidos) {
            return $partidos
                ->filter(function (Partido $partido) {
                    $statsLocal = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_local_id);
                    $statsVisitante = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_visitante_id);

                    return optional($statsLocal)->puntos_totales !== null
                        && optional($statsVisitante)->puntos_totales !== null;
                })
                ->sortByDesc('fecha_hora')
                ->first();
        };

        $proximoPartido = $resolverProximoPartido($partidosSenior)
            ?? $resolverProximoPartido($partidosSeniorB);

        $ultimoResultado = $resolverUltimoResultado($partidosSenior)
            ?? $resolverUltimoResultado($partidosSeniorB);

        $equiposActivos = Equipo::where('es_local', true)
            ->where('oculto', false)
            ->count();

        $jugadoresActivos = Jugador::where('oculto', false)
            ->whereHas('equipo', function ($query) {
                $query->where('es_local', true)->where('oculto', false);
            })
            ->count();

        // Estas cifras alimentan las tarjetas resumen de la portada.
        // No son texto fijo: salen de la base de datos en tiempo real.
        $clubStats = collect([
            [
                'valor' => $equiposActivos,
                'etiqueta' => 'Equipos activos',
                'texto' => 'Categorías y grupos del club con presencia pública en la web.',
            ],
            [
                'valor' => $jugadoresActivos,
                'etiqueta' => 'Jugadores visibles',
                'texto' => 'Plantilla actual mostrada entre todos los equipos del Bellreguard.',
            ],
            [
                'valor' => $partidosClub->count(),
                'etiqueta' => 'Partidos publicados',
                'texto' => 'Encuentros del club disponibles para consultar en calendario y resultados.',
            ],
            [
                'valor' => Noticia::where('oculto', false)->count(),
                'etiqueta' => 'Noticias activas',
                'texto' => 'Contenido del club ya publicado para seguir la actualidad del proyecto.',
            ],
        ]);

        $galeriasDestacadas = Galeria::with('imagenes')
            ->withCount('imagenes')
            ->where('oculto', false)
            ->latest()
            ->take(3)
            ->get();

        $clasificacionInicio = null;
        $clasificacionInicioFilas = collect();
        $clasificacionInicioFilaClub = null;

        // Se intenta enseñar primero la clasificación marcada como "activa".
        // Si hubiera varias, esta consulta prioriza la activa y luego ordena por nombre.
        $clasificacionActiva = Clasificacion::orderByDesc('activa')->orderBy('nombre')->first();

        if ($clasificacionActiva) {
            $jornadaClasificacionInicio = ClasificacionFila::where('clasificacion_id', $clasificacionActiva->id)
                ->whereNotNull('jornada')
                ->max('jornada');

            $filasClasificacionInicio = ClasificacionFila::with('equipo')
                ->where('clasificacion_id', $clasificacionActiva->id)
                ->when($jornadaClasificacionInicio, function ($query) use ($jornadaClasificacionInicio) {
                    $query->where('jornada', $jornadaClasificacionInicio);
                })
                ->orderByDesc('pts')
                ->orderByDesc('diff')
                ->orderByDesc('pf')
                ->get()
                ->values();

            if ($filasClasificacionInicio->isNotEmpty()) {
                // Aquí se añade una posición manual (1, 2, 3, ...)
                // porque en la base de datos no existe una columna "posicion".
                // La posición se deduce del orden final de las filas.
                $filasConPosicion = $filasClasificacionInicio->map(function (ClasificacionFila $fila, int $index) {
                    $fila->setAttribute('posicion_home', $index + 1);
                    return $fila;
                });

                $clasificacionInicio = $clasificacionActiva;
                $clasificacionInicioFilas = $filasConPosicion->take(5)->values();
                $clasificacionInicioFilaClub = $filasConPosicion->first(function (ClasificacionFila $fila) {
                    return (bool) optional($fila->equipo)->es_local
                        || str_contains(strtolower((string) optional($fila->equipo)->nombre), 'bellreguard');
                });

                if (
                    $clasificacionInicioFilaClub
                    && !$clasificacionInicioFilas->contains(fn (ClasificacionFila $fila) => $fila->id === $clasificacionInicioFilaClub->id)
                ) {
                    $clasificacionInicioFilas = $clasificacionInicioFilas
                        ->concat([$clasificacionInicioFilaClub])
                        ->values();
                }
            }
        }

        $noticias = Noticia::where('oculto', false)->latest()->take(6)->get();

        // Al final, todo este bloque de datos se manda a la vista "basket.inicio".
        // La vista ya no tiene que calcular nada complejo; solo mostrarlo.
        return view('basket.inicio', compact(
            'noticias',
            'proximoPartido',
            'ultimoResultado',
            'clubStats',
            'galeriasDestacadas',
            'clasificacionInicio',
            'clasificacionInicioFilas',
            'clasificacionInicioFilaClub'
        ));
    }

    /*
     * PÁGINA DE EQUIPO
     *
     * Objetivo:
     * mostrar los equipos del club por categoría y, dentro de la categoría elegida,
     * listar sus jugadores visibles.
     *
     * Flujo:
     * 1. sacar equipos locales visibles
     * 2. obtener categorías disponibles
     * 3. leer la categoría pedida por la URL
     * 4. elegir el equipo de esa categoría
     * 5. paginar sus jugadores
     */
    public function equipo(Request $request)
    {
        $equiposLocales = Equipo::where('es_local', true)
            ->where('oculto', false)
            ->whereHas('jugadores', function ($query) {
                $query->where('oculto', false);
            })
            ->get();

        $equiposLocales = $this->sortTeamsByCategoryPriority($equiposLocales);

        // De la colección de equipos se extraen solo las categorías.
        // Luego se eliminan duplicados para poder construir el selector.
        $categorias = $this->sortCategoriesByPriority($equiposLocales
            ->pluck('categoria')
            ->filter()
            ->unique()
            ->values());

        // La categoría llega por query string, por ejemplo:
        // /equipo?categoria=Senior
        // Si no llega ninguna o no existe, se usa la primera categoría válida.
        $categoriaSel = (string) $request->get('categoria', '');
        if ($categoriaSel === '' || !$categorias->contains($categoriaSel)) {
            $categoriaSel = 'Senior';
            if (!$categorias->contains($categoriaSel)) {
                $categoriaSel = (string) ($categorias->first() ?? 'Senior');
            }
        }

        $equipoMostrar = $equiposLocales
            ->where('categoria', $categoriaSel)
            ->first();

        $jugadores = null;
        if ($equipoMostrar) {
            $jugadores = $equipoMostrar->jugadores()
                ->where('oculto', false)
                ->orderBy('dorsal')
                ->paginate(9)
                ->withQueryString();
        }

        return view('basket.equipo', compact(
            'categorias',
            'equipoMostrar',
            'categoriaSel',
            'jugadores'
        ));
    }

    /*
     * FICHA PÚBLICA DE JUGADOR
     *
     * Enseña una página individual del jugador siempre que:
     * - el jugador sea visible
     * - pertenezca a un equipo local
     * - ese equipo también esté visible
     *
     * Además carga otros jugadores del mismo equipo para dejar
     * una navegación lateral sencilla dentro de la categoría.
     */
    public function jugadorDetalle(Jugador $jugador)
    {
        $jugador->load('equipo');

        abort_if(
            $jugador->oculto
            || !$jugador->equipo
            || !$jugador->equipo->es_local
            || $jugador->equipo->oculto,
            404
        );

        $companeros = Jugador::where('equipo_id', $jugador->equipo_id)
            ->where('oculto', false)
            ->whereKeyNot($jugador->id)
            ->orderBy('dorsal')
            ->take(4)
            ->get();

        return view('basket.jugador_detalle', compact('jugador', 'companeros'));
    }

    /*
     * TIENDA
     *
     * Método sencillo:
     * - carga productos visibles
     * - los ordena alfabéticamente
     * - los pagina de 9 en 9
     * - los envía a la vista pública
     */
    public function tienda()
    {
        $merchandising = Merchandising::where('oculto', false)
            ->orderBy('nombre', 'asc')
            ->paginate(9);

        $cartItems = $this->getCartItems();
        $cartSummary = $this->buildCartSummary($cartItems);
        $openCart = session('openCart', false);

        return view('basket.merchandising', compact('merchandising', 'cartItems', 'cartSummary', 'openCart'));
    }

    /*
     * FORMULARIO FINAL DEL PEDIDO
     *
     * Este método enseña una página de resumen con:
     * - productos de la cesta
     * - subtotal orientativo
     * - formulario de datos del cliente
     *
     * Si la cesta está vacía, devuelve al usuario a la tienda.
     */
    public function mostrarFormularioPedido()
    {
        $cartItems = $this->getCartItems();

        if (empty($cartItems)) {
            return redirect()->route('basket.tienda')->withErrors([
                'cesta' => 'Tu cesta está vacía. Añade al menos un producto antes de solicitar el pedido.',
            ]);
        }

        $cartSummary = $this->buildCartSummary($cartItems);

        return view('basket.merchandising_pedido', compact('cartItems', 'cartSummary'));
    }

    /*
     * AÑADIR PRODUCTO A LA CESTA
     *
     * Flujo:
     * 1. valida producto, talla y cantidad
     * 2. comprueba que el producto exista y sea visible
     * 3. valida la talla según las tallas reales del producto
     * 4. guarda o acumula la línea en sesión
     * 5. vuelve a la tienda abriendo el panel lateral
     */
    public function anadirProductoCesta(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|integer',
            'talla' => 'nullable|string|max:20',
            'cantidad' => 'required|integer|min:1|max:20',
        ]);

        $producto = Merchandising::whereKey($validated['producto_id'])
            ->where('oculto', false)
            ->firstOrFail();

        $tallasDisponibles = $this->extractAvailableSizes($producto);
        $tallaSeleccionada = trim((string) ($validated['talla'] ?? ''));

        if (!empty($tallasDisponibles) && !in_array($tallaSeleccionada, $tallasDisponibles, true)) {
            return back()->withErrors([
                'talla' => 'Selecciona una talla válida para este producto.',
            ])->withInput();
        }

        if (empty($tallasDisponibles)) {
            $tallaSeleccionada = 'Sin talla';
        }

        $cart = session()->get('basket_cart', []);
        $itemKey = $this->makeCartItemKey($producto->id, $tallaSeleccionada);

        if (isset($cart[$itemKey])) {
            $cart[$itemKey]['cantidad'] = min(20, $cart[$itemKey]['cantidad'] + (int) $validated['cantidad']);
        } else {
            $cart[$itemKey] = [
                'producto_id' => $producto->id,
                'nombre' => $producto->nombre,
                'talla' => $tallaSeleccionada,
                'precio' => (float) $producto->precio,
                'cantidad' => (int) $validated['cantidad'],
                'imagen_url' => $producto->imagen_merchan_url,
            ];
        }

        session()->put('basket_cart', $cart);
        session()->flash('openCart', true);

        return redirect()->route('basket.tienda')->with('mensaje', 'Producto añadido a la cesta.');
    }

    /*
     * ACTUALIZAR CANTIDAD DE LA CESTA
     *
     * Solo modifica una línea ya existente en sesión.
     * Si la línea no existe, simplemente vuelve a la tienda.
     */
    public function actualizarProductoCesta(Request $request, string $itemKey)
    {
        $validated = $request->validate([
            'cantidad' => 'required|integer|min:1|max:20',
        ]);

        $cart = session()->get('basket_cart', []);

        if (!isset($cart[$itemKey])) {
            return redirect()->route('basket.tienda')->withErrors([
                'cesta' => 'El producto que intentas actualizar ya no está en la cesta.',
            ]);
        }

        $cart[$itemKey]['cantidad'] = (int) $validated['cantidad'];

        session()->put('basket_cart', $cart);
        session()->flash('openCart', true);

        return redirect()->route('basket.tienda');
    }

    /*
     * ELIMINAR PRODUCTO DE LA CESTA
     *
     * Borra una línea concreta de la cesta en sesión y
     * mantiene abierto el panel lateral al volver a la tienda.
     */
    public function eliminarProductoCesta(string $itemKey)
    {
        $cart = session()->get('basket_cart', []);

        unset($cart[$itemKey]);

        session()->put('basket_cart', $cart);
        session()->flash('openCart', true);

        return redirect()->route('basket.tienda')->with('mensaje', 'Producto eliminado de la cesta.');
    }

    /*
     * ENVIAR PEDIDO DE MERCHANDISING
     *
     * Este método cierra el flujo público de tienda:
     * 1. valida los datos del cliente
     * 2. comprueba que la cesta siga teniendo productos
     * 3. construye el resumen económico
     * 4. envía un correo al club
     * 5. vacía la cesta y redirige a la tienda
     */
    public function enviarPedidoMerchan(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellidos' => 'required|string|max:150',
            'telefono' => 'required|string|max:30',
            'correo' => 'required|email|max:150',
            'notas' => 'nullable|string|max:1000',
        ]);

        $cartItems = $this->getCartItems();

        if (empty($cartItems)) {
            return redirect()->route('basket.tienda')->withErrors([
                'cesta' => 'Tu cesta está vacía. Añade productos antes de enviar una solicitud.',
            ]);
        }

        $cartSummary = $this->buildCartSummary($cartItems);
        $destinatario = env('MERCH_PEDIDOS_EMAIL', env('MERCH_INTERES_EMAIL', 'paupalopvila@gmail.com'));

        Mail::to($destinatario)->send(
            new MerchandisingPedidoMail($validated, $cartItems, $cartSummary)
        );

        session()->forget('basket_cart');

        return redirect()->route('basket.tienda')->with(
            'mensaje',
            'Tu solicitud de pedido se ha enviado correctamente. Te responderemos lo antes posible.'
        );
    }

    /*
     * CLASIFICACIÓN PÚBLICA
     *
     * Este método permite ver una clasificación de dos formas:
     * - modo general: la tabla más actual disponible
     * - modo jornada: una jornada concreta
     *
     * Además de la tabla, también carga los partidos de la jornada seleccionada.
     */
    public function clasificacion()
    {
        $clasificaciones = Clasificacion::orderByDesc('activa')->orderBy('nombre')->get();
        $clasificacionSeleccionada = null;

        if ($clasificaciones->isNotEmpty()) {
            $clasificacionId = request('clasificacion_id');

            if ($clasificacionId) {
                $clasificacionSeleccionada = $clasificaciones->firstWhere('id', (int) $clasificacionId);
            }

            if (!$clasificacionSeleccionada) {
                $clasificacionSeleccionada = $clasificaciones->firstWhere('activa', true) ?: $clasificaciones->first();
            }
        }

        $jornadas = collect();
        $jornadaSeleccionada = null;
        $jornadaClasificacionMostrada = null;
        $modoGeneral = true;
        $filas = collect();
        $partidosJornada = collect();

        if ($clasificacionSeleccionada) {
            // Las jornadas se sacan de dos sitios:
            // - de las filas históricas de clasificación
            // - de los partidos de liga asociados a esa clasificación
            // Luego se mezclan y se limpian duplicados.
            $jornadasClasificacion = ClasificacionFila::where('clasificacion_id', $clasificacionSeleccionada->id)
                ->whereNotNull('jornada')
                ->distinct()
                ->orderBy('jornada')
                ->pluck('jornada');

            $jornadasPartidos = Partido::where('clasificacion_id', $clasificacionSeleccionada->id)
                ->where('tipo_partido', 'liga')
                ->whereNotNull('jornada')
                ->where('oculto_en_clasificacion', false)
                ->distinct()
                ->orderBy('jornada')
                ->pluck('jornada');

            $jornadas = $jornadasClasificacion
                ->merge($jornadasPartidos)
                ->map(fn ($jornada) => (int) $jornada)
                ->filter(fn ($jornada) => $jornada > 0)
                ->unique()
                ->sort()
                ->values();

            $jornadaParam = request('jornada');
            if ($jornadaParam !== null && $jornadaParam !== '' && $jornadaParam !== 'general') {
                $jornadaSeleccionada = (int) $jornadaParam;
                $modoGeneral = $jornadaSeleccionada <= 0;
            }

            if ($modoGeneral) {
                // Si el usuario no ha pedido una jornada concreta,
                // se enseña la última clasificación disponible.
                $jornadaClasificacionMostrada = ClasificacionFila::where('clasificacion_id', $clasificacionSeleccionada->id)
                    ->whereNotNull('jornada')
                    ->max('jornada');
            } else {
                $jornadaClasificacionMostrada = ClasificacionFila::where('clasificacion_id', $clasificacionSeleccionada->id)
                    ->whereNotNull('jornada')
                    ->where('jornada', '<=', $jornadaSeleccionada)
                    ->max('jornada');

                if (!$jornadaClasificacionMostrada) {
                    $jornadaClasificacionMostrada = ClasificacionFila::where('clasificacion_id', $clasificacionSeleccionada->id)
                        ->whereNotNull('jornada')
                        ->max('jornada');
                }

                if ($jornadaClasificacionMostrada) {
                    $jornadaClasificacionMostrada = (int) $jornadaClasificacionMostrada;
                }

            }

            // La tabla se ordena como suele hacerse en una liga:
            // 1. más puntos
            // 2. mejor diferencia de puntos
            // 3. más puntos a favor
            $filas = ClasificacionFila::with('equipo')
                ->where('clasificacion_id', $clasificacionSeleccionada->id)
                ->when($jornadaClasificacionMostrada, function ($query) use ($jornadaClasificacionMostrada) {
                    $query->where('jornada', $jornadaClasificacionMostrada);
                })
                ->orderByDesc('pts')
                ->orderByDesc('diff')
                ->orderByDesc('pf')
                ->get();

            $partidosJornada = Partido::with(['equipoLocal', 'equipoVisitante', 'estadisticas'])
                ->where('clasificacion_id', $clasificacionSeleccionada->id)
                ->where('tipo_partido', 'liga')
                ->when(!$modoGeneral, function ($query) use ($jornadaSeleccionada) {
                    $query->where('jornada', $jornadaSeleccionada);
                })
                ->where('oculto_en_clasificacion', false)
                ->orderBy('jornada')
                ->orderBy('fecha_hora')
                ->get();
        }

        return view('basket.clasificacion', compact(
            'clasificaciones',
            'clasificacionSeleccionada',
            'jornadas',
            'jornadaSeleccionada',
            'jornadaClasificacionMostrada',
            'modoGeneral',
            'filas',
            'partidosJornada'
        ));
    }

    /*
     * ESTADÍSTICAS
     *
     * Este es uno de los métodos más "numéricos" del controlador.
     * Su función es construir un resumen de temporada para un equipo local.
     *
     * Qué calcula:
     * - partidos jugados
     * - victorias y derrotas
     * - puntos totales
     * - medias por partido
     * - porcentajes de tiro
     * - rebotes, asistencias, robos, pérdidas, etc.
     * - puntos por cuarto y prórroga
     *
     * Importante:
     * no guarda nada nuevo en base de datos.
     * Solo lee datos existentes y construye un resumen para mostrarlo.
     */
    public function estadisticas(Request $request)
    {
        $equiposLocales = Equipo::where('es_local', true)
            ->where('oculto', false)
            ->get(['id', 'nombre', 'categoria', 'imagen_club']);

        $equiposLocales = $this->sortTeamsByCategoryPriority($equiposLocales);

        $equipoSeleccionado = null;
        $resumenTemporada = null;

        if ($equiposLocales->isNotEmpty()) {
            $equipoId = $request->input('equipo_id');
            if ($equipoId) {
                $equipoSeleccionado = $equiposLocales->firstWhere('id', (int) $equipoId);
            }
            if (!$equipoSeleccionado) {
                $equipoSeleccionado = $this->resolvePreferredLocalTeam(
                    $equiposLocales,
                    ['CB Bellreguard Senior'],
                    ['Senior']
                ) ?: $this->resolvePreferredLocalTeam(
                    $equiposLocales,
                    ['CB Bellreguard Senior B'],
                    ['Senior B']
                ) ?: $equiposLocales->first();
            }
        }

        if ($equipoSeleccionado) {
            // Solo cuentan partidos de liga que ya tengan resultado final.
            // Si un partido no está cerrado, no debe alterar las estadísticas.
            $partidosLigaCompletados = Partido::with('estadisticas')
                ->where('tipo_partido', 'liga')
                ->whereNotNull('jornada')
                ->where(function ($query) use ($equipoSeleccionado) {
                    $query->where('equipo_local_id', $equipoSeleccionado->id)
                        ->orWhere('equipo_visitante_id', $equipoSeleccionado->id);
                })
                ->get()
                ->filter(function (Partido $partido) {
                    $statsLocal = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_local_id);
                    $statsVisitante = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_visitante_id);

                    return optional($statsLocal)->puntos_totales !== null
                        && optional($statsVisitante)->puntos_totales !== null;
                })
                ->values();

            // Esta consulta suma todas las estadísticas del equipo en la temporada.
            // selectRaw permite pedir muchos SUM(...) y COUNT(...) en una sola consulta.
            $stats = PartidoEstadistica::query()
                ->selectRaw('
                    COUNT(partido_estadisticas.id) as pj,
                    SUM(partido_estadisticas.fga) as fga,
                    SUM(partido_estadisticas.fgm) as fgm,
                    SUM(partido_estadisticas.t2a) as t2a,
                    SUM(partido_estadisticas.t2m) as t2m,
                    SUM(partido_estadisticas.t3a) as t3a,
                    SUM(partido_estadisticas.t3m) as t3m,
                    SUM(partido_estadisticas.fta) as fta,
                    SUM(partido_estadisticas.ftm) as ftm,
                    SUM(partido_estadisticas.reb_of) as reb_of,
                    SUM(partido_estadisticas.reb_def) as reb_def,
                    SUM(partido_estadisticas.ast) as ast,
                    SUM(partido_estadisticas.stl) as stl,
                    SUM(partido_estadisticas.blk) as blk,
                    SUM(partido_estadisticas.tov) as tov,
                    SUM(partido_estadisticas.pf) as pf,
                    SUM(partido_estadisticas.tf) as tf,
                    SUM(partido_estadisticas.puntos_totales) as pts
                ')
                ->join('partidos', 'partidos.id', '=', 'partido_estadisticas.partido_id')
                ->where('partidos.tipo_partido', 'liga')
                ->whereNotNull('partidos.jornada')
                ->where('partido_estadisticas.equipo_id', $equipoSeleccionado->id)
                ->whereNotNull('partido_estadisticas.puntos_totales')
                ->first();

            // Aquí se agrupan los puntos por periodo:
            // Q1, Q2, Q3, Q4 y posible prórroga.
            $cuartosRaw = PartidoCuarto::query()
                ->selectRaw('periodo, SUM(puntos) as puntos')
                ->join('partidos', 'partidos.id', '=', 'partido_cuartos.partido_id')
                ->where('partidos.tipo_partido', 'liga')
                ->whereNotNull('partidos.jornada')
                ->where('partido_cuartos.equipo_id', $equipoSeleccionado->id)
                ->groupBy('periodo')
                ->pluck('puntos', 'periodo');

            $prorrogasRaw = PartidoCuarto::query()
                ->selectRaw('SUM(puntos) as puntos, COUNT(DISTINCT partido_cuartos.partido_id) as partidos')
                ->join('partidos', 'partidos.id', '=', 'partido_cuartos.partido_id')
                ->where('partidos.tipo_partido', 'liga')
                ->whereNotNull('partidos.jornada')
                ->where('partido_cuartos.equipo_id', $equipoSeleccionado->id)
                ->where('partido_cuartos.periodo', 5)
                ->first();

            // A partir de aquí se convierten a enteros los datos agregados
            // para poder operar con ellos fácilmente y evitar nulos.
            $pj = (int) ($stats->pj ?? 0);
            $divisor = $pj > 0 ? $pj : 1;

            $fga = (int) ($stats->fga ?? 0);
            $fgm = (int) ($stats->fgm ?? 0);
            $t2a = (int) ($stats->t2a ?? 0);
            $t2m = (int) ($stats->t2m ?? 0);
            $t3a = (int) ($stats->t3a ?? 0);
            $t3m = (int) ($stats->t3m ?? 0);
            $fta = (int) ($stats->fta ?? 0);
            $ftm = (int) ($stats->ftm ?? 0);
            $rebOf = (int) ($stats->reb_of ?? 0);
            $rebDef = (int) ($stats->reb_def ?? 0);
            $rebTot = $rebOf + $rebDef;
            $ast = (int) ($stats->ast ?? 0);
            $stl = (int) ($stats->stl ?? 0);
            $blk = (int) ($stats->blk ?? 0);
            $tov = (int) ($stats->tov ?? 0);
            $pf = (int) ($stats->pf ?? 0);
            $tf = (int) ($stats->tf ?? 0);
            $pts = (int) ($stats->pts ?? 0);
            $astPer = $tov > 0 ? round($ast / $tov, 2) : null;

            $q1 = (int) ($cuartosRaw[1] ?? 0);
            $q2 = (int) ($cuartosRaw[2] ?? 0);
            $q3 = (int) ($cuartosRaw[3] ?? 0);
            $q4 = (int) ($cuartosRaw[4] ?? 0);
            $pr = (int) ($cuartosRaw[5] ?? 0);
            $partidosConProrroga = (int) ($prorrogasRaw->partidos ?? 0);
            $divisorProrroga = $partidosConProrroga > 0 ? $partidosConProrroga : 1;

            // La victoria se calcula comparando el marcador del equipo
            // contra el marcador del rival en cada partido.
            $victorias = $partidosLigaCompletados->filter(function (Partido $partido) use ($equipoSeleccionado) {
                $statsEquipo = $partido->estadisticas->firstWhere('equipo_id', $equipoSeleccionado->id);
                $statsRival = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_local_id === $equipoSeleccionado->id ? $partido->equipo_visitante_id : $partido->equipo_local_id);

                return (int) optional($statsEquipo)->puntos_totales > (int) optional($statsRival)->puntos_totales;
            })->count();

            $derrotas = max(0, $pj - $victorias);
            $porcentajeVictorias = $pj > 0 ? round(($victorias / $pj) * 100, 1) : null;

            $resumenTemporada = [
                'pj' => $pj,
                'victorias' => $victorias,
                'derrotas' => $derrotas,
                'porcentaje_victorias' => $porcentajeVictorias,
                'partidos_con_prorroga' => $partidosConProrroga,
                'totales' => [
                    'pts' => $pts,
                    'fga' => $fga,
                    'fgm' => $fgm,
                    'fg_fallados' => max(0, $fga - $fgm),
                    't2a' => $t2a,
                    't2m' => $t2m,
                    't2_fallados' => max(0, $t2a - $t2m),
                    't3a' => $t3a,
                    't3m' => $t3m,
                    't3_fallados' => max(0, $t3a - $t3m),
                    'fta' => $fta,
                    'ftm' => $ftm,
                    'ft_fallados' => max(0, $fta - $ftm),
                    'reb' => $rebTot,
                    'reb_of' => $rebOf,
                    'reb_def' => $rebDef,
                    'ast' => $ast,
                    'stl' => $stl,
                    'blk' => $blk,
                    'tov' => $tov,
                    'pf' => $pf,
                    'tf' => $tf,
                    'q1' => $q1,
                    'q2' => $q2,
                    'q3' => $q3,
                    'q4' => $q4,
                    'pr' => $pr,
                ],
                'medias' => [
                    'pts' => round($pts / $divisor, 1),
                    'reb' => round($rebTot / $divisor, 1),
                    'reb_of' => round($rebOf / $divisor, 1),
                    'reb_def' => round($rebDef / $divisor, 1),
                    'ast' => round($ast / $divisor, 1),
                    'stl' => round($stl / $divisor, 1),
                    'blk' => round($blk / $divisor, 1),
                    'tov' => round($tov / $divisor, 1),
                    'pf' => round($pf / $divisor, 1),
                    'tf' => round($tf / $divisor, 1),
                    'q1' => round($q1 / $divisor, 1),
                    'q2' => round($q2 / $divisor, 1),
                    'q3' => round($q3 / $divisor, 1),
                    'q4' => round($q4 / $divisor, 1),
                    'pr' => round($pr / $divisorProrroga, 1),
                ],
                'porcentajes' => [
                    'fg' => $fga > 0 ? round(($fgm / $fga) * 100, 1) : null,
                    't2' => $t2a > 0 ? round(($t2m / $t2a) * 100, 1) : null,
                    't3' => $t3a > 0 ? round(($t3m / $t3a) * 100, 1) : null,
                    'ft' => $fta > 0 ? round(($ftm / $fta) * 100, 1) : null,
                ],
                'ratios' => [
                    'ast_per' => $astPer,
                ],
            ];
        }

        return view('basket.estadisticas', compact('equiposLocales', 'equipoSeleccionado', 'resumenTemporada'));
    }

    // Helper: comprueba si en el partido participa alguno de los equipos prioritarios
    // que queremos destacar, por ejemplo Senior o Senior B.
    private function matchIncludesPreferredLocalTeam(Partido $partido, array $preferredNames, array $preferredCategories): bool
    {
        foreach ([$partido->equipoLocal, $partido->equipoVisitante] as $equipo) {
            if (!$equipo || !$equipo->es_local) {
                continue;
            }

            if ($this->teamMatchesPreference($equipo, $preferredNames, $preferredCategories)) {
                return true;
            }
        }

        return false;
    }

    // Helper: devuelve el primer equipo local que coincida
    // con nombres o categorías preferidas.
    private function resolvePreferredLocalTeam($equiposLocales, array $preferredNames, array $preferredCategories): ?Equipo
    {
        return $equiposLocales->first(
            fn (Equipo $equipo) => $this->teamMatchesPreference($equipo, $preferredNames, $preferredCategories)
        );
    }

    // Helper simple para reutilizar la comparación de nombre/categoría.
    private function teamMatchesPreference(Equipo $equipo, array $preferredNames, array $preferredCategories): bool
    {
        return in_array((string) $equipo->nombre, $preferredNames, true)
            || in_array((string) $equipo->categoria, $preferredCategories, true);
    }

    // Orden visual común: primero el Senior principal, después el resto de categorías.
    private function categoryPriorityList(): array
    {
        return ['Senior', 'Senior B', 'Senior C', 'Senior D', 'Junior', 'Junior A', 'Junior B', 'Cadete', 'Cadete A', 'Cadete B', 'Infantil', 'Infantil A', 'Infantil B', 'Alevín', 'Alevin', 'Benjamín', 'Benjamin', 'Prebenjamín', 'Prebenjamin'];
    }

    private function categoryPriorityIndex(?string $categoria): int
    {
        $categoria = (string) $categoria;
        $priorityList = $this->categoryPriorityList();
        $index = array_search($categoria, $priorityList, true);

        return $index !== false ? $index : count($priorityList);
    }

    private function sortCategoriesByPriority($categorias)
    {
        return collect($categorias)
            ->sortBy(fn ($categoria) => [$this->categoryPriorityIndex($categoria), (string) $categoria])
            ->values();
    }

    private function sortTeamsByCategoryPriority($equipos)
    {
        return collect($equipos)
            ->sortBy(fn (Equipo $equipo) => [$this->categoryPriorityIndex($equipo->categoria), (string) $equipo->nombre])
            ->values();
    }

    /*
     * PARTIDOS
     *
     * Muestra el calendario/listado público de partidos del club.
     * Igual que en portada, solo enseña partidos visibles en los que participa
     * al menos un equipo local del Bellreguard.
     */
    public function partidos()
    {
        $ahora = Carbon::now()->toDateTimeString();

        $partidos = Partido::with(['equipoLocal', 'equipoVisitante', 'estadisticas'])
            ->where('oculto', false)
            ->where(function ($query) {
                $query->whereHas('equipoLocal', function ($teamQuery) {
                    $teamQuery->where('es_local', true);
                })->orWhereHas('equipoVisitante', function ($teamQuery) {
                    $teamQuery->where('es_local', true);
                });
            })
            // Primero se muestran los próximos encuentros; después, el histórico ya jugado.
            ->orderByRaw('CASE WHEN fecha_hora >= ? THEN 0 ELSE 1 END', [$ahora])
            ->orderByRaw('CASE WHEN fecha_hora >= ? THEN fecha_hora END ASC', [$ahora])
            ->orderByRaw('CASE WHEN fecha_hora < ? THEN fecha_hora END DESC', [$ahora])
            ->paginate(10);
        return view('basket.partidos', compact('partidos'));
    }

    /*
     * DETALLE DE PARTIDO
     *
     * Carga un partido concreto con:
     * - equipo local
     * - equipo visitante
     * - estadísticas generales
     * - puntos por cuarto
     *
     * Después separa las estadísticas del local y del visitante
     * para que la vista pueda enseñarlas con claridad.
     */
    public function partidoDetalle($id)
    {
        $partido = Partido::with([
            'equipoLocal',
            'equipoVisitante',
            'estadisticas',
            'cuartos',
        ])
            ->where('oculto', false)
            ->where(function ($query) {
                $query->whereHas('equipoLocal', function ($teamQuery) {
                    $teamQuery->where('es_local', true);
                })->orWhereHas('equipoVisitante', function ($teamQuery) {
                    $teamQuery->where('es_local', true);
                });
            })
            ->whereKey($id)
            ->firstOrFail();

        $statsLocal = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_local_id);
        $statsVisitante = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_visitante_id);

        $this->attachQuarterStats($partido, $statsLocal, (int) $partido->equipo_local_id);
        $this->attachQuarterStats($partido, $statsVisitante, (int) $partido->equipo_visitante_id);

        return view('basket.partido_detalle', compact('partido', 'statsLocal', 'statsVisitante'));
    }

    // Cambia el booleano "oculto" de un equipo.
    // Si estaba visible, lo oculta. Si estaba oculto, lo vuelve a mostrar.
    public function toggleOcultoEquipo(Equipo $equipo)
    {
        $equipo->oculto = !$equipo->oculto;
        $equipo->save();

        return redirect()->route('basket.equipo')->with('mensaje', $equipo->oculto ? 'Se ha ocultado correctamente.' : 'Se ha mostrado correctamente.');
    }

    // Igual que el anterior, pero aplicado a jugadores.
    public function toggleOcultoJugador(Jugador $jugador)
    {
        $jugador->oculto = !$jugador->oculto;
        $jugador->save();

        return redirect()->route('basket.equipo', ['categoria' => optional($jugador->equipo)->categoria])->with('mensaje', $jugador->oculto ? 'Se ha ocultado correctamente.' : 'Se ha mostrado correctamente.');
    }

    // Igual que el anterior, pero aplicado a partidos públicos.
    public function toggleOcultoPartido(Partido $partido)
    {
        $partido->oculto = !$partido->oculto;
        $partido->save();

        $mensaje = $partido->oculto
            ? 'Se ha ocultado correctamente.'
            : 'Se ha mostrado correctamente.';

        return redirect()->route('basket.partidos')->with('mensaje', $mensaje);
    }

    /*
     * GALERÍA
     *
     * Lista las galerías públicas y permite filtrarlas por tipo.
     * Ejemplo de tipo: partidos, entrenamientos, eventos...
     */
    public function galeria()
    {
        $tipoSeleccionado = request('tipo', 'todos');
        $tipos = ['todos' => 'Todos'] + Galeria::TIPOS;

        $galerias = Galeria::with('imagenes')
            ->withCount('imagenes')
            ->where('oculto', false)
            ->when($tipoSeleccionado !== 'todos', function ($query) use ($tipoSeleccionado) {
                $query->where('tipo', $tipoSeleccionado);
            })
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('basket.galeria', compact('galerias', 'tipos', 'tipoSeleccionado'));
    }

    // Busca una galería concreta a partir del slug que llega en la URL.
    // Ejemplo: /galeria/torneo-infantil-2026
    public function galeriaDetalle($slug)
    {
        $galeria = Galeria::with('imagenes')
            ->where('oculto', false)
            ->where('slug', $slug)
            ->firstOrFail();

        return view('basket.galeria_detalle', compact('galeria'));
    }

    // Devuelve una vista estática de contacto.
    public function contacto()
    {
        return view('basket.contacto');
    }

    // Lista noticias visibles, ordenadas de más nuevas a más antiguas.
    public function noticia()
    {
        $noticias = Noticia::where('oculto', false)
            ->latest()
            ->paginate(5);
        return view('basket.noticias', compact('noticias'));
    }

    // Carga una noticia concreta usando su slug en lugar de su id.
    public function noticia_detalle($slug)
    {
        $noticia = Noticia::where('oculto', false)
            ->where('slug', $slug)
            ->firstOrFail();

        return view('basket.noticias_detalle', compact('noticia'));
    }

    // Alterna visible/oculto en noticias.
    public function toggleOcultoNoticia(Noticia $noticia)
    {
        $noticia->oculto = !$noticia->oculto;
        $noticia->save();

        return redirect()->route('basket.noticias')->with('mensaje', $noticia->oculto ? 'Se ha ocultado correctamente.' : 'Se ha mostrado correctamente.');
    }

    // Alterna visible/oculto en productos de merchandising.
    public function toggleOcultoMerchandising(Merchandising $merchandising)
    {
        $merchandising->oculto = !$merchandising->oculto;
        $merchandising->save();

        return redirect()->route('basket.tienda')->with('mensaje', $merchandising->oculto ? 'Se ha ocultado correctamente.' : 'Se ha mostrado correctamente.');
    }

    // Alterna visible/oculto en galerías.
    public function toggleOcultoGaleria(Galeria $galeria)
    {
        $galeria->oculto = !$galeria->oculto;
        $galeria->save();

        return redirect()->route('basket.galeria')->with('mensaje', $galeria->oculto ? 'Se ha ocultado correctamente.' : 'Se ha mostrado correctamente.');
    }

    // Este helper pega a cada bloque de estadísticas únicamente
    // los cuartos del equipo correcto, no los del partido entero mezclados.
    private function attachQuarterStats(Partido $partido, ?PartidoEstadistica $estadistica, int $equipoId): void
    {
        if (!$estadistica) {
            return;
        }

        $estadistica->setRelation(
            'cuartos',
            $partido->cuartos
                ->where('equipo_id', $equipoId)
                ->sortBy('periodo')
                ->values()
        );
    }

    /*
     * CESTA EN SESIÓN
     *
     * Devuelve las líneas guardadas en sesión como array indexado,
     * listo para pasarlo a las vistas públicas.
     */
    private function getCartItems(): array
    {
        return array_values(session()->get('basket_cart', []));
    }

    /*
     * RESUMEN DE CESTA
     *
     * Calcula:
     * - número total de unidades
     * - subtotal orientativo
     */
    private function buildCartSummary(array $cartItems): array
    {
        $units = 0;
        $subtotal = 0.0;

        foreach ($cartItems as $item) {
            $cantidad = (int) ($item['cantidad'] ?? 0);
            $precio = (float) ($item['precio'] ?? 0);

            $units += $cantidad;
            $subtotal += $cantidad * $precio;
        }

        return [
            'units' => $units,
            'subtotal' => $subtotal,
        ];
    }

    /*
     * TALLAS DISPONIBLES
     *
     * Convierte el texto guardado en base de datos a un array limpio.
     * Si el producto no tiene tallas reales, devuelve un array vacío.
     */
    private function extractAvailableSizes(Merchandising $producto): array
    {
        if (!$producto->talla) {
            return [];
        }

        return collect(explode(',', $producto->talla))
            ->map(fn ($talla) => trim((string) $talla))
            ->filter(fn ($talla) => $talla !== '' && strtolower($talla) !== 'sin talla')
            ->values()
            ->all();
    }

    /*
     * CLAVE DE LÍNEA DE CESTA
     *
     * Genera una clave estable combinando producto y talla
     * para que dos tallas distintas no se mezclen.
     */
    private function makeCartItemKey(int $productoId, string $talla): string
    {
        return $productoId . '-' . md5($talla);
    }
}
