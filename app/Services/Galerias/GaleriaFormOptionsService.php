<?php

namespace App\Services\Galerias;

use App\Models\Galeria;

class GaleriaFormOptionsService
{
    public function typeOptions(): array
    {
        return Galeria::TIPOS;
    }
}
