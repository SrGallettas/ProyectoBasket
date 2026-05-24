<?php

namespace App\Http\Requests\Equipos;

use App\Models\Equipo;
use App\Services\Equipos\EquipoFormOptionsService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class BaseEquipoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|min:5|max:255',
            'categoria' => ['required', 'string', Rule::in($this->categoryOptions())],
            'imagen_club' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_plantilla' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'oculto' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.min' => 'El título debe tener al menos 5 caracteres.',
            'nombre.required' => 'El nombre del equipo es obligatorio.',
            'categoria.required' => 'La categoría es obligatoria.',
            'categoria.in' => 'La categoría seleccionada no es válida.',
            'imagen_club.image' => 'El archivo debe ser una imagen.',
            'imagen_club.mimes' => 'Solo se permiten formatos: jpg, jpeg, png.',
            'imagen_club.max' => 'La imagen es demasiado pesada (máximo 2MB).',
            'foto_plantilla.image' => 'La foto de plantilla debe ser una imagen.',
            'foto_plantilla.mimes' => 'La foto de plantilla solo admite jpg, jpeg, png o webp.',
            'foto_plantilla.max' => 'La foto de plantilla es demasiado pesada (máximo 4MB).',
        ];
    }

    private function categoryOptions(): array
    {
        $service = new EquipoFormOptionsService();
        $equipo = $this->route('equipo');

        if ($equipo instanceof Equipo) {
            return $service->editOptions($equipo);
        }

        return $service->createOptions();
    }
}