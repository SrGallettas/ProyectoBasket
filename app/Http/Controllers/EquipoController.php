<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EquipoController extends Controller
{
    // Muestra la lista de todos los equipos
    public function index()
    {
        // Ordenamos por nombre de la A a la Z y paginamos de 10 en 10
        // El with count para contar el numero de jugadores de ese club
        $equipos = Equipo::withCount('jugadores')
            ->orderBy('nombre', 'asc')
            ->paginate(10);
        return view('admin.equipos.index', compact('equipos')); // Los manda a la vista index
    }

    // Muestra el formulario para crear un equipo nuevo
    public function create()
    {
        return view('admin.equipos.create'); // Carga la vista del formulario
    }

    // Guarda el equipo nuevo cuando pulsas "Enviar" o "Guardar"
    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|min:5|max:255',
            'categoria'   => 'required|string',
            'imagen_club' => 'nullable|image|mimes:jpeg,png,jpg',
        ], [
            'nombre.min'      => 'El título debe tener al menos 5 caracteres.',
            'nombre.required'    => 'El nombre del equipo es obligatorio.',
            'categoria.required' => 'La categoría es obligatoria.',
            'imagen_club.image'  => 'El archivo debe ser una imagen.',
            'imagen_club.mimes'  => 'Solo se permiten formatos: jpg, jpeg, png.',
        ]);

        $equipo = new Equipo(); // Crea un objeto equipo vacío
        $equipo->nombre = $request->nombre; // Rellena el nombre con lo que viene del formulario
        $equipo->categoria = $request->categoria; // Rellena la categoría
        // Si el checkbox está marcado llega en el request, si no, le asignamos false
        $equipo->es_local = $request->has('es_local');

        // Si el usuario ha subido una foto...
        if ($request->hasFile('imagen_club')) {
            $imagen = $request->file('imagen_club'); // Captura la foto
            $nombreImagen = time() . '.' . $imagen->getClientOriginalExtension(); // Le pone un nombre único (ej: 123456.jpg)

            $imagen->move(public_path('escudos'), $nombreImagen); // Mueve la foto a la carpeta public/escudos

            $equipo->imagen_club = $nombreImagen; // Guarda solo el nombre de la foto en la base de datos
        }

        $equipo->save(); // Guarda el equipo en la base de datos
        return redirect()->route('equipos.index')->with('mensaje', 'Equipo creado'); // Vuelve al listado con un aviso
    }

    // Borra un equipo de la base de datos
    public function destroy($id)
    {
        $equipo = Equipo::findOrFail($id); // Busca el equipo por su ID o da error si no existe

        // Si el equipo tiene foto, la borramos de la carpeta pública
        if ($equipo->imagen_club && file_exists(public_path('escudos/' . $equipo->imagen_club))) {
            unlink(public_path('escudos/' . $equipo->imagen_club));
        }

        $equipo->delete(); // Borra el registro de la base de datos
        return redirect()->route('equipos.index'); // Vuelve al listado
    }

    // Muestra el formulario para editar un equipo que ya existe
    public function edit($id)
    {
        $equipo = Equipo::findOrFail($id); // Busca los datos del equipo que quieres editar
        return view('admin.equipos.edit', compact('equipo')); // Los manda al formulario de edición
    }

    // Actualiza los datos del equipo cuando guardas los cambios en el Edit
    public function update(Request $request, $id)
    {
        $equipo = Equipo::findOrFail($id); // Busca el equipo en la base de datos
        $equipo->nombre = $request->nombre; // Actualiza el nombre
        $equipo->categoria = $request->categoria; // Actualiza la categoría

        $request->validate([
            'nombre'      => 'required|string|min:5|max:255',
            'categoria'   => 'required|string',
            'imagen_club' => 'nullable|image|mimes:jpeg,png,jpg',
        ], [
            'nombre.min'      => 'El título debe tener al menos 5 caracteres.',
            'nombre.required'    => 'El nombre del equipo es obligatorio.',
            'categoria.required' => 'La categoría es obligatoria.',
            'imagen_club.image'  => 'El archivo debe ser una imagen.',
            'imagen_club.mimes'  => 'Solo se permiten formatos: jpg, jpeg, png.',
        ]);

        // Si has subido una foto NUEVA...
        if ($request->hasFile('imagen_club')) {
            // Borramos la foto VIEJA para no dejar basura en el servidor
            if ($equipo->imagen_club && file_exists(public_path('escudos/' . $equipo->imagen_club))) {
                unlink(public_path('escudos/' . $equipo->imagen_club));
            }

            $imagen = $request->file('imagen_club'); // Captura la foto nueva
            $nombreImagen = time() . '.' . $imagen->getClientOriginalExtension(); // Nombre nuevo
            $imagen->move(public_path('escudos'), $nombreImagen); // La guarda en public/escudos

            $equipo->imagen_club = $nombreImagen; // Actualiza el nombre de la foto en la base de datos
        }

        $equipo->save(); // Guarda los cambios finales
        return redirect()->route('equipos.index')->with('mensaje', 'Equipo actualizado correctamente');
    }


    // Muestra la ficha detallada del equipo
    public function show($id)
    {
        // Buscamos el equipo o lanzamos error 404 si no existe
        $equipo = Equipo::findOrFail($id);

        // Retornamos la vista 'show' pasando los datos del equipo
        return view('admin.equipos.show', compact('equipo'));
    }

    ////////////////
    /// BUSCADOR///
    //////////////
    public function search(Request $request)
    {
        $search = $request->get('search');

        $equipos = Equipo::where('nombre', 'like', '%' . $search . '%')
            ->orderBy('nombre', 'ASC')
            ->paginate(3); 

        // Importante para que al cambiar de página no se pierda la búsqueda
        $equipos->appends(['search' => $search]);

        return view('admin.equipos.index', compact('equipos', 'search'));
    }
}
