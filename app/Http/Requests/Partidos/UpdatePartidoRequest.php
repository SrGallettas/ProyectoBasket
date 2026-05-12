<?php

namespace App\Http\Requests\Partidos;

use App\Services\Partidos\PartidoStatsValidationService;

class UpdatePartidoRequest extends BasePartidoRequest
{
    public function rules(): array
    {
        $rules = $this->baseRules();
        $statsValidationService = app(PartidoStatsValidationService::class);

        foreach (['local', 'visitante'] as $lado) {
            foreach ($statsValidationService->statFields() as $campo) {
                $rules["stats.$lado.$campo"] = 'nullable|integer|min:0';
            }

            foreach (array_keys($statsValidationService->periodLabels()) as $periodo) {
                $rules["quarters.$lado.$periodo"] = 'nullable|integer|min:0';
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'equipo_local_id.required' => 'El equipo local es obligatorio.',
            'equipo_visitante_id.required' => 'El equipo visitante es obligatorio.',
            'equipo_visitante_id.different' => 'Un equipo no puede jugar contra si mismo.',
            'fecha_hora.required' => 'La fecha y hora son obligatorias para el calendario.',
            'lugar.required' => 'Debes indicar donde se juega el partido.',
        ]);
    }

    public function after(): array
    {
        return [
            ...parent::after(),
            function ($validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $statsValidationService = app(PartidoStatsValidationService::class);

                foreach (['local', 'visitante'] as $lado) {
                    $stats = (array) $this->input("stats.$lado", []);
                    $quarters = (array) $this->input("quarters.$lado", []);

                    $statsValidationService->assertMadeNotGreaterThanAttempted($validator, $stats, $lado, 'fgm', 'fga', 'tiros de campo');
                    $statsValidationService->assertMadeNotGreaterThanAttempted($validator, $stats, $lado, 't2m', 't2a', 'tiros de 2');
                    $statsValidationService->assertMadeNotGreaterThanAttempted($validator, $stats, $lado, 't3m', 't3a', 'tiros de 3');
                    $statsValidationService->assertMadeNotGreaterThanAttempted($validator, $stats, $lado, 'ftm', 'fta', 'tiros libres');
                    $statsValidationService->assertFieldSumMatches($validator, $stats, $lado, 'fgm', ['t2m', 't3m'], 'los tiros de campo anotados');
                    $statsValidationService->assertFieldSumMatches($validator, $stats, $lado, 'fga', ['t2a', 't3a'], 'los tiros de campo intentados');
                    $statsValidationService->assertPointsTotalMatchesBreakdown($validator, $stats, $lado);
                    $statsValidationService->assertQuarterSumMatchesPointsTotal($validator, $stats, $quarters, $lado);
                }
            },
        ];
    }
}
