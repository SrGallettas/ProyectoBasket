<?php

namespace App\Services\Basket;

use App\Models\Clasificacion;
use App\Models\ClasificacionFila;
use App\Models\Equipo;
use App\Models\Galeria;
use App\Models\Jugador;
use App\Models\Noticia;
use App\Models\Partido;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HomePageService
{
    public function __construct(private readonly CategoryOrderService $categoryOrderService) {}

    public function buildViewData(): array
    {
        $partidosClub = $this->publishedClubMatches();
        $partidosSenior = $this->filterMatchesByPreferredTeam($partidosClub, ['CB Bellreguard Senior'], ['Senior']);
        $partidosSeniorB = $this->filterMatchesByPreferredTeam($partidosClub, ['CB Bellreguard Senior B'], ['Senior B']);

        [$clasificacionInicio, $clasificacionInicioFilas, $clasificacionInicioFilaClub] = $this->buildFeaturedStandings();

        return [
            'noticias' => Noticia::where('oculto', false)->latest()->take(6)->get(),
            'proximoPartido' => $this->resolveNextMatch($partidosSenior) ?? $this->resolveNextMatch($partidosSeniorB),
            'ultimoResultado' => $this->resolveLastResult($partidosSenior) ?? $this->resolveLastResult($partidosSeniorB),
            'clubStats' => $this->buildClubStats($partidosClub),
            'galeriasDestacadas' => Galeria::with('imagenes')
                ->withCount('imagenes')
                ->where('oculto', false)
                ->latest()
                ->take(3)
                ->get(),
            'clasificacionInicio' => $clasificacionInicio,
            'clasificacionInicioFilas' => $clasificacionInicioFilas,
            'clasificacionInicioFilaClub' => $clasificacionInicioFilaClub,
        ];
    }

    private function publishedClubMatches(): Collection
    {
        return Partido::with(['equipoLocal', 'equipoVisitante', 'estadisticas'])
            ->where('oculto', false)
            ->where(function ($query) {
                $query->whereHas('equipoLocal', function ($teamQuery) {
                    $teamQuery->where('es_local', true);
                })->orWhereHas('equipoVisitante', function ($teamQuery) {
                    $teamQuery->where('es_local', true);
                });
            })
            ->orderBy('fecha_hora', 'asc')
            ->get();
    }

    private function filterMatchesByPreferredTeam(Collection $partidos, array $preferredTeams, array $fallbackCategories): Collection
    {
        return $partidos->filter(
            fn (Partido $partido) => $this->categoryOrderService->matchIncludesPreferredLocalTeam(
                $partido,
                $preferredTeams,
                $fallbackCategories
            )
        );
    }

    private function resolveNextMatch(Collection $partidos): ?Partido
    {
        $ahora = Carbon::now();

        return $partidos
            ->filter(function (Partido $partido) use ($ahora) {
                $statsLocal = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_local_id);
                $statsVisitante = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_visitante_id);
                $sinResultadoFinal = optional($statsLocal)->puntos_totales === null
                    || optional($statsVisitante)->puntos_totales === null;

                return $sinResultadoFinal
                    && Carbon::parse($partido->fecha_hora)->greaterThanOrEqualTo($ahora);
            })
            ->sortBy('fecha_hora')
            ->first();
    }

    private function resolveLastResult(Collection $partidos): ?Partido
    {
        return $partidos
            ->filter(function (Partido $partido) {
                $statsLocal = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_local_id);
                $statsVisitante = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_visitante_id);

                return optional($statsLocal)->puntos_totales !== null
                    && optional($statsVisitante)->puntos_totales !== null;
            })
            ->sortByDesc('fecha_hora')
            ->first();
    }

    private function buildClubStats(Collection $partidosClub): Collection
    {
        $equiposActivos = Equipo::where('es_local', true)
            ->where('oculto', false)
            ->count();

        $jugadoresActivos = Jugador::where('oculto', false)
            ->whereHas('equipo', function ($query) {
                $query->where('es_local', true)->where('oculto', false);
            })
            ->count();

        return collect([
            [
                'valor' => $equiposActivos,
                'etiqueta' => __('public.home.stats.active_teams.label'),
                'texto' => __('public.home.stats.active_teams.text'),
            ],
            [
                'valor' => $jugadoresActivos,
                'etiqueta' => __('public.home.stats.visible_players.label'),
                'texto' => __('public.home.stats.visible_players.text'),
            ],
            [
                'valor' => $partidosClub->count(),
                'etiqueta' => __('public.home.stats.published_matches.label'),
                'texto' => __('public.home.stats.published_matches.text'),
            ],
            [
                'valor' => Noticia::where('oculto', false)->count(),
                'etiqueta' => __('public.home.stats.active_news.label'),
                'texto' => __('public.home.stats.active_news.text'),
            ],
        ]);
    }

    private function buildFeaturedStandings(): array
    {
        $clasificacionInicio = null;
        $clasificacionInicioFilas = collect();
        $clasificacionInicioFilaClub = null;

        $clasificacionActiva = Clasificacion::orderByDesc('activa')->orderBy('nombre')->first();

        if (! $clasificacionActiva) {
            return [$clasificacionInicio, $clasificacionInicioFilas, $clasificacionInicioFilaClub];
        }

        $jornadaClasificacionInicio = ClasificacionFila::where('clasificacion_id', $clasificacionActiva->id)
            ->whereNotNull('jornada')
            ->max('jornada');

        $filasClasificacionInicio = ClasificacionFila::with('equipo')
            ->where('clasificacion_id', $clasificacionActiva->id)
            ->when($jornadaClasificacionInicio, function ($query) use ($jornadaClasificacionInicio) {
                $query->where('jornada', $jornadaClasificacionInicio);
            })
            ->orderByDesc('pts')
            ->orderByDesc('diff')
            ->orderByDesc('pf')
            ->get()
            ->values();

        if ($filasClasificacionInicio->isEmpty()) {
            return [$clasificacionInicio, $clasificacionInicioFilas, $clasificacionInicioFilaClub];
        }

        $filasConPosicion = $filasClasificacionInicio->map(function (ClasificacionFila $fila, int $index) {
            $fila->setAttribute('posicion_home', $index + 1);

            return $fila;
        });

        $clasificacionInicio = $clasificacionActiva;
        $clasificacionInicioFilas = $filasConPosicion->take(5)->values();
        $clasificacionInicioFilaClub = $filasConPosicion->first(function (ClasificacionFila $fila) {
            return (bool) optional($fila->equipo)->es_local
                || str_contains(strtolower((string) optional($fila->equipo)->nombre), 'bellreguard');
        });

        if (
            $clasificacionInicioFilaClub
            && ! $clasificacionInicioFilas->contains(fn (ClasificacionFila $fila) => $fila->id === $clasificacionInicioFilaClub->id)
        ) {
            $clasificacionInicioFilas = $clasificacionInicioFilas
                ->concat([$clasificacionInicioFilaClub])
                ->values();
        }

        return [$clasificacionInicio, $clasificacionInicioFilas, $clasificacionInicioFilaClub];
    }
}
