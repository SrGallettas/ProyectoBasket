<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\MerchandisingPedidoMail;
use App\Models\Merchandising;
use App\Services\Basket\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TiendaController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function tienda()
    {
        $merchandising = Merchandising::where('oculto', false)
            ->orderBy('nombre', 'asc')
            ->paginate(9);

        $cartItems = $this->cartService->getCartItems();
        $cartSummary = $this->cartService->buildCartSummary($cartItems);
        $openCart = session('openCart', false);

        return view('basket.merchandising', compact('merchandising', 'cartItems', 'cartSummary', 'openCart'));
    }

    public function mostrarFormularioPedido()
    {
        $cartItems = $this->cartService->getCartItems();

        if (empty($cartItems)) {
            return redirect()->route('basket.tienda')->withErrors([
                'cesta' => 'Tu cesta está vacía. Añade al menos un producto antes de solicitar el pedido.',
            ]);
        }

        $cartSummary = $this->cartService->buildCartSummary($cartItems);

        return view('basket.merchandising_pedido', compact('cartItems', 'cartSummary'));
    }

    public function anadirProductoCesta(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|integer',
            'talla' => 'nullable|string|max:20',
            'cantidad' => 'required|integer|min:1|max:20',
        ]);

        $producto = Merchandising::whereKey($validated['producto_id'])
            ->where('oculto', false)
            ->firstOrFail();

        $tallasDisponibles = $this->cartService->extractAvailableSizes($producto);
        $tallaSeleccionada = trim((string) ($validated['talla'] ?? ''));

        if (! empty($tallasDisponibles) && ! in_array($tallaSeleccionada, $tallasDisponibles, true)) {
            return back()->withErrors([
                'talla' => 'Selecciona una talla válida para este producto.',
            ])->withInput();
        }

        if (empty($tallasDisponibles)) {
            $tallaSeleccionada = 'Sin talla';
        }

        $cart = session()->get('basket_cart', []);
        $itemKey = $this->cartService->makeCartItemKey($producto->id, $tallaSeleccionada);

        if (isset($cart[$itemKey])) {
            $cart[$itemKey]['cantidad'] = min(20, $cart[$itemKey]['cantidad'] + (int) $validated['cantidad']);
        } else {
            $cart[$itemKey] = [
                'producto_id' => $producto->id,
                'nombre' => $producto->nombre,
                'talla' => $tallaSeleccionada,
                'precio' => (float) $producto->precio,
                'cantidad' => (int) $validated['cantidad'],
                'imagen_url' => $producto->imagen_merchan_url,
            ];
        }

        session()->put('basket_cart', $cart);
        session()->flash('openCart', true);

        return redirect()->route('basket.tienda')->with('mensaje', 'Producto añadido a la cesta.');
    }

    public function actualizarProductoCesta(Request $request, string $itemKey)
    {
        $validated = $request->validate([
            'cantidad' => 'required|integer|min:1|max:20',
        ]);

        $cart = session()->get('basket_cart', []);

        if (! isset($cart[$itemKey])) {
            return redirect()->route('basket.tienda')->withErrors([
                'cesta' => 'El producto que intentas actualizar ya no está en la cesta.',
            ]);
        }

        $cart[$itemKey]['cantidad'] = (int) $validated['cantidad'];

        session()->put('basket_cart', $cart);
        session()->flash('openCart', true);

        return redirect()->route('basket.tienda');
    }

    public function eliminarProductoCesta(string $itemKey)
    {
        $cart = session()->get('basket_cart', []);

        unset($cart[$itemKey]);

        session()->put('basket_cart', $cart);
        session()->flash('openCart', true);

        return redirect()->route('basket.tienda')->with('mensaje', 'Producto eliminado de la cesta.');
    }

    public function enviarPedidoMerchan(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellidos' => 'required|string|max:150',
            'telefono' => 'required|string|max:30',
            'correo' => 'required|email|max:150',
            'notas' => 'nullable|string|max:1000',
        ]);

        $cartItems = $this->cartService->getCartItems();

        if (empty($cartItems)) {
            return redirect()->route('basket.tienda')->withErrors([
                'cesta' => 'Tu cesta está vacía. Añade productos antes de enviar una solicitud.',
            ]);
        }

        $cartSummary = $this->cartService->buildCartSummary($cartItems);
        $destinatario = env('MERCH_PEDIDOS_EMAIL', env('MERCH_INTERES_EMAIL', 'paupalopvila@gmail.com'));

        Mail::to($destinatario)->send(
            new MerchandisingPedidoMail($validated, $cartItems, $cartSummary)
        );

        session()->forget('basket_cart');

        return redirect()->route('basket.tienda')->with(
            'mensaje',
            'Tu solicitud de pedido se ha enviado correctamente. Te responderemos lo antes posible.'
        );
    }

    public function toggleOcultoMerchandising(Merchandising $merchandising)
    {
        $merchandising->oculto = ! $merchandising->oculto;
        $merchandising->save();

        return redirect()->route('basket.tienda')->with('mensaje', $merchandising->oculto ? 'Se ha ocultado correctamente.' : 'Se ha mostrado correctamente.');
    }
}
