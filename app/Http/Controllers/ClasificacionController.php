<?php

namespace App\Http\Controllers;

// Modelo principal de este controlador.
// Representa una clasificación o tabla de liga.
use App\Models\Clasificacion;

// Este modelo guarda una fila de clasificación por equipo y jornada.
use App\Models\ClasificacionFila;

// Equipo se usa para asociar clubes o plantillas a cada clasificación.
use App\Models\Equipo;

// Partido se usa para mostrar y recalcular resultados de una clasificación.
use App\Models\Partido;

// Request permite leer formularios y parámetros de la petición.
use Illuminate\Http\Request;

// DB se usa para agrupar operaciones críticas en transacciones.
use Illuminate\Support\Facades\DB;

class ClasificacionController extends Controller
{
    /*
     * LISTADO DE CLASIFICACIONES
     *
     * Este método se usa en el panel admin para mostrar
     * todas las clasificaciones creadas.
     * También permite buscar por nombre, categoría o temporada.
     *
     * Flujo:
     * 1. leer el texto del buscador
     * 2. construir la consulta
     * 3. cargar cuántos equipos tiene cada clasificación
     * 4. ordenar, paginar y enviar a la vista
     */
    public function index(Request $request)
    {
        // Recogemos el texto del buscador y quitamos espacios sobrantes.
        $search = trim((string) $request->input('search'));
        $sort = (string) $request->input('sort', 'created_at');
        $direction = strtolower((string) $request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        // Limitamos las columnas ordenables para mantener la URL controlada.
        $sortableColumns = [
            'nombre' => 'nombre',
            'categoria' => 'categoria',
            'temporada' => 'temporada',
            'equipos_count' => 'equipos_count',
            'activa' => 'activa',
            'created_at' => 'created_at',
        ];

        $sortColumn = $sortableColumns[$sort] ?? 'created_at';

        // Construimos la consulta base.
        // withCount('equipos') añade el número de equipos asociados a cada clasificación.
        $clasificaciones = Clasificacion::withCount('equipos')
            ->when($search !== '', function ($query) use ($search) {
                // Si hay búsqueda, se filtra por varios campos de texto.
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('nombre', 'like', '%' . $search . '%')
                        ->orWhere('categoria', 'like', '%' . $search . '%')
                        ->orWhere('temporada', 'like', '%' . $search . '%');
                });
            });

        // En texto usamos lower(...) para ordenar sin depender de mayúsculas.
        if (in_array($sortColumn, ['nombre', 'categoria', 'temporada'], true)) {
            $clasificaciones->orderByRaw('LOWER(' . $sortColumn . ') ' . $direction);
        } else {
            $clasificaciones->orderBy($sortColumn, $direction);
        }

        $clasificaciones = $clasificaciones
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        // Enviamos resultados y texto buscado a la vista.
        return view('admin.clasificaciones.index', compact('clasificaciones', 'search', 'sort', 'direction'));
    }

    /*
     * FORMULARIO DE CREACIÓN
     *
     * Este método no guarda nada.
     * Solo prepara la vista para crear una clasificación nueva.
     */
    public function create()
    {
        // Cargamos todos los equipos disponibles para poder seleccionarlos.
        $equipos = Equipo::orderBy('categoria')->orderBy('nombre')->get();

        return view('admin.clasificaciones.create', compact('equipos'));
    }

    /*
     * GUARDAR CLASIFICACIÓN NUEVA
     *
     * Este método procesa el formulario de creación.
     *
     * Qué hace:
     * 1. valida los datos básicos
     * 2. si se marca como activa, desactiva el resto
     * 3. crea la clasificación
     * 4. sincroniza los equipos asociados
     */
    public function store(Request $request)
    {
        // Validamos los datos principales y los equipos seleccionados.
        $data = $request->validate([
            'nombre' => 'required|string|max:120',
            'categoria' => 'nullable|string|max:120',
            'temporada' => 'nullable|string|max:40',
            'activa' => 'nullable|boolean',
            'equipos' => 'required|array|min:2',
            'equipos.*' => 'integer|exists:equipos,id',
        ], [
            'nombre.required' => 'Debes indicar un nombre para la clasificación.',
            'equipos.required' => 'Debes seleccionar equipos para la clasificación.',
            'equipos.min' => 'Debes seleccionar al menos 2 equipos.',
        ]);

        // Se usa transacción para que activar, crear y vincular equipos
        // se guarde todo junto o no se guarde nada.
        DB::transaction(function () use ($request, $data) {
            // Si esta clasificación pasa a ser la activa,
            // primero desactivamos cualquier otra que lo estuviera.
            if (!empty($data['activa'])) {
                Clasificacion::query()->update(['activa' => false]);
            }

            // Creamos el registro principal.
            $clasificacion = Clasificacion::create([
                'nombre' => $data['nombre'],
                'categoria' => $data['categoria'] ?? null,
                'temporada' => $data['temporada'] ?? null,
                'activa' => $request->boolean('activa'),
            ]);

            // Relacionamos los equipos seleccionados con la clasificación.
            $clasificacion->equipos()->sync($data['equipos'] ?? []);
        });

        return redirect()->route('clasificaciones.index')->with('mensaje', 'Clasificación creada correctamente.');
    }

    /*
     * VER DETALLE DE CLASIFICACIÓN
     *
     * Este método carga una clasificación concreta junto con:
     * - sus equipos
     * - las jornadas disponibles
     * - la tabla de una jornada concreta
     * - los partidos de esa misma jornada
     *
     * Si no se elige jornada manualmente, intenta mostrar la última disponible.
     */
    public function show(Clasificacion $clasificacion)
    {
        // Cargamos los equipos asociados para la vista.
        $clasificacion->load('equipos');

        // Buscamos qué jornadas tienen filas calculadas en esta clasificación.
        $jornadas = ClasificacionFila::where('clasificacion_id', $clasificacion->id)
            ->whereNotNull('jornada')
            ->distinct()
            ->orderBy('jornada')
            ->pluck('jornada');

        // Si llega una jornada por URL, se usa esa.
        // Si no, se intenta mostrar la última jornada disponible.
        $jornadaSeleccionada = request('jornada');
        if ($jornadaSeleccionada !== null) {
            $jornadaSeleccionada = (int) $jornadaSeleccionada;
        } elseif ($jornadas->isNotEmpty()) {
            $jornadaSeleccionada = (int) $jornadas->last();
        }

        // Por defecto dejamos una colección vacía si no hay jornada seleccionada.
        $filas = collect();
        if ($jornadaSeleccionada) {
            // Cargamos la tabla de esa jornada, ordenada por los criterios habituales.
            $filas = ClasificacionFila::with('equipo')
                ->where('clasificacion_id', $clasificacion->id)
                ->where('jornada', $jornadaSeleccionada)
                ->orderByDesc('pts')
                ->orderByDesc('diff')
                ->orderByDesc('pf')
                ->get();
        }

        // También cargamos los partidos de liga de esa clasificación.
        // Si hay jornada seleccionada, se filtran solo los de esa jornada.
        $partidos = Partido::with(['equipoLocal', 'equipoVisitante', 'estadisticas'])
            ->where('clasificacion_id', $clasificacion->id)
            ->where('tipo_partido', 'liga')
            ->when($jornadaSeleccionada, function ($query) use ($jornadaSeleccionada) {
                $query->where('jornada', $jornadaSeleccionada);
            })
            ->orderBy('fecha_hora')
            ->get();

        return view('admin.clasificaciones.show', compact('clasificacion', 'jornadas', 'jornadaSeleccionada', 'filas', 'partidos'));
    }

    /*
     * FORMULARIO DE EDICIÓN
     *
     * Busca una clasificación existente y carga la vista de edición.
     * También trae los equipos actuales para poder reajustarlos.
     */
    public function edit(Clasificacion $clasificacion)
    {
        // Cargamos todos los equipos disponibles para el selector.
        $equipos = Equipo::orderBy('categoria')->orderBy('nombre')->get();

        // Cargamos también los equipos ya vinculados a esta clasificación.
        $clasificacion->load('equipos');

        return view('admin.clasificaciones.edit', compact('clasificacion', 'equipos'));
    }

    /*
     * ACTUALIZAR CLASIFICACIÓN
     *
     * Este método guarda cambios sobre una clasificación ya existente.
     *
     * Qué hace:
     * 1. valida los datos
     * 2. si se marca como activa, desactiva el resto
     * 3. actualiza los campos principales
     * 4. sincroniza los equipos vinculados
     */
    public function update(Request $request, Clasificacion $clasificacion)
    {
        // Validamos igual que en creación.
        $data = $request->validate([
            'nombre' => 'required|string|max:120',
            'categoria' => 'nullable|string|max:120',
            'temporada' => 'nullable|string|max:40',
            'activa' => 'nullable|boolean',
            'equipos' => 'required|array|min:2',
            'equipos.*' => 'integer|exists:equipos,id',
        ], [
            'equipos.required' => 'Debes seleccionar equipos para la clasificación.',
            'equipos.min' => 'Debes seleccionar al menos 2 equipos.',
        ]);

        // La transacción evita dejar media actualización aplicada.
        DB::transaction(function () use ($request, $data, $clasificacion) {
            // Si esta pasa a ser la activa, el resto deben desactivarse.
            if (!empty($data['activa'])) {
                Clasificacion::where('id', '<>', $clasificacion->id)->update(['activa' => false]);
            }

            // Actualizamos los campos principales.
            $clasificacion->update([
                'nombre' => $data['nombre'],
                'categoria' => $data['categoria'] ?? null,
                'temporada' => $data['temporada'] ?? null,
                'activa' => $request->boolean('activa'),
            ]);

            // Reemplazamos la lista de equipos por la selección actual.
            $clasificacion->equipos()->sync($data['equipos'] ?? []);
        });

        return redirect()->route('clasificaciones.index')->with('mensaje', 'Clasificación actualizada correctamente.');
    }

    /*
     * ELIMINAR CLASIFICACIÓN
     *
     * Este método borra la clasificación seleccionada.
     */
    public function destroy(Clasificacion $clasificacion)
    {
        $clasificacion->delete();

        return redirect()->route('clasificaciones.index')->with('mensaje', 'Clasificación eliminada correctamente.');
    }

    /*
     * RECALCULAR CLASIFICACIÓN
     *
     * Este método reconstruye la tabla jornada a jornada
     * a partir de los partidos de liga asociados.
     *
     * Flujo:
     * 1. leer los equipos que participan en la clasificación
     * 2. cargar partidos de liga con jornada informada
     * 3. borrar filas antiguas
     * 4. recalcular acumulados partido a partido
     * 5. guardar una foto de la tabla en cada jornada
     */
    public function recalcular(Clasificacion $clasificacion)
    {
        // Sacamos los IDs de equipos vinculados a esta clasificación.
        $equipoIds = $clasificacion->equipos()->pluck('equipos.id')->map(static fn ($id) => (int) $id)->all();

        // Si no hay equipos asociados, no tiene sentido recalcular.
        if (empty($equipoIds)) {
            return redirect()->route('clasificaciones.show', $clasificacion)->with('mensaje', 'No hay equipos asignados a esta clasificación.');
        }

        // Cargamos solo partidos de liga con jornada definida.
        // También traemos estadísticas para poder leer los puntos finales.
        $partidos = Partido::with('estadisticas')
            ->where('clasificacion_id', $clasificacion->id)
            ->where('tipo_partido', 'liga')
            ->whereNotNull('jornada')
            ->orderBy('jornada')
            ->orderBy('fecha_hora')
            ->get();

        DB::transaction(function () use ($clasificacion, $equipoIds, $partidos) {
            // Primero borramos cualquier cálculo antiguo de esta clasificación.
            ClasificacionFila::where('clasificacion_id', $clasificacion->id)->delete();

            // Este array guarda los acumulados que van creciendo jornada a jornada.
            $acumulado = [];
            foreach ($equipoIds as $equipoId) {
                $acumulado[$equipoId] = [
                    'pj' => 0,
                    'pg' => 0,
                    'pp' => 0,
                    'pf' => 0,
                    'pc' => 0,
                    'pts' => 0,
                ];
            }

            // Identificamos las jornadas únicas presentes en los partidos.
            $jornadas = $partidos->pluck('jornada')
                ->filter()
                ->map(static fn ($jornada) => (int) $jornada)
                ->unique()
                ->sort()
                ->values();

            // Recorremos las jornadas en orden para que el acumulado tenga sentido.
            foreach ($jornadas as $jornada) {
                $partidosJornada = $partidos->where('jornada', $jornada);

                foreach ($partidosJornada as $partido) {
                    // Obtenemos los IDs real de local y visitante.
                    $localId = (int) $partido->equipo_local_id;
                    $visitanteId = (int) $partido->equipo_visitante_id;

                    // Si algún equipo del partido no pertenece a esta clasificación,
                    // el encuentro no debe afectar a esta tabla.
                    if (!in_array($localId, $equipoIds, true) || !in_array($visitanteId, $equipoIds, true)) {
                        continue;
                    }

                    // Buscamos las estadísticas finales de ambos equipos.
                    $statsLocal = $partido->estadisticas->firstWhere('equipo_id', $localId);
                    $statsVisitante = $partido->estadisticas->firstWhere('equipo_id', $visitanteId);

                    $puntosLocal = optional($statsLocal)->puntos_totales;
                    $puntosVisitante = optional($statsVisitante)->puntos_totales;

                    // Si el partido aún no tiene marcador final, se ignora.
                    if ($puntosLocal === null || $puntosVisitante === null) {
                        continue;
                    }

                    $puntosLocal = (int) $puntosLocal;
                    $puntosVisitante = (int) $puntosVisitante;

                    // Sumamos partidos jugados y puntos a favor/en contra.
                    $acumulado[$localId]['pj']++;
                    $acumulado[$visitanteId]['pj']++;
                    $acumulado[$localId]['pf'] += $puntosLocal;
                    $acumulado[$localId]['pc'] += $puntosVisitante;
                    $acumulado[$visitanteId]['pf'] += $puntosVisitante;
                    $acumulado[$visitanteId]['pc'] += $puntosLocal;

                    // Reparto de victorias, derrotas y puntos de clasificación.
                    if ($puntosLocal > $puntosVisitante) {
                        $acumulado[$localId]['pg']++;
                        $acumulado[$localId]['pts'] += 2;
                        $acumulado[$visitanteId]['pp']++;
                        $acumulado[$visitanteId]['pts'] += 1;
                    } elseif ($puntosVisitante > $puntosLocal) {
                        $acumulado[$visitanteId]['pg']++;
                        $acumulado[$visitanteId]['pts'] += 2;
                        $acumulado[$localId]['pp']++;
                        $acumulado[$localId]['pts'] += 1;
                    } else {
                        $acumulado[$localId]['pts'] += 1;
                        $acumulado[$visitanteId]['pts'] += 1;
                    }
                }

                // En cada jornada guardamos una "foto" completa de la tabla acumulada.
                $now = now();
                $rows = [];
                foreach ($equipoIds as $equipoId) {
                    $rows[] = [
                        'clasificacion_id' => $clasificacion->id,
                        'equipo_id' => $equipoId,
                        'jornada' => $jornada,
                        'pj' => $acumulado[$equipoId]['pj'],
                        'pg' => $acumulado[$equipoId]['pg'],
                        'pp' => $acumulado[$equipoId]['pp'],
                        'pf' => $acumulado[$equipoId]['pf'],
                        'pc' => $acumulado[$equipoId]['pc'],
                        'diff' => $acumulado[$equipoId]['pf'] - $acumulado[$equipoId]['pc'],
                        'pts' => $acumulado[$equipoId]['pts'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                // Insertamos de golpe todas las filas de esa jornada.
                ClasificacionFila::insert($rows);
            }
        });

        return redirect()->route('clasificaciones.show', $clasificacion)->with('mensaje', 'Clasificación recalculada correctamente.');
    }
}
