<?php

namespace Tests\Feature;

use App\Models\Equipo;
use App\Models\Jugador;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JugadorAdminCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_admin_puede_crear_un_jugador_con_imagen(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $equipo = Equipo::factory()->create([
            'es_local' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('jugadores.store'), [
            'nombre' => 'Pau',
            'apellido' => 'Ferrer',
            'dorsal' => 7,
            'posicion' => 'Base',
            'fecha_nacimiento' => '2005-01-20',
            'equipo_id' => $equipo->id,
            'imagen_jugador' => UploadedFile::fake()->image('jugador.jpg'),
            'oculto' => '1',
        ]);

        $response->assertRedirect(route('jugadores.index'));

        $jugador = Jugador::firstOrFail();

        $this->assertSame('Pau', $jugador->nombre);
        $this->assertTrue($jugador->oculto);
        Storage::disk('public')->assertExists('jugadores/'.$jugador->imagen_jugador);
    }

    public function test_un_admin_puede_actualizar_un_jugador_y_reemplazar_su_imagen(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $equipo = Equipo::factory()->create([
            'es_local' => true,
        ]);

        $jugador = Jugador::factory()->create([
            'equipo_id' => $equipo->id,
            'imagen_jugador' => 'anterior.jpg',
        ]);

        Storage::disk('public')->put('jugadores/anterior.jpg', 'contenido');

        $response = $this->actingAs($admin)->put(route('jugadores.update', $jugador), [
            'nombre' => 'Pep',
            'apellido' => 'Marti',
            'dorsal' => 10,
            'posicion' => 'Alero',
            'fecha_nacimiento' => '2004-02-11',
            'equipo_id' => $equipo->id,
            'imagen_jugador' => UploadedFile::fake()->image('nuevo.jpg'),
        ]);

        $response->assertRedirect(route('jugadores.index'));

        $jugador->refresh();

        $this->assertSame('Pep', $jugador->nombre);
        $this->assertSame('Marti', $jugador->apellido);
        Storage::disk('public')->assertMissing('jugadores/anterior.jpg');
        Storage::disk('public')->assertExists('jugadores/'.$jugador->imagen_jugador);
    }

    public function test_un_admin_puede_eliminar_un_jugador_y_su_imagen(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $equipo = Equipo::factory()->create([
            'es_local' => true,
        ]);

        $jugador = Jugador::factory()->create([
            'equipo_id' => $equipo->id,
            'imagen_jugador' => 'borrar.jpg',
        ]);

        Storage::disk('public')->put('jugadores/borrar.jpg', 'contenido');

        $response = $this->actingAs($admin)->delete(route('jugadores.destroy', $jugador));

        $response->assertRedirect(route('jugadores.index'));
        $this->assertDatabaseMissing('jugadores', ['id' => $jugador->id]);
        Storage::disk('public')->assertMissing('jugadores/borrar.jpg');
    }
}
