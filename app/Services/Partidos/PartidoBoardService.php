<?php

namespace App\Services\Partidos;

use App\Models\Partido;
use App\Models\PartidoAccionAnotacion;
use App\Models\PartidoEstadistica;
use Illuminate\Support\Facades\Schema;

class PartidoBoardService
{
    public function attachQuarterStatsToPartido(Partido $partido): void
    {
        foreach ($partido->estadisticas as $estadistica) {
            $estadistica->setRelation(
                'cuartos',
                $partido->cuartos
                    ->where('equipo_id', (int) $estadistica->equipo_id)
                    ->sortBy('periodo')
                    ->values()
            );
        }
    }

    public function lockPartidoForBoardUpdate(Partido $partido): void
    {
        Partido::whereKey($partido->id)
            ->lockForUpdate()
            ->first();
    }

    public function quickActionDefinitions(): array
    {
        $actions = [
            'ft_made' => ['label' => '+1 TL anotado', 'points' => 1, 'fields' => ['ftm' => 1, 'fta' => 1, 'puntos_totales' => 1]],
            'ft_miss' => ['label' => '+1 TL fallado', 'points' => 0, 'fields' => ['fta' => 1]],
            'two_made' => ['label' => '+2 anotado', 'points' => 2, 'fields' => ['t2m' => 1, 't2a' => 1, 'fgm' => 1, 'fga' => 1, 'puntos_totales' => 2]],
            'two_miss' => ['label' => '+2 fallado', 'points' => 0, 'fields' => ['t2a' => 1, 'fga' => 1]],
            'three_made' => ['label' => '+3 anotado', 'points' => 3, 'fields' => ['t3m' => 1, 't3a' => 1, 'fgm' => 1, 'fga' => 1, 'puntos_totales' => 3]],
            'three_miss' => ['label' => '+3 fallado', 'points' => 0, 'fields' => ['t3a' => 1, 'fga' => 1]],
            'reb_of' => ['label' => '+ rebote ofensivo', 'points' => 0, 'fields' => ['reb_of' => 1]],
            'reb_def' => ['label' => '+ rebote defensivo', 'points' => 0, 'fields' => ['reb_def' => 1]],
            'ast' => ['label' => '+ asistencia', 'points' => 0, 'fields' => ['ast' => 1]],
            'stl' => ['label' => '+ robo', 'points' => 0, 'fields' => ['stl' => 1]],
            'tov' => ['label' => '+ pérdida', 'points' => 0, 'fields' => ['tov' => 1]],
            'blk' => ['label' => '+ tapón', 'points' => 0, 'fields' => ['blk' => 1]],
            'pf' => ['label' => '+ falta', 'points' => 0, 'fields' => ['pf' => 1]],
            'tf' => ['label' => '+ técnica', 'points' => 0, 'fields' => ['tf' => 1]],
        ];

        foreach ($actions as $key => &$action) {
            $action['key'] = $key;
        }
        unset($action);

        return $actions;
    }

    public function statFields(): array
    {
        return [
            'fga', 'fgm', 't2a', 't2m', 't3a', 't3m', 'fta', 'ftm',
            'reb_of', 'reb_def', 'ast', 'blk', 'tov', 'stl', 'pf', 'tf', 'puntos_totales',
        ];
    }

    public function periodLabels(): array
    {
        return [1 => 'Q1', 2 => 'Q2', 3 => 'Q3', 4 => 'Q4', 5 => 'PR'];
    }

    public function emptyStatsPayload(): array
    {
        $payload = [];

        foreach ($this->statFields() as $campo) {
            $payload[$campo] = 0;
        }

        return $payload;
    }

    public function applyQuickActionToStat(PartidoEstadistica $estadistica, array $accion, int $modifier): void
    {
        foreach ($accion['fields'] as $campo => $delta) {
            $actual = (int) ($estadistica->{$campo} ?? 0);
            $estadistica->{$campo} = max(0, $actual + ($delta * $modifier));
        }
    }

    public function buildAnotacionResumen(Partido $partido): array
    {
        $statsLocal = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_local_id);
        $statsVisitante = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_visitante_id);

        $buildTeam = function (?PartidoEstadistica $stat, int $equipoId): array {
            $cuartos = collect($this->periodLabels())->mapWithKeys(function ($label, $periodo) use ($stat, $equipoId) {
                $valor = optional(optional($stat)->cuartos)->first(function ($cuarto) use ($periodo, $equipoId) {
                    return (int) $cuarto->periodo === (int) $periodo
                        && (int) $cuarto->equipo_id === $equipoId;
                });

                return [$periodo => (int) optional($valor)->puntos];
            })->all();

            return [
                'puntos' => (int) optional($stat)->puntos_totales,
                'cuartos' => $cuartos,
                'stats' => [
                    'fga' => (int) optional($stat)->fga,
                    'fgm' => (int) optional($stat)->fgm,
                    't2a' => (int) optional($stat)->t2a,
                    't2m' => (int) optional($stat)->t2m,
                    't3a' => (int) optional($stat)->t3a,
                    't3m' => (int) optional($stat)->t3m,
                    'fta' => (int) optional($stat)->fta,
                    'ftm' => (int) optional($stat)->ftm,
                    'reb_of' => (int) optional($stat)->reb_of,
                    'reb_def' => (int) optional($stat)->reb_def,
                    'ast' => (int) optional($stat)->ast,
                    'stl' => (int) optional($stat)->stl,
                    'tov' => (int) optional($stat)->tov,
                    'blk' => (int) optional($stat)->blk,
                    'pf' => (int) optional($stat)->pf,
                    'tf' => (int) optional($stat)->tf,
                ],
            ];
        };

        return [
            'local' => $buildTeam($statsLocal, (int) $partido->equipo_local_id),
            'visitante' => $buildTeam($statsVisitante, (int) $partido->equipo_visitante_id),
        ];
    }

    public function recentBoardActions(Partido $partido): array
    {
        if (! $this->boardActionsTableExists()) {
            return [];
        }

        return PartidoAccionAnotacion::query()
            ->where('partido_id', $partido->id)
            ->latest('id')
            ->limit(20)
            ->get()
            ->map(function (PartidoAccionAnotacion $action) {
                return [
                    'lado' => $action->lado,
                    'accion' => $action->accion,
                    'periodo' => (int) $action->periodo,
                    'timestamp' => optional($action->created_at)->format('H:i:s'),
                ];
            })
            ->all();
    }

    public function boardActionsTableExists(): bool
    {
        return Schema::hasTable('partido_acciones_anotacion');
    }

    public function boardPeriodSessionKey(Partido $partido): string
    {
        return 'partidos_anotacion.'.$partido->id.'.periodo';
    }
}
