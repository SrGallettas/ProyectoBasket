<?php

namespace App\Http\Requests\Noticias;

class StoreNoticiaRequest extends BaseNoticiaRequest
{
    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'imagen_noticia' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);
    }

    public function messages(): array
    {
        return array_merge($this->baseMessages(), [
            'imagen_noticia.required' => 'Una noticia sin foto no luce, sube una imagen.',
        ]);
    }
}
