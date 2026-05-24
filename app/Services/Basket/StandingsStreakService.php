<?php

namespace App\Services\Basket;

use App\Models\Partido;
use Illuminate\Support\Collection;

class StandingsStreakService
{
    public function buildForStandings(int $clasificacionId, array $teamIds, int $limit = 5): array
    {
        $teamIds = array_values(array_unique(array_map('intval', $teamIds)));

        if ($clasificacionId <= 0 || $teamIds === [] || $limit <= 0) {
            return [];
        }

        $streaks = [];
        foreach ($teamIds as $teamId) {
            $streaks[$teamId] = [];
        }

        $matches = Partido::with(['equipoLocal:id,nombre', 'equipoVisitante:id,nombre', 'estadisticas'])
            ->where('clasificacion_id', $clasificacionId)
            ->where('tipo_partido', 'liga')
            ->where('oculto_en_clasificacion', false)
            ->where(function ($query) use ($teamIds) {
                $query->whereIn('equipo_local_id', $teamIds)
                    ->orWhereIn('equipo_visitante_id', $teamIds);
            })
            ->orderByDesc('fecha_hora')
            ->orderByDesc('id')
            ->get();

        foreach ($matches as $match) {
            $localStats = $match->estadisticas->firstWhere('equipo_id', $match->equipo_local_id);
            $visitorStats = $match->estadisticas->firstWhere('equipo_id', $match->equipo_visitante_id);

            if (! $this->hasFinalScore($localStats) || ! $this->hasFinalScore($visitorStats)) {
                continue;
            }

            $localPoints = (int) $localStats->puntos_totales;
            $visitorPoints = (int) $visitorStats->puntos_totales;

            $this->pushResult(
                $streaks,
                (int) $match->equipo_local_id,
                $limit,
                $this->resolveResult($localPoints, $visitorPoints),
                $localPoints,
                $visitorPoints,
                (string) optional($match->equipoVisitante)->nombre
            );

            $this->pushResult(
                $streaks,
                (int) $match->equipo_visitante_id,
                $limit,
                $this->resolveResult($visitorPoints, $localPoints),
                $visitorPoints,
                $localPoints,
                (string) optional($match->equipoLocal)->nombre
            );

            if ($this->allTeamsCompleted($streaks, $limit)) {
                break;
            }
        }

        foreach ($streaks as $teamId => $results) {
            while (count($results) < $limit) {
                $results[] = null;
            }

            $streaks[$teamId] = $results;
        }

        return $streaks;
    }

    private function pushResult(array &$streaks, int $teamId, int $limit, string $result, int $pointsFor, int $pointsAgainst, string $opponent): void
    {
        if (! array_key_exists($teamId, $streaks) || count($streaks[$teamId]) >= $limit) {
            return;
        }

        $streaks[$teamId][] = [
            'result' => $result,
            'points_for' => $pointsFor,
            'points_against' => $pointsAgainst,
            'opponent' => $opponent,
        ];
    }

    private function hasFinalScore(mixed $stats): bool
    {
        return $stats !== null && $stats->puntos_totales !== null;
    }

    private function resolveResult(int $pointsFor, int $pointsAgainst): string
    {
        return match (true) {
            $pointsFor > $pointsAgainst => 'win',
            $pointsFor < $pointsAgainst => 'loss',
            default => 'draw',
        };
    }

    private function allTeamsCompleted(array $streaks, int $limit): bool
    {
        foreach ($streaks as $results) {
            if (count($results) < $limit) {
                return false;
            }
        }

        return true;
    }
}
