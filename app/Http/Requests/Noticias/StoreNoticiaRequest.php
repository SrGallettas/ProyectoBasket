<?php

namespace App\Http\Requests\Noticias;

class StoreNoticiaRequest extends BaseNoticiaRequest
{
    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'imagen_noticia' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);
    }

    public function messages(): array
    {
        return $this->baseMessages();
    }
}
