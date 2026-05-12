<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Clasificacion;
use App\Models\ClasificacionFila;
use App\Models\Equipo;
use App\Models\Partido;
use App\Services\Basket\CategoryOrderService;
use App\Services\Basket\StatsSummaryService;
use Illuminate\Http\Request;

class ClasificacionController extends Controller
{
    public function __construct(
        private readonly CategoryOrderService $categoryOrderService,
        private readonly StatsSummaryService $statsSummaryService,
    ) {}

    public function clasificacion()
    {
        $clasificaciones = Clasificacion::orderByDesc('activa')->orderBy('nombre')->get();
        $clasificacionSeleccionada = null;

        if ($clasificaciones->isNotEmpty()) {
            $clasificacionId = request('clasificacion_id');

            if ($clasificacionId) {
                $clasificacionSeleccionada = $clasificaciones->firstWhere('id', (int) $clasificacionId);
            }

            if (! $clasificacionSeleccionada) {
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

                if (! $jornadaClasificacionMostrada) {
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
                ->when(! $modoGeneral, function ($query) use ($jornadaSeleccionada) {
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
            ->get(['id', 'nombre', 'categoria', 'imagen_club']);

        $equiposLocales = $this->categoryOrderService->sortTeamsByCategoryPriority($equiposLocales);

        $equipoSeleccionado = null;
        $resumenTemporada = null;

        if ($equiposLocales->isNotEmpty()) {
            $equipoId = $request->input('equipo_id');
            if ($equipoId) {
                $equipoSeleccionado = $equiposLocales->firstWhere('id', (int) $equipoId);
            }
            if (! $equipoSeleccionado) {
                $equipoSeleccionado = $this->categoryOrderService->resolvePreferredLocalTeam(
                    $equiposLocales,
                    ['CB Bellreguard Senior'],
                    ['Senior']
                ) ?: $this->categoryOrderService->resolvePreferredLocalTeam(
                    $equiposLocales,
                    ['CB Bellreguard Senior B'],
                    ['Senior B']
                ) ?: $equiposLocales->first();
            }
        }

        if ($equipoSeleccionado) {
            $resumenTemporada = $this->statsSummaryService->buildTeamSeasonSummary($equipoSeleccionado);
        }

        return view('basket.estadisticas', compact('equiposLocales', 'equipoSeleccionado', 'resumenTemporada'));
    }
}
