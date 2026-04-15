<?php

namespace Tests\Feature;

use App\Models\Galeria;
use App\Models\ImagenGaleria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GaleriaPrincipalTest extends TestCase
{
    use RefreshDatabase;

    public function test_al_crear_una_galeria_se_guarda_la_imagen_principal_seleccionada(): void
    {
        Storage::fake('public');

        $editor = User::factory()->create([
            'role' => User::ROLE_EDITOR,
        ]);

        $response = $this->actingAs($editor)->post(route('galerias.store'), [
            'titulo' => 'Galeria principal',
            'descripcion' => 'Album de prueba',
            'tipo' => 'equipo',
            'imagen_principal_nueva' => 2,
            'imagenes' => [
                UploadedFile::fake()->image('foto-1.jpg'),
                UploadedFile::fake()->image('foto-2.jpg'),
                UploadedFile::fake()->image('foto-3.jpg'),
            ],
        ]);

        $response->assertRedirect(route('galerias.index'));

        $galeria = Galeria::with('imagenes')->firstOrFail();

        $this->assertCount(3, $galeria->imagenes);
        $this->assertSame(1, $galeria->imagenes->where('es_principal', true)->count());
        $this->assertSame(
            $galeria->imagenes->values()[2]->id,
            $galeria->imagenes->firstWhere('es_principal', true)?->id
        );
    }

    public function test_al_editar_una_galeria_una_imagen_nueva_puede_pasar_a_ser_la_principal(): void
    {
        Storage::fake('public');

        $editor = User::factory()->create([
            'role' => User::ROLE_EDITOR,
        ]);

        $galeria = Galeria::create([
            'titulo' => 'Galeria editable',
            'slug' => 'galeria-editable',
            'carpeta' => 'galeria-editable',
            'descripcion' => 'Album de prueba',
            'tipo' => 'equipo',
            'oculto' => false,
        ]);

        $primeraImagen = ImagenGaleria::create([
            'galeria_id' => $galeria->id,
            'archivo' => 'galeria-editable/existente-1.jpg',
            'es_principal' => true,
        ]);

        ImagenGaleria::create([
            'galeria_id' => $galeria->id,
            'archivo' => 'galeria-editable/existente-2.jpg',
            'es_principal' => false,
        ]);

        Storage::disk('public')->put($primeraImagen->archivo, 'contenido');
        Storage::disk('public')->put('galeria-editable/existente-2.jpg', 'contenido');

        $response = $this->actingAs($editor)->put(route('galerias.update', $galeria), [
            'titulo' => $galeria->titulo,
            'descripcion' => $galeria->descripcion,
            'tipo' => $galeria->tipo,
            'imagen_principal_nueva' => 1,
            'imagenes' => [
                UploadedFile::fake()->image('nueva-1.jpg'),
                UploadedFile::fake()->image('nueva-2.jpg'),
            ],
        ]);

        $response->assertRedirect(route('galerias.index'));

        $galeria->refresh()->load('imagenes');
        $imagenesOrdenadas = $galeria->imagenes->sortBy('id')->values();
        $imagenPrincipal = $imagenesOrdenadas->firstWhere('es_principal', true);

        $this->assertCount(4, $galeria->imagenes);
        $this->assertSame(1, $galeria->imagenes->where('es_principal', true)->count());
        $this->assertNotNull($imagenPrincipal);
        $this->assertSame($imagenesOrdenadas[3]->id, $imagenPrincipal->id);
    }
}
