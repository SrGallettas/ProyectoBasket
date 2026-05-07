<?php

namespace App\Http\Controllers;

// Request permite leer lo que llega desde formularios o por la URL.
use Illuminate\Http\Request;

// Modelo principal del controlador.
// Representa los partidos guardados en la base de datos.
use App\Models\Partido;

// Equipo se usa para cargar local y visitante en formularios.
use App\Models\Equipo;

// Clasificacion se usa para asociar partidos de liga a una clasificación concreta.
use App\Models\Clasificacion;

// PartidoEstadistica guarda las estadísticas generales de cada equipo en un partido.
use App\Models\PartidoEstadistica;

// PartidoCuarto guarda los puntos por cuarto o prórroga.
use App\Models\PartidoCuarto;

// Validator se usa cuando la validación es más compleja y se monta manualmente.
use Illuminate\Support\Facades\Validator;

// DB se usa para hacer borrados dentro de una transacción.
use Illuminate\Support\Facades\DB;

// ValidationException permite lanzar errores personalizados de validación.
use Illuminate\Validation\ValidationException;

class PartidoController extends Controller
{
    /*
         * LISTADO DE PARTIDOS
         *
         * Este método se usa en el panel de administración para mostrar
         * todos los partidos.
         * Además incluye buscador y paginación.
         *
         * Flujo:
         * 1. leer el texto del buscador
         * 2. construir la consulta
         * 3. si hay búsqueda, filtrar por nombre del equipo local o visitante
         * 4. cargar relaciones necesarias
         * 5. ordenar por fecha
         * 6. paginar y enviar a la vista
         */
    public function index(Request $request)
    {
        // Recogemos el texto del buscador.
        // Ejemplo: /admin/partidos?search=Senior
        $search = $request->input('search');
        $sort = (string) $request->input('sort', 'fecha_hora');
        $direction = strtolower((string) $request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        // Algunas columnas visibles pertenecen a la tabla partidos
        // y otras vienen de joins con equipos.
        $sortableColumns = [
            'fecha_hora' => 'partidos.fecha_hora',
            'categoria' => 'partidos.categoria',
            'equipo_local' => 'equipo_local.nombre',
            'equipo_visitante' => 'equipo_visitante.nombre',
            'lugar' => 'partidos.lugar',
            'oculto' => 'partidos.oculto',
        ];

        $sortColumn = $sortableColumns[$sort] ?? 'partidos.fecha_hora';

        // Construimos la consulta de partidos.
        // with(...) hace eager loading para traer también los equipos y clasificación.
        $partidos = Partido::with(['equipoLocal', 'equipoVisitante', 'clasificacion'])
            ->leftJoin('equipos as equipo_local', 'equipo_local.id', '=', 'partidos.equipo_local_id')
            ->leftJoin('equipos as equipo_visitante', 'equipo_visitante.id', '=', 'partidos.equipo_visitante_id')
            ->select('partidos.*')
            ->when($search, function ($query) use ($search) {
                // Si hay texto, buscamos por nombre del equipo local...
                $query->where('equipo_local.nombre', 'like', '%' . $search . '%')
                    // ...o por nombre del equipo visitante.
                    ->orWhere('equipo_visitante.nombre', 'like', '%' . $search . '%');
            });

        // En texto ignoramos mayúsculas para ordenar como humano.
        if (in_array($sortColumn, ['partidos.categoria', 'equipo_local.nombre', 'equipo_visitante.nombre', 'partidos.lugar'], true)) {
            $partidos->orderByRaw('LOWER(' . $sortColumn . ') ' . $direction);
        } else {
            $partidos->orderBy($sortColumn, $direction);
        }

        $partidos = $partidos
            ->orderBy('partidos.fecha_hora', 'desc')
            ->paginate(9) // 9 partidos por página
            ->withQueryString(); // mantiene ?search=... al cambiar de página

        // Enviamos resultados y texto buscado a la vista.
        return view('admin.partidos.index', compact('partidos', 'search', 'sort', 'direction'));
    }

    /*
         * FORMULARIO DE CREACIÓN
         *
         * Este método no guarda nada.
         * Solo prepara los datos necesarios para crear un partido.
         *
         * Carga:
         * - todos los equipos, para elegir local y visitante
         * - todas las clasificaciones, por si el partido es de liga
         */
    public function create()
    {
        // Traemos todos los equipos ordenados por nombre.
        $equipos = Equipo::orderBy('nombre', 'asc')->get();

        // Cargamos también sus equipos asociados porque luego puede hacer falta
        // comprobar o mostrar qué equipos pertenecen a cada clasificación.
        $clasificaciones = Clasificacion::with('equipos')->orderBy(
            'nombre',
            'asc'
        )->get();

        return view('admin.partidos.create', compact('equipos', 'clasificaciones'));
    }

    /*
         * GUARDAR PARTIDO NUEVO
         *
         * Este método procesa el formulario de creación.
         *
         * Qué hace:
         * 1. valida campos básicos
         * 2. valida reglas extra si el partido es de liga
         * 3. crea un partido vacío
         * 4. asigna los datos del formulario
         * 5. guarda el registro en base de datos
         * 6. vuelve al listado
         */
    public function store(Request $request)
    {
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
        ], [
            'equipo_local_id.required' => 'Debes seleccionar el equipo local.',
            'equipo_local_id.exists' => 'El equipo local seleccionado no es válido.',
            'equipo_visitante_id.required' => 'Debes seleccionar el equipo visitante.',
            'equipo_visitante_id.exists' => 'El equipo visitante seleccionado no es
  válido.',
            'equipo_visitante_id.different' => '¡Cuidado! Un equipo no puede jugar contra
  sí mismo.',
            'fecha_hora.required' => 'La fecha y la hora son obligatorias.',
            'fecha_hora.date' => 'El formato de fecha y hora no es correcto.',
            'lugar.required' => 'Debes indicar el lugar del partido (pabellón).',
            'categoria.required' => 'Debes seleccionar una categoría para el partido.',
            'tipo_partido.required' => 'Debes indicar si el partido es de liga o
  amistoso.',
        ]);

        // Si es partido de liga, aquí se comprueba que haya clasificación,
        // jornada y que ambos equipos pertenezcan a esa clasificación.
        $this->validateLigaAssignment($request);

        // Creamos una instancia vacía del modelo.
        $partido = new Partido();

        // Asignamos los datos básicos que llegan del formulario.
        $partido->equipo_local_id = $request->equipo_local_id;
        $partido->equipo_visitante_id = $request->equipo_visitante_id;
        $partido->fecha_hora = $request->fecha_hora;
        $partido->lugar = $request->lugar;
        $partido->categoria = $request->categoria;
        $partido->tipo_partido = $request->tipo_partido;

        // Clasificación y jornada solo tienen sentido en partidos de liga.
        $partido->clasificacion_id = $request->tipo_partido === 'liga' ?
            $request->clasificacion_id : null;
        $partido->jornada = $request->tipo_partido === 'liga' ? $request->jornada : null;

        // Campo booleano para ocultar el partido en la web pública.
        $partido->oculto = $request->boolean('oculto');

        // Este booleano solo aplica a partidos de liga.
        $partido->oculto_en_clasificacion = $request->tipo_partido === 'liga'
            ? $request->boolean('oculto_en_clasificacion')
            : false;

        // Los puntos o estadísticas no se guardan aquí.
        // Este método solo programa el partido.
        $partido->save();

        return redirect()->route('partidos.index')->with('mensaje', 'Partido programado
  correctamente');
    }

    /*
         * VER DETALLE DE UN PARTIDO
         *
         * Este método carga un partido concreto con toda la información
         * relacionada necesaria para la vista de detalle.
         *
         * Además agrupa los cuartos dentro de cada bloque de estadísticas
         * para que la vista lo tenga ya preparado.
         */
    public function show($id)
    {
        // Buscamos el partido y cargamos relaciones útiles.
        $partido = Partido::with([
            'equipoLocal',
            'equipoVisitante',
            'estadisticas',
            'cuartos'
        ])->findOrFail($id);

        // Reorganiza los cuartos para asociarlos al bloque estadístico correcto.
        $this->attachQuarterStatsToPartido($partido);

        return view('admin.partidos.show', compact('partido'));
    }

    /*
         * FORMULARIO DE EDICIÓN
         *
         * Busca un partido existente y carga la vista de edición.
         * También trae estadísticas, cuartos, equipos y clasificaciones
         * para poder editar todo desde el formulario.
         */
    public function edit(string $id)
    {
        // Buscamos el partido junto con todas las relaciones necesarias.
        $partido = Partido::with([
            'estadisticas',
            'cuartos',
            'equipoLocal',
            'equipoVisitante'
        ])->findOrFail($id);

        // Igual que en show(), dejamos los cuartos ya vinculados a sus estadísticas.
        $this->attachQuarterStatsToPartido($partido);

        // Cargamos todos los equipos para poder cambiar local o visitante.
        $equipos = Equipo::orderBy('nombre', 'asc')->get();

        // También cargamos las clasificaciones disponibles.
        $clasificaciones = Clasificacion::with('equipos')->orderBy(
            'nombre',
            'asc'
        )->get();

        return view('admin.partidos.edit', compact(
            'partido',
            'equipos',
            'clasificaciones'
        ));
    }

    /*
         * ACTUALIZAR PARTIDO
         *
         * Este método guarda cambios sobre un partido ya existente.
         *
         * Qué hace:
         * 1. construye reglas de validación básicas y dinámicas
         * 2. valida estadísticas y puntos por cuarto
         * 3. valida restricciones extra de partidos de liga
         * 4. busca el partido
         * 5. actualiza sus datos básicos
         * 6. guarda estadísticas y cuartos asociados
         * 7. vuelve al listado
         */
    public function update(Request $request, $id)
    {
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
        $validator = Validator::make($request->all(), $rules, [
            'equipo_local_id.required' => 'El equipo local es obligatorio.',
            'equipo_visitante_id.required' => 'El equipo visitante es obligatorio.',
            'equipo_visitante_id.different' => 'Un equipo no puede jugar contra sí
  mismo.',
            'fecha_hora.required' => 'La fecha y hora son obligatorias para el
  calendario.',
            'lugar.required' => 'Debes indicar dónde se juega el partido.',
        ]);

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
        $partido = Partido::findOrFail($id);

        // Actualizamos campos básicos.
        $partido->equipo_local_id = $request->equipo_local_id;
        $partido->equipo_visitante_id = $request->equipo_visitante_id;
        $partido->fecha_hora = $request->fecha_hora;
        $partido->lugar = $request->lugar;
        $partido->categoria = $request->categoria;
        $partido->tipo_partido = $request->tipo_partido;
        $partido->clasificacion_id = $request->tipo_partido === 'liga' ?
            $request->clasificacion_id : null;
        $partido->jornada = $request->tipo_partido === 'liga' ? $request->jornada : null;
        $partido->oculto = $request->boolean('oculto');
        $partido->oculto_en_clasificacion = $request->tipo_partido === 'liga'
            ? $request->boolean('oculto_en_clasificacion')
            : false;

        // Guardamos cambios del partido.
        $partido->save();

        // Sincronizamos estadísticas generales y puntos por cuarto.
        $this->syncPartidoStats($partido, $request);

        return redirect()->route('partidos.index')->with('mensaje', 'Partido actualizado
  correctamente.');
    }

    /*
         * ELIMINAR PARTIDO
         *
         * Este método borra:
         * 1. las estadísticas asociadas
         * 2. los puntos por cuarto asociados
         * 3. el propio partido
         *
         * Se hace dentro de una transacción para que todo se borre
         * de forma consistente.
         */
    public function destroy($id)
    {
        // Busca el partido o lanza 404 si no existe.
        $partido = Partido::findOrFail($id);

        DB::transaction(function () use ($partido) {
            // Primero se borran los datos dependientes.
            $partido->estadisticas()->delete();
            $partido->cuartos()->delete();

            // Después se borra el partido.
            $partido->delete();
        });

        return redirect()->route('partidos.index')->with('mensaje', 'Partido eliminado
  correctamente del club');
    }

    /*
         * OCULTAR / MOSTRAR PARTIDO
         *
         * No borra el partido.
         * Solo cambia el booleano "oculto".
         *
         * Si oculto = true:
         * el partido deja de aparecer en la web pública
         *
         * Si oculto = false:
         * vuelve a ser visible
         */
    public function toggleOculto(Partido $partido)
    {
        // Invertimos el valor actual.
        $partido->oculto = !$partido->oculto;
        $partido->save();

        $mensaje = $partido->oculto
            ? 'Partido ocultado de la web publica.'
            : 'Partido visible de nuevo en la web publica.';

        return redirect()->route('partidos.index')->with('mensaje', $mensaje);
    }

    /*
         * OCULTAR / MOSTRAR PARTIDO EN CLASIFICACIÓN
         *
         * Este método no oculta el partido entero de la web,
         * solo controla si debe aparecer dentro de la clasificación pública.
         */
    public function toggleOcultoClasificacion(Partido $partido)
    {
        // Invertimos el valor actual.
        $partido->oculto_en_clasificacion = !$partido->oculto_en_clasificacion;
        $partido->save();

        $mensaje = $partido->oculto_en_clasificacion
            ? 'Partido ocultado de la clasificación pública.'
            : 'Partido visible de nuevo en la clasificación pública.';

        return redirect()->route('partidos.index')->with('mensaje', $mensaje);
    }

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
        ], [
            'clasificacion_id.required' => 'En partidos de liga debes seleccionar una
  clasificación.',
            'jornada.required' => 'En partidos de liga debes indicar la jornada.',
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
                'clasificacion_id' => 'Los dos equipos deben pertenecer a la
  clasificación seleccionada.',
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
}
