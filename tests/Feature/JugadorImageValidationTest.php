<?php

namespace Tests\Feature;

use App\Models\Equipo;
use App\Models\Jugador;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class JugadorImageValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_rechaza_imagen_de_jugador_demasiado_pesada_al_crearlo(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $equipo = Equipo::factory()->create([
            'es_local' => true,
        ]);

        $archivoGrande = UploadedFile::fake()->image('jugador.jpg')->size(3000);

        $response = $this->actingAs($admin)
            ->from(route('jugadores.create'))
            ->post(route('jugadores.store'), [
                'nombre' => 'Pau',
                'apellido' => 'Ferrer',
                'dorsal' => 9,
                'posicion' => 'Base',
                'fecha_nacimiento' => '2005-01-20',
                'equipo_id' => $equipo->id,
                'imagen_jugador' => $archivoGrande,
            ]);

        $response->assertRedirect(route('jugadores.create'));
        $response->assertSessionHasErrors([
            'imagen_jugador' => 'La foto es demasiado pesada (máximo 2MB).',
        ]);
    }

    public function test_rechaza_imagen_de_jugador_demasiado_pesada_al_editarlo(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $equipo = Equipo::factory()->create([
            'es_local' => true,
        ]);

        $jugador = Jugador::factory()->create([
            'equipo_id' => $equipo->id,
        ]);

        $archivoGrande = UploadedFile::fake()->image('jugador.jpg')->size(3000);

        $response = $this->actingAs($admin)
            ->from(route('jugadores.edit', $jugador))
            ->put(route('jugadores.update', $jugador), [
                'nombre' => $jugador->nombre,
                'apellido' => $jugador->apellido,
                'dorsal' => $jugador->dorsal,
                'posicion' => $jugador->posicion,
                'fecha_nacimiento' => $jugador->fecha_nacimiento,
                'equipo_id' => $equipo->id,
                'imagen_jugador' => $archivoGrande,
            ]);

        $response->assertRedirect(route('jugadores.edit', $jugador));
        $response->assertSessionHasErrors([
            'imagen_jugador' => 'La foto es demasiado pesada (máximo 2MB).',
        ]);
    }
}
