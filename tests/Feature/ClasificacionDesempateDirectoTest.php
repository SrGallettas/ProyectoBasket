<?php

namespace Tests\Feature;

use App\Models\Clasificacion;
use App\Models\ClasificacionFila;
use App\Models\Equipo;
use App\Models\Partido;
use App\Models\PartidoEstadistica;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClasificacionDesempateDirectoTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_clasificacion_publica_prioriza_el_enfrentamiento_directo_antes_del_average_general(): void
    {
        [$clasificacion, $equipoA, $equipoB] = $this->crearEscenarioEmpateConMejorAverageGeneralPeroPeorDirecto();

        $response = $this->get(route('basket.clasificacion', ['clasificacion_id' => $clasificacion->id, 'jornada' => 3]));

        $response->assertOk();
        $response->assertSeeInOrder([
            '<strong>1</strong></td>',
            $equipoB->nombre,
            '<strong>2</strong></td>',
            $equipoA->nombre,
        ], false);
    }

    public function test_la_clasificacion_admin_prioriza_el_enfrentamiento_directo_antes_del_average_general(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        [$clasificacion, $equipoA, $equipoB] = $this->crearEscenarioEmpateConMejorAverageGeneralPeroPeorDirecto();

        $response = $this->actingAs($admin)->get(route('clasificaciones.show', ['clasificacion' => $clasificacion->id, 'jornada' => 3]));

        $response->assertOk();
        $response->assertSeeInOrder([
            '<td>1</td>',
            $equipoB->nombre,
            '<td>2</td>',
            $equipoA->nombre,
        ], false);
    }

    private function crearEscenarioEmpateConMejorAverageGeneralPeroPeorDirecto(): array
    {
        $equipoA = Equipo::factory()->create([
            'nombre' => 'CB Bellreguard A',
            'categoria' => 'Senior',
            'es_local' => true,
            'oculto' => false,
        ]);

        $equipoB = Equipo::factory()->create([
            'nombre' => 'CB Bellreguard B',
            'categoria' => 'Senior',
            'es_local' => false,
            'oculto' => false,
        ]);

        $equipoC = Equipo::factory()->create([
            'nombre' => 'CB Bellreguard C',
            'categoria' => 'Senior',
            'es_local' => false,
            'oculto' => false,
        ]);

        $clasificacion = Clasificacion::create([
            'nombre' => 'Liga Senior',
            'categoria' => 'Senior',
            'temporada' => '2025/2026',
            'activa' => true,
        ]);

        $clasificacion->equipos()->attach([$equipoA->id, $equipoB->id, $equipoC->id]);

        $this->crearPartido($clasificacion, 1, $equipoA, $equipoB, 60, 70);
        $this->crearPartido($clasificacion, 2, $equipoA, $equipoC, 95, 60);
        $this->crearPartido($clasificacion, 3, $equipoB, $equipoC, 61, 30);

        $this->recalcularClasificacion($clasificacion);

        return [$clasificacion, $equipoA, $equipoB];
    }

    private function recalcularClasificacion(Clasificacion $clasificacion): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('clasificaciones.recalcular', $clasificacion))
            ->assertRedirect(route('clasificaciones.show', $clasificacion));

        $this->assertDatabaseHas('clasificacion_filas', [
            'clasificacion_id' => $clasificacion->id,
            'equipo_id' => $clasificacion->equipos()->first()->id,
            'jornada' => 3,
        ]);
    }

    private function crearPartido(Clasificacion $clasificacion, int $jornada, Equipo $local, Equipo $visitante, int $puntosLocal, int $puntosVisitante): void
    {
        $partido = Partido::create([
            'equipo_local_id' => $local->id,
            'equipo_visitante_id' => $visitante->id,
            'clasificacion_id' => $clasificacion->id,
            'fecha_hora' => sprintf('2026-05-%02d 18:00:00', 10 + $jornada),
            'lugar' => 'Pabellón Municipal',
            'categoria' => 'Senior',
            'tipo_partido' => 'liga',
            'jornada' => $jornada,
            'oculto' => false,
            'oculto_en_clasificacion' => false,
        ]);

        PartidoEstadistica::create([
            'partido_id' => $partido->id,
            'equipo_id' => $local->id,
            'puntos_totales' => $puntosLocal,
        ]);

        PartidoEstadistica::create([
            'partido_id' => $partido->id,
            'equipo_id' => $visitante->id,
            'puntos_totales' => $puntosVisitante,
        ]);
    }
}
