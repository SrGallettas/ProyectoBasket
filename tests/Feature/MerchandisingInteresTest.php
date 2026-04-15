<?php

namespace Tests\Feature;

use App\Models\Merchandising;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MerchandisingInteresTest extends TestCase
{
    use RefreshDatabase;

    public function test_rechaza_interes_en_producto_oculto(): void
    {
        $productoOculto = Merchandising::factory()->create([
            'oculto' => true,
            'talla' => 'M,L',
        ]);

        $response = $this->post(route('basket.tienda.interes'), [
            'producto_id' => $productoOculto->id,
            'talla' => 'M',
        ]);

        $response->assertNotFound();
    }

    public function test_permite_interes_en_producto_visible(): void
    {
        Mail::fake();

        $productoVisible = Merchandising::factory()->create([
            'oculto' => false,
            'talla' => 'M,L',
        ]);

        $response = $this->from(route('basket.tienda'))->post(route('basket.tienda.interes'), [
            'producto_id' => $productoVisible->id,
            'talla' => 'M',
        ]);

        $response->assertRedirect(route('basket.tienda'));
        $response->assertSessionHas('mensaje', 'Tu interés se ha enviado correctamente.');
    }

    public function test_requiere_talla_en_producto_con_tallas_disponibles(): void
    {
        Mail::fake();

        $productoVisible = Merchandising::factory()->create([
            'oculto' => false,
            'talla' => 'M,L',
        ]);

        $response = $this->from(route('basket.tienda'))->post(route('basket.tienda.interes'), [
            'producto_id' => $productoVisible->id,
            'talla' => '',
        ]);

        $response->assertRedirect(route('basket.tienda'));
        $response->assertSessionHasErrors('talla');
    }

    public function test_permite_interes_en_producto_sin_tallas_con_valor_sin_talla(): void
    {
        $productoVisible = Merchandising::factory()->create([
            'oculto' => false,
            'talla' => 'Sin talla',
        ]);

        $response = $this->from(route('basket.tienda'))->post(route('basket.tienda.interes'), [
            'producto_id' => $productoVisible->id,
            'talla' => 'Sin talla',
        ]);

        $response->assertRedirect(route('basket.tienda'));
        $response->assertSessionHas('mensaje', 'Tu interés se ha enviado correctamente.');
    }
}
