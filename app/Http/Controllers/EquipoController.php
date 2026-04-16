<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EquipoController extends Controller
{
    // Muestra la lista de todos los equipos (Con buscador integrado)
    public function index(Request $request)
    {
        // 1. Capturamos lo que viene del buscador del componente
        $search = $request->input('search');

        // 2. Consulta: Si hay búsqueda filtra por nombre, si no, trae todos.
        // Usamos withQueryString() para que la búsqueda se mantenga al cambiar de página.
        $equipos = Equipo::withCount('jugadores')
            ->when($search, function ($query) use ($search) {
                $query->where('nombre', 'like', '%' . $search . '%');
            })
            ->orderBy('nombre', 'asc')
            ->paginate(10)
            ->withQueryString();

        // 3. Mandamos los equipos y la variable $search a la vista
        return view('admin.equipos.index', compact('equipos', 'search'));
    }

    // Muestra el formulario para crear un equipo nuevo
    public function create()
    {
        $categoryOptions = $this->categoryOptions();

        return view('admin.equipos.create', compact('categoryOptions')); // Carga la vista del formulario
    }

    // Guarda el equipo nuevo cuando pulsas "Enviar" o "Guardar"
    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|min:5|max:255',
            'categoria'   => 'required|string',
            'imagen_club' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_plantilla' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'oculto'      => 'nullable|boolean',
        ], [
            'nombre.min'      => 'El título debe tener al menos 5 caracteres.',
            'nombre.required'    => 'El nombre del equipo es obligatorio.',
            'categoria.required' => 'La categoría es obligatoria.',
            'imagen_club.image'  => 'El archivo debe ser una imagen.',
            'imagen_club.mimes'  => 'Solo se permiten formatos: jpg, jpeg, png.',
            'imagen_club.max'    => 'La imagen es demasiado pesada (máximo 2MB).',
            'foto_plantilla.image' => 'La foto de plantilla debe ser una imagen.',
            'foto_plantilla.mimes' => 'La foto de plantilla solo admite jpg, jpeg, png o webp.',
            'foto_plantilla.max' => 'La foto de plantilla es demasiado pesada (máximo 4MB).',
        ]);

        $equipo = new Equipo(); // Crea un objeto equipo vacío
        $equipo->nombre = $request->nombre; // Rellena el nombre con lo que viene del formulario
        $equipo->categoria = $request->categoria; // Rellena la categoría
        // Si el checkbox está marcado llega en el request, si no, le asignamos false
        $equipo->es_local = $request->has('es_local');
        $equipo->oculto = $request->boolean('oculto');

        // Si el usuario ha subido una foto...
        if ($request->hasFile('imagen_club')) {
            $imagen = $request->file('imagen_club'); // Captura la foto
            $nombreImagen = time() . '.' . $imagen->getClientOriginalExtension(); // Le pone un nombre único (ej: 123456.jpg)

            $imagen->storeAs('escudos', $nombreImagen, 'public');

            $equipo->imagen_club = $nombreImagen; // Guarda solo el nombre de la foto en la base de datos
        }

        if ($equipo->es_local && $request->hasFile('foto_plantilla')) {
            $imagenPlantilla = $request->file('foto_plantilla');
            $nombreImagenPlantilla = 'plantilla_' . time() . '.' . $imagenPlantilla->getClientOriginalExtension();

            $imagenPlantilla->storeAs('equipos/plantillas', $nombreImagenPlantilla, 'public');

            $equipo->foto_plantilla = $nombreImagenPlantilla;
        }

        $equipo->save(); // Guarda el equipo en la base de datos
        return redirect()->route('equipos.index')->with('mensaje', 'Equipo creado'); // Vuelve al listado con un aviso
    }

    // Borra un equipo de la base de datos
    public function destroy($id)
    {
        $equipo = Equipo::findOrFail($id); // Busca el equipo por su ID o da error si no existe

        $tienePartidosAsociados = $equipo->partidosComoLocal()->exists()
            || $equipo->partidosComoVisitante()->exists();

        if ($tienePartidosAsociados) {
            return redirect()
                ->route('equipos.index')
                ->withErrors([
                    'delete' => 'No puedes eliminar este equipo porque tiene partidos asociados.',
                ]);
        }

        // Si el equipo tiene foto, la borramos de la carpeta pública
        $this->deleteStoredImage($equipo->imagen_club, 'escudos');
        $this->deleteStoredImage($equipo->foto_plantilla, 'equipos/plantillas');

        $equipo->delete(); // Borra el registro de la base de datos
        return redirect()->route('equipos.index'); // Vuelve al listado
    }

    // Muestra el formulario para editar un equipo que ya existe
    public function edit($id)
    {
        $equipo = Equipo::findOrFail($id); // Busca los datos del equipo que quieres editar
        $categoryOptions = $this->categoryOptions();

        if (!in_array($equipo->categoria, $categoryOptions, true)) {
            $categoryOptions[] = $equipo->categoria;
        }

        return view('admin.equipos.edit', compact('equipo', 'categoryOptions')); // Los manda al formulario de edición
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
            'imagen_club' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_plantilla' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'oculto'      => 'nullable|boolean',
        ], [
            'nombre.min'      => 'El título debe tener al menos 5 caracteres.',
            'nombre.required'    => 'El nombre del equipo es obligatorio.',
            'categoria.required' => 'La categoría es obligatoria.',
            'imagen_club.image'  => 'El archivo debe ser una imagen.',
            'imagen_club.mimes'  => 'Solo se permiten formatos: jpg, jpeg, png.',
            'imagen_club.max'    => 'La imagen es demasiado pesada (máximo 2MB).',
            'foto_plantilla.image' => 'La foto de plantilla debe ser una imagen.',
            'foto_plantilla.mimes' => 'La foto de plantilla solo admite jpg, jpeg, png o webp.',
            'foto_plantilla.max' => 'La foto de plantilla es demasiado pesada (máximo 4MB).',
        ]);

        $equipo->es_local = $request->has('es_local');
        $equipo->oculto = $request->boolean('oculto');

        // Si has subido una foto NUEVA...
        if ($request->hasFile('imagen_club')) {
            // Borramos la foto VIEJA para no dejar basura en el servidor
            $this->deleteStoredImage($equipo->imagen_club, 'escudos');

            $imagen = $request->file('imagen_club'); // Captura la foto nueva
            $nombreImagen = time() . '.' . $imagen->getClientOriginalExtension(); // Nombre nuevo
            $imagen->storeAs('escudos', $nombreImagen, 'public');

            $equipo->imagen_club = $nombreImagen; // Actualiza el nombre de la foto en la base de datos
        }

        if (!$equipo->es_local && $equipo->foto_plantilla) {
            $this->deleteStoredImage($equipo->foto_plantilla, 'equipos/plantillas');
            $equipo->foto_plantilla = null;
        }

        if ($equipo->es_local && $request->hasFile('foto_plantilla')) {
            $this->deleteStoredImage($equipo->foto_plantilla, 'equipos/plantillas');

            $imagenPlantilla = $request->file('foto_plantilla');
            $nombreImagenPlantilla = 'plantilla_' . time() . '.' . $imagenPlantilla->getClientOriginalExtension();
            $imagenPlantilla->storeAs('equipos/plantillas', $nombreImagenPlantilla, 'public');

            $equipo->foto_plantilla = $nombreImagenPlantilla;
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

    public function toggleOculto(Equipo $equipo)
    {
        $equipo->oculto = !$equipo->oculto;
        $equipo->save();

        return redirect()->route('equipos.index')->with('mensaje', $equipo->oculto ? 'Equipo ocultado de la web publica.' : 'Equipo visible de nuevo en la web publica.');
    }

    private function deleteStoredImage(?string $filename, string $directory): void
    {
        if (!$filename || $filename === 'club_14.png' || $filename === 'basket.jpeg') {
            return;
        }

        Storage::disk('public')->delete($directory . '/' . ltrim($filename, '/'));
    }

    private function categoryOptions(): array
    {
        $bases = ['Senior', 'Junior', 'Cadete', 'Infantil', 'Alevín'];
        $suffixes = ['A', 'B', 'C', 'D'];
        $options = [];

        foreach ($bases as $base) {
            foreach ($suffixes as $suffix) {
                $options[] = $base . ' ' . $suffix;
            }
        }

        return $options;
    }
}
