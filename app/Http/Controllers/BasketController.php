<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Public\ClasificacionController as PublicClasificacionController;
use App\Http\Controllers\Public\EquipoController as PublicEquipoController;
use App\Http\Controllers\Public\GaleriaController as PublicGaleriaController;
use App\Http\Controllers\Public\HomeController as PublicHomeController;
use App\Http\Controllers\Public\NoticiaController as PublicNoticiaController;
use App\Http\Controllers\Public\PartidoController as PublicPartidoController;
use App\Http\Controllers\Public\TiendaController as PublicTiendaController;
use App\Models\Equipo;
use App\Models\Galeria;
use App\Models\Jugador;
use App\Models\Merchandising;
use App\Models\Noticia;
use App\Models\Partido;
use Illuminate\Http\Request;

class BasketController extends Controller
{
    public function inicio()
    {
<<<<<<< HEAD
        return app(PublicHomeController::class)->inicio();
=======
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
                'etiqueta' => __('public.home.stats.active_teams.label'),
                'texto' => __('public.home.stats.active_teams.text'),
            ],
            [
                'valor' => $jugadoresActivos,
                'etiqueta' => __('public.home.stats.visible_players.label'),
                'texto' => __('public.home.stats.visible_players.text'),
            ],
            [
                'valor' => $partidosClub->count(),
                'etiqueta' => __('public.home.stats.published_matches.label'),
                'texto' => __('public.home.stats.published_matches.text'),
            ],
            [
                'valor' => Noticia::where('oculto', false)->count(),
                'etiqueta' => __('public.home.stats.active_news.label'),
                'texto' => __('public.home.stats.active_news.text'),
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
>>>>>>> e0ab8cf (VERSION VALENCIANO)
    }

    public function equipo(Request $request)
    {
        return app(PublicEquipoController::class)->equipo($request);
    }

    public function jugadorDetalle(Jugador $jugador)
    {
        return app(PublicEquipoController::class)->jugadorDetalle($jugador);
    }

    public function tienda()
    {
        return app(PublicTiendaController::class)->tienda();
    }

    public function mostrarFormularioPedido()
    {
<<<<<<< HEAD
        return app(PublicTiendaController::class)->mostrarFormularioPedido();
=======
        $cartItems = $this->getCartItems();

        if (empty($cartItems)) {
            return redirect()->route('basket.tienda')->withErrors([
                'cesta' => __('public.store.validation.empty_cart'),
            ]);
        }

        $cartSummary = $this->buildCartSummary($cartItems);

        return view('basket.merchandising_pedido', compact('cartItems', 'cartSummary'));
>>>>>>> e0ab8cf (VERSION VALENCIANO)
    }

    public function anadirProductoCesta(Request $request)
    {
<<<<<<< HEAD
        return app(PublicTiendaController::class)->anadirProductoCesta($request);
=======
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
                'talla' => __('public.store.validation.invalid_size'),
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
>>>>>>> e0ab8cf (VERSION VALENCIANO)
    }

    public function actualizarProductoCesta(Request $request, string $itemKey)
    {
<<<<<<< HEAD
        return app(PublicTiendaController::class)->actualizarProductoCesta($request, $itemKey);
=======
        $validated = $request->validate([
            'cantidad' => 'required|integer|min:1|max:20',
        ]);

        $cart = session()->get('basket_cart', []);

        if (!isset($cart[$itemKey])) {
            return redirect()->route('basket.tienda')->withErrors([
                'cesta' => __('public.store.validation.missing_cart_item'),
            ]);
        }

        $cart[$itemKey]['cantidad'] = (int) $validated['cantidad'];

        session()->put('basket_cart', $cart);
        session()->flash('openCart', true);

        return redirect()->route('basket.tienda');
>>>>>>> e0ab8cf (VERSION VALENCIANO)
    }

    public function eliminarProductoCesta(string $itemKey)
    {
        return app(PublicTiendaController::class)->eliminarProductoCesta($itemKey);
    }

    public function enviarPedidoMerchan(Request $request)
    {
<<<<<<< HEAD
        return app(PublicTiendaController::class)->enviarPedidoMerchan($request);
=======
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
                'cesta' => __('public.order.validation.empty_cart'),
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
>>>>>>> e0ab8cf (VERSION VALENCIANO)
    }

    public function clasificacion()
    {
        return app(PublicClasificacionController::class)->clasificacion();
    }

    public function estadisticas(Request $request)
    {
        return app(PublicClasificacionController::class)->estadisticas($request);
    }

    public function partidos()
    {
        return app(PublicPartidoController::class)->partidos();
    }

    public function partidoDetalle($id)
    {
        return app(PublicPartidoController::class)->partidoDetalle($id);
    }

    public function toggleOcultoEquipo(Equipo $equipo)
    {
        return app(PublicEquipoController::class)->toggleOcultoEquipo($equipo);
    }

    public function toggleOcultoJugador(Jugador $jugador)
    {
        return app(PublicEquipoController::class)->toggleOcultoJugador($jugador);
    }

    public function toggleOcultoPartido(Partido $partido)
    {
        return app(PublicPartidoController::class)->toggleOcultoPartido($partido);
    }

    public function galeria()
    {
        return app(PublicGaleriaController::class)->galeria();
    }

    public function galeriaDetalle($slug)
    {
        return app(PublicGaleriaController::class)->galeriaDetalle($slug);
    }

    public function contacto()
    {
        return app(PublicHomeController::class)->contacto();
    }

    public function noticia()
    {
        return app(PublicNoticiaController::class)->noticia();
    }

    public function noticia_detalle($slug)
    {
        return app(PublicNoticiaController::class)->noticia_detalle($slug);
    }

    public function toggleOcultoNoticia(Noticia $noticia)
    {
        return app(PublicNoticiaController::class)->toggleOcultoNoticia($noticia);
    }

    public function toggleOcultoMerchandising(Merchandising $merchandising)
    {
        return app(PublicTiendaController::class)->toggleOcultoMerchandising($merchandising);
    }

    public function toggleOcultoGaleria(Galeria $galeria)
    {
        return app(PublicGaleriaController::class)->toggleOcultoGaleria($galeria);
    }
}
