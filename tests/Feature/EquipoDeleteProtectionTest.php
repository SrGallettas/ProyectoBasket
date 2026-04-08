<?php

namespace Tests\Feature;

use App\Models\Equipo;
use App\Models\Partido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipoDeleteProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_permita_eliminar_un_equipo_con_partidos_asociados(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $equipoLocal = Equipo::create([
            'nombre' => 'Bellreguard Senior',
            'categoria' => 'Senior',
            'imagen_club' => 'club_14.png',
            'es_local' => true,
            'oculto' => false,
        ]);

        $equipoVisitante = Equipo::create([
            'nombre' => 'Rival Senior',
            'categoria' => 'Senior',
            'imagen_club' => 'club_14.png',
            'es_local' => false,
            'oculto' => false,
        ]);

        Partido::create([
            'equipo_local_id' => $equipoLocal->id,
            'equipo_visitante_id' => $equipoVisitante->id,
            'fecha_hora' => '2026-04-07 18:00:00',
            'lugar' => 'Pabellón Municipal',
            'categoria' => 'Senior',
            'tipo_partido' => 'liga',
            'oculto' => false,
        ]);

        $response = $this->actingAs($admin)->delete(route('equipos.destroy', $equipoLocal));

        $response->assertRedirect(route('equipos.index'));
        $response->assertSessionHasErrors([
            'delete' => 'No puedes eliminar este equipo porque tiene partidos asociados.',
        ]);
        $this->assertDatabaseHas('equipos', [
            'id' => $equipoLocal->id,
        ]);
    }
}
