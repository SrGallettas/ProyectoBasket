<?php

namespace App\Http\Requests\Galerias;

class UpdateGaleriaRequest extends BaseGaleriaRequest
{
    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'imagenes' => 'nullable|array',
            'imagenes.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'eliminar_imagenes' => 'nullable|array',
            'eliminar_imagenes.*' => 'integer',
            'imagen_principal_existente' => 'nullable|integer',
            'imagen_principal_nueva' => 'nullable|integer|min:0',
        ]);
    }

    public function messages(): array
    {
        return $this->baseMessages();
    }
}
