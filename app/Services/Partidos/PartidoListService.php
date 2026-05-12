<?php

namespace App\Services\Partidos;

use App\Models\Partido;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PartidoListService
{
    public function paginateForAdmin(?string $search, string $sort = 'fecha_hora', string $direction = 'desc'): LengthAwarePaginator
    {
        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';

        $sortableColumns = [
            'fecha_hora' => 'partidos.fecha_hora',
            'categoria' => 'partidos.categoria',
            'equipo_local' => 'equipo_local.nombre',
            'equipo_visitante' => 'equipo_visitante.nombre',
            'lugar' => 'partidos.lugar',
            'oculto' => 'partidos.oculto',
        ];

        $sortColumn = $sortableColumns[$sort] ?? 'partidos.fecha_hora';

        $query = Partido::with(['equipoLocal', 'equipoVisitante', 'clasificacion'])
            ->leftJoin('equipos as equipo_local', 'equipo_local.id', '=', 'partidos.equipo_local_id')
            ->leftJoin('equipos as equipo_visitante', 'equipo_visitante.id', '=', 'partidos.equipo_visitante_id')
            ->select('partidos.*')
            ->when($search, function ($builder) use ($search) {
                $builder->where('equipo_local.nombre', 'like', '%'.$search.'%')
                    ->orWhere('equipo_visitante.nombre', 'like', '%'.$search.'%');
            });

        if (in_array($sortColumn, ['partidos.categoria', 'equipo_local.nombre', 'equipo_visitante.nombre', 'partidos.lugar'], true)) {
            $query->orderByRaw('LOWER('.$sortColumn.') '.$direction);
        } else {
            $query->orderBy($sortColumn, $direction);
        }

        return $query
            ->orderBy('partidos.fecha_hora', 'desc')
            ->paginate(9)
            ->withQueryString();
    }
}
