<?php

namespace App\Http\Controllers;

use App\Models\Jugador;
use App\Models\Equipo; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JugadorController extends Controller
{
    // Muestra la lista de jugadores
    public function index()
    {
        $jugadores = Jugador::orderBy('nombre', 'asc')->paginate(10);
        return view('admin.jugadores.index', compact('jugadores'));
    }

    // Muestra el formulario (pasando los equipos)
    public function create()
    {
        $equipos = Equipo::all(); // Necesitamos los equipos para el <select>
        return view('admin.jugadores.create', compact('equipos'));
    }

    // Guarda el jugador en la base de datos
    public function store(Request $request)
    {
        $request->validate([
            'nombre'           => 'required|string|max:255',
            'apellido'         => 'required|string|max:255',
            'dorsal'           => 'required|integer|min:0|max:99',
            'posicion'         => 'required|string',
            'fecha_nacimiento' => 'required|date',
            'equipo_id'        => 'required|exists:equipos,id',
            'imagen_jugador'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048', 
        ], [
            'nombre.required'           => 'El nombre es obligatorio.',
            'apellido.required'         => 'El apellido es obligatorio.',
            'dorsal.required'           => 'El dorsal es obligatorio.',
            'dorsal.integer'            => 'El dorsal debe ser un número.',
            'equipo_id.required'        => 'Debes asignar al jugador a un equipo.',
            'equipo_id.exists'          => 'El equipo seleccionado no es válido.',
            'imagen_jugador.image'      => 'El archivo debe ser una imagen.',
            'imagen_jugador.mimes'      => 'La foto debe ser JPG o PNG.',
            'imagen_jugador.max'        => 'La foto es demasiado pesada (máximo 2MB).',
        ]);

        $jugador = new Jugador();
        $jugador->nombre = $request->nombre;
        $jugador->apellido = $request->apellido;
        $jugador->dorsal = $request->dorsal;
        $jugador->posicion = $request->posicion;
        $jugador->fecha_nacimiento = $request->fecha_nacimiento;
        $jugador->equipo_id = $request->equipo_id;

        if ($request->hasFile('imagen_jugador')) {
            $imagen = $request->file('imagen_jugador');

            // Creamos un nombre único con el tiempo actual
            $nombreImagen = time() . '.' . $imagen->getClientOriginalExtension();

            // MOVER a public/jugadores (Igual que en equipos)
            $imagen->move(public_path('jugadores'), $nombreImagen);

            // Guardamos solo el nombre del archivo en la BD
            $jugador->imagen_jugador = $nombreImagen;
        }

        $jugador->save();
        return redirect()->route('jugadores.index')->with('mensaje', 'Jugador creado con éxito');
    }

    public function edit($id)
    {
        $jugador = Jugador::findOrFail($id);
        $equipos = Equipo::all(); // Para el desplegable de cambio de equipo

        return view('admin.jugadores.edit', compact('jugador', 'equipos'));
    }

    public function update(Request $request, $id)
    {

        $request->validate([
            'nombre'           => 'required|string|max:255',
            'apellido'         => 'required|string|max:255',
            'dorsal'           => 'required|integer|min:0|max:99',
            'posicion'         => 'required|string',
            'fecha_nacimiento' => 'required|date',
            'equipo_id'        => 'required|exists:equipos,id',
            'imagen_jugador'   => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // 10MB
        ], [
            'nombre.required'           => 'El nombre es obligatorio.',
            'apellido.required'         => 'El apellido es obligatorio.',
            'dorsal.required'           => 'El dorsal es obligatorio.',
            'dorsal.integer'            => 'El dorsal debe ser un número.',
            'equipo_id.required'        => 'Debes asignar al jugador a un equipo.',
            'equipo_id.exists'          => 'El equipo seleccionado no es válido.',
            'imagen_jugador.image'      => 'El archivo debe ser una imagen.',
            'imagen_jugador.mimes'      => 'La foto debe ser JPG o PNG.',
            'imagen_jugador.max'        => 'La foto es demasiado pesada (máximo 10MB).',
        ]);

        $jugador = Jugador::findOrFail($id);

        $jugador->nombre = $request->nombre;
        $jugador->apellido = $request->apellido;
        $jugador->dorsal = $request->dorsal;
        $jugador->posicion = $request->posicion;
        $jugador->fecha_nacimiento = $request->fecha_nacimiento;
        $jugador->equipo_id = $request->equipo_id;

        if ($request->hasFile('imagen_jugador')) {
            // 1. Definir la ruta de la imagen antigua
            $rutaAntigua = public_path('jugadores/' . $jugador->imagen_jugador);

            // 2. Borrar la foto anterior si existe
            if ($jugador->imagen_jugador && file_exists($rutaAntigua)) {
                unlink($rutaAntigua);
            }

            // 3. Guardar la nueva foto usando MOVE (igual que en equipos)
            $imagen = $request->file('imagen_jugador');
            $nombreImagen = time() . '.' . $imagen->getClientOriginalExtension();
            $imagen->move(public_path('jugadores'), $nombreImagen);

            $jugador->imagen_jugador = $nombreImagen;
        }

        $jugador->save();
        return redirect()->route('jugadores.index')->with('mensaje', 'Jugador actualizado correctamente');
    }


    public function destroy($id)
    {
        // 1. Buscamos al jugador o lanzamos error 404 si no existe
        $jugador = Jugador::findOrFail($id);

        // 2. Definimos la ruta de su imagen
        $rutaImagen = public_path('jugadores/' . $jugador->imagen_jugador);

        // 3. Si tiene imagen y el archivo existe físicamente, lo borramos
        if ($jugador->imagen_jugador && file_exists($rutaImagen)) {
            unlink($rutaImagen);
        }

        // 4. Borramos el registro de la base de datos
        $jugador->delete();

        // 5. Redirigimos con un mensaje de éxito
        return redirect()->route('jugadores.index')->with('mensaje', 'Jugador eliminado correctamente del club');
    }

    public function show($id)
    {
        // Usamos with('equipo') para traer también los datos del equipo al que pertenece
        $jugador = Jugador::with('equipo')->findOrFail($id);
        return view('admin.jugadores.show', compact('jugador'));
    }

    ///////////////
    // BUSCADOR //
    /////////////
    public function search(Request $request)
    {
        $search = $request->get('search');

        $jugadores = Jugador::where('nombre', 'like', '%' . $search . '%')
            ->orWhere('apellido', 'like', '%' . $search . '%')
            ->orderBy('apellido', 'ASC')
            ->paginate(10);
            
        // Importante para que al cambiar de página no se pierda la búsqueda
        $jugadores->appends(['search' => $search]);

        return view('admin.jugadores.index', compact('jugadores', 'search'));
    }
}
