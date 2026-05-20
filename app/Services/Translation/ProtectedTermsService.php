<?php

namespace App\Services\Translation;

use App\Models\Equipo;
use App\Models\Jugador;
use App\Models\Partido;
use Illuminate\Support\Facades\Cache;

class ProtectedTermsService
{
    public const CACHE_KEY = 'translations.protected_terms.news';

    public function forNews(?string $author): array
    {
        $terms = Cache::rememberForever(self::CACHE_KEY, function () {
            $teamTerms = Equipo::query()
                ->select(['nombre', 'categoria'])
                ->get()
                ->flatMap(function (Equipo $equipo) {
                    return [
                        $equipo->nombre,
                        $equipo->categoria,
                    ];
                })
                ->all();

            $playerTerms = Jugador::query()
                ->select(['nombre', 'apellido'])
                ->get()
                ->flatMap(function (Jugador $jugador) {
                    $fullName = trim($jugador->nombre.' '.$jugador->apellido);

                    return array_filter([
                        $jugador->nombre,
                        $jugador->apellido,
                        $fullName !== '' ? $fullName : null,
                    ]);
                })
                ->all();

            $venueTerms = Partido::query()
                ->select(['lugar', 'categoria'])
                ->get()
                ->flatMap(function (Partido $partido) {
                    return array_filter([
                        $partido->lugar,
                        $partido->categoria,
                    ]);
                })
                ->all();

            return array_values(array_unique(array_filter([
                ...$teamTerms,
                ...$playerTerms,
                ...$venueTerms,
            ])));
        });

        return array_values(array_unique(array_filter([
            $author,
            ...$terms,
        ])));
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
