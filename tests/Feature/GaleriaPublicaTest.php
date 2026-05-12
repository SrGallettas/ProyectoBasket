<?php

namespace Tests\Feature;

use App\Models\Galeria;
use App\Models\ImagenGaleria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GaleriaPublicaTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_listado_publico_muestra_galerias_visibles_y_su_portada(): void
    {
        $visible = Galeria::create([
            'titulo' => 'Torneo infantil',
            'slug' => 'torneo-infantil',
            'carpeta' => 'torneo-infantil',
            'descripcion' => 'Resumen del torneo infantil.',
            'tipo' => 'partidos',
            'oculto' => false,
        ]);

        ImagenGaleria::create([
            'galeria_id' => $visible->id,
            'archivo' => 'torneo-infantil/portada.jpg',
            'es_principal' => true,
        ]);

        Galeria::create([
            'titulo' => 'Galería oculta',
            'slug' => 'galeria-oculta',
            'carpeta' => 'galeria-oculta',
            'descripcion' => 'No debe salir en público.',
            'tipo' => 'eventos',
            'oculto' => true,
        ]);

        $response = $this->get(route('basket.galeria'));

        $response->assertOk();
        $response->assertSee('Torneo infantil');
        $response->assertSee('torneo-infantil/portada.jpg');
        $response->assertDontSee('Galería oculta');
    }
}
