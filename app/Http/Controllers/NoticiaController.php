<?php

namespace App\Http\Controllers;

use App\Http\Requests\Noticias\StoreNoticiaRequest;
use App\Http\Requests\Noticias\UpdateNoticiaRequest;
use App\Models\Noticia;
use App\Services\Noticias\NoticiaListService;
use App\Services\Noticias\NoticiaWriteService;
use Illuminate\Http\Request;

class NoticiaController extends Controller
{
    public function __construct(
        private readonly NoticiaListService $listService,
        private readonly NoticiaWriteService $writeService,
    ) {}

    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = (string) $request->input('sort', 'created_at');
        $direction = (string) $request->input('direction', 'desc');
        $noticias = $this->listService->paginateForAdmin($search, $sort, $direction);

        return view('admin.noticias.index', compact('noticias', 'search', 'sort', 'direction'));
    }

    public function create()
    {
        return view('admin.noticias.create');
    }

    public function store(StoreNoticiaRequest $request)
    {
        $this->writeService->create($request->validated());

        return redirect()->route('noticias.index')->with('mensaje', __('admin.flash.news_created'));
    }

    public function edit(string $id)
    {
        $noticia = Noticia::findOrFail($id);

        return view('admin.noticias.edit', compact('noticia'));
    }

    public function update(UpdateNoticiaRequest $request, string $id)
    {
        $noticia = Noticia::findOrFail($id);
        $this->writeService->update($noticia, $request->validated());

        return redirect()->route('noticias.index')->with('mensaje', __('admin.flash.news_updated'));
    }

    public function destroy(string $id)
    {
        $noticia = Noticia::findOrFail($id);
        $this->writeService->delete($noticia);

        return redirect()->route('noticias.index')->with('mensaje', __('admin.flash.news_deleted'));
    }

    public function show(string $id)
    {
        $noticia = Noticia::findOrFail($id);

        return view('admin.noticias.show', compact('noticia'));
    }

    public function toggleOculto(Noticia $noticia)
    {
        $noticia->oculto = ! $noticia->oculto;
        $noticia->save();

        return redirect()->route('noticias.index')->with(
            'mensaje',
            $noticia->oculto
                ? __('admin.flash.news_hidden')
                : __('admin.flash.news_shown')
        );
    }
}
