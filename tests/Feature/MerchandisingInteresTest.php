<?php

namespace Tests\Feature;

use App\Mail\MerchandisingPedidoMail;
use App\Models\Merchandising;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MerchandisingInteresTest extends TestCase
{
    use RefreshDatabase;

    public function test_rechaza_anadir_a_cesta_un_producto_oculto(): void
    {
        $productoOculto = Merchandising::factory()->create([
            'oculto' => true,
            'talla' => 'M,L',
        ]);

        $response = $this->post(route('basket.tienda.cesta.add'), [
            'producto_id' => $productoOculto->id,
            'talla' => 'M',
            'cantidad' => 1,
        ]);

        $response->assertNotFound();
    }

    public function test_permite_anadir_a_cesta_un_producto_visible(): void
    {
        $productoVisible = Merchandising::factory()->create([
            'oculto' => false,
            'talla' => 'M,L',
        ]);

        $response = $this->from(route('basket.tienda'))->post(route('basket.tienda.cesta.add'), [
            'producto_id' => $productoVisible->id,
            'talla' => 'M',
            'cantidad' => 2,
        ]);

        $response->assertRedirect(route('basket.tienda'));
        $response->assertSessionHas('mensaje', 'Producto añadido a la cesta.');
        $response->assertSessionHas('basket_cart');
    }

    public function test_requiere_talla_en_producto_con_tallas_disponibles(): void
    {
        $productoVisible = Merchandising::factory()->create([
            'oculto' => false,
            'talla' => 'M,L',
        ]);

        $response = $this->from(route('basket.tienda'))->post(route('basket.tienda.cesta.add'), [
            'producto_id' => $productoVisible->id,
            'talla' => '',
            'cantidad' => 1,
        ]);

        $response->assertRedirect(route('basket.tienda'));
        $response->assertSessionHasErrors('talla');
    }

    public function test_permite_anadir_a_cesta_un_producto_sin_tallas(): void
    {
        $productoVisible = Merchandising::factory()->create([
            'oculto' => false,
            'talla' => 'Sin talla',
        ]);

        $response = $this->from(route('basket.tienda'))->post(route('basket.tienda.cesta.add'), [
            'producto_id' => $productoVisible->id,
            'talla' => 'Sin talla',
            'cantidad' => 1,
        ]);

        $response->assertRedirect(route('basket.tienda'));
        $response->assertSessionHas('mensaje', 'Producto añadido a la cesta.');
    }

    public function test_envia_la_solicitud_del_pedido_por_correo(): void
    {
        Mail::fake();

        $productoVisible = Merchandising::factory()->create([
            'oculto' => false,
            'nombre' => 'Camiseta roja',
            'talla' => 'M,L',
            'precio' => 25,
        ]);

        $this->withSession([
            'basket_cart' => [
                $productoVisible->id . '-' . md5('M') => [
                    'producto_id' => $productoVisible->id,
                    'nombre' => $productoVisible->nombre,
                    'talla' => 'M',
                    'precio' => 25.0,
                    'cantidad' => 2,
                    'imagen_url' => $productoVisible->imagen_merchan_url,
                ],
            ],
        ]);

        $response = $this->post(route('basket.tienda.pedido.enviar'), [
            'nombre' => 'Pau',
            'apellidos' => 'Garcia Torres',
            'telefono' => '600123123',
            'correo' => 'pau@example.com',
            'notas' => 'Prefiero recogida en pista.',
        ]);

        $response->assertRedirect(route('basket.tienda'));
        $response->assertSessionHas('mensaje', 'Tu solicitud de pedido se ha enviado correctamente. Te responderemos lo antes posible.');
        $response->assertSessionMissing('basket_cart');

        Mail::assertSent(MerchandisingPedidoMail::class);
    }
}
