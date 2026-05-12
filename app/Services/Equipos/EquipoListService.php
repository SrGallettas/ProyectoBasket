<?php

namespace App\Services\Equipos;

use App\Models\Equipo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EquipoListService
{
    public function paginateForAdmin(?string $search, string $sort = 'nombre', string $direction = 'asc'): LengthAwarePaginator
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        $sortableColumns = [
            'nombre' => 'nombre',
            'categoria' => 'categoria',
            'jugadores_count' => 'jugadores_count',
            'oculto' => 'oculto',
        ];

        $sortColumn = $sortableColumns[$sort] ?? 'nombre';

        $query = Equipo::withCount('jugadores')
            ->when($search, function ($builder) use ($search) {
                $builder->where('nombre', 'like', '%'.$search.'%');
            });

        if (in_array($sortColumn, ['nombre', 'categoria'], true)) {
            $query->orderByRaw('LOWER('.$sortColumn.') '.$direction);
        } else {
            $query->orderBy($sortColumn, $direction);
        }

        return $query
            ->orderByRaw('LOWER(nombre) asc')
            ->paginate(10)
            ->withQueryString();
    }
}
