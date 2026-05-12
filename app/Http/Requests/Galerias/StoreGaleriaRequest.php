<?php

namespace App\Http\Requests\Galerias;

class StoreGaleriaRequest extends BaseGaleriaRequest
{
    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'imagenes' => 'required|array|min:1',
            'imagenes.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
            'imagen_principal_nueva' => 'nullable|integer|min:0',
        ]);
    }

    public function messages(): array
    {
        return $this->baseMessages();
    }
}
