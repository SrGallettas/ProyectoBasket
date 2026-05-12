<?php

namespace Tests\Feature;

use App\Models\Galeria;
use App\Models\ImagenGaleria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GaleriaAdminCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_editor_puede_crear_una_galeria_con_imagenes(): void
    {
        Storage::fake('public');

        $editor = User::factory()->create([
            'role' => User::ROLE_EDITOR,
        ]);

        $response = $this->actingAs($editor)->post(route('galerias.store'), [
            'titulo' => 'Album senior',
            'descripcion' => 'Resumen fotografico',
            'tipo' => 'partidos',
            'imagenes' => [
                UploadedFile::fake()->image('foto-1.jpg'),
                UploadedFile::fake()->image('foto-2.jpg'),
            ],
            'imagen_principal_nueva' => 1,
        ]);

        $response->assertRedirect(route('galerias.index'));

        $galeria = Galeria::with('imagenes')->firstOrFail();

        $this->assertSame('album-senior', $galeria->slug);
        $this->assertCount(2, $galeria->imagenes);
        $this->assertSame(1, $galeria->imagenes->where('es_principal', true)->count());

        foreach ($galeria->imagenes as $imagen) {
            Storage::disk('public')->assertExists('galerias/'.$imagen->archivo);
        }
    }

    public function test_un_editor_no_puede_dejar_una_galeria_sin_imagenes_al_editarla(): void
    {
        Storage::fake('public');

        $editor = User::factory()->create([
            'role' => User::ROLE_EDITOR,
        ]);

        $galeria = Galeria::create([
            'titulo' => 'Album editable',
            'slug' => 'album-editable',
            'carpeta' => 'album-editable',
            'descripcion' => 'Resumen',
            'tipo' => 'equipo',
            'oculto' => false,
        ]);

        $imagen = ImagenGaleria::create([
            'galeria_id' => $galeria->id,
            'archivo' => 'album-editable/existente.jpg',
            'es_principal' => true,
        ]);

        Storage::disk('public')->put('galerias/'.$imagen->archivo, 'contenido');

        $response = $this->actingAs($editor)
            ->from(route('galerias.edit', $galeria))
            ->put(route('galerias.update', $galeria), [
                'titulo' => $galeria->titulo,
                'descripcion' => $galeria->descripcion,
                'tipo' => $galeria->tipo,
                'eliminar_imagenes' => [$imagen->id],
            ]);

        $response->assertRedirect(route('galerias.edit', $galeria));
        $response->assertSessionHasErrors([
            'imagenes' => 'La galeria debe tener al menos una imagen.',
        ]);
        $this->assertDatabaseHas('imagenes_galeria', ['id' => $imagen->id]);
    }

    public function test_un_editor_puede_eliminar_una_galeria_y_sus_imagenes(): void
    {
        Storage::fake('public');

        $editor = User::factory()->create([
            'role' => User::ROLE_EDITOR,
        ]);

        $galeria = Galeria::create([
            'titulo' => 'Album borrar',
            'slug' => 'album-borrar',
            'carpeta' => 'album-borrar',
            'descripcion' => 'Resumen',
            'tipo' => 'eventos',
            'oculto' => false,
        ]);

        $imagen = ImagenGaleria::create([
            'galeria_id' => $galeria->id,
            'archivo' => 'album-borrar/foto.jpg',
            'es_principal' => true,
        ]);

        Storage::disk('public')->put('galerias/'.$imagen->archivo, 'contenido');

        $response = $this->actingAs($editor)->delete(route('galerias.destroy', $galeria));

        $response->assertRedirect(route('galerias.index'));
        $this->assertDatabaseMissing('galerias', ['id' => $galeria->id]);
        $this->assertDatabaseMissing('imagenes_galeria', ['id' => $imagen->id]);
        Storage::disk('public')->assertMissing('galerias/'.$imagen->archivo);
    }
}
