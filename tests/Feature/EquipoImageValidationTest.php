<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class EquipoImageValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_rechaza_escudo_demasiado_pesado_al_crear_equipo(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $archivoGrande = UploadedFile::fake()->image('escudo.jpg')->size(3000);

        $response = $this->actingAs($admin)
            ->from(route('equipos.create'))
            ->post(route('equipos.store'), [
                'nombre' => 'Senior Prueba',
                'categoria' => 'Senior',
                'imagen_club' => $archivoGrande,
            ]);

        $response->assertRedirect(route('equipos.create'));
        $response->assertSessionHasErrors([
            'imagen_club' => 'La imagen es demasiado pesada (máximo 2MB).',
        ]);
    }
}
