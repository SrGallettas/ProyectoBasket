<?php

namespace App\Http\Requests\Partidos;

use App\Services\Partidos\PartidoLigaAssignmentService;
use Illuminate\Foundation\Http\FormRequest;

abstract class BasePartidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->baseRules();
    }

    public function messages(): array
    {
        return $this->baseMessages();
    }

    public function after(): array
    {
        return [
            function ($validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                app(PartidoLigaAssignmentService::class)->validate($this->all());
            },
        ];
    }

    protected function baseRules(): array
    {
        return [
            'equipo_local_id' => 'required|exists:equipos,id',
            'equipo_visitante_id' => 'required|exists:equipos,id|different:equipo_local_id',
            'fecha_hora' => 'required|date',
            'lugar' => 'required|string|max:255',
            'categoria' => 'required|string',
            'tipo_partido' => 'required|in:liga,amistoso',
            'clasificacion_id' => 'nullable|exists:clasificaciones,id',
            'jornada' => 'nullable|integer|min:1',
            'oculto' => 'nullable|boolean',
            'oculto_en_clasificacion' => 'nullable|boolean',
        ];
    }

    protected function baseMessages(): array
    {
        return [
            'equipo_local_id.required' => 'Debes seleccionar el equipo local.',
            'equipo_local_id.exists' => 'El equipo local seleccionado no es valido.',
            'equipo_visitante_id.required' => 'Debes seleccionar el equipo visitante.',
            'equipo_visitante_id.exists' => 'El equipo visitante seleccionado no es valido.',
            'equipo_visitante_id.different' => '¡Cuidado! Un equipo no puede jugar contra si mismo.',
            'fecha_hora.required' => 'La fecha y la hora son obligatorias.',
            'fecha_hora.date' => 'El formato de fecha y hora no es correcto.',
            'lugar.required' => 'Debes indicar el lugar del partido (pabellon).',
            'categoria.required' => 'Debes seleccionar una categoria para el partido.',
            'tipo_partido.required' => 'Debes indicar si el partido es de liga o amistoso.',
        ];
    }
}
