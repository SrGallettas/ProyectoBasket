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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class BasketController extends Controller
{
    public function inicio()
    {
        $ahora = Carbon::now();

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

        $partidosSeniorA = $partidosClub->filter(fn (Partido $partido) => $this->matchIncludesPreferredLocalTeam(
            $partido,
            ['CB Bellreguard Senior A'],
            ['Senior A']
        ));

        $partidosSeniorB = $partidosClub->filter(fn (Partido $partido) => $this->matchIncludesPreferredLocalTeam(
            $partido,
            ['CB Bellreguard Senior B'],
            ['Senior B']
        ));

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

        $proximoPartido = $resolverProximoPartido($partidosSeniorA)
            ?? $resolverProximoPartido($partidosSeniorB);

        $ultimoResultado = $resolverUltimoResultado($partidosSeniorA)
            ?? $resolverUltimoResultado($partidosSeniorB);

        $equiposActivos = Equipo::where('es_local', true)
            ->where('oculto', false)
            ->count();

        $jugadoresActivos = Jugador::where('oculto', false)
            ->whereHas('equipo', function ($query) {
                $query->where('es_local', true)->where('oculto', false);
            })
            ->count();

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

    public function equipo(Request $request)
    {
        $equiposLocales = Equipo::where('es_local', true)
            ->where('oculto', false)
            ->whereHas('jugadores', function ($query) {
                $query->where('oculto', false);
            })
            ->orderBy('categoria')
            ->get();

        $categorias = $equiposLocales
            ->pluck('categoria')
            ->filter()
            ->unique()
            ->values();

        $categoriaSel = (string) $request->get('categoria', '');
        if ($categoriaSel === '' || !$categorias->contains($categoriaSel)) {
            $categoriaSel = (string) ($categorias->first() ?? 'Senior');
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
                $equipoSeleccionado = $this->resolvePreferredLocalTeam(
                    $equiposLocales,
                    ['CB Bellreguard Senior A'],
                    ['Senior A']
                ) ?: $equiposLocales->first();
            }
        }

        if ($equipoSeleccionado) {
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

            $prorrogasRaw = PartidoCuarto::query()
                ->selectRaw('SUM(puntos) as puntos, COUNT(DISTINCT partido_cuartos.partido_id) as partidos')
                ->join('partidos', 'partidos.id', '=', 'partido_cuartos.partido_id')
                ->where('partidos.tipo_partido', 'liga')
                ->whereNotNull('partidos.jornada')
                ->where('partido_cuartos.equipo_id', $equipoSeleccionado->id)
                ->where('partido_cuartos.periodo', 5)
                ->first();

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

    private function resolvePreferredLocalTeam($equiposLocales, array $preferredNames, array $preferredCategories): ?Equipo
    {
        return $equiposLocales->first(
            fn (Equipo $equipo) => $this->teamMatchesPreference($equipo, $preferredNames, $preferredCategories)
        );
    }

    private function teamMatchesPreference(Equipo $equipo, array $preferredNames, array $preferredCategories): bool
    {
        return in_array((string) $equipo->nombre, $preferredNames, true)
            || in_array((string) $equipo->categoria, $preferredCategories, true);
    }

    public function partidos()
    {
        $partidos = Partido::with(['equipoLocal', 'equipoVisitante', 'estadisticas'])
            ->where('oculto', false)
            ->where(function ($query) {
                $query->whereHas('equipoLocal', function ($teamQuery) {
                    $teamQuery->where('es_local', true);
                })->orWhereHas('equipoVisitante', function ($teamQuery) {
                    $teamQuery->where('es_local', true);
                });
            })
            ->orderBy('fecha_hora', 'asc')
            ->paginate(10);
        return view('basket.partidos', compact('partidos'));
    }

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
            'producto_id' => 'required|integer',
            'talla' => 'required|string|max:20',
        ]);

        $producto = Merchandising::whereKey($validated['producto_id'])
            ->where('oculto', false)
            ->firstOrFail();
        $talla = trim($validated['talla']);
        $tallasDisponibles = $producto->talla
            ? array_map('trim', explode(',', $producto->talla))
            : [];

        if (!empty($tallasDisponibles) && !in_array($talla, $tallasDisponibles, true)) {
            return back()->withErrors([
                'talla' => 'Selecciona una talla valida para este producto.'
            ])->withInput();
        }

        if (empty($tallasDisponibles) && $talla !== 'Sin talla') {
            return back()->withErrors([
                'talla' => 'La talla seleccionada no es valida para este producto.'
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
}
