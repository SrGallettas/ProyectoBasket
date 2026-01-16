<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Merchandising;

class MerchandisingController extends Controller
{

    // Muestra la lista de noticias
    public function index()
    {
        $merchandising = Merchandising::orderBy('nombre', 'asc')->paginate(10);
        return view('admin.merchandising.index', compact('merchandising'));
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
}
