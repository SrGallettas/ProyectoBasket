<?php

namespace App\Services\Partidos;

use App\Models\Clasificacion;
use App\Models\Equipo;

class PartidoFormOptionsService
{
    public function forForm(): array
    {
        return [
            'equipos' => Equipo::orderBy('nombre', 'asc')->get(),
            'clasificaciones' => Clasificacion::with('equipos')->orderBy('nombre', 'asc')->get(),
        ];
    }
}
