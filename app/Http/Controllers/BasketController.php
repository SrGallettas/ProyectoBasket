<?php

namespace App\Http\Controllers;

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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class BasketController extends Controller
{
    public function inicio()
    {
        $noticias = Noticia::where('oculto', false)->latest()->take(6)->get();
        return view('basket.inicio', compact("noticias"));
    }

    public function equipo(Request $request)
    {
        // 1. Obtenemos los equipos locales para los botones
        $equiposLocales = Equipo::where('es_local', true)
            ->where('oculto', false)
            ->orderBy('categoria')
            ->get();

        // 2. Filtro por categoría (por defecto 'Senior')
        $categoriaSel = $request->get('categoria', 'Senior');

        // 3. Buscamos el equipo específico
        $equipoMostrar = Equipo::where('es_local', true)
            ->where('oculto', false)
            ->where('categoria', $categoriaSel)
            ->first();

        // 4. Paginamos los jugadores (1 por página)
        $jugadores = null;
        if ($equipoMostrar) {
            $jugadores = $equipoMostrar->jugadores()
                ->where('oculto', false)
                ->orderBy('dorsal')
                ->paginate(9)
                ->withQueryString();
        }

        return view('basket.equipo', compact('equiposLocales', 'equipoMostrar', 'categoriaSel', 'jugadores'));
    }

    public function tienda()
    {
        $merchandising = Merchandising::where('oculto', false)
            ->orderBy('nombre', 'asc')
            ->paginate(9);
        return view('basket.merchandising', compact('merchandising'));
    }

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
            $jornadasClasificacion = ClasificacionFila::where('clasificacion_id', $clasificacionSeleccionada->id)
                ->whereNotNull('jornada')
                ->distinct()
                ->orderBy('jornada')
                ->pluck('jornada');

            $jornadasPartidos = Partido::where('clasificacion_id', $clasificacionSeleccionada->id)
                ->where('tipo_partido', 'liga')
                ->whereNotNull('jornada')
                ->where('oculto', false)
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
                ->where('oculto', false)
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

    public function estadisticas(Request $request)
    {
        $equiposLocales = Equipo::where('es_local', true)
            ->where('oculto', false)
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'categoria', 'imagen_club']);

        $equipoSeleccionado = null;
        $resumenTemporada = null;

        if ($equiposLocales->isNotEmpty()) {
            $equipoId = $request->input('equipo_id');
            if ($equipoId) {
                $equipoSeleccionado = $equiposLocales->firstWhere('id', (int) $equipoId);
            }
            if (!$equipoSeleccionado) {
                $equipoSeleccionado = $equiposLocales->first();
            }
        }

        if ($equipoSeleccionado) {
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

            $cuartosRaw = PartidoCuarto::query()
                ->selectRaw('periodo, SUM(puntos) as puntos')
                ->join('partidos', 'partidos.id', '=', 'partido_cuartos.partido_id')
                ->where('partidos.tipo_partido', 'liga')
                ->whereNotNull('partidos.jornada')
                ->where('partido_cuartos.equipo_id', $equipoSeleccionado->id)
                ->groupBy('periodo')
                ->pluck('puntos', 'periodo');

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

            $resumenTemporada = [
                'pj' => $pj,
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
                    'pr' => round($pr / $divisor, 1),
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

    public function partidos()
    {
        $partidos = Partido::with(['equipoLocal', 'equipoVisitante', 'estadisticas'])
            ->where('oculto', false)
            ->orderBy('fecha_hora', 'asc')
            ->paginate(10);
        return view('basket.partidos', compact('partidos'));
    }

    public function partidoDetalle($id)
    {
        $partido = Partido::with([
            'equipoLocal',
            'equipoVisitante',
            'estadisticas.cuartos',
        ])
            ->where('oculto', false)
            ->whereKey($id)
            ->firstOrFail();

        $statsLocal = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_local_id);
        $statsVisitante = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_visitante_id);

        return view('basket.partido_detalle', compact('partido', 'statsLocal', 'statsVisitante'));
    }

    public function toggleOcultoEquipo(Equipo $equipo)
    {
        $equipo->oculto = !$equipo->oculto;
        $equipo->save();

        return redirect()->route('basket.equipo')->with('mensaje', $equipo->oculto ? 'Se ha ocultado correctamente.' : 'Se ha mostrado correctamente.');
    }

    public function toggleOcultoJugador(Jugador $jugador)
    {
        $jugador->oculto = !$jugador->oculto;
        $jugador->save();

        return redirect()->route('basket.equipo', ['categoria' => optional($jugador->equipo)->categoria])->with('mensaje', $jugador->oculto ? 'Se ha ocultado correctamente.' : 'Se ha mostrado correctamente.');
    }

    public function toggleOculto(Partido $partido)
    {
        $partido->oculto = !$partido->oculto;
        $partido->save();

        $mensaje = $partido->oculto
            ? 'Se ha ocultado correctamente.'
            : 'Se ha mostrado correctamente.';

        return redirect()->route('basket.partidos')->with('mensaje', $mensaje);
    }

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

    public function galeriaDetalle($slug)
    {
        $galeria = Galeria::with('imagenes')
            ->where('oculto', false)
            ->where('slug', $slug)
            ->firstOrFail();

        return view('basket.galeria_detalle', compact('galeria'));
    }

    public function contacto()
    {
        return view('basket.contacto');
    }

    public function noticia()
    {
        $noticias = Noticia::where('oculto', false)
            ->latest()
            ->paginate(5);
        return view('basket.noticias', compact('noticias'));
    }

    public function noticia_detalle($slug)
    {
        $noticia = Noticia::where('oculto', false)
            ->where('slug', $slug)
            ->firstOrFail();

        return view('basket.noticias_detalle', compact('noticia'));
    }

    public function toggleOcultoNoticia(Noticia $noticia)
    {
        $noticia->oculto = !$noticia->oculto;
        $noticia->save();

        return redirect()->route('basket.noticias')->with('mensaje', $noticia->oculto ? 'Se ha ocultado correctamente.' : 'Se ha mostrado correctamente.');
    }

    public function toggleOcultoMerchandising(Merchandising $merchandising)
    {
        $merchandising->oculto = !$merchandising->oculto;
        $merchandising->save();

        return redirect()->route('basket.tienda')->with('mensaje', $merchandising->oculto ? 'Se ha ocultado correctamente.' : 'Se ha mostrado correctamente.');
    }

    public function toggleOcultoGaleria(Galeria $galeria)
    {
        $galeria->oculto = !$galeria->oculto;
        $galeria->save();

        return redirect()->route('basket.galeria')->with('mensaje', $galeria->oculto ? 'Se ha ocultado correctamente.' : 'Se ha mostrado correctamente.');
    }

    public function enviarInteresMerchan(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:merchandising,id',
            'talla' => 'nullable|string|max:20',
        ]);

        $producto = Merchandising::findOrFail($validated['producto_id']);
        $talla = $validated['talla'] ?? 'Sin talla especificada';
        $tallasDisponibles = $producto->talla
            ? array_map('trim', explode(',', $producto->talla))
            : [];

        if (!empty($tallasDisponibles) && !in_array($talla, $tallasDisponibles, true)) {
            return back()->withErrors([
                'talla' => 'Selecciona una talla valida para este producto.'
            ])->withInput();
        }

        $destinatario = env('MERCH_INTERES_EMAIL', 'info@bellreguardcb.com');
        $asunto = 'Interés ' . $producto->nombre;

        $mensaje = "Hola Bellreguard CB,\n\n"
            . "Estoy interesado en el producto: {$producto->nombre}.\n"
            . "Me gustaría pedirlo en la talla: {$talla}.\n\n"
            . "Gracias.";

        Mail::raw($mensaje, function ($mail) use ($destinatario, $asunto) {
            $mail->to($destinatario)->subject($asunto);
        });

        return back()->with('mensaje', 'Tu interés se ha enviado correctamente.');
    }
}
