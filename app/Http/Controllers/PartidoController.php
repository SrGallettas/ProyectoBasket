<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Partido;
use App\Models\Equipo;
use App\Models\Clasificacion;
use App\Models\PartidoEstadistica;
use App\Models\PartidoCuarto;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PartidoController extends Controller
{
    // Muestra la lista de partidos con buscador integrado
    public function index(Request $request)
    {
        // 1. Capturamos el término de búsqueda
        $search = $request->input('search');

        // 2. Consulta: Buscamos por nombre de equipo local o visitante
        $partidos = Partido::with(['equipoLocal', 'equipoVisitante', 'clasificacion'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('equipoLocal', function ($q) use ($search) {
                    $q->where('nombre', 'like', '%' . $search . '%');
                })
                    ->orWhereHas('equipoVisitante', function ($q) use ($search) {
                        $q->where('nombre', 'like', '%' . $search . '%');
                    });
            })
            ->orderBy('fecha_hora', 'desc') // Los más recientes primero
            ->paginate(9)
            ->withQueryString();

        // 3. Retornamos la vista de partidos (asegúrate de crear la carpeta admin/partidos)
        return view('admin.partidos.index', compact('partidos', 'search'));
    }


    // Muestra el formulario para crear un nuevo partido
    public function create()
    {
        // 1. Traemos todos los equipos ordenados por nombre
        $equipos = Equipo::orderBy('nombre', 'asc')->get();
        $clasificaciones = Clasificacion::with('equipos')->orderBy('nombre', 'asc')->get();

        // 2. Enviamos los equipos a la vista de partidos
        return view('admin.partidos.create', compact('equipos', 'clasificaciones'));
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
            'tipo_partido'        => 'required|in:liga,amistoso',
            'clasificacion_id'    => 'nullable|exists:clasificaciones,id',
            'jornada'             => 'nullable|integer|min:1',
            'oculto'              => 'nullable|boolean',
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
            'tipo_partido.required' => 'Debes indicar si el partido es de liga o amistoso.',
        ]);

        $this->validateLigaAssignment($request);

        // 2. CREACIÓN DEL OBJETO (Igual que hiciste con Jugador)
        $partido = new Partido();

        // Asignamos los valores del formulario
        $partido->equipo_local_id = $request->equipo_local_id;
        $partido->equipo_visitante_id = $request->equipo_visitante_id;
        $partido->fecha_hora = $request->fecha_hora;
        $partido->lugar = $request->lugar;
        $partido->categoria = $request->categoria;
        $partido->tipo_partido = $request->tipo_partido;
        $partido->clasificacion_id = $request->tipo_partido === 'liga' ? $request->clasificacion_id : null;
        $partido->jornada = $request->tipo_partido === 'liga' ? $request->jornada : null;
        $partido->oculto = $request->boolean('oculto');

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
        $partido = Partido::with(['equipoLocal', 'equipoVisitante', 'estadisticas', 'cuartos'])->findOrFail($id);
        $this->attachQuarterStatsToPartido($partido);

        return view('admin.partidos.show', compact('partido'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $partido = Partido::with(['estadisticas', 'cuartos', 'equipoLocal', 'equipoVisitante'])->findOrFail($id); // Busca el partido que quieres editar
        $this->attachQuarterStatsToPartido($partido);
        $equipos = Equipo::orderBy('nombre', 'asc')->get(); // Trae todos los equipos para que puedas cambiar el local o visitante si quieres
        $clasificaciones = Clasificacion::with('equipos')->orderBy('nombre', 'asc')->get();
        return view('admin.partidos.edit', compact('partido', 'equipos', 'clasificaciones')); // Los manda al formulario de edición
    }

    /**
     * Update the specified resource in storage.
     */


    public function update(Request $request, $id)
    {
        // 1. VALIDACIÓN: Comprobamos que los datos recibidos son correctos.
        $rules = [
            'equipo_local_id'     => 'required|exists:equipos,id',
            'equipo_visitante_id' => 'required|exists:equipos,id|different:equipo_local_id',
            'fecha_hora'          => 'required|date',
            'lugar'               => 'required|string|max:255',
            'categoria'           => 'required|string',
            'tipo_partido'        => 'required|in:liga,amistoso',
            'clasificacion_id'    => 'nullable|exists:clasificaciones,id',
            'jornada'             => 'nullable|integer|min:1',
            'oculto'              => 'nullable|boolean',
        ];

        foreach (['local', 'visitante'] as $lado) {
            foreach ($this->statFields() as $campo) {
                $rules["stats.$lado.$campo"] = 'nullable|integer|min:0';
            }

            foreach (array_keys($this->periodLabels()) as $periodo) {
                $rules["quarters.$lado.$periodo"] = 'nullable|integer|min:0';
            }
        }

        $validator = Validator::make($request->all(), $rules, [
            // Mensajes de error personalizados para que el admin sepa qué falla
            'equipo_local_id.required'     => 'El equipo local es obligatorio.',
            'equipo_visitante_id.required'      => 'El equipo visitante es obligatorio.',
            'equipo_visitante_id.different' => 'Un equipo no puede jugar contra sí mismo.',
            'fecha_hora.required'          => 'La fecha y hora son obligatorias para el calendario.',
            'lugar.required'               => 'Debes indicar dónde se juega el partido.',
        ]);

        $validator->after(function ($validator) use ($request) {
            foreach (['local', 'visitante'] as $lado) {
                $stats = (array) $request->input("stats.$lado", []);

                $this->assertMadeNotGreaterThanAttempted($validator, $stats, $lado, 'fgm', 'fga', 'tiros de campo');
                $this->assertMadeNotGreaterThanAttempted($validator, $stats, $lado, 't2m', 't2a', 'tiros de 2');
                $this->assertMadeNotGreaterThanAttempted($validator, $stats, $lado, 't3m', 't3a', 'tiros de 3');
                $this->assertMadeNotGreaterThanAttempted($validator, $stats, $lado, 'ftm', 'fta', 'tiros libres');
            }
        });

        $validator->validate();
        $this->validateLigaAssignment($request);

        // 2. BUSQUEDA: Localizamos el partido por su ID o devolvemos error 404
        $partido = Partido::findOrFail($id);

        // 3. ASIGNACIÓN: Actualizamos los campos con los datos del formulario
        $partido->equipo_local_id     = $request->equipo_local_id;
        $partido->equipo_visitante_id = $request->equipo_visitante_id;
        $partido->fecha_hora          = $request->fecha_hora;
        $partido->lugar               = $request->lugar;
        $partido->categoria           = $request->categoria;
        $partido->tipo_partido        = $request->tipo_partido;
        $partido->clasificacion_id    = $request->tipo_partido === 'liga' ? $request->clasificacion_id : null;
        $partido->jornada             = $request->tipo_partido === 'liga' ? $request->jornada : null;
        $partido->oculto              = $request->boolean('oculto');

        // 4. PERSISTENCIA: Guardamos los cambios en la base de datos
        $partido->save();
        $this->syncPartidoStats($partido, $request);

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

    public function toggleOculto(Partido $partido)
    {
        $partido->oculto = !$partido->oculto;
        $partido->save();

        $mensaje = $partido->oculto
            ? 'Partido ocultado de la web publica.'
            : 'Partido visible de nuevo en la web publica.';

        return redirect()->route('partidos.index')->with('mensaje', $mensaje);
    }

    private function statFields(): array
    {
        return [
            'fga',
            'fgm',
            't2a',
            't2m',
            't3a',
            't3m',
            'fta',
            'ftm',
            'reb_of',
            'reb_def',
            'ast',
            'blk',
            'tov',
            'stl',
            'pf',
            'tf',
            'puntos_totales',
        ];
    }

    private function periodLabels(): array
    {
        return [
            1 => 'Q1',
            2 => 'Q2',
            3 => 'Q3',
            4 => 'Q4',
            5 => 'PR',
        ];
    }

    private function syncPartidoStats(Partido $partido, Request $request): void
    {
        $equiposPorLado = [
            'local' => (int) $partido->equipo_local_id,
            'visitante' => (int) $partido->equipo_visitante_id,
        ];

        foreach ($equiposPorLado as $lado => $equipoId) {
            $inputStats = (array) $request->input("stats.$lado", []);
            $payload = [];

            foreach ($this->statFields() as $campo) {
                $valor = $inputStats[$campo] ?? null;
                $payload[$campo] = ($valor === '' || $valor === null) ? null : (int) $valor;
            }

            $hasStats = collect($payload)->filter(static fn ($value) => $value !== null)->isNotEmpty();

            if (!$hasStats) {
                PartidoEstadistica::where('partido_id', $partido->id)->where('equipo_id', $equipoId)->delete();
                PartidoCuarto::where('partido_id', $partido->id)->where('equipo_id', $equipoId)->delete();
                continue;
            }

            PartidoEstadistica::updateOrCreate(
                [
                    'partido_id' => $partido->id,
                    'equipo_id' => $equipoId,
                ],
                $payload
            );

            $quarterInput = (array) $request->input("quarters.$lado", []);
            $seenPeriods = [];

            foreach (array_keys($this->periodLabels()) as $periodo) {
                $valor = $quarterInput[$periodo] ?? null;

                if ($valor === '' || $valor === null) {
                    PartidoCuarto::where('partido_id', $partido->id)
                        ->where('equipo_id', $equipoId)
                        ->where('periodo', $periodo)
                        ->delete();
                    continue;
                }

                PartidoCuarto::updateOrCreate(
                    [
                        'partido_id' => $partido->id,
                        'equipo_id' => $equipoId,
                        'periodo' => $periodo,
                    ],
                    ['puntos' => (int) $valor]
                );

                $seenPeriods[] = $periodo;
            }

            PartidoCuarto::where('partido_id', $partido->id)
                ->where('equipo_id', $equipoId)
                ->whereNotIn('periodo', $seenPeriods)
                ->delete();
        }
    }

    private function assertMadeNotGreaterThanAttempted($validator, array $stats, string $lado, string $madeKey, string $attemptKey, string $label): void
    {
        $made = $stats[$madeKey] ?? null;
        $attempt = $stats[$attemptKey] ?? null;

        if ($made === null || $attempt === null || $made === '' || $attempt === '') {
            return;
        }

        if ((int) $made > (int) $attempt) {
            $equipoTxt = $lado === 'local' ? 'local' : 'visitante';
            $validator->errors()->add("stats.$lado.$madeKey", "En el equipo {$equipoTxt}, los anotados de {$label} no pueden superar los intentados.");
        }
    }

    private function validateLigaAssignment(Request $request): void
    {
        $tipo = $request->input('tipo_partido');

        if ($tipo !== 'liga') {
            return;
        }

        $request->validate([
            'clasificacion_id' => 'required|exists:clasificaciones,id',
            'jornada' => 'required|integer|min:1',
        ], [
            'clasificacion_id.required' => 'En partidos de liga debes seleccionar una clasificación.',
            'jornada.required' => 'En partidos de liga debes indicar la jornada.',
        ]);

        $clasificacion = Clasificacion::with('equipos:id')->findOrFail((int) $request->input('clasificacion_id'));
        $equiposClasificacion = $clasificacion->equipos->pluck('id')->map(static fn ($id) => (int) $id)->all();

        $equipoLocalId = (int) $request->input('equipo_local_id');
        $equipoVisitanteId = (int) $request->input('equipo_visitante_id');

        if (!in_array($equipoLocalId, $equiposClasificacion, true) || !in_array($equipoVisitanteId, $equiposClasificacion, true)) {
            throw ValidationException::withMessages([
                'clasificacion_id' => 'Los dos equipos deben pertenecer a la clasificación seleccionada.',
            ]);
        }
    }

    private function attachQuarterStatsToPartido(Partido $partido): void
    {
        foreach ($partido->estadisticas as $estadistica) {
            $estadistica->setRelation(
                'cuartos',
                $partido->cuartos
                    ->where('equipo_id', (int) $estadistica->equipo_id)
                    ->sortBy('periodo')
                    ->values()
            );
        }
    }
}
