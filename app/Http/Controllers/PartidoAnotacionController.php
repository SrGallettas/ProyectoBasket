<?php

namespace App\Http\Controllers;

use App\Models\Partido;
use App\Models\PartidoAccionAnotacion;
use App\Models\PartidoCuarto;
use App\Models\PartidoEstadistica;
use App\Services\Partidos\PartidoBoardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PartidoAnotacionController extends Controller
{
    public function __construct(private readonly PartidoBoardService $boardService) {}

    public function anotacion(Partido $partido, Request $request)
    {
        $partido->load([
            'equipoLocal',
            'equipoVisitante',
            'estadisticas',
            'cuartos',
        ]);

        $this->boardService->attachQuarterStatsToPartido($partido);

        $periodoActivo = (int) $request->session()->get($this->boardService->boardPeriodSessionKey($partido), 1);

        if (! array_key_exists($periodoActivo, $this->boardService->periodLabels())) {
            $periodoActivo = 1;
            $request->session()->put($this->boardService->boardPeriodSessionKey($partido), $periodoActivo);
        }

        $history = $this->boardService->recentBoardActions($partido);
        $acciones = $this->boardService->quickActionDefinitions();
        $resumen = $this->boardService->buildAnotacionResumen($partido);

        return view('admin.partidos.anotacion', compact(
            'partido',
            'periodoActivo',
            'history',
            'acciones',
            'resumen'
        ));
    }

    public function cambiarPeriodoAnotacion(Partido $partido, Request $request)
    {
        $validated = $request->validate([
            'periodo' => 'required|integer|in:1,2,3,4,5',
        ]);

        $request->session()->put(
            $this->boardService->boardPeriodSessionKey($partido),
            (int) $validated['periodo']
        );

        return redirect()->route('partidos.anotacion', $partido);
    }

    public function registrarAccionAnotacion(Partido $partido, Request $request)
    {
        $validated = $request->validate([
            'lado' => 'required|in:local,visitante',
            'accion' => 'required|string',
        ]);

        if (! $this->boardService->boardActionsTableExists()) {
            return redirect()
                ->route('partidos.anotacion', $partido)
                ->withErrors(['anotacion' => 'Falta ejecutar la migración de la mesa de anotación.']);
        }

        $acciones = $this->boardService->quickActionDefinitions();
        $accion = $acciones[$validated['accion']] ?? null;

        if (! $accion) {
            throw ValidationException::withMessages([
                'accion' => 'La acción seleccionada no es válida.',
            ]);
        }

        $periodoActivo = (int) $request->session()->get($this->boardService->boardPeriodSessionKey($partido), 1);
        $equipoId = $validated['lado'] === 'local'
            ? (int) $partido->equipo_local_id
            : (int) $partido->equipo_visitante_id;

        DB::transaction(function () use ($partido, $equipoId, $accion, $periodoActivo) {
            $this->boardService->lockPartidoForBoardUpdate($partido);

            $estadistica = PartidoEstadistica::firstOrCreate(
                [
                    'partido_id' => $partido->id,
                    'equipo_id' => $equipoId,
                ],
                $this->boardService->emptyStatsPayload()
            );

            $this->boardService->applyQuickActionToStat($estadistica, $accion, 1);
            $estadistica->save();

            if (($accion['points'] ?? 0) > 0) {
                $cuarto = PartidoCuarto::firstOrCreate(
                    [
                        'partido_id' => $partido->id,
                        'equipo_id' => $equipoId,
                        'periodo' => $periodoActivo,
                    ],
                    ['puntos' => 0]
                );

                $cuarto->puntos = max(0, (int) $cuarto->puntos + (int) $accion['points']);
                $cuarto->save();
            }

            PartidoAccionAnotacion::create([
                'partido_id' => $partido->id,
                'equipo_id' => $equipoId,
                'lado' => $equipoId === (int) $partido->equipo_local_id ? 'local' : 'visitante',
                'accion' => $accion['key'],
                'periodo' => $periodoActivo,
                'user_id' => auth()->id(),
            ]);
        });

        return redirect()
            ->route('partidos.anotacion', $partido)
            ->with('mensaje', 'Acción registrada.');
    }

    public function deshacerAccionAnotacion(Partido $partido, Request $request)
    {
        if (! $this->boardService->boardActionsTableExists()) {
            return redirect()
                ->route('partidos.anotacion', $partido)
                ->withErrors(['anotacion' => 'Falta ejecutar la migración de la mesa de anotación.']);
        }

        $undone = false;

        DB::transaction(function () use ($partido, &$undone) {
            $this->boardService->lockPartidoForBoardUpdate($partido);

            $lastAction = PartidoAccionAnotacion::query()
                ->where('partido_id', $partido->id)
                ->latest('id')
                ->first();

            if (! $lastAction) {
                return;
            }

            $acciones = $this->boardService->quickActionDefinitions();
            $accion = $acciones[$lastAction->accion] ?? null;

            if (! $accion) {
                return;
            }

            $estadistica = PartidoEstadistica::firstOrCreate(
                [
                    'partido_id' => $partido->id,
                    'equipo_id' => $lastAction->equipo_id,
                ],
                $this->boardService->emptyStatsPayload()
            );

            $this->boardService->applyQuickActionToStat($estadistica, $accion, -1);
            $estadistica->save();

            if (($accion['points'] ?? 0) > 0) {
                $cuarto = PartidoCuarto::firstOrCreate(
                    [
                        'partido_id' => $partido->id,
                        'equipo_id' => $lastAction->equipo_id,
                        'periodo' => (int) $lastAction->periodo,
                    ],
                    ['puntos' => 0]
                );

                $cuarto->puntos = max(0, (int) $cuarto->puntos - (int) $accion['points']);
                $cuarto->save();
            }

            $lastAction->delete();
            $undone = true;
        });

        if (! $undone) {
            return redirect()
                ->route('partidos.anotacion', $partido)
                ->withErrors(['anotacion' => 'No hay acciones recientes para deshacer.']);
        }

        return redirect()
            ->route('partidos.anotacion', $partido)
            ->with('mensaje', 'Última acción deshecha.');
    }

    public function confirmarAnotacion(Partido $partido, Request $request)
    {
        $request->session()->forget($this->boardService->boardPeriodSessionKey($partido));

        return redirect()
            ->route('partidos.index')
            ->with('mensaje', 'Sesión de anotación finalizada. Las estadísticas guardadas siguen disponibles en el partido.');
    }
}
