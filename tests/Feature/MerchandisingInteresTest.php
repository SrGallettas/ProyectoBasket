<?php

namespace Tests\Feature;

use App\Mail\MerchandisingPedidoMail;
use App\Models\Merchandising;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MerchandisingInteresTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirige_la_antigua_ficha_publica_a_la_tienda(): void
    {
        $productoVisible = Merchandising::factory()->create([
            'oculto' => false,
        ]);

        $this->get(route('basket.tienda.producto', $productoVisible))
            ->assertRedirect(route('basket.tienda'));
    }

    public function test_oculta_la_ficha_publica_de_un_producto_oculto(): void
    {
        $productoOculto = Merchandising::factory()->create([
            'oculto' => true,
        ]);

        $this->get(route('basket.tienda.producto', $productoOculto))->assertNotFound();
    }

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
                $productoVisible->id.'-'.md5('M') => [
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

    public function test_la_cesta_revalida_el_precio_actual_del_producto_antes_de_enviar_el_pedido(): void
    {
        Mail::fake();

        $productoVisible = Merchandising::factory()->create([
            'oculto' => false,
            'nombre' => 'Camiseta roja',
            'talla' => 'M,L',
            'precio' => 30,
        ]);

        $this->withSession([
            'basket_cart' => [
                $productoVisible->id.'-'.md5('M') => [
                    'producto_id' => $productoVisible->id,
                    'nombre' => 'Precio antiguo',
                    'talla' => 'M',
                    'precio' => 25.0,
                    'cantidad' => 2,
                    'imagen_url' => 'antigua.jpg',
                ],
            ],
        ]);

        $response = $this->post(route('basket.tienda.pedido.enviar'), [
            'nombre' => 'Pau',
            'apellidos' => 'Garcia Torres',
            'telefono' => '600123123',
            'correo' => 'pau@example.com',
            'notas' => null,
        ]);

        $response->assertRedirect(route('basket.tienda'));

        Mail::assertSent(MerchandisingPedidoMail::class, function (MerchandisingPedidoMail $mail) {
            return (float) $mail->cartSummary['subtotal'] === 60.0
                && $mail->cartItems[0]['nombre'] === 'Camiseta roja'
                && (float) $mail->cartItems[0]['precio'] === 30.0;
        });
    }

    public function test_elimina_de_la_cesta_productos_ya_ocultos_antes_de_mostrar_el_pedido(): void
    {
        $productoOculto = Merchandising::factory()->create([
            'oculto' => true,
            'talla' => 'M,L',
        ]);

        $this->withSession([
            'basket_cart' => [
                $productoOculto->id.'-'.md5('M') => [
                    'producto_id' => $productoOculto->id,
                    'nombre' => $productoOculto->nombre,
                    'talla' => 'M',
                    'precio' => 25.0,
                    'cantidad' => 1,
                    'imagen_url' => $productoOculto->imagen_merchan_url,
                ],
            ],
        ]);

        $response = $this->get(route('basket.tienda.pedido'));

        $response->assertRedirect(route('basket.tienda'));
        $response->assertSessionHasErrors('cesta');
        $response->assertSessionHas('basket_cart', []);
    }

    public function test_permita_guardar_en_admin_un_producto_sin_talla(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('merchandising.store'), [
                'nombre' => 'Bufanda oficial',
                'precio' => 18,
                'talla' => ['Sin talla'],
                'descripcion' => 'Producto sin tallaje.',
                'imagen_merchan' => UploadedFile::fake()->image('bufanda.jpg')->size(500),
            ]);

        $response->assertRedirect(route('merchandising.index'));
        $this->assertDatabaseHas('merchandising', [
            'nombre' => 'Bufanda oficial',
            'talla' => 'Sin talla',
        ]);
    }
}
