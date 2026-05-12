<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Partido;
use App\Models\PartidoEstadistica;
use Carbon\Carbon;

class PartidoController extends Controller
{
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
            ->orderByRaw('CASE WHEN fecha_hora >= ? THEN 0 ELSE 1 END', [$ahora])
            ->orderByRaw('CASE WHEN fecha_hora >= ? THEN fecha_hora END ASC', [$ahora])
            ->orderByRaw('CASE WHEN fecha_hora < ? THEN fecha_hora END DESC', [$ahora])
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

    public function toggleOcultoPartido(Partido $partido)
    {
        $partido->oculto = ! $partido->oculto;
        $partido->save();

        $mensaje = $partido->oculto
            ? 'Se ha ocultado correctamente.'
            : 'Se ha mostrado correctamente.';

        return redirect()->route('basket.partidos')->with('mensaje', $mensaje);
    }

    private function attachQuarterStats(Partido $partido, ?PartidoEstadistica $estadistica, int $equipoId): void
    {
        if (! $estadistica) {
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
