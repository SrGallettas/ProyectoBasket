<?php

namespace Database\Seeders;

use App\Models\Clasificacion;
use App\Models\ClasificacionFila;
use App\Models\Equipo;
use App\Models\Partido;
use App\Models\PartidoCuarto;
use App\Models\PartidoEstadistica;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LigaSimulacionSeeder extends Seeder
{
    private const CLASIFICACION_NOMBRE = 'SIM Liga 10 Equipos';
    private const EQUIPO_PREFIX = 'SIM L10';

    public function run(): void
    {
        DB::transaction(function () {
            $this->cleanupPreviousSimulation();

            $clasificacion = Clasificacion::create([
                'nombre' => self::CLASIFICACION_NOMBRE,
                'categoria' => 'Senior',
                'temporada' => '2026-2027',
                'activa' => false,
            ]);

            $equipos = $this->createSimulationTeams();
            $clasificacion->equipos()->sync($equipos->pluck('id')->all());

            $jornadas = $this->buildDoubleRoundRobin($equipos->pluck('id')->all());
            $inicioLiga = now()->subDays(140)->startOfDay();

            foreach ($jornadas as $idxJornada => $partidosJornada) {
                $numeroJornada = $idxJornada + 1;

                foreach ($partidosJornada as $idxPartido => $duelo) {
                    [$localId, $visitanteId] = $duelo;

                    $fecha = (clone $inicioLiga)
                        ->addDays(($numeroJornada - 1) * 7)
                        ->addHours(10 + ($idxPartido * 2));

                    $partido = Partido::create([
                        'equipo_local_id' => $localId,
                        'equipo_visitante_id' => $visitanteId,
                        'clasificacion_id' => $clasificacion->id,
                        'fecha_hora' => $fecha,
                        'lugar' => 'Pabellón Simulado ' . str_pad((string) $numeroJornada, 2, '0', STR_PAD_LEFT),
                        'categoria' => 'Senior',
                        'tipo_partido' => 'liga',
                        'jornada' => $numeroJornada,
                        'oculto' => false,
                    ]);

                    $statsLocal = $this->generateTeamStats();
                    $statsVisitante = $this->generateTeamStats();

                    PartidoEstadistica::create(array_merge($statsLocal, [
                        'partido_id' => $partido->id,
                        'equipo_id' => $localId,
                    ]));

                    PartidoEstadistica::create(array_merge($statsVisitante, [
                        'partido_id' => $partido->id,
                        'equipo_id' => $visitanteId,
                    ]));

                    $usarProrroga = abs($statsLocal['puntos_totales'] - $statsVisitante['puntos_totales']) <= 6 && random_int(1, 100) <= 15;
                    $periodos = $usarProrroga ? [1, 2, 3, 4, 5] : [1, 2, 3, 4];
                    $cuartosLocal = $this->splitPointsByPeriods($statsLocal['puntos_totales'], $periodos);
                    $cuartosVisitante = $this->splitPointsByPeriods($statsVisitante['puntos_totales'], $periodos);

                    foreach ($periodos as $periodo) {
                        PartidoCuarto::create([
                            'partido_id' => $partido->id,
                            'equipo_id' => $localId,
                            'periodo' => $periodo,
                            'puntos' => $cuartosLocal[$periodo] ?? 0,
                        ]);

                        PartidoCuarto::create([
                            'partido_id' => $partido->id,
                            'equipo_id' => $visitanteId,
                            'periodo' => $periodo,
                            'puntos' => $cuartosVisitante[$periodo] ?? 0,
                        ]);
                    }
                }
            }

            $this->recalculateClasificacion($clasificacion);
        });
    }

    private function cleanupPreviousSimulation(): void
    {
        $clasificacion = Clasificacion::where('nombre', self::CLASIFICACION_NOMBRE)->first();
        if ($clasificacion) {
            $equipoIds = $clasificacion->equipos()->pluck('equipos.id')->all();
            Partido::where('clasificacion_id', $clasificacion->id)->delete();
            ClasificacionFila::where('clasificacion_id', $clasificacion->id)->delete();
            $clasificacion->equipos()->detach();
            $clasificacion->delete();

            if (!empty($equipoIds)) {
                Equipo::whereIn('id', $equipoIds)
                    ->where('nombre', 'like', self::EQUIPO_PREFIX . '%')
                    ->delete();
            }
        }
    }

    private function createSimulationTeams()
    {
        $equipos = collect();

        $equipos->push(Equipo::create([
            'nombre' => self::EQUIPO_PREFIX . ' Bellreguard',
            'categoria' => 'Senior',
            'imagen_club' => null,
            'es_local' => true,
            'oculto' => false,
        ]));

        for ($i = 1; $i <= 9; $i++) {
            $equipos->push(Equipo::create([
                'nombre' => self::EQUIPO_PREFIX . ' Rival ' . $i,
                'categoria' => 'Senior',
                'imagen_club' => null,
                'es_local' => false,
                'oculto' => false,
            ]));
        }

        return $equipos;
    }

    private function buildDoubleRoundRobin(array $teamIds): array
    {
        $ids = array_values($teamIds);
        if (count($ids) % 2 !== 0) {
            $ids[] = null;
        }

        $numTeams = count($ids);
        $numRounds = $numTeams - 1;
        $half = (int) ($numTeams / 2);
        $rounds = [];

        for ($round = 0; $round < $numRounds; $round++) {
            $matches = [];
            for ($i = 0; $i < $half; $i++) {
                $home = $ids[$i];
                $away = $ids[$numTeams - 1 - $i];

                if ($home === null || $away === null) {
                    continue;
                }

                if ($round % 2 === 0) {
                    $matches[] = [$home, $away];
                } else {
                    $matches[] = [$away, $home];
                }
            }
            $rounds[] = $matches;

            $fixed = array_shift($ids);
            $last = array_pop($ids);
            array_unshift($ids, $fixed);
            array_splice($ids, 1, 0, [$last]);
        }

        $returnRounds = [];
        foreach ($rounds as $matches) {
            $returnRound = [];
            foreach ($matches as [$home, $away]) {
                $returnRound[] = [$away, $home];
            }
            $returnRounds[] = $returnRound;
        }

        return array_merge($rounds, $returnRounds);
    }

    private function generateTeamStats(): array
    {
        $fga = random_int(50, 82);
        $t3a = random_int(14, min(36, $fga - 8));
        $t2a = max(8, $fga - $t3a);

        $t2m = random_int((int) floor($t2a * 0.38), (int) floor($t2a * 0.62));
        $t3m = random_int((int) floor($t3a * 0.24), (int) floor($t3a * 0.46));
        $fgm = $t2m + $t3m;

        $fta = random_int(8, 30);
        $ftm = random_int((int) floor($fta * 0.58), (int) floor($fta * 0.90));

        $rebOf = random_int(6, 18);
        $rebDef = random_int(18, 37);
        $ast = random_int(8, 26);
        $blk = random_int(1, 9);
        $tov = random_int(7, 22);
        $stl = random_int(4, 16);
        $pf = random_int(10, 28);
        $tf = random_int(0, 3);

        $puntos = ($t2m * 2) + ($t3m * 3) + $ftm;

        return [
            'fgm' => $fgm,
            'fga' => $fga,
            't2m' => $t2m,
            't2a' => $t2a,
            't3m' => $t3m,
            't3a' => $t3a,
            'ftm' => $ftm,
            'fta' => $fta,
            'reb_of' => $rebOf,
            'reb_def' => $rebDef,
            'ast' => $ast,
            'blk' => $blk,
            'tov' => $tov,
            'stl' => $stl,
            'pf' => $pf,
            'tf' => $tf,
            'puntos_totales' => $puntos,
        ];
    }

    private function splitPointsByPeriods(int $total, array $periodos): array
    {
        $remaining = $total;
        $result = [];
        $count = count($periodos);

        foreach ($periodos as $idx => $periodo) {
            $left = $count - $idx;

            if ($left === 1) {
                $result[$periodo] = max(0, $remaining);
                break;
            }

            $maxAlloc = max(0, $remaining - ($left - 1) * 8);
            $minAlloc = max(0, min(8, $remaining));
            $value = $maxAlloc > $minAlloc ? random_int($minAlloc, $maxAlloc) : $minAlloc;
            $result[$periodo] = $value;
            $remaining -= $value;
        }

        return $result;
    }

    private function recalculateClasificacion(Clasificacion $clasificacion): void
    {
        $equipoIds = $clasificacion->equipos()->pluck('equipos.id')->map(static fn ($id) => (int) $id)->all();
        if (empty($equipoIds)) {
            return;
        }

        $partidos = Partido::with('estadisticas')
            ->where('clasificacion_id', $clasificacion->id)
            ->where('tipo_partido', 'liga')
            ->whereNotNull('jornada')
            ->orderBy('jornada')
            ->orderBy('fecha_hora')
            ->get();

        ClasificacionFila::where('clasificacion_id', $clasificacion->id)->delete();

        $acumulado = [];
        foreach ($equipoIds as $equipoId) {
            $acumulado[$equipoId] = [
                'pj' => 0,
                'pg' => 0,
                'pp' => 0,
                'pf' => 0,
                'pc' => 0,
                'pts' => 0,
            ];
        }

        $jornadas = $partidos->pluck('jornada')
            ->filter()
            ->map(static fn ($jornada) => (int) $jornada)
            ->unique()
            ->sort()
            ->values();

        foreach ($jornadas as $jornada) {
            $partidosJornada = $partidos->where('jornada', $jornada);

            foreach ($partidosJornada as $partido) {
                $localId = (int) $partido->equipo_local_id;
                $visitanteId = (int) $partido->equipo_visitante_id;

                if (!in_array($localId, $equipoIds, true) || !in_array($visitanteId, $equipoIds, true)) {
                    continue;
                }

                $statsLocal = $partido->estadisticas->firstWhere('equipo_id', $localId);
                $statsVisitante = $partido->estadisticas->firstWhere('equipo_id', $visitanteId);

                $puntosLocal = optional($statsLocal)->puntos_totales;
                $puntosVisitante = optional($statsVisitante)->puntos_totales;
                if ($puntosLocal === null || $puntosVisitante === null) {
                    continue;
                }

                $puntosLocal = (int) $puntosLocal;
                $puntosVisitante = (int) $puntosVisitante;

                $acumulado[$localId]['pj']++;
                $acumulado[$visitanteId]['pj']++;
                $acumulado[$localId]['pf'] += $puntosLocal;
                $acumulado[$localId]['pc'] += $puntosVisitante;
                $acumulado[$visitanteId]['pf'] += $puntosVisitante;
                $acumulado[$visitanteId]['pc'] += $puntosLocal;

                if ($puntosLocal > $puntosVisitante) {
                    $acumulado[$localId]['pg']++;
                    $acumulado[$localId]['pts'] += 2;
                    $acumulado[$visitanteId]['pp']++;
                    $acumulado[$visitanteId]['pts'] += 1;
                } elseif ($puntosVisitante > $puntosLocal) {
                    $acumulado[$visitanteId]['pg']++;
                    $acumulado[$visitanteId]['pts'] += 2;
                    $acumulado[$localId]['pp']++;
                    $acumulado[$localId]['pts'] += 1;
                } else {
                    $acumulado[$localId]['pts'] += 1;
                    $acumulado[$visitanteId]['pts'] += 1;
                }
            }

            $now = now();
            $rows = [];
            foreach ($equipoIds as $equipoId) {
                $rows[] = [
                    'clasificacion_id' => $clasificacion->id,
                    'equipo_id' => $equipoId,
                    'jornada' => $jornada,
                    'pj' => $acumulado[$equipoId]['pj'],
                    'pg' => $acumulado[$equipoId]['pg'],
                    'pp' => $acumulado[$equipoId]['pp'],
                    'pf' => $acumulado[$equipoId]['pf'],
                    'pc' => $acumulado[$equipoId]['pc'],
                    'diff' => $acumulado[$equipoId]['pf'] - $acumulado[$equipoId]['pc'],
                    'pts' => $acumulado[$equipoId]['pts'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            ClasificacionFila::insert($rows);
        }
    }
}
