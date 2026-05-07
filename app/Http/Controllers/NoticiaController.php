<?php

namespace App\Http\Controllers;

// Request permite leer lo que llega desde formularios o por la URL.
use Illuminate\Http\Request;

// Modelo principal de este controlador.
// Representa las noticias guardadas en la base de datos.
use App\Models\Noticia;

// Str se usa para generar slugs limpios a partir del título.
use Illuminate\Support\Str;

// Storage permite borrar imágenes antiguas del disco.
use Illuminate\Support\Facades\Storage;

class NoticiaController extends Controller
{
    /*
     * LISTADO DE NOTICIAS
     *
     * Este método se usa en el panel admin para mostrar
     * todas las noticias guardadas.
     * También permite buscar por título, autor o subtítulo.
     *
     * Flujo:
     * 1. leer el texto del buscador
     * 2. construir la consulta
     * 3. si hay búsqueda, filtrar resultados
     * 4. ordenar por fecha de creación
     * 5. paginar y enviar a la vista
     */
    public function index(Request $request)
    {
        // Recogemos el texto que el usuario escribe en el buscador.
        $search = $request->input('search');
        $sort = (string) $request->input('sort', 'created_at');
        $direction = strtolower((string) $request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        // Limitamos las columnas ordenables que puede pedir la vista.
        $sortableColumns = [
            'titulo' => 'titulo',
            'autor' => 'autor',
            'created_at' => 'created_at',
            'oculto' => 'oculto',
        ];

        $sortColumn = $sortableColumns[$sort] ?? 'created_at';

        // Construimos la consulta de noticias.
        // Si existe búsqueda, filtramos por varios campos de texto.
        $noticias = Noticia::when($search, function ($query) use ($search) {
            $query->where('titulo', 'like', '%' . $search . '%')
                ->orWhere('autor', 'like', '%' . $search . '%')
                ->orWhere('subtitulo', 'like', '%' . $search . '%');
        });

        // En texto ignoramos mayúsculas para ordenar de forma natural.
        if (in_array($sortColumn, ['titulo', 'autor'], true)) {
            $noticias->orderByRaw('LOWER(' . $sortColumn . ') ' . $direction);
        } else {
            $noticias->orderBy($sortColumn, $direction);
        }

        $noticias = $noticias
            ->orderBy('created_at', 'desc') // las más nuevas primero
            ->paginate(9)
            ->withQueryString();

        // Enviamos resultados y texto buscado a la vista.
        return view('admin.noticias.index', compact('noticias', 'search', 'sort', 'direction'));
    }

    /*
     * FORMULARIO DE CREACIÓN
     *
     * Este método no guarda nada.
     * Solo devuelve la vista para crear una noticia nueva.
     */
    public function create()
    {
        return view('admin.noticias.create');
    }

    /*
     * GUARDAR NOTICIA NUEVA
     *
     * Este método procesa el formulario de creación.
     *
     * Qué hace:
     * 1. valida los campos obligatorios
     * 2. crea una noticia nueva
     * 3. genera un slug único
     * 4. guarda la imagen subida
     * 5. guarda el registro en base de datos
     */
    public function store(Request $request)
    {
        $request->validate([
            'titulo'         => 'required|string|min:5|max:255',
            'subtitulo'      => 'required|string|max:500',
            'contenido'      => 'required|string',
            'autor'          => 'required|string|max:100',
            'imagen_noticia' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'oculto'         => 'nullable|boolean',
        ], [
            'titulo.required'         => '¡Oye! El título es obligatorio para publicar.',
            'titulo.min'              => 'El título es muy corto, pon al menos 5 caracteres.',
            'subtitulo.required'      => 'Debes poner un breve resumen o subtítulo.',
            'contenido.required'      => 'La noticia no puede estar vacía, escribe el contenido.',
            'autor.required'          => 'Dinos quién escribe la noticia.',
            'imagen_noticia.required' => 'Una noticia sin foto no luce, sube una imagen.',
            'imagen_noticia.image'    => 'El archivo debe ser una imagen válida.',
            'imagen_noticia.max'      => 'La foto pesa demasiado, el máximo son 2MB.',
        ]);

        // Creamos una instancia vacía del modelo.
        $noticia = new Noticia();

        // Asignamos los datos básicos que llegan desde el formulario.
        $noticia->titulo = $request->titulo;
        $noticia->subtitulo = $request->subtitulo;
        $noticia->contenido = $request->contenido;
        $noticia->autor = $request->autor;
        $noticia->imagen_noticia = $request->imagen_noticia;

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
        } else {
            // Si no llega imagen, se usa una imagen por defecto.
            $noticia->imagen_noticia = 'basket.jpeg';
        }

        $noticia->save();

        return redirect()->route('noticias.index')->with('mensaje', 'Noticia creada correctamente');
    }

    /*
     * FORMULARIO DE EDICIÓN
     *
     * Busca una noticia ya existente y carga la vista de edición.
     */
    public function edit($id)
    {
        // Busca la noticia o lanza 404 si no existe.
        $noticia = Noticia::findOrFail($id);

        return view('admin.noticias.edit', compact('noticia'));
    }

    /*
     * ACTUALIZAR NOTICIA
     *
     * Este método guarda cambios sobre una noticia ya existente.
     *
     * Qué hace:
     * 1. valida los datos
     * 2. busca la noticia
     * 3. actualiza campos básicos
     * 4. recalcula el slug evitando duplicados
     * 5. si llega una imagen nueva, borra la anterior y guarda la nueva
     * 6. guarda el registro actualizado
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'titulo'         => 'required|string|min:5|max:255',
            'subtitulo'      => 'required|string|max:500',
            'contenido'      => 'required|string',
            'autor'          => 'required|string|max:100',
            'imagen_noticia' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'oculto'         => 'nullable|boolean',
        ], [
            'titulo.required' => 'El título de la noticia es obligatorio.',
            'titulo.string'   => 'El título debe ser un texto válido.',
            'titulo.min'      => 'El título es demasiado corto (mínimo 5 caracteres).',
            'titulo.max'      => 'El título no puede superar los 255 caracteres.',
            'subtitulo.required' => 'La entradilla o resumen es obligatoria.',
            'subtitulo.max'      => 'El resumen es demasiado largo (máximo 500 caracteres).',
            'contenido.required' => 'El cuerpo de la noticia no puede estar vacío.',
            'autor.required' => 'Debes indicar quién es el autor de la noticia.',
            'autor.max'      => 'El nombre del autor no debe superar los 100 caracteres.',
            'imagen_noticia.image' => 'El archivo debe ser una imagen real.',
            'imagen_noticia.mimes' => 'Formatos permitidos: jpeg, png, jpg o webp.',
            'imagen_noticia.max'   => 'La imagen es muy pesada. El máximo permitido son 2MB.',
        ]);

        // Buscamos la noticia actual.
        $noticia = Noticia::findOrFail($id);

        // Actualizamos los campos básicos.
        $noticia->titulo = $request->titulo;
        $noticia->subtitulo = $request->subtitulo;
        $noticia->contenido = $request->contenido;
        $noticia->autor = $request->autor;
        $noticia->oculto = $request->boolean('oculto');

        // Generamos de nuevo el slug a partir del título.
        // La noticia actual se excluye de la comprobación para no chocar consigo misma.
        $slugBase = Str::slug($request->titulo);
        $slug = $slugBase;
        $contador = 1;

        // Si el slug ya existe en otra noticia, se añade un número al final.
        while (Noticia::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = $slugBase . '-' . $contador;
            $contador++;
        }

        $noticia->slug = $slug;

        // Si llega una nueva imagen, primero se borra la antigua y luego se guarda la nueva.
        if ($request->hasFile('imagen_noticia')) {
            $this->deleteStoredImage($noticia->imagen_noticia, 'noticias');

            $imagen = $request->file('imagen_noticia');
            $nombreImagen = time() . '.' . $imagen->getClientOriginalExtension();
            $imagen->storeAs('noticias', $nombreImagen, 'public');

            $noticia->imagen_noticia = $nombreImagen;
        }

        // Guardamos los cambios finales en la base de datos.
        $noticia->save();

        return redirect()->route('noticias.index')->with('mensaje', 'Noticia actualizada correctamente');
    }

    /*
     * ELIMINAR NOTICIA
     *
     * Este método borra la imagen asociada del disco
     * y después elimina el registro de la base de datos.
     */
    public function destroy($id)
    {
        // Busca la noticia o lanza 404 si no existe.
        $noticia = Noticia::findOrFail($id);

        // Si tiene imagen propia, se elimina del disco.
        $this->deleteStoredImage($noticia->imagen_noticia, 'noticias');

        // Finalmente se borra el registro.
        $noticia->delete();

        return redirect()->route('noticias.index')->with('mensaje', 'Noticia eliminada correctamente');
    }

    /*
     * VER DETALLE DE NOTICIA
     *
     * Carga una noticia concreta para mostrarla en la vista de detalle.
     */
    public function show($id)
    {
        // Busca la noticia o lanza 404 si no existe.
        $noticia = Noticia::findOrFail($id);

        return view('admin.noticias.show', compact('noticia'));
    }

    /*
     * OCULTAR / MOSTRAR NOTICIA
     *
     * No borra la noticia.
     * Solo cambia el booleano "oculto" para controlar si se ve o no en la web pública.
     */
    public function toggleOculto(Noticia $noticia)
    {
        // Invertimos el valor actual.
        $noticia->oculto = !$noticia->oculto;
        $noticia->save();

        return redirect()->route('noticias.index')->with(
            'mensaje',
            $noticia->oculto
                ? 'Noticia ocultada de la web publica.'
                : 'Noticia visible de nuevo en la web publica.'
        );
    }

    /*
     * BORRAR IMAGEN DEL DISCO
     *
     * Este helper centraliza el borrado físico de archivos.
     *
     * Protecciones:
     * - si no hay nombre de archivo, no hace nada
     * - si la imagen es una imagen por defecto, no se borra
     */
    private function deleteStoredImage(?string $filename, string $directory): void
    {
        // Estas imágenes se consideran de sistema y no deben borrarse.
        if (!$filename || $filename === 'club_14.png' || $filename === 'basket.jpeg') {
            return;
        }

        // Se elimina el archivo dentro del disco "public".
        Storage::disk('public')->delete($directory . '/' . ltrim($filename, '/'));
    }
}
