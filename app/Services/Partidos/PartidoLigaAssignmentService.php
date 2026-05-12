<?php

namespace App\Services\Partidos;

use App\Models\Clasificacion;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PartidoLigaAssignmentService
{
    public function validate(array $data): void
    {
        if (($data['tipo_partido'] ?? null) !== 'liga') {
            return;
        }

        Validator::make($data, [
            'clasificacion_id' => 'required|exists:clasificaciones,id',
            'jornada' => 'required|integer|min:1',
        ], [
            'clasificacion_id.required' => 'En partidos de liga debes seleccionar una clasificacion.',
            'jornada.required' => 'En partidos de liga debes indicar la jornada.',
        ])->validate();

        $clasificacion = Clasificacion::with('equipos:id')->findOrFail((int) $data['clasificacion_id']);
        $equiposClasificacion = $clasificacion->equipos
            ->pluck('id')
            ->map(static fn ($id) => (int) $id)
            ->all();

        $equipoLocalId = (int) ($data['equipo_local_id'] ?? 0);
        $equipoVisitanteId = (int) ($data['equipo_visitante_id'] ?? 0);

        if (! in_array($equipoLocalId, $equiposClasificacion, true) || ! in_array($equipoVisitanteId, $equiposClasificacion, true)) {
            throw ValidationException::withMessages([
                'clasificacion_id' => 'Los dos equipos deben pertenecer a la clasificacion seleccionada.',
            ]);
        }
    }

    public function normalize(array $data): array
    {
        $isLiga = ($data['tipo_partido'] ?? null) === 'liga';

        return [
            'equipo_local_id' => (int) $data['equipo_local_id'],
            'equipo_visitante_id' => (int) $data['equipo_visitante_id'],
            'fecha_hora' => $data['fecha_hora'],
            'lugar' => $data['lugar'],
            'categoria' => $data['categoria'],
            'tipo_partido' => $data['tipo_partido'],
            'clasificacion_id' => $isLiga ? ($data['clasificacion_id'] ?? null) : null,
            'jornada' => $isLiga ? ($data['jornada'] ?? null) : null,
            'oculto' => filter_var($data['oculto'] ?? false, FILTER_VALIDATE_BOOL),
            'oculto_en_clasificacion' => $isLiga
                ? filter_var($data['oculto_en_clasificacion'] ?? false, FILTER_VALIDATE_BOOL)
                : false,
        ];
    }
}
