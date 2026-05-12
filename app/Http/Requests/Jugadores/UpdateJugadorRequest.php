<?php

namespace App\Http\Requests\Jugadores;

class UpdateJugadorRequest extends BaseJugadorRequest
{
    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'imagen_jugador' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
    }

    public function messages(): array
    {
        return $this->baseMessages();
    }
}
