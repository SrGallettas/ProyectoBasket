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

class ClasificacionRecalculoTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_cuenta_en_la_tabla_los_partidos_ocultos_en_clasificacion(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $equipoLocal = Equipo::factory()->create([
            'es_local' => true,
            'oculto' => false,
            'categoria' => 'Senior',
        ]);

        $equipoVisitante = Equipo::factory()->create([
            'es_local' => false,
            'oculto' => false,
            'categoria' => 'Senior',
        ]);

        $clasificacion = Clasificacion::create([
            'nombre' => 'Liga Senior',
            'categoria' => 'Senior',
            'temporada' => '2025/2026',
            'activa' => true,
        ]);

        $clasificacion->equipos()->attach([$equipoLocal->id, $equipoVisitante->id]);

        $partidoVisible = Partido::create([
            'equipo_local_id' => $equipoLocal->id,
            'equipo_visitante_id' => $equipoVisitante->id,
            'clasificacion_id' => $clasificacion->id,
            'fecha_hora' => '2026-05-10 18:00:00',
            'lugar' => 'Pabellón Municipal',
            'categoria' => 'Senior',
            'tipo_partido' => 'liga',
            'jornada' => 1,
            'oculto' => false,
            'oculto_en_clasificacion' => false,
        ]);

        PartidoEstadistica::create([
            'partido_id' => $partidoVisible->id,
            'equipo_id' => $equipoLocal->id,
            'puntos_totales' => 70,
        ]);

        PartidoEstadistica::create([
            'partido_id' => $partidoVisible->id,
            'equipo_id' => $equipoVisitante->id,
            'puntos_totales' => 60,
        ]);

        $partidoOculto = Partido::create([
            'equipo_local_id' => $equipoLocal->id,
            'equipo_visitante_id' => $equipoVisitante->id,
            'clasificacion_id' => $clasificacion->id,
            'fecha_hora' => '2026-05-17 18:00:00',
            'lugar' => 'Pabellón Municipal',
            'categoria' => 'Senior',
            'tipo_partido' => 'liga',
            'jornada' => 2,
            'oculto' => false,
            'oculto_en_clasificacion' => true,
        ]);

        PartidoEstadistica::create([
            'partido_id' => $partidoOculto->id,
            'equipo_id' => $equipoLocal->id,
            'puntos_totales' => 80,
        ]);

        PartidoEstadistica::create([
            'partido_id' => $partidoOculto->id,
            'equipo_id' => $equipoVisitante->id,
            'puntos_totales' => 50,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('clasificaciones.recalcular', $clasificacion));

        $response->assertRedirect(route('clasificaciones.show', $clasificacion));

        $filaLocal = ClasificacionFila::query()
            ->where('clasificacion_id', $clasificacion->id)
            ->where('equipo_id', $equipoLocal->id)
            ->latest('jornada')
            ->first();

        $filaVisitante = ClasificacionFila::query()
            ->where('clasificacion_id', $clasificacion->id)
            ->where('equipo_id', $equipoVisitante->id)
            ->latest('jornada')
            ->first();

        $this->assertNotNull($filaLocal);
        $this->assertNotNull($filaVisitante);

        $this->assertSame(1, $filaLocal->jornada);
        $this->assertSame(1, $filaLocal->pj);
        $this->assertSame(1, $filaLocal->pg);
        $this->assertSame(0, $filaLocal->pp);
        $this->assertSame(70, $filaLocal->pf);
        $this->assertSame(60, $filaLocal->pc);
        $this->assertSame(10, $filaLocal->diff);
        $this->assertSame(2, $filaLocal->pts);

        $this->assertSame(1, $filaVisitante->jornada);
        $this->assertSame(1, $filaVisitante->pj);
        $this->assertSame(0, $filaVisitante->pg);
        $this->assertSame(1, $filaVisitante->pp);
        $this->assertSame(60, $filaVisitante->pf);
        $this->assertSame(70, $filaVisitante->pc);
        $this->assertSame(-10, $filaVisitante->diff);
        $this->assertSame(1, $filaVisitante->pts);
    }

    public function test_recalcula_automaticamente_la_tabla_si_cambian_los_equipos_de_la_clasificacion(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $equipoLocal = Equipo::factory()->create([
            'es_local' => true,
            'oculto' => false,
            'categoria' => 'Senior',
        ]);

        $equipoVisitanteOriginal = Equipo::factory()->create([
            'es_local' => false,
            'oculto' => false,
            'categoria' => 'Senior',
        ]);

        $equipoVisitanteNuevo = Equipo::factory()->create([
            'es_local' => false,
            'oculto' => false,
            'categoria' => 'Senior',
        ]);

        $clasificacion = Clasificacion::create([
            'nombre' => 'Liga Senior',
            'categoria' => 'Senior',
            'temporada' => '2025/2026',
            'activa' => true,
        ]);

        $clasificacion->equipos()->attach([$equipoLocal->id, $equipoVisitanteOriginal->id]);

        $partido = Partido::create([
            'equipo_local_id' => $equipoLocal->id,
            'equipo_visitante_id' => $equipoVisitanteOriginal->id,
            'clasificacion_id' => $clasificacion->id,
            'fecha_hora' => '2026-05-10 18:00:00',
            'lugar' => 'Pabellón Municipal',
            'categoria' => 'Senior',
            'tipo_partido' => 'liga',
            'jornada' => 1,
            'oculto' => false,
            'oculto_en_clasificacion' => false,
        ]);

        PartidoEstadistica::create([
            'partido_id' => $partido->id,
            'equipo_id' => $equipoLocal->id,
            'puntos_totales' => 70,
        ]);

        PartidoEstadistica::create([
            'partido_id' => $partido->id,
            'equipo_id' => $equipoVisitanteOriginal->id,
            'puntos_totales' => 60,
        ]);

        $this->actingAs($admin)
            ->post(route('clasificaciones.recalcular', $clasificacion))
            ->assertRedirect(route('clasificaciones.show', $clasificacion));

        $this->assertDatabaseHas('clasificacion_filas', [
            'clasificacion_id' => $clasificacion->id,
            'equipo_id' => $equipoVisitanteOriginal->id,
            'jornada' => 1,
        ]);

        $response = $this
            ->actingAs($admin)
            ->put(route('clasificaciones.update', $clasificacion), [
                'nombre' => 'Liga Senior',
                'categoria' => 'Senior',
                'temporada' => '2025/2026',
                'activa' => 1,
                'equipos' => [$equipoLocal->id, $equipoVisitanteNuevo->id],
            ]);

        $response->assertRedirect(route('clasificaciones.index'));

        $this->assertDatabaseMissing('clasificacion_filas', [
            'clasificacion_id' => $clasificacion->id,
            'equipo_id' => $equipoVisitanteOriginal->id,
        ]);

        $this->assertDatabaseMissing('clasificacion_filas', [
            'clasificacion_id' => $clasificacion->id,
            'equipo_id' => $equipoLocal->id,
            'jornada' => 1,
        ]);

        $this->assertDatabaseCount('clasificacion_filas', 0);
    }
}
