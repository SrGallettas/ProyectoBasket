<?php

namespace App\Services\Basket;

use App\Models\Equipo;
use App\Models\Partido;

class CategoryOrderService
{
    public function displayCategory(?string $categoria): string
    {
        $categoria = (string) $categoria;

        if ($categoria === '' || app()->getLocale() !== 'val') {
            return $categoria;
        }

        return match ($categoria) {
            'Senior' => 'Sènior',
            'Senior B' => 'Sènior B',
            'Senior C' => 'Sènior C',
            'Senior D' => 'Sènior D',
            'Junior' => 'Júnior',
            'Junior A' => 'Júnior A',
            'Junior B' => 'Júnior B',
            'Cadete' => 'Cadet',
            'Cadete A' => 'Cadet A',
            'Cadete B' => 'Cadet B',
            'Alevín', 'Alevin' => 'Aleví',
            'Benjamín', 'Benjamin' => 'Benjamí',
            'Prebenjamín', 'Prebenjamin' => 'Prebenjamí',
            default => $categoria,
        };
    }

    public function matchIncludesPreferredLocalTeam(Partido $partido, array $preferredNames, array $preferredCategories): bool
    {
        foreach ([$partido->equipoLocal, $partido->equipoVisitante] as $equipo) {
            if (! $equipo || ! $equipo->es_local) {
                continue;
            }

            if ($this->teamMatchesPreference($equipo, $preferredNames, $preferredCategories)) {
                return true;
            }
        }

        return false;
    }

    public function resolvePreferredLocalTeam($equiposLocales, array $preferredNames, array $preferredCategories): ?Equipo
    {
        return collect($equiposLocales)->first(
            fn (Equipo $equipo) => $this->teamMatchesPreference($equipo, $preferredNames, $preferredCategories)
        );
    }

    public function teamMatchesPreference(Equipo $equipo, array $preferredNames, array $preferredCategories): bool
    {
        return in_array((string) $equipo->nombre, $preferredNames, true)
            || in_array((string) $equipo->categoria, $preferredCategories, true);
    }

    public function sortCategoriesByPriority($categorias)
    {
        return collect($categorias)
            ->sortBy(fn ($categoria) => [$this->categoryPriorityIndex($categoria), (string) $categoria])
            ->values();
    }

    public function sortTeamsByCategoryPriority($equipos)
    {
        return collect($equipos)
            ->sortBy(fn (Equipo $equipo) => [$this->categoryPriorityIndex($equipo->categoria), (string) $equipo->nombre])
            ->values();
    }

    public function categoryPriorityList(): array
    {
        return ['Senior', 'Senior B', 'Senior C', 'Senior D', 'Junior', 'Junior A', 'Junior B', 'Cadete', 'Cadete A', 'Cadete B', 'Infantil', 'Infantil A', 'Infantil B', 'Alevín', 'Alevin', 'Benjamín', 'Benjamin', 'Prebenjamín', 'Prebenjamin'];
    }

    public function categoryPriorityIndex(?string $categoria): int
    {
        $categoria = (string) $categoria;
        $priorityList = $this->categoryPriorityList();
        $index = array_search($categoria, $priorityList, true);

        return $index !== false ? $index : count($priorityList);
    }
}
