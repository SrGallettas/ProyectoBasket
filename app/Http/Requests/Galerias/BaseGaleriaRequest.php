<?php

namespace App\Http\Requests\Galerias;

use App\Models\Galeria;
use Illuminate\Foundation\Http\FormRequest;

abstract class BaseGaleriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function baseRules(): array
    {
        return [
            'idioma_origen' => 'required|in:es,val',
            'titulo' => 'required|string|min:3|max:255',
            'descripcion' => 'required|string|max:2000',
            'tipo' => 'required|in:'.implode(',', array_keys(Galeria::TIPOS)),
            'oculto' => 'nullable|boolean',
        ];
    }

    public function baseMessages(): array
    {
        return [
            'idioma_origen.required' => __('admin.galleries.validation.source_language_required'),
            'idioma_origen.in' => __('admin.galleries.validation.source_language_invalid'),
            'imagenes.required' => 'Debes subir al menos una imagen.',
            'imagenes.array' => 'Las imágenes deben enviarse en formato válido.',
            'imagenes.min' => 'La galeria debe tener al menos una imagen.',
            'imagenes.*.image' => 'Cada archivo debe ser una imagen válida.',
            'imagenes.*.mimes' => 'Las imágenes deben ser jpg, jpeg, png o webp.',
            'imagenes.*.max' => 'Cada imagen puede pesar como máximo 4MB.',
        ];
    }
}
