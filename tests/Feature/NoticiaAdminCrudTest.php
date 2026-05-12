<?php

namespace Tests\Feature;

use App\Models\Noticia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NoticiaAdminCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_editor_puede_crear_una_noticia_con_slug_unico_e_imagen(): void
    {
        Storage::fake('public');

        $editor = User::factory()->create([
            'role' => User::ROLE_EDITOR,
        ]);

        Noticia::factory()->create([
            'titulo' => 'Cronica del senior',
            'slug' => 'cronica-del-senior',
        ]);

        $response = $this->actingAs($editor)->post(route('noticias.store'), [
            'titulo' => 'Cronica del senior',
            'subtitulo' => 'Resumen del partido',
            'contenido' => 'Contenido de prueba de la noticia.',
            'autor' => 'Club',
            'imagen_noticia' => UploadedFile::fake()->image('noticia.jpg'),
        ]);

        $response->assertRedirect(route('noticias.index'));

        $noticia = Noticia::latest('id')->firstOrFail();

        $this->assertSame('cronica-del-senior-1', $noticia->slug);
        Storage::disk('public')->assertExists('noticias/'.$noticia->imagen_noticia);
    }

    public function test_un_editor_puede_actualizar_una_noticia_y_reemplazar_su_imagen(): void
    {
        Storage::fake('public');

        $editor = User::factory()->create([
            'role' => User::ROLE_EDITOR,
        ]);

        $noticia = Noticia::factory()->create([
            'titulo' => 'Titulo inicial',
            'slug' => 'titulo-inicial',
            'imagen_noticia' => 'anterior.jpg',
        ]);

        Storage::disk('public')->put('noticias/anterior.jpg', 'contenido');

        $response = $this->actingAs($editor)->put(route('noticias.update', $noticia), [
            'titulo' => 'Titulo actualizado',
            'subtitulo' => 'Nuevo subtitulo',
            'contenido' => 'Nuevo contenido de prueba.',
            'autor' => 'Redaccion',
            'imagen_noticia' => UploadedFile::fake()->image('nueva.jpg'),
            'oculto' => '1',
        ]);

        $response->assertRedirect(route('noticias.index'));

        $noticia->refresh();

        $this->assertSame('titulo-actualizado', $noticia->slug);
        $this->assertTrue($noticia->oculto);
        Storage::disk('public')->assertMissing('noticias/anterior.jpg');
        Storage::disk('public')->assertExists('noticias/'.$noticia->imagen_noticia);
    }

    public function test_un_editor_puede_eliminar_una_noticia_y_su_imagen(): void
    {
        Storage::fake('public');

        $editor = User::factory()->create([
            'role' => User::ROLE_EDITOR,
        ]);

        $noticia = Noticia::factory()->create([
            'imagen_noticia' => 'borrar.jpg',
        ]);

        Storage::disk('public')->put('noticias/borrar.jpg', 'contenido');

        $response = $this->actingAs($editor)->delete(route('noticias.destroy', $noticia));

        $response->assertRedirect(route('noticias.index'));
        $this->assertDatabaseMissing('noticias', ['id' => $noticia->id]);
        Storage::disk('public')->assertMissing('noticias/borrar.jpg');
    }
}
