<?php

namespace App\Services\Noticias;

use App\Models\Noticia;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NoticiaListService
{
    public function paginateForAdmin(?string $search, string $sort = 'created_at', string $direction = 'desc'): LengthAwarePaginator
    {
        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';

        $sortableColumns = [
            'titulo' => 'titulo_es',
            'autor' => 'autor',
            'created_at' => 'created_at',
            'oculto' => 'oculto',
        ];

        $sortColumn = $sortableColumns[$sort] ?? 'created_at';

        $query = Noticia::query()
            ->when($search, function ($builder) use ($search) {
                $builder->where(function ($subquery) use ($search) {
                    $subquery->where('titulo_es', 'like', '%'.$search.'%')
                        ->orWhere('titulo_val', 'like', '%'.$search.'%')
                        ->orWhere('autor', 'like', '%'.$search.'%')
                        ->orWhere('subtitulo_es', 'like', '%'.$search.'%')
                        ->orWhere('subtitulo_val', 'like', '%'.$search.'%');
                });
            });

        if (in_array($sortColumn, ['titulo_es', 'autor'], true)) {
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
