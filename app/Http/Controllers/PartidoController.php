<?php

namespace App\Http\Controllers;

use App\Http\Requests\Partidos\StorePartidoRequest;
use App\Http\Requests\Partidos\UpdatePartidoRequest;
use App\Models\Partido;
use App\Services\Partidos\PartidoBoardService;
use App\Services\Partidos\PartidoFormOptionsService;
use App\Services\Partidos\PartidoListService;
use App\Services\Partidos\PartidoWriteService;
use Illuminate\Http\Request;

class PartidoController extends Controller
{
    public function __construct(
        private readonly PartidoBoardService $boardService,
        private readonly PartidoListService $listService,
        private readonly PartidoFormOptionsService $formOptionsService,
        private readonly PartidoWriteService $writeService,
    ) {}

    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = (string) $request->input('sort', 'fecha_hora');
        $direction = (string) $request->input('direction', 'desc');
        $partidos = $this->listService->paginateForAdmin($search, $sort, $direction);

        return view('admin.partidos.index', compact('partidos', 'search', 'sort', 'direction'));
    }

    public function create()
    {
        ['equipos' => $equipos, 'clasificaciones' => $clasificaciones] = $this->formOptionsService->forForm();

        return view('admin.partidos.create', compact('equipos', 'clasificaciones'));
    }

    public function store(StorePartidoRequest $request)
    {
<<<<<<< HEAD
        $this->writeService->create($request->validated());
=======
        // Validación base del formulario.
        $request->validate([
            'equipo_local_id'     => 'required|exists:equipos,id',
            'equipo_visitante_id' => 'required|exists:equipos,id|
  different:equipo_local_id',
            'fecha_hora'          => 'required|date',
            'lugar'               => 'required|string|max:255',
            'categoria'           => 'required|string',
            'tipo_partido'        => 'required|in:liga,amistoso',
            'clasificacion_id'    => 'nullable|exists:clasificaciones,id',
            'jornada'             => 'nullable|integer|min:1',
            'oculto'              => 'nullable|boolean',
            'oculto_en_clasificacion' => 'nullable|boolean',
        ]);
>>>>>>> e0ab8cf (VERSION VALENCIANO)

        return redirect()->route('partidos.index')->with('mensaje', 'Partido programado correctamente');
    }

    public function show($id)
    {
        $partido = Partido::with(['equipoLocal', 'equipoVisitante', 'estadisticas', 'cuartos'])->findOrFail($id);
        $this->boardService->attachQuarterStatsToPartido($partido);

        return view('admin.partidos.show', compact('partido'));
    }

    public function edit(string $id)
    {
        $partido = Partido::with(['estadisticas', 'cuartos', 'equipoLocal', 'equipoVisitante'])->findOrFail($id);
        $this->boardService->attachQuarterStatsToPartido($partido);

        ['equipos' => $equipos, 'clasificaciones' => $clasificaciones] = $this->formOptionsService->forForm();

        return view('admin.partidos.edit', compact('partido', 'equipos', 'clasificaciones'));
    }

    public function update(UpdatePartidoRequest $request, string $id)
    {
<<<<<<< HEAD
=======
        // Reglas base del formulario.
        $rules = [
            'equipo_local_id'     => 'required|exists:equipos,id',
            'equipo_visitante_id' => 'required|exists:equipos,id|
  different:equipo_local_id',
            'fecha_hora'          => 'required|date',
            'lugar'               => 'required|string|max:255',
            'categoria'           => 'required|string',
            'tipo_partido'        => 'required|in:liga,amistoso',
            'clasificacion_id'    => 'nullable|exists:clasificaciones,id',
            'jornada'             => 'nullable|integer|min:1',
            'oculto'              => 'nullable|boolean',
            'oculto_en_clasificacion' => 'nullable|boolean',
        ];

        // Añadimos reglas dinámicas para estadísticas de local y visitante.
        foreach (['local', 'visitante'] as $lado) {
            foreach ($this->statFields() as $campo) {
                $rules["stats.$lado.$campo"] = 'nullable|integer|min:0';
            }

            // También añadimos reglas para los puntos por cuarto o prórroga.
            foreach (array_keys($this->periodLabels()) as $periodo) {
                $rules["quarters.$lado.$periodo"] = 'nullable|integer|min:0';
            }
        }

        // Montamos el validador manual porque luego se le añaden comprobaciones extra.
        $validator = Validator::make($request->all(), $rules);

        // after(...) permite ejecutar validaciones adicionales
        // una vez revisadas las reglas normales.
        $validator->after(function ($validator) use ($request) {
            foreach (['local', 'visitante'] as $lado) {
                $stats = (array) $request->input("stats.$lado", []);

                // Comprobamos que las canastas anotadas no superen a las intentadas.
                $this->assertMadeNotGreaterThanAttempted(
                    $validator,
                    $stats,
                    $lado,
                    'fgm',
                    'fga',
                    'tiros de campo'
                );
                $this->assertMadeNotGreaterThanAttempted(
                    $validator,
                    $stats,
                    $lado,
                    't2m',
                    't2a',
                    'tiros de 2'
                );
                $this->assertMadeNotGreaterThanAttempted(
                    $validator,
                    $stats,
                    $lado,
                    't3m',
                    't3a',
                    'tiros de 3'
                );
                $this->assertMadeNotGreaterThanAttempted(
                    $validator,
                    $stats,
                    $lado,
                    'ftm',
                    'fta',
                    'tiros libres'
                );
            }
        });

        // Si algo falla, aquí se lanza la validación.
        $validator->validate();

        // Si el partido es de liga, comprobamos además coherencia con clasificación y jornada .
        $this->validateLigaAssignment($request);

        // Buscamos el partido actual.
>>>>>>> e0ab8cf (VERSION VALENCIANO)
        $partido = Partido::findOrFail($id);
        $this->writeService->update($partido, $request->validated());

        return redirect()->route('partidos.index')->with('mensaje', 'Partido actualizado correctamente.');
    }

    public function destroy(string $id)
    {
        $partido = Partido::findOrFail($id);
        $this->writeService->delete($partido);

        return redirect()->route('partidos.index')->with('mensaje', 'Partido eliminado correctamente del club');
    }

    public function toggleOculto(Partido $partido)
    {
        $partido->oculto = ! $partido->oculto;
        $partido->save();

        $mensaje = $partido->oculto
            ? 'Partido ocultado de la web publica.'
            : 'Partido visible de nuevo en la web publica.';

        return redirect()->route('partidos.index')->with('mensaje', $mensaje);
    }

    public function toggleOcultoClasificacion(Partido $partido)
    {
        $partido->oculto_en_clasificacion = ! $partido->oculto_en_clasificacion;
        $partido->save();

        $mensaje = $partido->oculto_en_clasificacion
            ? 'Partido ocultado de la clasificación pública.'
            : 'Partido visible de nuevo en la clasificación pública.';

        return redirect()->route('partidos.index')->with('mensaje', $mensaje);
    }
<<<<<<< HEAD
=======

    /*
         * CAMPOS DE ESTADÍSTICAS
         *
         * Este método centraliza los nombres de las columnas que forman
         * una estadística de equipo dentro de un partido.
         *
         * Se usa en update() para dos cosas:
         * 1. construir reglas de validación sin repetir nombres a mano
         * 2. montar el payload que se guarda en PartidoEstadistica
         *
         * Si algún día añades o quitas una estadística, lo normal es tocar
         * esta lista y el resto del flujo seguirá usando esos mismos campos.
         */
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

        /*
         * ETIQUETAS DE PERIODOS
         *
         * Este mapa define qué periodos admite el sistema.
         *
         * La clave numérica es el valor interno que se usa para leer y guardar
         * los puntos por cuarto en la base de datos.
         * El texto es la etiqueta corta que puede mostrar la vista.
         *
         * En este caso:
         * - 1, 2, 3 y 4 son los cuatro cuartos normales
         * - 5 representa la prórroga
         */
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

    /*
         * SINCRONIZAR ESTADÍSTICAS DEL PARTIDO
         *
         * Este método mantiene sincronizadas dos tablas hijas del partido:
         * - PartidoEstadistica: estadísticas generales de cada equipo
         * - PartidoCuarto: puntos anotados en cada periodo
         *
         * La idea es tratar local y visitante por separado, porque cada uno
         * puede tener sus propias estadísticas y sus propios cuartos.
         *
         * Flujo real:
         * 1. leer del request los datos de un lado del partido
         * 2. normalizar vacíos a null y números a int
         * 3. si todo está vacío, interpretar que no debe quedar nada guardado
         * 4. si hay datos, hacer updateOrCreate de la estadística general
         * 5. procesar cada cuarto/prórroga con la misma lógica
         * 6. borrar posibles restos antiguos de una edición anterior
         *
         * Con esto evitamos dejar datos "huérfanos" cuando el admin edita
         * un partido y vacía campos que antes sí tenían valor.
         */
    private function syncPartidoStats(Partido $partido, Request $request): void
    {
        // Traducimos "local" y "visitante" al ID real del equipo en este partido.
        $equiposPorLado = [
            'local' => (int) $partido->equipo_local_id,
            'visitante' => (int) $partido->equipo_visitante_id,
        ];

        foreach ($equiposPorLado as $lado => $equipoId) {
            // Leemos stats.local.* o stats.visitante.* según el lado.
            $inputStats = (array) $request->input("stats.$lado", []);
            $payload = [];

            // Recorremos todos los campos esperados y los dejamos listos para BD.
            foreach ($this->statFields() as $campo) {
                $valor = $inputStats[$campo] ?? null;

                // Un input vacío del formulario no se guarda como cadena vacía:
                // se convierte a null. Si hay valor, se fuerza a entero.
                $payload[$campo] = ($valor === '' || $valor === null) ? null : (int)
                $valor;
            }

            // Si todos los campos han quedado a null, entendemos que este equipo
            // no debe tener estadísticas guardadas para este partido.
            $hasStats = collect($payload)->filter(static fn($value) => $value !==
                null)->isNotEmpty();

            // Si no queda ninguna estadística, borramos también los cuartos
            // de ese equipo para que no sobrevivan datos incoherentes.
            if (!$hasStats) {
                PartidoEstadistica::where('partido_id', $partido->id)->where(
                    'equipo_id',
                    $equipoId
                )->delete();
                PartidoCuarto::where('partido_id', $partido->id)->where(
                    'equipo_id',
                    $equipoId
                )->delete();
                continue;
            }

            // Si ya existía una fila de estadísticas la actualizamos;
            // si no existía, la creamos.
            PartidoEstadistica::updateOrCreate(
                [
                    'partido_id' => $partido->id,
                    'equipo_id' => $equipoId,
                ],
                $payload
            );

            // Leemos quarters.local.* o quarters.visitante.*.
            $quarterInput = (array) $request->input("quarters.$lado", []);

            // Aquí iremos anotando qué periodos siguen presentes tras la edición.
            $seenPeriods = [];

            foreach (array_keys($this->periodLabels()) as $periodo) {
                $valor = $quarterInput[$periodo] ?? null;

                // Si ese periodo viene vacío, eliminamos solo ese cuarto.
                // Esto permite borrar, por ejemplo, una prórroga que antes existía.
                if ($valor === '' || $valor === null) {
                    PartidoCuarto::where('partido_id', $partido->id)
                        ->where('equipo_id', $equipoId)
                        ->where('periodo', $periodo)
                        ->delete();
                    continue;
                }

                // Si hay puntos, guardamos el periodo: se actualiza si existe
                // y se crea si todavía no estaba.
                PartidoCuarto::updateOrCreate(
                    [
                        'partido_id' => $partido->id,
                        'equipo_id' => $equipoId,
                        'periodo' => $periodo,
                    ],
                    ['puntos' => (int) $valor]
                );

                // Lo marcamos como periodo vigente en esta edición.
                $seenPeriods[] = $periodo;
            }

            // Limpieza final: si en la base de datos quedaba algún periodo
            // antiguo que ya no aparece en el formulario, se borra.
            PartidoCuarto::where('partido_id', $partido->id)
                ->where('equipo_id', $equipoId)
                ->whereNotIn('periodo', $seenPeriods)
                ->delete();
        }
    }

        /*
         * VALIDAR ANOTADOS VS INTENTADOS
         *
         * Este método compara dos campos que van en pareja:
         * - anotados
         * - intentados
         *
         * Ejemplos de parejas:
         * - fgm frente a fga
         * - t3m frente a t3a
         * - ftm frente a fta
         *
         * Si el número de anotados supera al de intentados, no se lanza una
         * excepción directamente: se añade un error manual al validador para
         * que el formulario muestre el mensaje en el campo correspondiente.
         */
    private function assertMadeNotGreaterThanAttempted($validator, array $stats, string
    $lado, string $madeKey, string $attemptKey, string $label): void
    {
        // Leemos los dos campos que queremos comparar.
        $made = $stats[$madeKey] ?? null;
        $attempt = $stats[$attemptKey] ?? null;

        // Si alguno no viene informado, no intentamos validar esa relación.
        if ($made === null || $attempt === null || $made === '' || $attempt === '') {
            return;
        }

        // Caso incoherente: no puedes anotar más de lo que has intentado.
        if ((int) $made > (int) $attempt) {
            $equipoTxt = $lado === 'local' ? 'local' : 'visitante';

            $validator->errors()->add(
                "stats.$lado.$madeKey",
                "En el equipo {$equipoTxt}, los anotados de {$label} no pueden superar
  los intentados."
            );
        }
    }

    /*
         * VALIDAR ASIGNACIÓN A LIGA
         *
         * Esta es una validación de negocio adicional para partidos de liga.
         *
         * Las reglas básicas del formulario ya comprueban que ciertos IDs
         * existan, pero aquí se revisa algo más específico:
         * 1. que el partido realmente sea de tipo "liga"
         * 2. que se haya elegido una clasificación
         * 3. que se haya indicado la jornada
         * 4. que el equipo local y el visitante pertenezcan a esa clasificación
         *
         * Si el partido es amistoso, este helper no hace nada.
         */
    private function validateLigaAssignment(Request $request): void
    {
        // Leemos el tipo para saber si esta validación aplica o no.
        $tipo = $request->input('tipo_partido');

        // En amistosos no hay clasificación ni jornada obligatoria.
        if ($tipo !== 'liga') {
            return;
        }

        // Primera capa de validación: comprobar que hay clasificación
        // y que la jornada es un entero válido.
        $request->validate([
            'clasificacion_id' => 'required|exists:clasificaciones,id',
            'jornada' => 'required|integer|min:1',
        ]);

        // Cargamos la clasificación escogida junto con los equipos que pertenecen a ella.
        $clasificacion = Clasificacion::with('equipos:id')->findOrFail((int)
        $request->input('clasificacion_id'));

        // Nos quedamos solo con una lista simple de IDs para comparar rápido.
        $equiposClasificacion = $clasificacion->equipos
            ->pluck('id')
            ->map(static fn($id) => (int) $id)
            ->all();

        $equipoLocalId = (int) $request->input('equipo_local_id');
        $equipoVisitanteId = (int) $request->input('equipo_visitante_id');

        // Si alguno de los dos equipos no está dentro de esa clasificación,
        // el partido sería incoherente y se aborta con error de validación.
        if (!in_array($equipoLocalId, $equiposClasificacion, true) || ! in_array($equipoVisitanteId, $equiposClasificacion, true)) {
            throw ValidationException::withMessages([
                'clasificacion_id' => __('admin.matches.validation.league_team_membership'),
            ]);
        }
    }

    /*
         * ASOCIAR CUARTOS A LAS ESTADÍSTICAS
         *
         * Este método no guarda nada en base de datos.
         * Solo reorganiza en memoria los datos ya cargados del partido.
         *
         * El partido trae dos colecciones separadas:
         * - estadisticas
         * - cuartos
         *
         * Aquí lo que hacemos es recorrer cada bloque de estadísticas y
         * "engancharle" únicamente los cuartos del mismo equipo.
         *
         * Así, en la vista, cuando pintas una estadística de un equipo,
         * ya tienes dentro sus cuartos correctos y ordenados por periodo.
         */
    private function attachQuarterStatsToPartido(Partido $partido): void
    {
        foreach ($partido->estadisticas as $estadistica) {
            // Filtramos los cuartos de ese mismo equipo y los ordenamos
            // para que la vista los consuma directamente.
            $estadistica->setRelation(
                'cuartos',
                $partido->cuartos
                    ->where('equipo_id', (int) $estadistica->equipo_id)
                    ->sortBy('periodo')
                    ->values()
            );
        }
    }
>>>>>>> e0ab8cf (VERSION VALENCIANO)
}
