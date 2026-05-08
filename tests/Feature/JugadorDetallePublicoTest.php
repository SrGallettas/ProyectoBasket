<?php

namespace Tests\Feature;

use App\Models\Equipo;
use App\Models\Jugador;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JugadorDetallePublicoTest extends TestCase
{
    use RefreshDatabase;

    public function test_muestra_la_ficha_publica_de_un_jugador_visible(): void
    {
        $equipo = Equipo::factory()->create([
            'nombre' => 'CB Bellreguard Senior',
            'categoria' => 'Senior',
            'es_local' => true,
            'oculto' => false,
        ]);

        $jugador = Jugador::factory()->create([
            'equipo_id' => $equipo->id,
            'nombre' => 'Pau',
            'apellido' => 'Palop',
            'posicion' => 'Base',
            'dorsal' => 7,
            'oculto' => false,
        ]);

        $response = $this->get(route('basket.jugadores.detalle', $jugador));

        $response->assertOk();
        $response->assertSee('Pau');
        $response->assertSee('Palop');
        $response->assertSee('CB Bellreguard Senior');
    }

    public function test_no_muestra_la_ficha_publica_de_un_jugador_oculto(): void
    {
        $equipo = Equipo::factory()->create([
            'es_local' => true,
            'oculto' => false,
        ]);

        $jugador = Jugador::factory()->create([
            'equipo_id' => $equipo->id,
            'oculto' => true,
        ]);

        $response = $this->get(route('basket.jugadores.detalle', $jugador));

        $response->assertNotFound();
    }
}
