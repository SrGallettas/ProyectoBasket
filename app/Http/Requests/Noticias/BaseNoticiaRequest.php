<?php

namespace App\Http\Requests\Noticias;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseNoticiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function baseRules(): array
    {
        return [
            'titulo' => 'required|string|min:5|max:255',
            'subtitulo' => 'required|string|max:500',
            'contenido' => 'required|string',
            'autor' => 'required|string|max:100',
            'oculto' => 'nullable|boolean',
        ];
    }

    public function baseMessages(): array
    {
        return [
            'titulo.required' => 'El título de la noticia es obligatorio.',
            'titulo.min' => 'El título es demasiado corto (mínimo 5 caracteres).',
            'titulo.max' => 'El título no puede superar los 255 caracteres.',
            'subtitulo.required' => 'La entradilla o resumen es obligatoria.',
            'subtitulo.max' => 'El resumen es demasiado largo (máximo 500 caracteres).',
            'contenido.required' => 'El cuerpo de la noticia no puede estar vacío.',
            'autor.required' => 'Debes indicar quién es el autor de la noticia.',
            'autor.max' => 'El nombre del autor no debe superar los 100 caracteres.',
            'imagen_noticia.image' => 'El archivo debe ser una imagen válida.',
            'imagen_noticia.mimes' => 'Formatos permitidos: jpeg, png, jpg o webp.',
            'imagen_noticia.max' => 'La imagen es muy pesada. El máximo permitido son 2MB.',
        ];
    }
}
