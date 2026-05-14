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
            'idioma_origen' => 'required|in:es,val',
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
            'idioma_origen.required' => __('admin.news.validation.source_language_required'),
            'idioma_origen.in' => __('admin.news.validation.source_language_invalid'),
            'titulo.required' => __('admin.news.validation.title_required'),
            'titulo.min' => __('admin.news.validation.title_min'),
            'titulo.max' => __('admin.news.validation.title_max'),
            'subtitulo.required' => __('admin.news.validation.subtitle_required'),
            'subtitulo.max' => __('admin.news.validation.subtitle_max'),
            'contenido.required' => __('admin.news.validation.content_required'),
            'autor.required' => __('admin.news.validation.author_required'),
            'autor.max' => __('admin.news.validation.author_max'),
            'imagen_noticia.image' => __('admin.news.validation.image'),
            'imagen_noticia.mimes' => __('admin.news.validation.image_mimes'),
            'imagen_noticia.max' => __('admin.news.validation.image_max'),
        ];
    }
}
