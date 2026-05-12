<?php

namespace Tests\Feature;

use App\Models\Equipo;
use App\Models\Partido;
use App\Models\PartidoAccionAnotacion;
use App\Models\PartidoCuarto;
use App\Models\PartidoEstadistica;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartidoAnotacionAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_registra_una_accion_rapida_y_suma_puntos_al_cuarto_activo(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $equipoLocal = Equipo::factory()->create([
            'es_local' => true,
            'oculto' => false,
        ]);

        $equipoVisitante = Equipo::factory()->create([
            'es_local' => false,
            'oculto' => false,
        ]);

        $partido = Partido::create([
            'equipo_local_id' => $equipoLocal->id,
            'equipo_visitante_id' => $equipoVisitante->id,
            'fecha_hora' => '2026-05-10 18:00:00',
            'lugar' => 'Pabellón Municipal',
            'categoria' => 'Senior',
            'tipo_partido' => 'liga',
            'oculto' => false,
            'oculto_en_clasificacion' => false,
        ]);

        $response = $this
            ->actingAs($admin)
            ->withSession(['partidos_anotacion.'.$partido->id.'.periodo' => 2])
            ->post(route('partidos.anotacion.accion', $partido), [
                'lado' => 'local',
                'accion' => 'three_made',
            ]);

        $response->assertRedirect(route('partidos.anotacion', $partido));

        $this->assertDatabaseHas('partido_estadisticas', [
            'partido_id' => $partido->id,
            'equipo_id' => $equipoLocal->id,
            't3m' => 1,
            't3a' => 1,
            'fgm' => 1,
            'fga' => 1,
            'puntos_totales' => 3,
        ]);

        $this->assertDatabaseHas('partido_cuartos', [
            'partido_id' => $partido->id,
            'equipo_id' => $equipoLocal->id,
            'periodo' => 2,
            'puntos' => 3,
        ]);

        $this->assertDatabaseHas('partido_acciones_anotacion', [
            'partido_id' => $partido->id,
            'equipo_id' => $equipoLocal->id,
            'lado' => 'local',
            'accion' => 'three_made',
            'periodo' => 2,
            'user_id' => $admin->id,
        ]);
    }

    public function test_acumula_varias_acciones_consecutivas_en_el_mismo_partido(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $equipoLocal = Equipo::factory()->create([
            'es_local' => true,
            'oculto' => false,
        ]);

        $equipoVisitante = Equipo::factory()->create([
            'es_local' => false,
            'oculto' => false,
        ]);

        $partido = Partido::create([
            'equipo_local_id' => $equipoLocal->id,
            'equipo_visitante_id' => $equipoVisitante->id,
            'fecha_hora' => '2026-05-10 18:00:00',
            'lugar' => 'Pabellón Municipal',
            'categoria' => 'Senior',
            'tipo_partido' => 'liga',
            'oculto' => false,
            'oculto_en_clasificacion' => false,
        ]);

        $this->actingAs($admin)
            ->withSession(['partidos_anotacion.'.$partido->id.'.periodo' => 3])
            ->post(route('partidos.anotacion.accion', $partido), [
                'lado' => 'local',
                'accion' => 'two_made',
            ])
            ->assertRedirect(route('partidos.anotacion', $partido));

        $this->actingAs($admin)
            ->withSession(['partidos_anotacion.'.$partido->id.'.periodo' => 3])
            ->post(route('partidos.anotacion.accion', $partido), [
                'lado' => 'local',
                'accion' => 'ft_made',
            ])
            ->assertRedirect(route('partidos.anotacion', $partido));

        $this->assertDatabaseHas('partido_estadisticas', [
            'partido_id' => $partido->id,
            'equipo_id' => $equipoLocal->id,
            'fgm' => 1,
            'fga' => 1,
            't2m' => 1,
            't2a' => 1,
            'ftm' => 1,
            'fta' => 1,
            'puntos_totales' => 3,
        ]);

        $this->assertDatabaseHas('partido_cuartos', [
            'partido_id' => $partido->id,
            'equipo_id' => $equipoLocal->id,
            'periodo' => 3,
            'puntos' => 3,
        ]);

        $this->assertDatabaseCount('partido_acciones_anotacion', 2);
    }

    public function test_deshace_la_ultima_accion_registrada(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $equipoLocal = Equipo::factory()->create([
            'es_local' => true,
            'oculto' => false,
        ]);

        $equipoVisitante = Equipo::factory()->create([
            'es_local' => false,
            'oculto' => false,
        ]);

        $partido = Partido::create([
            'equipo_local_id' => $equipoLocal->id,
            'equipo_visitante_id' => $equipoVisitante->id,
            'fecha_hora' => '2026-05-10 18:00:00',
            'lugar' => 'Pabellón Municipal',
            'categoria' => 'Senior',
            'tipo_partido' => 'liga',
            'oculto' => false,
            'oculto_en_clasificacion' => false,
        ]);

        PartidoEstadistica::create([
            'partido_id' => $partido->id,
            'equipo_id' => $equipoLocal->id,
            'fgm' => 1,
            'fga' => 1,
            't2m' => 1,
            't2a' => 1,
            't3m' => 0,
            't3a' => 0,
            'ftm' => 0,
            'fta' => 0,
            'reb_of' => 0,
            'reb_def' => 0,
            'ast' => 0,
            'blk' => 0,
            'tov' => 0,
            'stl' => 0,
            'pf' => 0,
            'tf' => 0,
            'puntos_totales' => 2,
        ]);

        PartidoCuarto::create([
            'partido_id' => $partido->id,
            'equipo_id' => $equipoLocal->id,
            'periodo' => 1,
            'puntos' => 2,
        ]);

        PartidoAccionAnotacion::create([
            'partido_id' => $partido->id,
            'equipo_id' => $equipoLocal->id,
            'lado' => 'local',
            'accion' => 'two_made',
            'periodo' => 1,
            'user_id' => $admin->id,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('partidos.anotacion.deshacer', $partido));

        $response->assertRedirect(route('partidos.anotacion', $partido));

        $this->assertDatabaseHas('partido_estadisticas', [
            'partido_id' => $partido->id,
            'equipo_id' => $equipoLocal->id,
            'fgm' => 0,
            'fga' => 0,
            't2m' => 0,
            't2a' => 0,
            'puntos_totales' => 0,
        ]);

        $this->assertDatabaseHas('partido_cuartos', [
            'partido_id' => $partido->id,
            'equipo_id' => $equipoLocal->id,
            'periodo' => 1,
            'puntos' => 0,
        ]);

        $this->assertDatabaseMissing('partido_acciones_anotacion', [
            'partido_id' => $partido->id,
            'equipo_id' => $equipoLocal->id,
            'accion' => 'two_made',
            'periodo' => 1,
        ]);
    }

    public function test_muestra_error_si_no_hay_acciones_para_deshacer(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $equipoLocal = Equipo::factory()->create([
            'es_local' => true,
            'oculto' => false,
        ]);

        $equipoVisitante = Equipo::factory()->create([
            'es_local' => false,
            'oculto' => false,
        ]);

        $partido = Partido::create([
            'equipo_local_id' => $equipoLocal->id,
            'equipo_visitante_id' => $equipoVisitante->id,
            'fecha_hora' => '2026-05-10 18:00:00',
            'lugar' => 'Pabellón Municipal',
            'categoria' => 'Senior',
            'tipo_partido' => 'liga',
            'oculto' => false,
            'oculto_en_clasificacion' => false,
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('partidos.anotacion', $partido))
            ->post(route('partidos.anotacion.deshacer', $partido));

        $response->assertRedirect(route('partidos.anotacion', $partido));
        $response->assertSessionHasErrors(['anotacion']);
    }

    public function test_deshace_la_ultima_accion_real_del_partido_aunque_la_haya_hecho_otro_usuario(): void
    {
        $adminUno = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $adminDos = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $equipoLocal = Equipo::factory()->create([
            'es_local' => true,
            'oculto' => false,
        ]);

        $equipoVisitante = Equipo::factory()->create([
            'es_local' => false,
            'oculto' => false,
        ]);

        $partido = Partido::create([
            'equipo_local_id' => $equipoLocal->id,
            'equipo_visitante_id' => $equipoVisitante->id,
            'fecha_hora' => '2026-05-10 18:00:00',
            'lugar' => 'Pabellón Municipal',
            'categoria' => 'Senior',
            'tipo_partido' => 'liga',
            'oculto' => false,
            'oculto_en_clasificacion' => false,
        ]);

        PartidoEstadistica::create([
            'partido_id' => $partido->id,
            'equipo_id' => $equipoLocal->id,
            'fgm' => 1,
            'fga' => 1,
            't2m' => 1,
            't2a' => 1,
            't3m' => 0,
            't3a' => 0,
            'ftm' => 0,
            'fta' => 0,
            'reb_of' => 0,
            'reb_def' => 0,
            'ast' => 0,
            'blk' => 0,
            'tov' => 0,
            'stl' => 0,
            'pf' => 0,
            'tf' => 0,
            'puntos_totales' => 2,
        ]);

        PartidoEstadistica::create([
            'partido_id' => $partido->id,
            'equipo_id' => $equipoVisitante->id,
            'fgm' => 1,
            'fga' => 1,
            't2m' => 0,
            't2a' => 0,
            't3m' => 1,
            't3a' => 1,
            'ftm' => 0,
            'fta' => 0,
            'reb_of' => 0,
            'reb_def' => 0,
            'ast' => 0,
            'blk' => 0,
            'tov' => 0,
            'stl' => 0,
            'pf' => 0,
            'tf' => 0,
            'puntos_totales' => 3,
        ]);

        PartidoCuarto::create([
            'partido_id' => $partido->id,
            'equipo_id' => $equipoLocal->id,
            'periodo' => 1,
            'puntos' => 2,
        ]);

        PartidoCuarto::create([
            'partido_id' => $partido->id,
            'equipo_id' => $equipoVisitante->id,
            'periodo' => 1,
            'puntos' => 3,
        ]);

        PartidoAccionAnotacion::create([
            'partido_id' => $partido->id,
            'equipo_id' => $equipoLocal->id,
            'lado' => 'local',
            'accion' => 'two_made',
            'periodo' => 1,
            'user_id' => $adminUno->id,
        ]);

        PartidoAccionAnotacion::create([
            'partido_id' => $partido->id,
            'equipo_id' => $equipoVisitante->id,
            'lado' => 'visitante',
            'accion' => 'three_made',
            'periodo' => 1,
            'user_id' => $adminDos->id,
        ]);

        $response = $this
            ->actingAs($adminUno)
            ->post(route('partidos.anotacion.deshacer', $partido));

        $response->assertRedirect(route('partidos.anotacion', $partido));

        $this->assertDatabaseHas('partido_estadisticas', [
            'partido_id' => $partido->id,
            'equipo_id' => $equipoLocal->id,
            'puntos_totales' => 2,
        ]);

        $this->assertDatabaseHas('partido_estadisticas', [
            'partido_id' => $partido->id,
            'equipo_id' => $equipoVisitante->id,
            'fgm' => 0,
            'fga' => 0,
            't3m' => 0,
            't3a' => 0,
            'puntos_totales' => 0,
        ]);

        $this->assertDatabaseHas('partido_cuartos', [
            'partido_id' => $partido->id,
            'equipo_id' => $equipoLocal->id,
            'periodo' => 1,
            'puntos' => 2,
        ]);

        $this->assertDatabaseHas('partido_cuartos', [
            'partido_id' => $partido->id,
            'equipo_id' => $equipoVisitante->id,
            'periodo' => 1,
            'puntos' => 0,
        ]);

        $this->assertDatabaseHas('partido_acciones_anotacion', [
            'partido_id' => $partido->id,
            'equipo_id' => $equipoLocal->id,
            'accion' => 'two_made',
        ]);

        $this->assertDatabaseMissing('partido_acciones_anotacion', [
            'partido_id' => $partido->id,
            'equipo_id' => $equipoVisitante->id,
            'accion' => 'three_made',
        ]);
    }

    public function test_al_cambiar_los_equipos_del_partido_se_limpian_stats_cuartos_y_acciones_previas(): void
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

        $partido = Partido::create([
            'equipo_local_id' => $equipoLocal->id,
            'equipo_visitante_id' => $equipoVisitanteOriginal->id,
            'fecha_hora' => '2026-05-10 18:00:00',
            'lugar' => 'Pabellón Municipal',
            'categoria' => 'Senior',
            'tipo_partido' => 'amistoso',
            'oculto' => false,
            'oculto_en_clasificacion' => false,
        ]);

        PartidoEstadistica::create([
            'partido_id' => $partido->id,
            'equipo_id' => $equipoLocal->id,
            'fgm' => 1,
            'fga' => 1,
            't2m' => 1,
            't2a' => 1,
            't3m' => 0,
            't3a' => 0,
            'ftm' => 0,
            'fta' => 0,
            'reb_of' => 0,
            'reb_def' => 0,
            'ast' => 0,
            'blk' => 0,
            'tov' => 0,
            'stl' => 0,
            'pf' => 0,
            'tf' => 0,
            'puntos_totales' => 2,
        ]);

        PartidoEstadistica::create([
            'partido_id' => $partido->id,
            'equipo_id' => $equipoVisitanteOriginal->id,
            'fgm' => 1,
            'fga' => 1,
            't2m' => 0,
            't2a' => 0,
            't3m' => 1,
            't3a' => 1,
            'ftm' => 0,
            'fta' => 0,
            'reb_of' => 0,
            'reb_def' => 0,
            'ast' => 0,
            'blk' => 0,
            'tov' => 0,
            'stl' => 0,
            'pf' => 0,
            'tf' => 0,
            'puntos_totales' => 3,
        ]);

        PartidoCuarto::create([
            'partido_id' => $partido->id,
            'equipo_id' => $equipoVisitanteOriginal->id,
            'periodo' => 1,
            'puntos' => 3,
        ]);

        PartidoAccionAnotacion::create([
            'partido_id' => $partido->id,
            'equipo_id' => $equipoVisitanteOriginal->id,
            'lado' => 'visitante',
            'accion' => 'three_made',
            'periodo' => 1,
            'user_id' => $admin->id,
        ]);

        $response = $this
            ->actingAs($admin)
            ->put(route('partidos.update', $partido), [
                'equipo_local_id' => $equipoLocal->id,
                'equipo_visitante_id' => $equipoVisitanteNuevo->id,
                'fecha_hora' => '2026-05-10 18:00:00',
                'lugar' => 'Pabellón Municipal',
                'categoria' => 'Senior',
                'tipo_partido' => 'amistoso',
            ]);

        $response->assertRedirect(route('partidos.index'));

        $partido->refresh();

        $this->assertSame($equipoVisitanteNuevo->id, $partido->equipo_visitante_id);

        $this->assertDatabaseMissing('partido_estadisticas', [
            'partido_id' => $partido->id,
            'equipo_id' => $equipoVisitanteOriginal->id,
        ]);

        $this->assertDatabaseMissing('partido_cuartos', [
            'partido_id' => $partido->id,
            'equipo_id' => $equipoVisitanteOriginal->id,
        ]);

        $this->assertDatabaseMissing('partido_acciones_anotacion', [
            'partido_id' => $partido->id,
            'equipo_id' => $equipoVisitanteOriginal->id,
        ]);

        $this->assertDatabaseCount('partido_estadisticas', 0);
        $this->assertDatabaseCount('partido_cuartos', 0);
        $this->assertDatabaseCount('partido_acciones_anotacion', 0);
    }

    public function test_un_editor_no_puede_entrar_a_la_mesa_de_anotacion(): void
    {
        $editor = User::factory()->create([
            'role' => User::ROLE_EDITOR,
        ]);

        $equipoLocal = Equipo::factory()->create([
            'es_local' => true,
            'oculto' => false,
        ]);

        $equipoVisitante = Equipo::factory()->create([
            'es_local' => false,
            'oculto' => false,
        ]);

        $partido = Partido::create([
            'equipo_local_id' => $equipoLocal->id,
            'equipo_visitante_id' => $equipoVisitante->id,
            'fecha_hora' => '2026-05-10 18:00:00',
            'lugar' => 'Pabellón Municipal',
            'categoria' => 'Senior',
            'tipo_partido' => 'amistoso',
            'oculto' => false,
            'oculto_en_clasificacion' => false,
        ]);

        $this->actingAs($editor)
            ->get(route('partidos.anotacion', $partido))
            ->assertForbidden();
    }

    public function test_no_permite_guardar_puntos_totales_que_no_cuadran_con_t2_t3_y_tiros_libres(): void
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

        $partido = Partido::create([
            'equipo_local_id' => $equipoLocal->id,
            'equipo_visitante_id' => $equipoVisitante->id,
            'fecha_hora' => '2026-05-10 18:00:00',
            'lugar' => 'Pabellón Municipal',
            'categoria' => 'Senior',
            'tipo_partido' => 'amistoso',
            'oculto' => false,
            'oculto_en_clasificacion' => false,
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('partidos.edit', $partido))
            ->put(route('partidos.update', $partido), [
                'equipo_local_id' => $equipoLocal->id,
                'equipo_visitante_id' => $equipoVisitante->id,
                'fecha_hora' => '2026-05-10 18:00:00',
                'lugar' => 'Pabellón Municipal',
                'categoria' => 'Senior',
                'tipo_partido' => 'amistoso',
                'stats' => [
                    'local' => [
                        'fga' => 8,
                        'fgm' => 8,
                        't2a' => 5,
                        't2m' => 5,
                        't3a' => 3,
                        't3m' => 3,
                        'fta' => 2,
                        'ftm' => 2,
                        'reb_of' => 0,
                        'reb_def' => 0,
                        'ast' => 0,
                        'blk' => 0,
                        'tov' => 0,
                        'stl' => 0,
                        'pf' => 0,
                        'tf' => 0,
                        'puntos_totales' => 10,
                    ],
                ],
            ]);

        $response->assertRedirect(route('partidos.edit', $partido));
        $response->assertSessionHasErrors(['stats.local.puntos_totales']);
    }

    public function test_no_permite_guardar_cuartos_que_no_cuadran_con_los_puntos_totales(): void
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

        $partido = Partido::create([
            'equipo_local_id' => $equipoLocal->id,
            'equipo_visitante_id' => $equipoVisitante->id,
            'fecha_hora' => '2026-05-10 18:00:00',
            'lugar' => 'Pabellón Municipal',
            'categoria' => 'Senior',
            'tipo_partido' => 'amistoso',
            'oculto' => false,
            'oculto_en_clasificacion' => false,
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('partidos.edit', $partido))
            ->put(route('partidos.update', $partido), [
                'equipo_local_id' => $equipoLocal->id,
                'equipo_visitante_id' => $equipoVisitante->id,
                'fecha_hora' => '2026-05-10 18:00:00',
                'lugar' => 'Pabellón Municipal',
                'categoria' => 'Senior',
                'tipo_partido' => 'amistoso',
                'stats' => [
                    'local' => [
                        'fga' => 6,
                        'fgm' => 6,
                        't2a' => 6,
                        't2m' => 6,
                        't3a' => 0,
                        't3m' => 0,
                        'fta' => 0,
                        'ftm' => 0,
                        'reb_of' => 0,
                        'reb_def' => 0,
                        'ast' => 0,
                        'blk' => 0,
                        'tov' => 0,
                        'stl' => 0,
                        'pf' => 0,
                        'tf' => 0,
                        'puntos_totales' => 12,
                    ],
                ],
                'quarters' => [
                    'local' => [
                        1 => 4,
                        2 => 4,
                        3 => 1,
                        4 => 1,
                    ],
                ],
            ]);

        $response->assertRedirect(route('partidos.edit', $partido));
        $response->assertSessionHasErrors(['quarters.local']);
    }
}
