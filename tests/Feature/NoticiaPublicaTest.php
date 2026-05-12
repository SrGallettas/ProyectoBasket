<?php

namespace Tests\Feature;

use App\Models\Noticia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoticiaPublicaTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_listado_publico_solo_muestra_noticias_visibles(): void
    {
        $visible = Noticia::factory()->create([
            'titulo' => 'Crónica visible',
            'slug' => 'cronica-visible',
            'oculto' => false,
        ]);

        $oculta = Noticia::factory()->create([
            'titulo' => 'Crónica oculta',
            'slug' => 'cronica-oculta',
            'oculto' => true,
        ]);

        $response = $this->get(route('basket.noticias'));

        $response->assertOk();
        $response->assertSee($visible->titulo);
        $response->assertDontSee($oculta->titulo);
    }

    public function test_muestra_el_detalle_publico_por_slug_de_una_noticia_visible(): void
    {
        $noticia = Noticia::factory()->create([
            'titulo' => 'Victoria del senior',
            'slug' => 'victoria-del-senior',
            'subtitulo' => 'Resumen del partido',
            'oculto' => false,
        ]);

        $response = $this->get(route('noticias.detalle', $noticia->slug));

        $response->assertOk();
        $response->assertSee('Victoria del senior');
        $response->assertSee('Resumen del partido');
    }

    public function test_no_muestra_el_detalle_publico_de_una_noticia_oculta(): void
    {
        $noticia = Noticia::factory()->create([
            'slug' => 'noticia-oculta',
            'oculto' => true,
        ]);

        $response = $this->get(route('noticias.detalle', $noticia->slug));

        $response->assertNotFound();
    }

    public function test_el_detalle_publico_enlaza_de_vuelta_al_listado_publico_de_noticias(): void
    {
        $noticia = Noticia::factory()->create([
            'slug' => 'volver-al-listado',
            'oculto' => false,
        ]);

        $response = $this->get(route('noticias.detalle', $noticia->slug));

        $response->assertOk();
        $response->assertSee(route('basket.noticias'), false);
        $response->assertDontSee(route('noticias.index'), false);
    }
}
