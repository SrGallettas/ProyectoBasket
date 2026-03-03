<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Merchandising;

class MerchandisingController extends Controller
{

    // Muestra la lista de productos (Con buscador integrado)
    public function index(Request $request)
    {
        // 1. Capturamos el término de búsqueda que envía el componente
        $search = $request->input('search');

        // 2. Consulta: Si hay búsqueda filtra por nombre o descripción, si no, trae todos.
        $merchandising = Merchandising::when($search, function($query) use ($search) {
                $query->where('nombre', 'like', '%' . $search . '%')
                      ->orWhere('descripcion', 'like', '%' . $search . '%');
            })
            ->orderBy('nombre', 'asc')
            ->paginate(10)
            ->withQueryString(); // Mantiene el parámetro ?search= en la paginación

        // 3. Mandamos los datos y la variable $search para la "X" de limpiar búsqueda
        return view('admin.merchandising.index', compact('merchandising', 'search'));
    }

    // Muestra el formulario para crear el producto
    public function create()
    {
        return view('admin.merchandising.create');
    }

    public function store(Request $request)
    {

        $request->validate([
            'nombre'         => 'required|string|max:255',
            'precio'         => 'required|numeric|min:0',
            'talla'          => 'required|array|min:1',
            'imagen_merchan' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'descripcion'    => 'nullable|string',
        ], [
            // Mensajes personalizados para que el usuario entienda el error
            'nombre.required' => 'Tienes que poner un nombre al producto.',
            'precio.required' => 'El precio es obligatorio.',
            'talla.required'  => 'Selecciona al menos una talla disponible.',
            'imagen_merchan.required' => 'La foto es obligatoria para la tienda.',
            'imagen_merchan.image'      => 'El archivo debe ser una imagen.',
            'imagen_merchan.mimes'      => 'La foto debe ser JPG o PNG.',
            'imagen_merchan.max'        => 'La foto es demasiado pesada (máximo 2MB).',
        ]);

        $item = new Merchandising();
        $item->nombre = $request->nombre;
        $item->descripcion = $request->descripcion;
        $item->precio = $request->precio;

        
        if ($request->has('talla') && is_array($request->talla)) {
            // Convierte ['S', 'M'] en "S,M"
            $item->talla = implode(',', $request->talla);
        } else {
            $item->talla = null;
        }

        if ($request->hasFile('imagen_merchan')) {
            $imagen = $request->file('imagen_merchan');

            // Creamos un nombre único con el tiempo actual
            $nombreImagen = time() . '.' . $imagen->getClientOriginalExtension();

            // MOVER a public/jugadores (Igual que en equipos)
            $imagen->move(public_path('merchandising'), $nombreImagen);

            // Guardamos solo el nombre del archivo en la BD
            $item->imagen_merchan = $nombreImagen;
        }

        $item->save();
        return redirect()->route('merchandising.index')->with('mensaje', 'Jugador creado con éxito');
    }


    public function edit($id)
    {
        $item = Merchandising::findOrFail($id);
        
        // Convertimos el string de la BD "S,M,L" de nuevo a un array para que los checkboxes funcionen
        $tallasSeleccionadas = explode(',', $item->talla);

        return view('admin.merchandising.edit', compact('item', 'tallasSeleccionadas'));
    }

    // UPDATE: Procesa los cambios
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre'         => 'required|string|max:255',
            'precio'         => 'required|numeric|min:0',
            'talla'          => 'required|array|min:1',
            'imagen_merchan' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Nullable al editar
            'descripcion'    => 'nullable|string',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'precio.required' => 'El precio es obligatorio.',
            'talla.required'  => 'Selecciona al menos una talla.',
        ]);

        $item = Merchandising::findOrFail($id);
        $item->nombre = $request->nombre;
        $item->descripcion = $request->descripcion;
        $item->precio = $request->precio;

        // Tallas: Array a String
        if ($request->has('talla')) {
            $item->talla = implode(',', $request->talla);
        }

        // Gestión de Imagen
        if ($request->hasFile('imagen_merchan')) {
            // Borrar la foto anterior si existe
            $rutaAntigua = public_path('merchandising/' . $item->imagen_merchan);
            if ($item->imagen_merchan && file_exists($rutaAntigua)) {
                unlink($rutaAntigua);
            }

            // Subir la nueva
            $imagen = $request->file('imagen_merchan');
            $nombreImagen = time() . '.' . $imagen->getClientOriginalExtension();
            $imagen->move(public_path('merchandising'), $nombreImagen);
            $item->imagen_merchan = $nombreImagen;
        }

        $item->save();
        return redirect()->route('merchandising.index')->with('mensaje', 'Producto actualizado correctamente');
    }

    // DESTROY: Elimina el registro y su foto
    public function destroy($id)
    {
        $item = Merchandising::findOrFail($id);

        // Borrar archivo físico
        $rutaImagen = public_path('merchandising/' . $item->imagen_merchan);
        if ($item->imagen_merchan && file_exists($rutaImagen)) {
            unlink($rutaImagen);
        }

        $item->delete();
        return redirect()->route('merchandising.index')->with('mensaje', 'Producto eliminado correctamente');
    }


    public function show($id)
{
    $item = Merchandising::findOrFail($id);
    
    // Convertimos el string "S,M,L" en array para listarlo bonito con badges
    $tallas = $item->talla ? explode(',', $item->talla) : [];

    return view('admin.merchandising.show', compact('item', 'tallas'));
}
}
