<?php

namespace App\Services\Jugadores;

use App\Models\Jugador;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class JugadorListService
{
    public function paginateForAdmin(?string $search, string $sort = 'nombre', string $direction = 'asc'): LengthAwarePaginator
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        $sortableColumns = [
            'dorsal' => 'jugadores.dorsal',
            'nombre' => 'jugadores.nombre',
            'apellido' => 'jugadores.apellido',
            'club' => 'equipos.nombre',
            'categoria' => 'equipos.categoria',
            'posicion' => 'jugadores.posicion',
            'fecha_nacimiento' => 'jugadores.fecha_nacimiento',
            'oculto' => 'jugadores.oculto',
        ];

        $sortColumn = $sortableColumns[$sort] ?? 'jugadores.nombre';

        $query = Jugador::with('equipo')
            ->leftJoin('equipos', 'equipos.id', '=', 'jugadores.equipo_id')
            ->select('jugadores.*')
            ->when($search, function ($builder) use ($search) {
                $builder->where(function ($subquery) use ($search) {
                    $subquery->where('jugadores.nombre', 'like', '%'.$search.'%')
                        ->orWhere('jugadores.apellido', 'like', '%'.$search.'%')
                        ->orWhere('jugadores.dorsal', 'like', '%'.$search.'%');
                });
            });

        if (in_array($sortColumn, ['jugadores.nombre', 'jugadores.apellido', 'jugadores.posicion', 'equipos.nombre', 'equipos.categoria'], true)) {
            $query->orderByRaw('LOWER('.$sortColumn.') '.$direction);
        } else {
            $query->orderBy($sortColumn, $direction);
        }

        return $query
            ->orderByRaw('LOWER(jugadores.nombre) asc')
            ->paginate(10)
            ->withQueryString();
    }
}
