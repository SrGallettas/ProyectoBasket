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
<<<<<<< HEAD
        $this->writeService->create($request->validated());
=======
        $request->validate([
            'titulo'         => 'required|string|min:5|max:255',
            'subtitulo'      => 'required|string|max:500',
            'contenido'      => 'required|string',
            'autor'          => 'required|string|max:100',
            'imagen_noticia' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'oculto'         => 'nullable|boolean',
        ], [
            'titulo.required'         => __('admin.news.validation.title_required'),
            'titulo.min'              => __('admin.news.validation.title_min'),
            'subtitulo.required'      => __('admin.news.validation.subtitle_required'),
            'contenido.required'      => __('admin.news.validation.content_required'),
            'autor.required'          => __('admin.news.validation.author_required'),
            'imagen_noticia.image'    => __('admin.news.validation.image'),
            'imagen_noticia.mimes'    => __('admin.news.validation.image_mimes'),
            'imagen_noticia.max'      => __('admin.news.validation.image_max'),
        ]);

        // Creamos una instancia vacía del modelo.
        $noticia = new Noticia();

        // Asignamos los datos básicos que llegan desde el formulario.
        $noticia->titulo = $request->titulo;
        $noticia->subtitulo = $request->subtitulo;
        $noticia->contenido = $request->contenido;
        $noticia->autor = $request->autor;
        $noticia->imagen_noticia = null;

        // Campo booleano para decidir si la noticia se ve o no en la web pública.
        $noticia->oculto = $request->boolean('oculto');

        // Generamos un slug a partir del título.
        // Si ya existe, añadimos un número al final para que sea único.
        $slugBase = Str::slug($request->titulo);
        $slug = $slugBase;
        $contador = 1;

        // Comprobamos si ya existe ese slug y añadimos un número si es necesario.
        while (Noticia::where('slug', $slug)->exists()) {
            $slug = $slugBase . '-' . $contador;
            $contador++;
        }

        $noticia->slug = $slug;

        // Si el usuario ha subido una imagen, la guardamos en disco.
        if ($request->hasFile('imagen_noticia')) {
            $imagen = $request->file('imagen_noticia');

            // Se genera un nombre único con time() para evitar nombres repetidos.
            $nombreImagen = time() . '.' . $imagen->getClientOriginalExtension();

            // Se guarda en storage/app/public/noticias.
            $imagen->storeAs('noticias', $nombreImagen, 'public');

            // En la base de datos guardamos solo el nombre del archivo.
            $noticia->imagen_noticia = $nombreImagen;
        }

        $noticia->save();
>>>>>>> e0ab8cf (VERSION VALENCIANO)

        return redirect()->route('noticias.index')->with('mensaje', 'Noticia creada correctamente');
    }

    public function edit(string $id)
    {
        $noticia = Noticia::findOrFail($id);

        return view('admin.noticias.edit', compact('noticia'));
    }

    public function update(UpdateNoticiaRequest $request, string $id)
    {
<<<<<<< HEAD
=======
        $request->validate([
            'titulo'         => 'required|string|min:5|max:255',
            'subtitulo'      => 'required|string|max:500',
            'contenido'      => 'required|string',
            'autor'          => 'required|string|max:100',
            'imagen_noticia' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'oculto'         => 'nullable|boolean',
        ], [
            'titulo.required' => __('admin.news.validation.title_required'),
            'titulo.min'      => __('admin.news.validation.title_min'),
            'subtitulo.required' => __('admin.news.validation.subtitle_required'),
            'contenido.required' => __('admin.news.validation.content_required'),
            'autor.required' => __('admin.news.validation.author_required'),
            'imagen_noticia.image' => __('admin.news.validation.image'),
            'imagen_noticia.mimes' => __('admin.news.validation.image_mimes'),
            'imagen_noticia.max'   => __('admin.news.validation.image_max'),
        ]);

        // Buscamos la noticia actual.
>>>>>>> e0ab8cf (VERSION VALENCIANO)
        $noticia = Noticia::findOrFail($id);
        $this->writeService->update($noticia, $request->validated());

        return redirect()->route('noticias.index')->with('mensaje', 'Noticia actualizada correctamente');
    }

    public function destroy(string $id)
    {
        $noticia = Noticia::findOrFail($id);
        $this->writeService->delete($noticia);

        return redirect()->route('noticias.index')->with('mensaje', 'Noticia eliminada correctamente');
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
                ? 'Noticia ocultada de la web publica.'
                : 'Noticia visible de nuevo en la web publica.'
        );
    }
}
