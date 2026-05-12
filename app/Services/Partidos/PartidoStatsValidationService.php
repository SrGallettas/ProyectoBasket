<?php

namespace App\Services\Partidos;

class PartidoStatsValidationService
{
    public function __construct(private readonly PartidoBoardService $boardService) {}

    public function statFields(): array
    {
        return $this->boardService->statFields();
    }

    public function periodLabels(): array
    {
        return $this->boardService->periodLabels();
    }

    public function assertMadeNotGreaterThanAttempted($validator, array $stats, string $lado, string $madeKey, string $attemptKey, string $label): void
    {
        $made = $stats[$madeKey] ?? null;
        $attempt = $stats[$attemptKey] ?? null;

        if ($made === null || $attempt === null || $made === '' || $attempt === '') {
            return;
        }

        if ((int) $made > (int) $attempt) {
            $equipoTxt = $lado === 'local' ? 'local' : 'visitante';

            $validator->errors()->add(
                "stats.$lado.$madeKey",
                "En el equipo {$equipoTxt}, los anotados de {$label} no pueden superar los intentados."
            );
        }
    }

    public function assertFieldSumMatches($validator, array $stats, string $lado, string $targetKey, array $sourceKeys, string $label): void
    {
        $target = $stats[$targetKey] ?? null;
        $sourceValues = [];

        foreach ($sourceKeys as $sourceKey) {
            $sourceValues[$sourceKey] = $stats[$sourceKey] ?? null;
        }

        if ($target === null || $target === '') {
            return;
        }

        foreach ($sourceValues as $value) {
            if ($value === null || $value === '') {
                return;
            }
        }

        $target = (int) $target;
        $sum = collect($sourceValues)->sum(static fn ($value) => (int) $value);

        if ($target !== $sum) {
            $equipoTxt = $lado === 'local' ? 'local' : 'visitante';

            $validator->errors()->add(
                "stats.$lado.$targetKey",
                "En el equipo {$equipoTxt}, {$label} deben cuadrar con el desglose informado."
            );
        }
    }

    public function assertPointsTotalMatchesBreakdown($validator, array $stats, string $lado): void
    {
        $requiredKeys = ['t2m', 't3m', 'ftm', 'puntos_totales'];

        foreach ($requiredKeys as $key) {
            $value = $stats[$key] ?? null;

            if ($value === null || $value === '') {
                return;
            }
        }

        $expectedPoints = ((int) $stats['t2m'] * 2)
            + ((int) $stats['t3m'] * 3)
            + (int) $stats['ftm'];

        if ((int) $stats['puntos_totales'] !== $expectedPoints) {
            $equipoTxt = $lado === 'local' ? 'local' : 'visitante';

            $validator->errors()->add(
                "stats.$lado.puntos_totales",
                "En el equipo {$equipoTxt}, los puntos totales deben coincidir con T2, T3 y tiros libres."
            );
        }
    }

    public function assertQuarterSumMatchesPointsTotal($validator, array $stats, array $quarters, string $lado): void
    {
        $pointsTotal = $stats['puntos_totales'] ?? null;

        if ($pointsTotal === null || $pointsTotal === '') {
            return;
        }

        $quarterValues = [];

        foreach (array_keys($this->periodLabels()) as $periodo) {
            $value = $quarters[$periodo] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            $quarterValues[] = (int) $value;
        }

        if ($quarterValues === []) {
            return;
        }

        if (array_sum($quarterValues) !== (int) $pointsTotal) {
            $equipoTxt = $lado === 'local' ? 'local' : 'visitante';

            $validator->errors()->add(
                "quarters.$lado",
                "En el equipo {$equipoTxt}, la suma de los cuartos debe coincidir con los puntos totales."
            );
        }
    }
}
