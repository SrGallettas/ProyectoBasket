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
        $bases = ['Senior', 'Junior', 'Cadete', 'Infantil', 'Alevín'];
        $suffixes = ['A', 'B', 'C', 'D'];
        $options = ['Senior'];

        foreach ($bases as $base) {
            foreach ($suffixes as $suffix) {
                if ($base === 'Senior' && $suffix === 'A') {
                    continue;
                }

                $options[] = $base.' '.$suffix;
            }
        }

        return $options;
    }
}
