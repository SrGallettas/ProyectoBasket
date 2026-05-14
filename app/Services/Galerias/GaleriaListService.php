<?php

namespace App\Services\Galerias;

use App\Models\Galeria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GaleriaListService
{
    public function paginateForAdmin(?string $search, string $sort = 'created_at', string $direction = 'desc'): LengthAwarePaginator
    {
        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';

        $sortableColumns = [
            'titulo' => 'titulo_es',
            'tipo' => 'tipo',
            'imagenes_count' => 'imagenes_count',
            'slug' => 'slug',
            'oculto' => 'oculto',
            'created_at' => 'created_at',
        ];

        $sortColumn = $sortableColumns[$sort] ?? 'created_at';

        $query = Galeria::withCount('imagenes')
            ->with('portadaImagen')
            ->when($search, function ($builder) use ($search) {
                $builder->where(function ($subquery) use ($search) {
                    $subquery->where('titulo_es', 'like', '%'.$search.'%')
                        ->orWhere('titulo_val', 'like', '%'.$search.'%')
                        ->orWhere('titulo', 'like', '%'.$search.'%')
                        ->orWhere('descripcion_es', 'like', '%'.$search.'%')
                        ->orWhere('descripcion_val', 'like', '%'.$search.'%')
                        ->orWhere('descripcion', 'like', '%'.$search.'%')
                        ->orWhere('tipo', 'like', '%'.$search.'%');
                });
            });

        if (in_array($sortColumn, ['titulo', 'tipo', 'slug'], true)) {
            $query->orderByRaw('LOWER('.$sortColumn.') '.$direction);
        } else {
            $query->orderBy($sortColumn, $direction);
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->paginate(9)
            ->withQueryString();
    }
}
