<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Noticia;
use Illuminate\Support\Str;

class NoticiaController extends Controller
{

    // Muestra la lista de noticias
    public function index()
    {
        $noticias = Noticia::orderBy('titulo', 'asc')->paginate(10);
        return view('admin.noticias.index', compact('noticias'));
    }

    // Muestra el formulario 
    public function create()
    {
        return view('admin.noticias.create');
    }


    // Guarda la noticia en la base de datos
    public function store(Request $request)
    {
        $request->validate([
            'titulo'           => 'required|string|min:5|max:255',
            'subtitulo'        => 'required|string|max:500',
            'contenido'        => 'required|string',
            'autor'            => 'required|string|max:100',
            'imagen_noticia'   => 'required|image|mimes:jpeg,png,jpg,webp|max:2048', // Max 2MB
        ], [
            // Mensajes personalizados
            'titulo.required'         => '¡Oye! El título es obligatorio para publicar.',
            'titulo.min'              => 'El título es muy corto, pon al menos 5 caracteres.',
            'subtitulo.required'      => 'Debes poner un breve resumen o subtítulo.',
            'contenido.required'      => 'La noticia no puede estar vacía, escribe el contenido.',
            'autor.required'          => 'Dinos quién escribe la noticia.',
            'imagen_noticia.required' => 'Una noticia sin foto no luce, sube una imagen.',
            'imagen_noticia.image'    => 'El archivo debe ser una imagen válida.',
            'imagen_noticia.max'      => 'La foto pesa demasiado, el máximo son 2MB.',
        ]);

        $noticia = new Noticia();
        $noticia->titulo = $request->titulo;
        $noticia->subtitulo = $request->subtitulo;
        $noticia->contenido = $request->contenido;
        $noticia->autor = $request->autor;
        $noticia->imagen_noticia = $request->imagen_noticia;

        // LO QUE SE HACE AQUI ES SENCILLO, SI EL SLUG SE REPITE, LE METEMOS UN NÚMERO DETRÁS PARA QUE NO DE ERROR
        $slugBase = Str::slug($request->titulo);
        $slug = $slugBase;
        $contador = 1;

        // Comprobamos si ya existe ese slug y añadimos un número si es necesario
        while (Noticia::where('slug', $slug)->exists()) {
            $slug = $slugBase . '-' . $contador;
            $contador++;
        }

        $noticia->slug = $slug;

        if ($request->hasFile('imagen_noticia')) {
            $imagen = $request->file('imagen_noticia');

            // Creamos un nombre único con el tiempo actual
            $nombreImagen = time() . '.' . $imagen->getClientOriginalExtension();

            // MOVER a public/jugadores 
            $imagen->move(public_path('noticias'), $nombreImagen);

            // Guardamos solo el nombre del archivo en la BD
            $noticia->imagen_noticia = $nombreImagen;
        }


        $noticia->save();
        return redirect()->route('noticias.index')->with('mensaje', 'Noticia creada correctamente');
    }

    // EDIT
    public function edit($id)
    {

        $noticia = Noticia::findOrFail($id);
        return view('admin.noticias.edit', compact('noticia'));
    }



    // UPDATE
    public function update(Request $request, $id)
    {
        $request->validate([
            'titulo'           => 'required|string|min:5|max:255',
            'subtitulo'        => 'required|string|max:500',
            'contenido'        => 'required|string',
            'autor'            => 'required|string|max:100',
            'imagen_noticia'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            // Mensajes para el Título
            'titulo.required' => 'El título de la noticia es obligatorio.',
            'titulo.string'   => 'El título debe ser un texto válido.',
            'titulo.min'      => 'El título es demasiado corto (mínimo 5 caracteres).',
            'titulo.max'      => 'El título no puede superar los 255 caracteres.',

            // Mensajes para el Subtítulo
            'subtitulo.required' => 'La entradilla o resumen es obligatoria.',
            'subtitulo.max'      => 'El resumen es demasiado largo (máximo 500 caracteres).',

            // Mensajes para el Contenido
            'contenido.required' => 'El cuerpo de la noticia no puede estar vacío.',

            // Mensajes para el Autor
            'autor.required' => 'Debes indicar quién es el autor de la noticia.',
            'autor.max'      => 'El nombre del autor no debe superar los 100 caracteres.',

            // Mensajes para la Imagen
            'imagen_noticia.image' => 'El archivo debe ser una imagen real.',
            'imagen_noticia.mimes' => 'Formatos permitidos: jpeg, png, jpg o webp.',
            'imagen_noticia.max'   => 'La imagen es muy pesada. El máximo permitido son 2MB.',
        ]);

        // 2. Buscar la noticia
        $noticia = Noticia::findOrFail($id);

        // 3. Actualizar campos básicos
        $noticia->titulo = $request->titulo;
        $noticia->subtitulo = $request->subtitulo;
        $noticia->contenido = $request->contenido;
        $noticia->autor = $request->autor;

        // 4. Lógica del SLUG (Evitar el error de duplicado)
        $slugBase = Str::slug($request->titulo);
        $slug = $slugBase;
        $contador = 1;

        // Buscamos si el slug existe, pero ignoramos la noticia que estamos editando actualmente
        while (Noticia::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = $slugBase . '-' . $contador;
            $contador++;
        }
        $noticia->slug = $slug;

        // 5. Gestión de la IMAGEN
        if ($request->hasFile('imagen_noticia')) {
            // 1. Definir la ruta de la imagen antigua
            $rutaAntigua = public_path('noticias/' . $noticia->imagen_noticia);

            // 2. Borrar la foto anterior si existe
            if ($noticia->imagen_noticia && file_exists($rutaAntigua)) {
                unlink($rutaAntigua);
            }

            // 3. Guardar la nueva foto usando MOVE (igual que en equipos)
            $imagen = $request->file('imagen_noticia');
            $nombreImagen = time() . '.' . $imagen->getClientOriginalExtension();
            $imagen->move(public_path('noticias'), $nombreImagen);

            $noticia->imagen_noticia = $nombreImagen;
        }

        // 6. Guardar cambios
        $noticia->save();

        return redirect()->route('noticias.index')->with('mensaje', 'Noticia actualizada correctamente');
    }



    // DELETE/ DESTROY 
    public function destroy($id)
    {
        // 1. Buscamos al jugador o lanzamos error 404 si no existe
        $noticia = Noticia::findOrFail($id);

        // 2. Definimos la ruta de su imagen
        $rutaImagen = public_path('noticias/' . $noticia->imagen_noticia);

        // 3. Si tiene imagen y el archivo existe físicamente, lo borramos
        if ($noticia->imagen_noticia && file_exists($rutaImagen)) {
            unlink($rutaImagen);
        }

        // 4. Borramos el registro de la base de datos
        $noticia->delete();

        // 5. Redirigimos con un mensaje de éxito
        return redirect()->route('noticias.index')->with('mensaje', 'Noticia eliminada correctamente');
    }

    public function show ($id) 
    {
        $noticia = Noticia::findOrFail($id);
        return view('admin.noticias.show', compact('noticia'));
    }
    
}
