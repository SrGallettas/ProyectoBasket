<?php

namespace App\Services\Partidos;

use App\Models\Partido;
use Illuminate\Support\Facades\DB;

class PartidoWriteService
{
    public function __construct(
        private readonly PartidoLigaAssignmentService $ligaAssignmentService,
        private readonly PartidoStatsSyncService $statsSyncService,
    ) {}

    public function create(array $data): Partido
    {
        return DB::transaction(function () use ($data) {
            $partido = Partido::create($this->ligaAssignmentService->normalize($data));
            $puntosLocal = $data["puntos_local"] ?? null;
            $puntosVisitante = $data["puntos_visitante"] ?? null;

            if ($puntosLocal !== null && $puntosLocal !== "" && $puntosVisitante !== null && $puntosVisitante !== "") {
                $this->statsSyncService->syncPartidoStats(
                    $partido,
                    [
                        "local" => ["puntos_totales" => $puntosLocal],
                        "visitante" => ["puntos_totales" => $puntosVisitante],
                    ],
                    []
                );
            }

            return $partido;
        });
    }

    public function update(Partido $partido, array $data): Partido
    {
        $payload = $this->ligaAssignmentService->normalize($data);
        $teamsChanged = $this->statsSyncService->partidoTeamsChanged(
            $partido,
            (int) $payload['equipo_local_id'],
            (int) $payload['equipo_visitante_id']
        );

        DB::transaction(function () use ($partido, $payload, $data, $teamsChanged) {
            if ($teamsChanged) {
                $this->statsSyncService->clearPartidoBoardData($partido);
            }

            $partido->fill($payload);
            $partido->save();

            $this->statsSyncService->syncPartidoStats(
                $partido,
                (array) ($data['stats'] ?? []),
                (array) ($data['quarters'] ?? [])
            );
        });

        return $partido->refresh();
    }

    public function delete(Partido $partido): void
    {
        DB::transaction(function () use ($partido) {
            $this->statsSyncService->clearPartidoBoardData($partido);
            $partido->delete();
        });
    }
}
