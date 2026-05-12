<?php

namespace App\Http\Controllers;

use App\Http\Requests\Partidos\StorePartidoRequest;
use App\Http\Requests\Partidos\UpdatePartidoRequest;
use App\Models\Partido;
use App\Services\Partidos\PartidoBoardService;
use App\Services\Partidos\PartidoFormOptionsService;
use App\Services\Partidos\PartidoListService;
use App\Services\Partidos\PartidoWriteService;
use Illuminate\Http\Request;

class PartidoController extends Controller
{
    public function __construct(
        private readonly PartidoBoardService $boardService,
        private readonly PartidoListService $listService,
        private readonly PartidoFormOptionsService $formOptionsService,
        private readonly PartidoWriteService $writeService,
    ) {}

    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = (string) $request->input('sort', 'fecha_hora');
        $direction = (string) $request->input('direction', 'desc');
        $partidos = $this->listService->paginateForAdmin($search, $sort, $direction);

        return view('admin.partidos.index', compact('partidos', 'search', 'sort', 'direction'));
    }

    public function create()
    {
        ['equipos' => $equipos, 'clasificaciones' => $clasificaciones] = $this->formOptionsService->forForm();

        return view('admin.partidos.create', compact('equipos', 'clasificaciones'));
    }

    public function store(StorePartidoRequest $request)
    {
        $this->writeService->create($request->validated());

        return redirect()->route('partidos.index')->with('mensaje', 'Partido programado correctamente');
    }

    public function show($id)
    {
        $partido = Partido::with(['equipoLocal', 'equipoVisitante', 'estadisticas', 'cuartos'])->findOrFail($id);
        $this->boardService->attachQuarterStatsToPartido($partido);

        return view('admin.partidos.show', compact('partido'));
    }

    public function edit(string $id)
    {
        $partido = Partido::with(['estadisticas', 'cuartos', 'equipoLocal', 'equipoVisitante'])->findOrFail($id);
        $this->boardService->attachQuarterStatsToPartido($partido);

        ['equipos' => $equipos, 'clasificaciones' => $clasificaciones] = $this->formOptionsService->forForm();

        return view('admin.partidos.edit', compact('partido', 'equipos', 'clasificaciones'));
    }

    public function update(UpdatePartidoRequest $request, string $id)
    {
        $partido = Partido::findOrFail($id);
        $this->writeService->update($partido, $request->validated());

        return redirect()->route('partidos.index')->with('mensaje', 'Partido actualizado correctamente.');
    }

    public function destroy(string $id)
    {
        $partido = Partido::findOrFail($id);
        $this->writeService->delete($partido);

        return redirect()->route('partidos.index')->with('mensaje', 'Partido eliminado correctamente del club');
    }

    public function toggleOculto(Partido $partido)
    {
        $partido->oculto = ! $partido->oculto;
        $partido->save();

        $mensaje = $partido->oculto
            ? 'Partido ocultado de la web publica.'
            : 'Partido visible de nuevo en la web publica.';

        return redirect()->route('partidos.index')->with('mensaje', $mensaje);
    }

    public function toggleOcultoClasificacion(Partido $partido)
    {
        $partido->oculto_en_clasificacion = ! $partido->oculto_en_clasificacion;
        $partido->save();

        $mensaje = $partido->oculto_en_clasificacion
            ? 'Partido ocultado de la clasificación pública.'
            : 'Partido visible de nuevo en la clasificación pública.';

        return redirect()->route('partidos.index')->with('mensaje', $mensaje);
    }
}
