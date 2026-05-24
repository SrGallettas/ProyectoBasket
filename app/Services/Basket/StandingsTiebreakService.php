<?php

namespace App\Services\Basket;

use App\Models\ClasificacionFila;
use App\Models\Partido;
use Illuminate\Support\Collection;

class StandingsTiebreakService
{
    public function sortRows(Collection $rows, int $clasificacionId, ?int $jornada = null): Collection
    {
        if ($rows->count() <= 1) {
            return $rows->values();
        }

        return $rows
            ->groupBy(fn (ClasificacionFila $row) => (int) $row->pts)
            ->sortKeysDesc()
            ->flatMap(fn (Collection $group) => $this->sortTiedGroup($group->values(), $clasificacionId, $jornada))
            ->values();
    }

    private function sortTiedGroup(Collection $rows, int $clasificacionId, ?int $jornada): Collection
    {
        if ($rows->count() <= 1) {
            return $rows->values();
        }

        $teamIds = $rows->pluck('equipo_id')
            ->map(static fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $miniLeague = $this->buildMiniLeagueStats($clasificacionId, $teamIds, $jornada);

        return $rows->sort(function (ClasificacionFila $a, ClasificacionFila $b) use ($miniLeague) {
            $aStats = $miniLeague[(int) $a->equipo_id] ?? ['wins' => 0, 'diff' => 0, 'pf' => 0];
            $bStats = $miniLeague[(int) $b->equipo_id] ?? ['wins' => 0, 'diff' => 0, 'pf' => 0];

            $aTuple = [
                (int) $aStats['wins'],
                (int) $aStats['diff'],
                (int) $a->diff,
                (int) $a->pf,
            ];

            $bTuple = [
                (int) $bStats['wins'],
                (int) $bStats['diff'],
                (int) $b->diff,
                (int) $b->pf,
            ];

            return $bTuple <=> $aTuple;
        })->values();
    }

    private function buildMiniLeagueStats(int $clasificacionId, array $teamIds, ?int $jornada): array
    {
        $stats = [];
        foreach ($teamIds as $teamId) {
            $stats[(int) $teamId] = [
                'wins' => 0,
                'diff' => 0,
                'pf' => 0,
            ];
        }

        if ($clasificacionId <= 0 || count($teamIds) < 2) {
            return $stats;
        }

        $matches = Partido::with('estadisticas')
            ->where('clasificacion_id', $clasificacionId)
            ->where('tipo_partido', 'liga')
            ->where('oculto_en_clasificacion', false)
            ->when($jornada !== null, function ($query) use ($jornada) {
                $query->whereNotNull('jornada')->where('jornada', '<=', $jornada);
            })
            ->whereIn('equipo_local_id', $teamIds)
            ->whereIn('equipo_visitante_id', $teamIds)
            ->orderBy('jornada')
            ->orderBy('fecha_hora')
            ->get();

        foreach ($matches as $match) {
            $localId = (int) $match->equipo_local_id;
            $visitorId = (int) $match->equipo_visitante_id;
            $localStats = $match->estadisticas->firstWhere('equipo_id', $localId);
            $visitorStats = $match->estadisticas->firstWhere('equipo_id', $visitorId);
            $localPoints = optional($localStats)->puntos_totales;
            $visitorPoints = optional($visitorStats)->puntos_totales;

            if ($localPoints === null || $visitorPoints === null) {
                continue;
            }

            $localPoints = (int) $localPoints;
            $visitorPoints = (int) $visitorPoints;

            $stats[$localId]['pf'] += $localPoints;
            $stats[$localId]['diff'] += $localPoints - $visitorPoints;
            $stats[$visitorId]['pf'] += $visitorPoints;
            $stats[$visitorId]['diff'] += $visitorPoints - $localPoints;

            if ($localPoints > $visitorPoints) {
                $stats[$localId]['wins']++;
            } elseif ($visitorPoints > $localPoints) {
                $stats[$visitorId]['wins']++;
            }
        }

        return $stats;
    }
}
