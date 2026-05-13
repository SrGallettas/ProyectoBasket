<?php

namespace App\Http\Controllers;

use App\Http\Requests\Jugadores\StoreJugadorRequest;
use App\Http\Requests\Jugadores\UpdateJugadorRequest;
use App\Models\Equipo;
use App\Models\Jugador;
use App\Services\Jugadores\JugadorFormOptionsService;
use App\Services\Jugadores\JugadorListService;
use App\Services\Jugadores\JugadorWriteService;
use Illuminate\Http\Request;

class JugadorController extends Controller
{
    public function __construct(
        private readonly JugadorListService $listService,
        private readonly JugadorFormOptionsService $formOptionsService,
        private readonly JugadorWriteService $writeService,
    ) {}

    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = (string) $request->input('sort', 'nombre');
        $direction = (string) $request->input('direction', 'asc');
        $jugadores = $this->listService->paginateForAdmin($search, $sort, $direction);

        return view('admin.jugadores.index', compact('jugadores', 'search', 'sort', 'direction'));
    }

    public function create()
    {
        $equipos = $this->formOptionsService->localTeams();

        return view('admin.jugadores.create', compact('equipos'));
    }

    public function store(StoreJugadorRequest $request)
    {
<<<<<<< HEAD
        $this->writeService->create($request->validated());
=======
        $request->validate([
            'nombre'           => 'required|string|max:255',
            'apellido'         => 'required|string|max:255',
            'dorsal'           => 'required|integer|min:0|max:99',
            'posicion'         => 'required|string',
            'fecha_nacimiento' => 'required|date',
            'equipo_id'        => [
                'required',

                // Esta validación no solo comprueba que exista el id.
                // También obliga a que ese id pertenezca a un equipo local.
                Rule::exists('equipos', 'id')->where(fn($query) => $query->where('es_local', true)),
            ],
            'imagen_jugador'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'oculto'           => 'nullable|boolean',
        ]);

        // Creamos el objeto jugador.
        $jugador = new Jugador();

        // Asignamos los valores básicos desde el formulario.
        $jugador->nombre = $request->nombre;
        $jugador->apellido = $request->apellido;
        $jugador->dorsal = $request->dorsal;
        $jugador->posicion = $request->posicion;
        $jugador->fecha_nacimiento = $request->fecha_nacimiento;
        $jugador->equipo_id = $request->equipo_id;

        // Campo booleano para controlar si aparece o no en la web pública.
        $jugador->oculto = $request->boolean('oculto');

        // Si el usuario subió imagen del jugador...
        if ($request->hasFile('imagen_jugador')) {
            $imagen = $request->file('imagen_jugador');

            // Se genera un nombre con time() para evitar choques de nombres.
            $nombreImagen = time() . '.' . $imagen->getClientOriginalExtension();

            // Se guarda en storage/app/public/jugadores
            $imagen->storeAs('jugadores', $nombreImagen, 'public');

            // En la BD se guarda solo el nombre del archivo.
            $jugador->imagen_jugador = $nombreImagen;
        }

        // Guardamos el jugador en la base de datos.
        $jugador->save();
>>>>>>> e0ab8cf (VERSION VALENCIANO)

        return redirect()->route('jugadores.index')->with('mensaje', 'Jugador creado con éxito');
    }

    public function edit(string $id)
    {
        $jugador = Jugador::findOrFail($id);
        $equipos = $this->formOptionsService->localTeams();

        return view('admin.jugadores.edit', compact('jugador', 'equipos'));
    }

    public function update(UpdateJugadorRequest $request, string $id)
    {
<<<<<<< HEAD
=======
        $request->validate([
            'nombre'           => 'required|string|max:255',
            'apellido'         => 'required|string|max:255',
            'dorsal'           => 'required|integer|min:0|max:99',
            'posicion'         => 'required|string',
            'fecha_nacimiento' => 'required|date',
            'equipo_id'        => [
                'required',
                Rule::exists('equipos', 'id')->where(fn($query) => $query->where('es_local', true)),
            ],

            // En update aquí admiten archivos más grandes: hasta 10MB.
            'imagen_jugador'   => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
            'oculto'           => 'nullable|boolean',
        ]);

        // Buscamos el jugador actual.
>>>>>>> e0ab8cf (VERSION VALENCIANO)
        $jugador = Jugador::findOrFail($id);
        $this->writeService->update($jugador, $request->validated());

        return redirect()->route('jugadores.index')->with('mensaje', 'Jugador actualizado correctamente');
    }

    public function destroy(string $id)
    {
        $jugador = Jugador::findOrFail($id);
        $this->writeService->delete($jugador);

        return redirect()->route('jugadores.index')->with('mensaje', 'Jugador eliminado correctamente del club');
    }

    public function show(string $id)
    {
        $jugador = Jugador::with('equipo')->findOrFail($id);

        return view('admin.jugadores.show', compact('jugador'));
    }

    public function toggleOculto(Jugador $jugador)
    {
        $jugador->oculto = ! $jugador->oculto;
        $jugador->save();

        return redirect()->route('jugadores.index')->with(
            'mensaje',
            $jugador->oculto
                ? 'Jugador ocultado de la web publica.'
                : 'Jugador visible de nuevo en la web publica.'
        );
    }
}
