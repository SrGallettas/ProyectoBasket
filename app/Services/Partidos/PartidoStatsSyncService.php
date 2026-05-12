<?php

namespace App\Services\Partidos;

use App\Models\Partido;
use App\Models\PartidoAccionAnotacion;
use App\Models\PartidoCuarto;
use App\Models\PartidoEstadistica;

class PartidoStatsSyncService
{
    public function __construct(private readonly PartidoBoardService $boardService) {}

    public function periodLabels(): array
    {
        return $this->boardService->periodLabels();
    }

    public function statFields(): array
    {
        return $this->boardService->statFields();
    }

    public function partidoTeamsChanged(Partido $partido, int $equipoLocalId, int $equipoVisitanteId): bool
    {
        return (int) $partido->equipo_local_id !== $equipoLocalId
            || (int) $partido->equipo_visitante_id !== $equipoVisitanteId;
    }

    public function clearPartidoBoardData(Partido $partido): void
    {
        $partido->estadisticas()->delete();
        $partido->cuartos()->delete();

        if ($this->boardService->boardActionsTableExists()) {
            PartidoAccionAnotacion::where('partido_id', $partido->id)->delete();
        }
    }

    public function syncPartidoStats(Partido $partido, array $statsBySide, array $quartersBySide): void
    {
        $equiposPorLado = [
            'local' => (int) $partido->equipo_local_id,
            'visitante' => (int) $partido->equipo_visitante_id,
        ];

        foreach ($equiposPorLado as $lado => $equipoId) {
            $inputStats = (array) ($statsBySide[$lado] ?? []);
            $payload = [];

            foreach ($this->statFields() as $campo) {
                $valor = $inputStats[$campo] ?? null;
                $payload[$campo] = ($valor === '' || $valor === null) ? null : (int) $valor;
            }

            $hasStats = collect($payload)->filter(static fn ($value) => $value !== null)->isNotEmpty();

            if (! $hasStats) {
                PartidoEstadistica::where('partido_id', $partido->id)
                    ->where('equipo_id', $equipoId)
                    ->delete();

                PartidoCuarto::where('partido_id', $partido->id)
                    ->where('equipo_id', $equipoId)
                    ->delete();

                continue;
            }

            PartidoEstadistica::updateOrCreate(
                [
                    'partido_id' => $partido->id,
                    'equipo_id' => $equipoId,
                ],
                $payload
            );

            $quarterInput = (array) ($quartersBySide[$lado] ?? []);
            $seenPeriods = [];

            foreach (array_keys($this->periodLabels()) as $periodo) {
                $valor = $quarterInput[$periodo] ?? null;

                if ($valor === '' || $valor === null) {
                    PartidoCuarto::where('partido_id', $partido->id)
                        ->where('equipo_id', $equipoId)
                        ->where('periodo', $periodo)
                        ->delete();

                    continue;
                }

                PartidoCuarto::updateOrCreate(
                    [
                        'partido_id' => $partido->id,
                        'equipo_id' => $equipoId,
                        'periodo' => $periodo,
                    ],
                    ['puntos' => (int) $valor]
                );

                $seenPeriods[] = $periodo;
            }

            PartidoCuarto::where('partido_id', $partido->id)
                ->where('equipo_id', $equipoId)
                ->whereNotIn('periodo', $seenPeriods)
                ->delete();
        }
    }
}
