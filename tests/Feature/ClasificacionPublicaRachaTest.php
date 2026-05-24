<?php

namespace Tests\Feature;

use App\Models\Clasificacion;
use App\Models\ClasificacionFila;
use App\Models\Equipo;
use App\Models\Partido;
use App\Models\PartidoEstadistica;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClasificacionPublicaRachaTest extends TestCase
{
    use RefreshDatabase;

    public function test_muestra_la_racha_de_los_ultimos_cinco_partidos_en_la_tabla_publica(): void
    {
        $equipoPrincipal = Equipo::factory()->create([
            'nombre' => 'CB Bellreguard Senior',
            'es_local' => true,
            'oculto' => false,
            'categoria' => 'Senior',
        ]);

        $rivales = collect(range(1, 6))->map(function (int $index) {
            return Equipo::factory()->create([
                'nombre' => 'Rival ' . $index,
                'es_local' => false,
                'oculto' => false,
                'categoria' => 'Senior',
            ]);
        });

        $clasificacion = Clasificacion::create([
            'nombre' => 'Liga Senior',
            'categoria' => 'Senior',
            'temporada' => '2025/2026',
            'activa' => true,
        ]);

        $clasificacion->equipos()->attach(array_merge([$equipoPrincipal->id], $rivales->pluck('id')->all()));

        ClasificacionFila::create([
            'clasificacion_id' => $clasificacion->id,
            'equipo_id' => $equipoPrincipal->id,
            'jornada' => 6,
            'pj' => 6,
            'pg' => 3,
            'pp' => 2,
            'pf' => 420,
            'pc' => 401,
            'diff' => 19,
            'pts' => 9,
        ]);

        $this->crearPartidoLiga($clasificacion->id, $equipoPrincipal, $rivales[0], '2026-05-01 18:00:00', 72, 66);
        $this->crearPartidoLiga($clasificacion->id, $equipoPrincipal, $rivales[1], '2026-05-08 18:00:00', 61, 67);
        $this->crearPartidoLiga($clasificacion->id, $equipoPrincipal, $rivales[2], '2026-05-15 18:00:00', 70, 70);
        $this->crearPartidoLiga($clasificacion->id, $equipoPrincipal, $rivales[3], '2026-05-22 18:00:00', 80, 71);
        $this->crearPartidoLiga($clasificacion->id, $equipoPrincipal, $rivales[4], '2026-05-29 18:00:00', 77, 62);
        $this->crearPartidoLiga($clasificacion->id, $equipoPrincipal, $rivales[5], '2026-04-24 18:00:00', 95, 40, true);

        $response = $this->get(route('basket.clasificacion', ['clasificacion_id' => $clasificacion->id]));

        $response->assertOk();
        $response->assertSee('Ult. 5', false);
        $response->assertSeeInOrder([
            'title="Victoria · 77-62 vs Rival 5"',
            'title="Victoria · 80-71 vs Rival 4"',
            'title="Empate · 70-70 vs Rival 3"',
            'title="Derrota · 61-67 vs Rival 2"',
            'title="Victoria · 72-66 vs Rival 1"',
        ], false);
        $response->assertDontSee('95-40 vs Rival 6', false);
    }

    private function crearPartidoLiga(
        int $clasificacionId,
        Equipo $equipoLocal,
        Equipo $equipoVisitante,
        string $fechaHora,
        int $puntosLocal,
        int $puntosVisitante,
        bool $ocultoEnClasificacion = false,
    ): void {
        $partido = Partido::create([
            'equipo_local_id' => $equipoLocal->id,
            'equipo_visitante_id' => $equipoVisitante->id,
            'clasificacion_id' => $clasificacionId,
            'fecha_hora' => $fechaHora,
            'lugar' => 'Pabellón Municipal',
            'categoria' => 'Senior',
            'tipo_partido' => 'liga',
            'jornada' => 1,
            'oculto' => false,
            'oculto_en_clasificacion' => $ocultoEnClasificacion,
        ]);

        PartidoEstadistica::create([
            'partido_id' => $partido->id,
            'equipo_id' => $equipoLocal->id,
            'puntos_totales' => $puntosLocal,
        ]);

        PartidoEstadistica::create([
            'partido_id' => $partido->id,
            'equipo_id' => $equipoVisitante->id,
            'puntos_totales' => $puntosVisitante,
        ]);
    }
}
