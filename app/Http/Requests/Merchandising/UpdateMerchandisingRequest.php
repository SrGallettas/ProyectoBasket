<?php

namespace App\Http\Requests\Merchandising;

class UpdateMerchandisingRequest extends BaseMerchandisingRequest
{
    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'imagen_merchan' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
    }

    public function messages(): array
    {
        return array_merge($this->baseMessages(), [
            'nombre.required' => 'El nombre es obligatorio.',
            'precio.required' => 'El precio es obligatorio.',
            'talla.required' => 'Selecciona al menos una talla.',
        ]);
    }
}
