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
        $this->writeService->create($request->validated());

        return redirect()->route('merchandising.index')->with('mensaje', __('admin.flash.product_created'));
    }

    public function edit(string $id)
    {
        $item = Merchandising::findOrFail($id);
        $tallasSeleccionadas = $this->formOptionsService->selectedSizes($item);

        return view('admin.merchandising.edit', compact('item', 'tallasSeleccionadas'));
    }

    public function update(UpdateMerchandisingRequest $request, string $id)
    {
        $item = Merchandising::findOrFail($id);
        $this->writeService->update($item, $request->validated());

        return redirect()->route('merchandising.index')->with('mensaje', __('admin.flash.product_updated'));
    }

    public function destroy(string $id)
    {
        $item = Merchandising::findOrFail($id);
        $this->writeService->delete($item);

        return redirect()->route('merchandising.index')->with('mensaje', __('admin.flash.product_deleted'));
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
                ? __('admin.flash.product_hidden')
                : __('admin.flash.product_shown')
        );
    }
}
