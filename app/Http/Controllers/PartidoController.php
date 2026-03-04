<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Partido;
use App\Models\Equipo;

class PartidoController extends Controller
{
    // Muestra la lista de partidos con buscador integrado
    public function index(Request $request)
    {
        // 1. Capturamos el término de búsqueda
        $search = $request->input('search');

        // 2. Consulta: Buscamos por nombre de equipo local o visitante
        $partidos = Partido::with(['equipoLocal', 'equipoVisitante'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('equipoLocal', function ($q) use ($search) {
                    $q->where('nombre', 'like', '%' . $search . '%');
                })
                    ->orWhereHas('equipoVisitante', function ($q) use ($search) {
                        $q->where('nombre', 'like', '%' . $search . '%');
                    });
            })
            ->orderBy('fecha_hora', 'desc') // Los más recientes primero
            ->paginate(10)
            ->withQueryString();

        // 3. Retornamos la vista de partidos (asegúrate de crear la carpeta admin/partidos)
        return view('admin.partidos.index', compact('partidos', 'search'));
    }


    // Muestra el formulario para crear un nuevo partido
    public function create()
    {
        // 1. Traemos todos los equipos ordenados por nombre
        $equipos = Equipo::orderBy('nombre', 'asc')->get();

        // 2. Enviamos los equipos a la vista de partidos
        return view('admin.partidos.create', compact('equipos'));
    }
    /**
     * Store a newly created resource in storage.
     */
    // Guarda el nuevo partido en la base de datos
    public function store(Request $request)
    {
        // 1. VALIDACIÓN (Todos los campos obligatorios)
        $request->validate([
            'equipo_local_id'     => 'required|exists:equipos,id',
            'equipo_visitante_id' => 'required|exists:equipos,id|different:equipo_local_id', // different evita que sea el mismo
            'fecha_hora'          => 'required|date',
            'lugar'               => 'required|string|max:255',
            'categoria'           => 'required|string',
        ], [
            // Mensajes personalizados
            'equipo_local_id.required'     => 'Debes seleccionar el equipo local.',
            'equipo_local_id.exists'       => 'El equipo local seleccionado no es válido.',
            'equipo_visitante_id.required' => 'Debes seleccionar el equipo visitante.',
            'equipo_visitante_id.exists'   => 'El equipo visitante seleccionado no es válido.',
            'equipo_visitante_id.different' => '¡Cuidado! Un equipo no puede jugar contra sí mismo.',
            'fecha_hora.required'          => 'La fecha y la hora son obligatorias.',
            'fecha_hora.date'              => 'El formato de fecha y hora no es correcto.',
            'lugar.required'               => 'Debes indicar el lugar del partido (pabellón).',
            'categoria.required' => 'Debes seleccionar una categoría para el partido.',
        ]);

        // 2. CREACIÓN DEL OBJETO (Igual que hiciste con Jugador)
        $partido = new Partido();

        // Asignamos los valores del formulario
        $partido->equipo_local_id = $request->equipo_local_id;
        $partido->equipo_visitante_id = $request->equipo_visitante_id;
        $partido->fecha_hora = $request->fecha_hora;
        $partido->lugar = $request->lugar;
        $partido->categoria = $request->categoria;

        // Nota: Los puntos no los tocamos aquí, se quedan como NULL en la BD
        // porque el partido aún no se ha jugado.

        // 3. GUARDAR Y REDIRECCIONAR
        $partido->save();

        // Volvemos al listado con el mensaje verde de éxito
        return redirect()->route('partidos.index')->with('mensaje', 'Partido programado correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $partido = Partido::with(['equipoLocal', 'equipoVisitante'])->findOrFail($id);

        return view('admin.partidos.show', compact('partido'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $partido = Partido::findOrFail($id); // Busca el partido que quieres editar
        $equipos = Equipo::orderBy('nombre', 'asc')->get(); // Trae todos los equipos para que puedas cambiar el local o visitante si quieres
        return view('admin.partidos.edit', compact('partido', 'equipos')); // Los manda al formulario de edición
    }

    /**
     * Update the specified resource in storage.
     */


    public function update(Request $request, $id)
    {
        // 1. VALIDACIÓN: Comprobamos que los datos recibidos son correctos.
        $request->validate([
            'equipo_local_id'     => 'required|exists:equipos,id',
            'equipo_visitante_id' => 'required|exists:equipos,id|different:equipo_local_id',
            'fecha_hora'          => 'required|date',
            'lugar'               => 'required|string|max:255',
            'categoria'           => 'required|string',
        ], [
            // Mensajes de error personalizados para que el admin sepa qué falla
            'equipo_local_id.required'     => 'El equipo local es obligatorio.',
            'equipo_visitante_id.required'      => 'El equipo visitante es obligatorio.',
            'equipo_visitante_id.different' => 'Un equipo no puede jugar contra sí mismo.',
            'fecha_hora.required'          => 'La fecha y hora son obligatorias para el calendario.',
            'lugar.required'               => 'Debes indicar dónde se juega el partido.',
        ]);

        // 2. BUSQUEDA: Localizamos el partido por su ID o devolvemos error 404
        $partido = Partido::findOrFail($id);

        // 3. ASIGNACIÓN: Actualizamos los campos con los datos del formulario
        $partido->equipo_local_id     = $request->equipo_local_id;
        $partido->equipo_visitante_id = $request->equipo_visitante_id;
        $partido->fecha_hora          = $request->fecha_hora;
        $partido->lugar               = $request->lugar;
        $partido->categoria           = $request->categoria;

        // 4. PERSISTENCIA: Guardamos los cambios en la base de datos
        $partido->save();

        // 5. REDIRECCIÓN: Volvemos al listado con el mensaje de confirmación
        return redirect()->route('partidos.index')->with('mensaje', 'Partido actualizado correctamente.');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $partido = Partido::findOrFail($id);

        $partido->delete();

        return redirect()->route('partidos.index')->with('mensaje', 'Partido eliminado correctamente del club');
    }
}
