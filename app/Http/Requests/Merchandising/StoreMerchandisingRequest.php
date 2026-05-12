<?php

namespace App\Http\Requests\Merchandising;

class StoreMerchandisingRequest extends BaseMerchandisingRequest
{
    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'imagen_merchan' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
    }

    public function messages(): array
    {
        return array_merge($this->baseMessages(), [
            'imagen_merchan.required' => 'La foto es obligatoria para la tienda.',
            'imagen_merchan.image' => 'El archivo debe ser una imagen.',
            'imagen_merchan.mimes' => 'La foto debe ser JPG o PNG.',
            'imagen_merchan.max' => 'La foto es demasiado pesada (maximo 2MB).',
        ]);
    }
}
