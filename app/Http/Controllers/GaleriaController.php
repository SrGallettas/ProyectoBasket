<?php

namespace App\Http\Controllers;

use App\Http\Requests\Galerias\StoreGaleriaRequest;
use App\Http\Requests\Galerias\UpdateGaleriaRequest;
use App\Models\Galeria;
use App\Services\Galerias\GaleriaFormOptionsService;
use App\Services\Galerias\GaleriaListService;
use App\Services\Galerias\GaleriaWriteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GaleriaController extends Controller
{
    public function __construct(
        private readonly GaleriaListService $listService,
        private readonly GaleriaFormOptionsService $formOptionsService,
        private readonly GaleriaWriteService $writeService,
    ) {}

    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = (string) $request->input('sort', 'created_at');
        $direction = (string) $request->input('direction', 'desc');
        $galerias = $this->listService->paginateForAdmin($search, $sort, $direction);

        return view('admin.galerias.index', [
            'galerias' => $galerias,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'tipos' => $this->formOptionsService->typeOptions(),
        ]);
    }

    public function create()
    {
        return view('admin.galerias.create', [
            'tipos' => $this->formOptionsService->typeOptions(),
        ]);
    }

    public function store(StoreGaleriaRequest $request)
    {
        $this->writeService->create($request->validated());

        return $this->withTranslationWarning(
            redirect()->route('galerias.index')->with('mensaje', __('admin.flash.gallery_created'))
        );
    }

    public function show(string $id)
    {
        $galeria = Galeria::with('imagenes')->findOrFail($id);

        return view('admin.galerias.show', [
            'galeria' => $galeria,
            'tipos' => $this->formOptionsService->typeOptions(),
        ]);
    }

    public function edit(string $id)
    {
        $galeria = Galeria::with('imagenes')->findOrFail($id);

        return view('admin.galerias.edit', [
            'galeria' => $galeria,
            'tipos' => $this->formOptionsService->typeOptions(),
        ]);
    }

    public function update(UpdateGaleriaRequest $request, string $id)
    {
        $galeria = Galeria::with('imagenes')->findOrFail($id);
        $this->writeService->update($galeria, $request->validated());

        return $this->withTranslationWarning(
            redirect()->route('galerias.index')->with('mensaje', __('admin.flash.gallery_updated'))
        );
    }

    public function destroy(string $id)
    {
        $galeria = Galeria::with('imagenes')->findOrFail($id);
        $this->writeService->delete($galeria);

        return redirect()->route('galerias.index')->with('mensaje', __('admin.flash.gallery_deleted'));
    }

    public function toggleOculto(Galeria $galeria)
    {
        $galeria->oculto = ! $galeria->oculto;
        $galeria->save();

        return redirect()->route('galerias.index')->with(
            'mensaje',
            $galeria->oculto
                ? __('admin.flash.gallery_hidden')
                : __('admin.flash.gallery_shown')
        );
    }

    private function withTranslationWarning(RedirectResponse $response): RedirectResponse
    {
        $warning = $this->writeService->consumeTranslationWarning();

        return $warning ? $response->with('warning', $warning) : $response;
    }
}
