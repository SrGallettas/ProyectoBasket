<?php

namespace Tests\Feature;

use App\Models\Equipo;
use App\Models\Jugador;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipoPublicoTest extends TestCase
{
    use RefreshDatabase;

    public function test_muestra_el_equipo_local_visible_con_jugadores_publicados(): void
    {
        $equipo = Equipo::factory()->create([
            'nombre' => 'CB Bellreguard Junior',
            'categoria' => 'Junior',
            'es_local' => true,
            'oculto' => false,
        ]);

        Jugador::factory()->create([
            'equipo_id' => $equipo->id,
            'nombre' => 'Marc',
            'apellido' => 'Soler',
            'oculto' => false,
        ]);

        $response = $this->get(route('basket.equipo', ['categoria' => 'Junior']));

        $response->assertOk();
        $response->assertSee('CB Bellreguard Junior');
        $response->assertSee('Marc');
        $response->assertSee('Soler');
    }

    public function test_no_lista_equipos_sin_jugadores_visibles(): void
    {
        Equipo::factory()->create([
            'nombre' => 'CB Bellreguard Cadete',
            'categoria' => 'Cadete',
            'es_local' => true,
            'oculto' => false,
        ]);

        $response = $this->get(route('basket.equipo'));

        $response->assertOk();
        $response->assertDontSee('CB Bellreguard Cadete');
        $response->assertDontSee('Cadete');
    }
}
