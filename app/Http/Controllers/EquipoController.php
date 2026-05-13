<?php

namespace App\Http\Controllers;

use App\Http\Requests\Equipos\StoreEquipoRequest;
use App\Http\Requests\Equipos\UpdateEquipoRequest;
use App\Models\Equipo;
use App\Services\Equipos\EquipoFormOptionsService;
use App\Services\Equipos\EquipoListService;
use App\Services\Equipos\EquipoWriteService;
use Illuminate\Http\Request;

class EquipoController extends Controller
{
    public function __construct(
        private readonly EquipoListService $listService,
        private readonly EquipoFormOptionsService $formOptionsService,
        private readonly EquipoWriteService $writeService,
    ) {}

    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = (string) $request->input('sort', 'nombre');
        $direction = (string) $request->input('direction', 'asc');
        $equipos = $this->listService->paginateForAdmin($search, $sort, $direction);

        return view('admin.equipos.index', compact('equipos', 'search', 'sort', 'direction'));
    }

    public function create()
    {
        $categoryOptions = $this->formOptionsService->createOptions();

        return view('admin.equipos.create', compact('categoryOptions'));
    }

    public function store(StoreEquipoRequest $request)
    {
<<<<<<< HEAD
        $this->writeService->create($request->all());
=======
        // Validación del formulario.
        // Si algo falla, Laravel vuelve automáticamente atrás con errores.
        $request->validate([
            'nombre'      => 'required|string|min:5|max:255',
            'categoria'   => 'required|string',
            'imagen_club' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_plantilla' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'oculto'      => 'nullable|boolean',
        ]);
>>>>>>> e0ab8cf (VERSION VALENCIANO)

        return redirect()->route('equipos.index')->with('mensaje', 'Equipo creado');
    }

    public function destroy(string $id)
    {
        $equipo = Equipo::findOrFail($id);

        if (! $this->writeService->delete($equipo)) {
            return redirect()
                ->route('equipos.index')
                ->withErrors([
                    'delete' => 'No puedes eliminar este equipo porque tiene partidos asociados.',
                ]);
        }

        return redirect()->route('equipos.index');
    }

    public function edit(string $id)
    {
        $equipo = Equipo::findOrFail($id);
        $categoryOptions = $this->formOptionsService->editOptions($equipo);

        return view('admin.equipos.edit', compact('equipo', 'categoryOptions'));
    }

    public function update(UpdateEquipoRequest $request, string $id)
    {
        $equipo = Equipo::findOrFail($id);
        $this->writeService->update($equipo, $request->all());

<<<<<<< HEAD
        return redirect()->route('equipos.index')->with('mensaje', 'Equipo actualizado correctamente');
=======
        // Se asignan estos campos antes de validar, aunque normalmente
        // suele validarse primero. Aquí funciona porque luego si la validación falla,
        // Laravel corta el flujo y no llega a guardar.
        $equipo->nombre = $request->nombre;
        $equipo->categoria = $request->categoria;

        // Validación igual que en store().
        $request->validate([
            'nombre'      => 'required|string|min:5|max:255',
            'categoria'   => 'required|string',
            'imagen_club' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_plantilla' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'oculto'      => 'nullable|boolean',
        ]);

        // Se recalculan estos booleanos desde el formulario.
        $equipo->es_local = $request->has('es_local');
        $equipo->oculto = $request->boolean('oculto');

        // Si han subido un nuevo escudo...
        if ($request->hasFile('imagen_club')) {
            // Antes de guardar el nuevo, borramos el antiguo para no dejar basura.
            $this->deleteStoredImage($equipo->imagen_club, 'escudos');

            $imagen = $request->file('imagen_club');
            $nombreImagen = time() . '.' . $imagen->getClientOriginalExtension();
            $imagen->storeAs('escudos', $nombreImagen, 'public');

            // Ahora el equipo apunta a la nueva imagen.
            $equipo->imagen_club = $nombreImagen;
        }

        // Si el equipo deja de ser local, ya no debería tener foto de plantilla.
        if (!$equipo->es_local && $equipo->foto_plantilla) {
            $this->deleteStoredImage($equipo->foto_plantilla, 'equipos/plantillas');
            $equipo->foto_plantilla = null;
        }

        // Si sí es local y además suben nueva foto de plantilla, la reemplazamos.
        if ($equipo->es_local && $request->hasFile('foto_plantilla')) {
            $this->deleteStoredImage($equipo->foto_plantilla, 'equipos/plantillas');

            $imagenPlantilla = $request->file('foto_plantilla');
            $nombreImagenPlantilla = 'plantilla_' . time() . '.' .
                $imagenPlantilla->getClientOriginalExtension();
            $imagenPlantilla->storeAs(
                'equipos/plantillas',
                $nombreImagenPlantilla,
                'public'
            );

            $equipo->foto_plantilla = $nombreImagenPlantilla;
        }

        // Persistimos cambios.
        $equipo->save();

        return redirect()->route('equipos.index')->with('mensaje', 'Equipo actualizado
  correctamente');
>>>>>>> e0ab8cf (VERSION VALENCIANO)
    }

    public function show(string $id)
    {
        $equipo = Equipo::findOrFail($id);

        return view('admin.equipos.show', compact('equipo'));
    }

    public function toggleOculto(Equipo $equipo)
    {
        $equipo->oculto = ! $equipo->oculto;
        $equipo->save();

        return redirect()->route('equipos.index')->with(
            'mensaje',
            $equipo->oculto
                ? 'Equipo ocultado de la web publica.'
                : 'Equipo visible de nuevo en la web publica.'
        );
    }
}
