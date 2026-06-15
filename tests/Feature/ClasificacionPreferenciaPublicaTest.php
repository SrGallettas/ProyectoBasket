<?php

namespace Tests\Feature;

use App\Models\Clasificacion;
use App\Models\Equipo;
use App\Services\Basket\CategoryOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClasificacionPreferenciaPublicaTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_clasificacion_publica_prefiere_senior_a_antes_que_la_activa(): void
    {
        $seniorA = Clasificacion::create([
            'nombre' => 'Liga Senior A',
            'categoria' => 'Senior A',
            'temporada' => '2026/27',
            'activa' => false,
        ]);

        $nuevaActiva = Clasificacion::create([
            'nombre' => 'Liga Junior',
            'categoria' => 'Junior',
            'temporada' => '2026/27',
            'activa' => true,
        ]);

        $response = $this->get(route('basket.clasificacion'));

        $response->assertOk();
        $response->assertSee('<option value="'.$seniorA->id.'" selected>', false);
        $response->assertDontSee('<option value="'.$nuevaActiva->id.'" selected>', false);
    }

    public function test_respeta_la_clasificacion_elegida_por_url(): void
    {
        Clasificacion::create([
            'nombre' => 'Liga Senior A',
            'categoria' => 'Senior A',
            'temporada' => '2026/27',
            'activa' => false,
        ]);

        $junior = Clasificacion::create([
            'nombre' => 'Liga Junior',
            'categoria' => 'Junior',
            'temporada' => '2026/27',
            'activa' => true,
        ]);

        $response = $this->get(route('basket.clasificacion', ['clasificacion_id' => $junior->id]));

        $response->assertOk();
        $response->assertSee('<option value="'.$junior->id.'" selected>', false);
    }

    public function test_estadisticas_prefiere_senior_a_antes_que_senior(): void
    {
        $seniorA = Equipo::factory()->create([
            'nombre' => 'CB Bellreguard Senior A',
            'categoria' => 'Senior A',
            'es_local' => true,
            'oculto' => false,
        ]);

        $senior = Equipo::factory()->create([
            'nombre' => 'CB Bellreguard Senior',
            'categoria' => 'Senior',
            'es_local' => true,
            'oculto' => false,
        ]);

        $response = $this->get(route('basket.estadisticas'));

        $response->assertOk();
        $response->assertSee('<option value="'.$seniorA->id.'" selected>', false);
        $response->assertDontSee('<option value="'.$senior->id.'" selected>', false);
    }

    public function test_el_servicio_compartido_prefiere_senior_a_para_la_home(): void
    {
        $seniorA = Clasificacion::create([
            'nombre' => 'Liga Senior A',
            'categoria' => 'Senior A',
            'temporada' => '2026/27',
            'activa' => false,
        ]);

        Clasificacion::create([
            'nombre' => 'Liga Junior',
            'categoria' => 'Junior',
            'temporada' => '2026/27',
            'activa' => true,
        ]);

        $seleccionada = app(CategoryOrderService::class)->resolvePreferredClassification(Clasificacion::all());

        $this->assertTrue($seleccionada->is($seniorA));
    }
}
