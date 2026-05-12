<?php

namespace App\Http\Requests\Merchandising;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseMerchandisingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function baseRules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'talla' => 'required|array|min:1',
            'descripcion' => 'nullable|string',
            'oculto' => 'nullable|boolean',
        ];
    }

    public function baseMessages(): array
    {
        return [
            'nombre.required' => 'Tienes que poner un nombre al producto.',
            'precio.required' => 'El precio es obligatorio.',
            'talla.required' => 'Selecciona al menos una talla disponible.',
        ];
    }
}
