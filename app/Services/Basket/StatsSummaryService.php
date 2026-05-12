<?php

namespace App\Services\Basket;

use App\Models\Equipo;
use App\Models\Partido;
use App\Models\PartidoCuarto;
use App\Models\PartidoEstadistica;

class StatsSummaryService
{
    public function buildTeamSeasonSummary(Equipo $equipo): array
    {
        $partidosLigaCompletados = Partido::with('estadisticas')
            ->where('tipo_partido', 'liga')
            ->whereNotNull('jornada')
            ->where(function ($query) use ($equipo) {
                $query->where('equipo_local_id', $equipo->id)
                    ->orWhere('equipo_visitante_id', $equipo->id);
            })
            ->get()
            ->filter(function (Partido $partido) {
                $statsLocal = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_local_id);
                $statsVisitante = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_visitante_id);

                return optional($statsLocal)->puntos_totales !== null
                    && optional($statsVisitante)->puntos_totales !== null;
            })
            ->values();

        $stats = PartidoEstadistica::query()
            ->selectRaw('
                COUNT(partido_estadisticas.id) as pj,
                SUM(partido_estadisticas.fga) as fga,
                SUM(partido_estadisticas.fgm) as fgm,
                SUM(partido_estadisticas.t2a) as t2a,
                SUM(partido_estadisticas.t2m) as t2m,
                SUM(partido_estadisticas.t3a) as t3a,
                SUM(partido_estadisticas.t3m) as t3m,
                SUM(partido_estadisticas.fta) as fta,
                SUM(partido_estadisticas.ftm) as ftm,
                SUM(partido_estadisticas.reb_of) as reb_of,
                SUM(partido_estadisticas.reb_def) as reb_def,
                SUM(partido_estadisticas.ast) as ast,
                SUM(partido_estadisticas.stl) as stl,
                SUM(partido_estadisticas.blk) as blk,
                SUM(partido_estadisticas.tov) as tov,
                SUM(partido_estadisticas.pf) as pf,
                SUM(partido_estadisticas.tf) as tf,
                SUM(partido_estadisticas.puntos_totales) as pts
            ')
            ->join('partidos', 'partidos.id', '=', 'partido_estadisticas.partido_id')
            ->where('partidos.tipo_partido', 'liga')
            ->whereNotNull('partidos.jornada')
            ->where('partido_estadisticas.equipo_id', $equipo->id)
            ->whereNotNull('partido_estadisticas.puntos_totales')
            ->first();

        $cuartosRaw = PartidoCuarto::query()
            ->selectRaw('periodo, SUM(puntos) as puntos')
            ->join('partidos', 'partidos.id', '=', 'partido_cuartos.partido_id')
            ->where('partidos.tipo_partido', 'liga')
            ->whereNotNull('partidos.jornada')
            ->where('partido_cuartos.equipo_id', $equipo->id)
            ->groupBy('periodo')
            ->pluck('puntos', 'periodo');

        $prorrogasRaw = PartidoCuarto::query()
            ->selectRaw('SUM(puntos) as puntos, COUNT(DISTINCT partido_cuartos.partido_id) as partidos')
            ->join('partidos', 'partidos.id', '=', 'partido_cuartos.partido_id')
            ->where('partidos.tipo_partido', 'liga')
            ->whereNotNull('partidos.jornada')
            ->where('partido_cuartos.equipo_id', $equipo->id)
            ->where('partido_cuartos.periodo', 5)
            ->first();

        $pj = (int) ($stats->pj ?? 0);
        $divisor = $pj > 0 ? $pj : 1;

        $fga = (int) ($stats->fga ?? 0);
        $fgm = (int) ($stats->fgm ?? 0);
        $t2a = (int) ($stats->t2a ?? 0);
        $t2m = (int) ($stats->t2m ?? 0);
        $t3a = (int) ($stats->t3a ?? 0);
        $t3m = (int) ($stats->t3m ?? 0);
        $fta = (int) ($stats->fta ?? 0);
        $ftm = (int) ($stats->ftm ?? 0);
        $rebOf = (int) ($stats->reb_of ?? 0);
        $rebDef = (int) ($stats->reb_def ?? 0);
        $rebTot = $rebOf + $rebDef;
        $ast = (int) ($stats->ast ?? 0);
        $stl = (int) ($stats->stl ?? 0);
        $blk = (int) ($stats->blk ?? 0);
        $tov = (int) ($stats->tov ?? 0);
        $pf = (int) ($stats->pf ?? 0);
        $tf = (int) ($stats->tf ?? 0);
        $pts = (int) ($stats->pts ?? 0);
        $astPer = $tov > 0 ? round($ast / $tov, 2) : null;

        $q1 = (int) ($cuartosRaw[1] ?? 0);
        $q2 = (int) ($cuartosRaw[2] ?? 0);
        $q3 = (int) ($cuartosRaw[3] ?? 0);
        $q4 = (int) ($cuartosRaw[4] ?? 0);
        $pr = (int) ($cuartosRaw[5] ?? 0);
        $partidosConProrroga = (int) ($prorrogasRaw->partidos ?? 0);
        $divisorProrroga = $partidosConProrroga > 0 ? $partidosConProrroga : 1;

        $victorias = $partidosLigaCompletados->filter(function (Partido $partido) use ($equipo) {
            $statsEquipo = $partido->estadisticas->firstWhere('equipo_id', $equipo->id);
            $statsRival = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_local_id === $equipo->id ? $partido->equipo_visitante_id : $partido->equipo_local_id);

            return (int) optional($statsEquipo)->puntos_totales > (int) optional($statsRival)->puntos_totales;
        })->count();

        $derrotas = max(0, $pj - $victorias);
        $porcentajeVictorias = $pj > 0 ? round(($victorias / $pj) * 100, 1) : null;

        return [
            'pj' => $pj,
            'victorias' => $victorias,
            'derrotas' => $derrotas,
            'porcentaje_victorias' => $porcentajeVictorias,
            'partidos_con_prorroga' => $partidosConProrroga,
            'totales' => [
                'pts' => $pts,
                'fga' => $fga,
                'fgm' => $fgm,
                'fg_fallados' => max(0, $fga - $fgm),
                't2a' => $t2a,
                't2m' => $t2m,
                't2_fallados' => max(0, $t2a - $t2m),
                't3a' => $t3a,
                't3m' => $t3m,
                't3_fallados' => max(0, $t3a - $t3m),
                'fta' => $fta,
                'ftm' => $ftm,
                'ft_fallados' => max(0, $fta - $ftm),
                'reb' => $rebTot,
                'reb_of' => $rebOf,
                'reb_def' => $rebDef,
                'ast' => $ast,
                'stl' => $stl,
                'blk' => $blk,
                'tov' => $tov,
                'pf' => $pf,
                'tf' => $tf,
                'q1' => $q1,
                'q2' => $q2,
                'q3' => $q3,
                'q4' => $q4,
                'pr' => $pr,
            ],
            'medias' => [
                'pts' => round($pts / $divisor, 1),
                'reb' => round($rebTot / $divisor, 1),
                'reb_of' => round($rebOf / $divisor, 1),
                'reb_def' => round($rebDef / $divisor, 1),
                'ast' => round($ast / $divisor, 1),
                'stl' => round($stl / $divisor, 1),
                'blk' => round($blk / $divisor, 1),
                'tov' => round($tov / $divisor, 1),
                'pf' => round($pf / $divisor, 1),
                'tf' => round($tf / $divisor, 1),
                'q1' => round($q1 / $divisor, 1),
                'q2' => round($q2 / $divisor, 1),
                'q3' => round($q3 / $divisor, 1),
                'q4' => round($q4 / $divisor, 1),
                'pr' => round($pr / $divisorProrroga, 1),
            ],
            'porcentajes' => [
                'fg' => $fga > 0 ? round(($fgm / $fga) * 100, 1) : null,
                't2' => $t2a > 0 ? round(($t2m / $t2a) * 100, 1) : null,
                't3' => $t3a > 0 ? round(($t3m / $t3a) * 100, 1) : null,
                'ft' => $fta > 0 ? round(($ftm / $fta) * 100, 1) : null,
            ],
            'ratios' => [
                'ast_per' => $astPer,
            ],
        ];
    }
}
