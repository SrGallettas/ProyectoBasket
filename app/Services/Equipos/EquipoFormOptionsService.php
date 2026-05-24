<?php

namespace App\Services\Equipos;

use App\Models\Equipo;

class EquipoFormOptionsService
{
    public function createOptions(): array
    {
        return $this->baseCategoryOptions();
    }

    public function editOptions(Equipo $equipo): array
    {
        $options = $this->baseCategoryOptions();

        if (! in_array($equipo->categoria, $options, true)) {
            $options[] = $equipo->categoria;
        }

        return $options;
    }

    private function baseCategoryOptions(): array
    {
        $options = ['Senior'];
        $bases = ['Junior', 'Cadete', 'Infantil', 'Alevín'];
        $suffixes = ['A', 'B'];

        foreach ($bases as $base) {
            foreach ($suffixes as $suffix) {
                $options[] = $base.' '.$suffix;
            }
        }

        return $options;
    }
}
