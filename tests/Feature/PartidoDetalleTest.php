<?php

namespace Tests\Feature;

use App\Models\Equipo;
use App\Models\Partido;
use App\Models\PartidoCuarto;
use App\Models\PartidoEstadistica;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartidoDetalleTest extends TestCase
{
    use RefreshDatabase;

    public function test_muestra_los_cuartos_del_equipo_correcto_en_el_detalle_publico(): void
    {
        $equipoLocal = Equipo::create([
            'nombre' => 'Bellreguard Local',
            'categoria' => 'Senior',
            'imagen_club' => 'club_14.png',
            'es_local' => true,
            'oculto' => false,
        ]);

        $equipoVisitante = Equipo::create([
            'nombre' => 'Rival Visitante',
            'categoria' => 'Senior',
            'imagen_club' => 'club_14.png',
            'es_local' => false,
            'oculto' => false,
        ]);

        $partido = Partido::create([
            'equipo_local_id' => $equipoLocal->id,
            'equipo_visitante_id' => $equipoVisitante->id,
            'fecha_hora' => '2026-04-07 18:00:00',
            'lugar' => 'Pabellón Municipal',
            'categoria' => 'Senior',
            'tipo_partido' => 'liga',
            'oculto' => false,
        ]);

        PartidoEstadistica::create([
            'partido_id' => $partido->id,
            'equipo_id' => $equipoLocal->id,
            'puntos_totales' => 66,
        ]);

        PartidoEstadistica::create([
            'partido_id' => $partido->id,
            'equipo_id' => $equipoVisitante->id,
            'puntos_totales' => 88,
        ]);

        PartidoCuarto::insert([
            [
                'partido_id' => $partido->id,
                'equipo_id' => $equipoLocal->id,
                'periodo' => 1,
                'puntos' => 11,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'partido_id' => $partido->id,
                'equipo_id' => $equipoLocal->id,
                'periodo' => 2,
                'puntos' => 22,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'partido_id' => $partido->id,
                'equipo_id' => $equipoVisitante->id,
                'periodo' => 1,
                'puntos' => 33,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'partido_id' => $partido->id,
                'equipo_id' => $equipoVisitante->id,
                'periodo' => 2,
                'puntos' => 44,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->get(route('basket.partidos.detalle', $partido->id));

        $response->assertOk();
        $response->assertSeeInOrder([
            '<td>Bellreguard Local</td>',
            '<td>11</td>',
            '<td>22</td>',
            '<td>0</td>',
            '<td>0</td>',
            '<td>0</td>',
            '<td>66</td>',
        ], false);
        $response->assertSeeInOrder([
            '<td>Rival Visitante</td>',
            '<td>33</td>',
            '<td>44</td>',
            '<td>0</td>',
            '<td>0</td>',
            '<td>0</td>',
            '<td>88</td>',
        ], false);
    }
}
