<?php

namespace App\Http\Requests\Jugadores;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class BaseJugadorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function baseRules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'dorsal' => 'required|integer|min:0|max:99',
            'posicion' => 'required|string',
            'fecha_nacimiento' => 'required|date',
            'equipo_id' => [
                'required',
                Rule::exists('equipos', 'id')->where(fn ($query) => $query->where('es_local', true)),
            ],
            'oculto' => 'nullable|boolean',
        ];
    }

    public function baseMessages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'dorsal.required' => 'El dorsal es obligatorio.',
            'dorsal.integer' => 'El dorsal debe ser un número.',
            'equipo_id.required' => 'Debes asignar al jugador a un equipo.',
            'equipo_id.exists' => 'El equipo seleccionado no es válido.',
            'imagen_jugador.image' => 'El archivo debe ser una imagen.',
            'imagen_jugador.mimes' => 'La foto debe ser JPG o PNG.',
            'imagen_jugador.max' => 'La foto es demasiado pesada (máximo 2MB).',
        ];
    }
}
