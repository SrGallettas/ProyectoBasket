<?php

namespace App\Http\Controllers;

use App\Http\Requests\Equipos\StoreEquipoRequest;
use App\Http\Requests\Equipos\UpdateEquipoRequest;
use App\Models\Equipo;
use App\Services\Equipos\EquipoFormOptionsService;
use App\Services\Equipos\EquipoListService;
use App\Services\Equipos\EquipoWriteService;
use Illuminate\Http\Request;

class EquipoController extends Controller
{
    public function __construct(
        private readonly EquipoListService $listService,
        private readonly EquipoFormOptionsService $formOptionsService,
        private readonly EquipoWriteService $writeService,
    ) {}

    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = (string) $request->input('sort', 'nombre');
        $direction = (string) $request->input('direction', 'asc');
        $equipos = $this->listService->paginateForAdmin($search, $sort, $direction);

        return view('admin.equipos.index', compact('equipos', 'search', 'sort', 'direction'));
    }

    public function create()
    {
        $categoryOptions = $this->formOptionsService->createOptions();

        return view('admin.equipos.create', compact('categoryOptions'));
    }

    public function store(StoreEquipoRequest $request)
    {
        $this->writeService->create($request->all());

        return redirect()->route('equipos.index')->with('mensaje', __('admin.flash.team_created'));
    }

    public function destroy(string $id)
    {
        $equipo = Equipo::findOrFail($id);

        if (! $this->writeService->delete($equipo)) {
            return redirect()
                ->route('equipos.index')
                ->withErrors([
                    'delete' => __('admin.flash.team_delete_blocked'),
                ]);
        }

        return redirect()->route('equipos.index');
    }

    public function edit(string $id)
    {
        $equipo = Equipo::findOrFail($id);
        $categoryOptions = $this->formOptionsService->editOptions($equipo);

        return view('admin.equipos.edit', compact('equipo', 'categoryOptions'));
    }

    public function update(UpdateEquipoRequest $request, string $id)
    {
        $equipo = Equipo::findOrFail($id);
        $this->writeService->update($equipo, $request->all());

        return redirect()->route('equipos.index')->with('mensaje', __('admin.flash.team_updated'));
    }

    public function show(string $id)
    {
        $equipo = Equipo::findOrFail($id);

        return view('admin.equipos.show', compact('equipo'));
    }

    public function toggleOculto(Equipo $equipo)
    {
        $equipo->oculto = ! $equipo->oculto;
        $equipo->save();

        return redirect()->route('equipos.index')->with(
            'mensaje',
            $equipo->oculto
                ? __('admin.flash.team_hidden')
                : __('admin.flash.team_shown')
        );
    }
}
