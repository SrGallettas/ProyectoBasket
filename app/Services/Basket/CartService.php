<?php

namespace App\Services\Basket;

use App\Models\Merchandising;

class CartService
{
    public function getCartItems(): array
    {
        $cart = session()->get('basket_cart', []);

        if (empty($cart)) {
            return [];
        }

        $productos = Merchandising::whereIn(
            'id',
            collect($cart)->pluck('producto_id')->filter()->unique()->values()
        )
            ->where('oculto', false)
            ->get()
            ->keyBy('id');

        $sanitizedCart = [];

        foreach ($cart as $item) {
            $productoId = (int) ($item['producto_id'] ?? 0);
            $cantidad = max(1, min(20, (int) ($item['cantidad'] ?? 1)));
            $tallaSeleccionada = trim((string) ($item['talla'] ?? ''));

            /** @var Merchandising|null $producto */
            $producto = $productos->get($productoId);
            if (! $producto) {
                continue;
            }

            $tallasDisponibles = $this->extractAvailableSizes($producto);

            if (! empty($tallasDisponibles) && ! in_array($tallaSeleccionada, $tallasDisponibles, true)) {
                continue;
            }

            if (empty($tallasDisponibles)) {
                $tallaSeleccionada = 'Sin talla';
            }

            $itemKey = $this->makeCartItemKey($producto->id, $tallaSeleccionada);

            if (isset($sanitizedCart[$itemKey])) {
                $sanitizedCart[$itemKey]['cantidad'] = min(20, $sanitizedCart[$itemKey]['cantidad'] + $cantidad);

                continue;
            }

            $sanitizedCart[$itemKey] = [
                'producto_id' => $producto->id,
                'nombre' => $producto->nombre,
                'talla' => $tallaSeleccionada,
                'precio' => (float) $producto->precio,
                'cantidad' => $cantidad,
                'imagen_url' => $producto->imagen_merchan_url,
            ];
        }

        if ($sanitizedCart !== $cart) {
            session()->put('basket_cart', $sanitizedCart);
        }

        return array_values($sanitizedCart);
    }

    public function buildCartSummary(array $cartItems): array
    {
        $units = 0;
        $subtotal = 0.0;

        foreach ($cartItems as $item) {
            $cantidad = (int) ($item['cantidad'] ?? 0);
            $precio = (float) ($item['precio'] ?? 0);

            $units += $cantidad;
            $subtotal += $cantidad * $precio;
        }

        return [
            'units' => $units,
            'subtotal' => $subtotal,
        ];
    }

    public function extractAvailableSizes(Merchandising $producto): array
    {
        if (! $producto->talla) {
            return [];
        }

        return collect(explode(',', $producto->talla))
            ->map(fn ($talla) => trim((string) $talla))
            ->filter(fn ($talla) => $talla !== '' && strtolower($talla) !== 'sin talla')
            ->values()
            ->all();
    }

    public function makeCartItemKey(int $productoId, string $talla): string
    {
        return $productoId.'-'.md5($talla);
    }
}
