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

    public function test_el_filtro_publico_de_galeria_muestra_solo_el_tipo_seleccionado(): void
    {
        Galeria::create([
            'titulo' => 'Album partidos',
            'slug' => 'album-partidos',
            'carpeta' => 'album-partidos',
            'descripcion' => 'Solo partidos.',
            'tipo' => 'partidos',
            'oculto' => false,
        ]);

        Galeria::create([
            'titulo' => 'Album eventos',
            'slug' => 'album-eventos',
            'carpeta' => 'album-eventos',
            'descripcion' => 'Solo eventos.',
            'tipo' => 'eventos',
            'oculto' => false,
        ]);

        $response = $this->get(route('basket.galeria', ['tipo' => 'partidos']));

        $response->assertOk();
        $response->assertSee('Album partidos');
        $response->assertDontSee('Album eventos');
    }

    public function test_un_tipo_invalido_en_galeria_vuelve_al_listado_general(): void
    {
        Galeria::create([
            'titulo' => 'Album general',
            'slug' => 'album-general',
            'carpeta' => 'album-general',
            'descripcion' => 'Visible en listado general.',
            'tipo' => 'equipo',
            'oculto' => false,
        ]);

        $response = $this->get(route('basket.galeria', ['tipo' => 'categoria-rara']));

        $response->assertOk();
        $response->assertSee('Album general');
    }
    public function test_los_filtros_de_galeria_siguen_visibles_aunque_el_tipo_no_tenga_albums(): void
    {
        Galeria::create([
            'titulo' => 'Album entrenamientos',
            'slug' => 'album-entrenamientos',
            'carpeta' => 'album-entrenamientos',
            'descripcion' => 'Visible solo en entrenamientos.',
            'tipo' => 'entrenamientos',
            'oculto' => false,
        ]);

        $response = $this->get(route('basket.galeria', ['tipo' => 'eventos']));

        $response->assertOk();
        $response->assertSee('No hay álbumes para este filtro');
        $response->assertSee('Todos');
        $response->assertSee('Partidos');
        $response->assertSee('Entrenamientos');
        $response->assertSee('Eventos');
    }
}
