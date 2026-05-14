<?php

namespace App\Http\Controllers;

use App\Http\Requests\Jugadores\StoreJugadorRequest;
use App\Http\Requests\Jugadores\UpdateJugadorRequest;
use App\Models\Equipo;
use App\Models\Jugador;
use App\Services\Jugadores\JugadorFormOptionsService;
use App\Services\Jugadores\JugadorListService;
use App\Services\Jugadores\JugadorWriteService;
use Illuminate\Http\Request;

class JugadorController extends Controller
{
    public function __construct(
        private readonly JugadorListService $listService,
        private readonly JugadorFormOptionsService $formOptionsService,
        private readonly JugadorWriteService $writeService,
    ) {}

    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = (string) $request->input('sort', 'nombre');
        $direction = (string) $request->input('direction', 'asc');
        $jugadores = $this->listService->paginateForAdmin($search, $sort, $direction);

        return view('admin.jugadores.index', compact('jugadores', 'search', 'sort', 'direction'));
    }

    public function create()
    {
        $equipos = $this->formOptionsService->localTeams();

        return view('admin.jugadores.create', compact('equipos'));
    }

    public function store(StoreJugadorRequest $request)
    {
        $this->writeService->create($request->validated());

        return redirect()->route('jugadores.index')->with('mensaje', __('admin.flash.player_created'));
    }

    public function edit(string $id)
    {
        $jugador = Jugador::findOrFail($id);
        $equipos = $this->formOptionsService->localTeams();

        return view('admin.jugadores.edit', compact('jugador', 'equipos'));
    }

    public function update(UpdateJugadorRequest $request, string $id)
    {
        $jugador = Jugador::findOrFail($id);
        $this->writeService->update($jugador, $request->validated());

        return redirect()->route('jugadores.index')->with('mensaje', __('admin.flash.player_updated'));
    }

    public function destroy(string $id)
    {
        $jugador = Jugador::findOrFail($id);
        $this->writeService->delete($jugador);

        return redirect()->route('jugadores.index')->with('mensaje', __('admin.flash.player_deleted'));
    }

    public function show(string $id)
    {
        $jugador = Jugador::with('equipo')->findOrFail($id);

        return view('admin.jugadores.show', compact('jugador'));
    }

    public function toggleOculto(Jugador $jugador)
    {
        $jugador->oculto = ! $jugador->oculto;
        $jugador->save();

        return redirect()->route('jugadores.index')->with(
            'mensaje',
            $jugador->oculto
                ? __('admin.flash.player_hidden')
                : __('admin.flash.player_shown')
        );
    }
}
