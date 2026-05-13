<?php

namespace App\Http\Controllers;

use App\Http\Requests\Merchandising\StoreMerchandisingRequest;
use App\Http\Requests\Merchandising\UpdateMerchandisingRequest;
use App\Models\Merchandising;
use App\Services\Merchandising\MerchandisingFormOptionsService;
use App\Services\Merchandising\MerchandisingListService;
use App\Services\Merchandising\MerchandisingWriteService;
use Illuminate\Http\Request;

class MerchandisingController extends Controller
{
    public function __construct(
        private readonly MerchandisingListService $listService,
        private readonly MerchandisingFormOptionsService $formOptionsService,
        private readonly MerchandisingWriteService $writeService,
    ) {}

    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = (string) $request->input('sort', 'nombre');
        $direction = (string) $request->input('direction', 'asc');
        $merchandising = $this->listService->paginateForAdmin($search, $sort, $direction);

        return view('admin.merchandising.index', compact('merchandising', 'search', 'sort', 'direction'));
    }

    public function create()
    {
        return view('admin.merchandising.create');
    }

    public function store(StoreMerchandisingRequest $request)
    {
<<<<<<< HEAD
        $this->writeService->create($request->validated());
=======
        // Validación del formulario.
        // Si algo falla, Laravel vuelve automáticamente atrás con errores.
        $request->validate([
            'nombre'         => 'required|string|max:255',
            'precio'         => 'required|numeric|min:0',
            'talla'          => 'required|array|min:1',
            'imagen_merchan' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'descripcion'    => 'nullable|string',
            'oculto'         => 'nullable|boolean',
        ]);
>>>>>>> e0ab8cf (VERSION VALENCIANO)

        return redirect()->route('merchandising.index')->with('mensaje', 'Producto creado con exito');
    }

    public function edit(string $id)
    {
        $item = Merchandising::findOrFail($id);
        $tallasSeleccionadas = $this->formOptionsService->selectedSizes($item);

        return view('admin.merchandising.edit', compact('item', 'tallasSeleccionadas'));
    }

    public function update(UpdateMerchandisingRequest $request, string $id)
    {
<<<<<<< HEAD
=======
        // Validamos los datos recibidos.
        $request->validate([
            'nombre'         => 'required|string|max:255',
            'precio'         => 'required|numeric|min:0',
            'talla'          => 'required|array|min:1',
            'imagen_merchan' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'descripcion'    => 'nullable|string',
            'oculto'         => 'nullable|boolean',
        ]);

        // Buscamos el registro actual.
>>>>>>> e0ab8cf (VERSION VALENCIANO)
        $item = Merchandising::findOrFail($id);
        $this->writeService->update($item, $request->validated());

        return redirect()->route('merchandising.index')->with('mensaje', 'Producto actualizado correctamente');
    }

    public function destroy(string $id)
    {
        $item = Merchandising::findOrFail($id);
        $this->writeService->delete($item);

        return redirect()->route('merchandising.index')->with('mensaje', 'Producto eliminado correctamente');
    }

    public function show(string $id)
    {
        $item = Merchandising::findOrFail($id);
        $tallas = $this->formOptionsService->displaySizes($item);

        return view('admin.merchandising.show', compact('item', 'tallas'));
    }

    public function toggleOculto(Merchandising $merchandising)
    {
        $merchandising->oculto = ! $merchandising->oculto;
        $merchandising->save();

        return redirect()->route('merchandising.index')->with(
            'mensaje',
            $merchandising->oculto
                ? 'Producto ocultado de la web publica.'
                : 'Producto visible de nuevo en la web publica.'
        );
    }
}
