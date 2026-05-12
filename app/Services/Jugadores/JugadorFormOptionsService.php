<?php

namespace App\Services\Jugadores;

use App\Models\Equipo;
use Illuminate\Support\Collection;

class JugadorFormOptionsService
{
    public function localTeams(): Collection
    {
        return Equipo::where('es_local', true)
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get();
    }
}
