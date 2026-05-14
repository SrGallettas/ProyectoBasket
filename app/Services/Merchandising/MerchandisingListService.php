<?php

namespace App\Services\Merchandising;

use App\Models\Merchandising;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MerchandisingListService
{
    public function paginateForAdmin(?string $search, string $sort = 'nombre', string $direction = 'asc'): LengthAwarePaginator
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        $sortableColumns = [
            'nombre' => 'nombre_es',
            'talla' => 'talla',
            'precio' => 'precio',
            'oculto' => 'oculto',
        ];

        $sortColumn = $sortableColumns[$sort] ?? 'nombre';

        $query = Merchandising::query()->when($search, function ($builder) use ($search) {
            $builder->where('nombre_es', 'like', '%'.$search.'%')
                ->orWhere('nombre_val', 'like', '%'.$search.'%')
                ->orWhere('descripcion_es', 'like', '%'.$search.'%')
                ->orWhere('descripcion_val', 'like', '%'.$search.'%');
        });

        if (in_array($sortColumn, ['nombre_es', 'talla'], true)) {
            $query->orderByRaw('LOWER('.$sortColumn.') '.$direction);
        } else {
            $query->orderBy($sortColumn, $direction);
        }

        return $query
            ->orderByRaw('LOWER(nombre_es) asc')
            ->paginate(9)
            ->withQueryString();
    }
}
