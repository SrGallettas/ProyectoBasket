<?php

namespace App\Http\Controllers;

use App\Models\Clasificacion;
use App\Models\ClasificacionFila;
use App\Models\Equipo;
use App\Models\Partido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClasificacionController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $clasificaciones = Clasificacion::withCount('equipos')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('nombre', 'like', '%' . $search . '%')
                        ->orWhere('categoria', 'like', '%' . $search . '%')
                        ->orWhere('temporada', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.clasificaciones.index', compact('clasificaciones', 'search'));
    }

    public function create()
    {
        $equipos = Equipo::orderBy('categoria')->orderBy('nombre')->get();

        return view('admin.clasificaciones.create', compact('equipos'));
    }

    public function store(Request $request)
    {
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

        DB::transaction(function () use ($request, $data) {
            if (!empty($data['activa'])) {
                Clasificacion::query()->update(['activa' => false]);
            }

            $clasificacion = Clasificacion::create([
                'nombre' => $data['nombre'],
                'categoria' => $data['categoria'] ?? null,
                'temporada' => $data['temporada'] ?? null,
                'activa' => $request->boolean('activa'),
            ]);

            $clasificacion->equipos()->sync($data['equipos'] ?? []);
        });

        return redirect()->route('clasificaciones.index')->with('mensaje', 'Clasificación creada correctamente.');
    }

    public function show(Clasificacion $clasificacion)
    {
        $clasificacion->load('equipos');

        $jornadas = ClasificacionFila::where('clasificacion_id', $clasificacion->id)
            ->whereNotNull('jornada')
            ->distinct()
            ->orderBy('jornada')
            ->pluck('jornada');

        $jornadaSeleccionada = request('jornada');
        if ($jornadaSeleccionada !== null) {
            $jornadaSeleccionada = (int) $jornadaSeleccionada;
        } elseif ($jornadas->isNotEmpty()) {
            $jornadaSeleccionada = (int) $jornadas->last();
        }

        $filas = collect();
        if ($jornadaSeleccionada) {
            $filas = ClasificacionFila::with('equipo')
                ->where('clasificacion_id', $clasificacion->id)
                ->where('jornada', $jornadaSeleccionada)
                ->orderByDesc('pts')
                ->orderByDesc('diff')
                ->orderByDesc('pf')
                ->get();
        }

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

    public function edit(Clasificacion $clasificacion)
    {
        $equipos = Equipo::orderBy('categoria')->orderBy('nombre')->get();
        $clasificacion->load('equipos');

        return view('admin.clasificaciones.edit', compact('clasificacion', 'equipos'));
    }

    public function update(Request $request, Clasificacion $clasificacion)
    {
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

        DB::transaction(function () use ($request, $data, $clasificacion) {
            if (!empty($data['activa'])) {
                Clasificacion::where('id', '<>', $clasificacion->id)->update(['activa' => false]);
            }

            $clasificacion->update([
                'nombre' => $data['nombre'],
                'categoria' => $data['categoria'] ?? null,
                'temporada' => $data['temporada'] ?? null,
                'activa' => $request->boolean('activa'),
            ]);

            $clasificacion->equipos()->sync($data['equipos'] ?? []);
        });

        return redirect()->route('clasificaciones.index')->with('mensaje', 'Clasificación actualizada correctamente.');
    }

    public function destroy(Clasificacion $clasificacion)
    {
        $clasificacion->delete();

        return redirect()->route('clasificaciones.index')->with('mensaje', 'Clasificación eliminada correctamente.');
    }

    public function recalcular(Clasificacion $clasificacion)
    {
        $equipoIds = $clasificacion->equipos()->pluck('equipos.id')->map(static fn ($id) => (int) $id)->all();

        if (empty($equipoIds)) {
            return redirect()->route('clasificaciones.show', $clasificacion)->with('mensaje', 'No hay equipos asignados a esta clasificación.');
        }

        $partidos = Partido::with('estadisticas')
            ->where('clasificacion_id', $clasificacion->id)
            ->where('tipo_partido', 'liga')
            ->whereNotNull('jornada')
            ->orderBy('jornada')
            ->orderBy('fecha_hora')
            ->get();

        DB::transaction(function () use ($clasificacion, $equipoIds, $partidos) {
            ClasificacionFila::where('clasificacion_id', $clasificacion->id)->delete();

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

            $jornadas = $partidos->pluck('jornada')
                ->filter()
                ->map(static fn ($jornada) => (int) $jornada)
                ->unique()
                ->sort()
                ->values();

            foreach ($jornadas as $jornada) {
                $partidosJornada = $partidos->where('jornada', $jornada);

                foreach ($partidosJornada as $partido) {
                    $localId = (int) $partido->equipo_local_id;
                    $visitanteId = (int) $partido->equipo_visitante_id;

                    if (!in_array($localId, $equipoIds, true) || !in_array($visitanteId, $equipoIds, true)) {
                        continue;
                    }

                    $statsLocal = $partido->estadisticas->firstWhere('equipo_id', $localId);
                    $statsVisitante = $partido->estadisticas->firstWhere('equipo_id', $visitanteId);

                    $puntosLocal = optional($statsLocal)->puntos_totales;
                    $puntosVisitante = optional($statsVisitante)->puntos_totales;

                    if ($puntosLocal === null || $puntosVisitante === null) {
                        continue;
                    }

                    $puntosLocal = (int) $puntosLocal;
                    $puntosVisitante = (int) $puntosVisitante;

                    $acumulado[$localId]['pj']++;
                    $acumulado[$visitanteId]['pj']++;
                    $acumulado[$localId]['pf'] += $puntosLocal;
                    $acumulado[$localId]['pc'] += $puntosVisitante;
                    $acumulado[$visitanteId]['pf'] += $puntosVisitante;
                    $acumulado[$visitanteId]['pc'] += $puntosLocal;

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

                ClasificacionFila::insert($rows);
            }
        });

        return redirect()->route('clasificaciones.show', $clasificacion)->with('mensaje', 'Clasificación recalculada correctamente.');
    }
}
