<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Equipo;
use App\Models\Jugador;
use App\Services\Basket\CategoryOrderService;
use Illuminate\Http\Request;

class EquipoController extends Controller
{
    public function __construct(private readonly CategoryOrderService $categoryOrderService) {}

    public function equipo(Request $request)
    {
        $equiposLocales = Equipo::where('es_local', true)
            ->where('oculto', false)
            ->whereHas('jugadores', function ($query) {
                $query->where('oculto', false);
            })
            ->get();

        $equiposLocales = $this->categoryOrderService->sortTeamsByCategoryPriority($equiposLocales);
        $categorias = $this->categoryOrderService->sortCategoriesByPriority($equiposLocales
            ->pluck('categoria')
            ->filter()
            ->unique()
            ->values());

        $categoriaSel = (string) $request->get('categoria', '');
        if ($categoriaSel === '' || ! $categorias->contains($categoriaSel)) {
            $categoriaSel = 'Senior';
            if (! $categorias->contains($categoriaSel)) {
                $categoriaSel = (string) ($categorias->first() ?? 'Senior');
            }
        }

        $equipoMostrar = $equiposLocales
            ->where('categoria', $categoriaSel)
            ->first();

        $jugadores = null;
        if ($equipoMostrar) {
            $jugadores = $equipoMostrar->jugadores()
                ->where('oculto', false)
                ->orderBy('dorsal')
                ->paginate(9)
                ->withQueryString();
        }

        return view('basket.equipo', compact(
            'categorias',
            'equipoMostrar',
            'categoriaSel',
            'jugadores'
        ));
    }

    public function jugadorDetalle(Jugador $jugador)
    {
        $jugador->load('equipo');

        abort_if(
            $jugador->oculto
            || ! $jugador->equipo
            || ! $jugador->equipo->es_local
            || $jugador->equipo->oculto,
            404
        );

        $companeros = Jugador::where('equipo_id', $jugador->equipo_id)
            ->where('oculto', false)
            ->whereKeyNot($jugador->id)
            ->orderBy('dorsal')
            ->take(4)
            ->get();

        return view('basket.jugador_detalle', compact('jugador', 'companeros'));
    }

    public function toggleOcultoEquipo(Equipo $equipo)
    {
        $equipo->oculto = ! $equipo->oculto;
        $equipo->save();

        return redirect()->route('basket.equipo')->with('mensaje', $equipo->oculto ? __('public.flash.hidden') : __('public.flash.shown'));
    }

    public function toggleOcultoJugador(Jugador $jugador)
    {
        $jugador->oculto = ! $jugador->oculto;
        $jugador->save();

        return redirect()->route('basket.equipo', ['categoria' => optional($jugador->equipo)->categoria])->with('mensaje', $jugador->oculto ? __('public.flash.hidden') : __('public.flash.shown'));
    }
}
