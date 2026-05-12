<?php

namespace App\Services\Merchandising;

use App\Models\Merchandising;

class MerchandisingFormOptionsService
{
    public function selectedSizes(Merchandising $item): array
    {
        return $item->talla ? explode(',', $item->talla) : [];
    }

    public function displaySizes(Merchandising $item): array
    {
        return $this->selectedSizes($item);
    }
}
