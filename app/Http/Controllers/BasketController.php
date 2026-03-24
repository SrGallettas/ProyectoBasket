<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Noticia;
use App\Models\Merchandising;
use App\Models\Partido;
use App\Models\Galeria;
use App\Models\Jugador;
use App\Models\Clasificacion;
use App\Models\ClasificacionFila;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class BasketController extends Controller
{
    public function inicio()
    {
        $noticias = Noticia::where('oculto', false)->latest()->take(6)->get();
        return view('basket.inicio', compact("noticias"));
    }

    public function equipo(Request $request)
    {
        // 1. Obtenemos los equipos locales para los botones
        $equiposLocales = Equipo::where('es_local', true)
            ->where('oculto', false)
            ->orderBy('categoria')
            ->get();

        // 2. Filtro por categoría (por defecto 'Senior')
        $categoriaSel = $request->get('categoria', 'Senior');

        // 3. Buscamos el equipo específico
        $equipoMostrar = Equipo::where('es_local', true)
            ->where('oculto', false)
            ->where('categoria', $categoriaSel)
            ->first();

        // 4. Paginamos los jugadores (1 por página)
        $jugadores = null;
        if ($equipoMostrar) {
            $jugadores = $equipoMostrar->jugadores()
                ->where('oculto', false)
                ->orderBy('dorsal')
                ->paginate(9)
                ->withQueryString();
        }

        return view('basket.equipo', compact('equiposLocales', 'equipoMostrar', 'categoriaSel', 'jugadores'));
    }

    public function tienda()
    {
        $merchandising = Merchandising::where('oculto', false)
            ->orderBy('nombre', 'asc')
            ->paginate(9);
        return view('basket.merchandising', compact('merchandising'));
    }

    public function clasificacion()
    {
        $clasificaciones = Clasificacion::orderByDesc('activa')->orderBy('nombre')->get();
        $clasificacionSeleccionada = null;

        if ($clasificaciones->isNotEmpty()) {
            $clasificacionId = request('clasificacion_id');

            if ($clasificacionId) {
                $clasificacionSeleccionada = $clasificaciones->firstWhere('id', (int) $clasificacionId);
            }

            if (!$clasificacionSeleccionada) {
                $clasificacionSeleccionada = $clasificaciones->firstWhere('activa', true) ?: $clasificaciones->first();
            }
        }

        $jornadas = collect();
        $jornadaSeleccionada = null;
        $filas = collect();
        $partidosJornada = collect();

        if ($clasificacionSeleccionada) {
            $jornadasClasificacion = ClasificacionFila::where('clasificacion_id', $clasificacionSeleccionada->id)
                ->whereNotNull('jornada')
                ->distinct()
                ->orderBy('jornada')
                ->pluck('jornada');

            $jornadasPartidos = Partido::where('clasificacion_id', $clasificacionSeleccionada->id)
                ->where('tipo_partido', 'liga')
                ->whereNotNull('jornada')
                ->where('oculto', false)
                ->distinct()
                ->orderBy('jornada')
                ->pluck('jornada');

            $jornadas = $jornadasClasificacion
                ->merge($jornadasPartidos)
                ->map(fn ($jornada) => (int) $jornada)
                ->filter(fn ($jornada) => $jornada > 0)
                ->unique()
                ->sort()
                ->values();

            $jornadaSeleccionada = request('jornada');
            if ($jornadaSeleccionada !== null) {
                $jornadaSeleccionada = (int) $jornadaSeleccionada;
            } elseif ($jornadas->isNotEmpty()) {
                $jornadaSeleccionada = (int) $jornadas->last();
            }

            if ($jornadaSeleccionada) {
                $filas = ClasificacionFila::with('equipo')
                    ->where('clasificacion_id', $clasificacionSeleccionada->id)
                    ->where('jornada', $jornadaSeleccionada)
                    ->orderByDesc('pts')
                    ->orderByDesc('diff')
                    ->orderByDesc('pf')
                    ->get();

                $partidosJornada = Partido::with(['equipoLocal', 'equipoVisitante', 'estadisticas'])
                    ->where('clasificacion_id', $clasificacionSeleccionada->id)
                    ->where('tipo_partido', 'liga')
                    ->where('jornada', $jornadaSeleccionada)
                    ->where('oculto', false)
                    ->orderBy('fecha_hora')
                    ->get();
            }
        }

        return view('basket.clasificacion', compact(
            'clasificaciones',
            'clasificacionSeleccionada',
            'jornadas',
            'jornadaSeleccionada',
            'filas',
            'partidosJornada'
        ));
    }

    public function estadisticas()
    {
        return view('basket.estadisticas');
    }

    public function partidos()
    {
        $partidos = Partido::with(['equipoLocal', 'equipoVisitante', 'estadisticas'])
            ->where('oculto', false)
            ->orderBy('fecha_hora', 'asc')
            ->paginate(10);
        return view('basket.partidos', compact('partidos'));
    }

    public function partidoDetalle($id)
    {
        $partido = Partido::with([
            'equipoLocal',
            'equipoVisitante',
            'estadisticas.cuartos',
        ])
            ->where('oculto', false)
            ->whereKey($id)
            ->firstOrFail();

        $statsLocal = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_local_id);
        $statsVisitante = $partido->estadisticas->firstWhere('equipo_id', $partido->equipo_visitante_id);

        return view('basket.partido_detalle', compact('partido', 'statsLocal', 'statsVisitante'));
    }

    public function toggleOcultoEquipo(Equipo $equipo)
    {
        $equipo->oculto = !$equipo->oculto;
        $equipo->save();

        return redirect()->route('basket.equipo')->with('mensaje', $equipo->oculto ? 'Se ha ocultado correctamente.' : 'Se ha mostrado correctamente.');
    }

    public function toggleOcultoJugador(Jugador $jugador)
    {
        $jugador->oculto = !$jugador->oculto;
        $jugador->save();

        return redirect()->route('basket.equipo', ['categoria' => optional($jugador->equipo)->categoria])->with('mensaje', $jugador->oculto ? 'Se ha ocultado correctamente.' : 'Se ha mostrado correctamente.');
    }

    public function toggleOculto(Partido $partido)
    {
        $partido->oculto = !$partido->oculto;
        $partido->save();

        $mensaje = $partido->oculto
            ? 'Se ha ocultado correctamente.'
            : 'Se ha mostrado correctamente.';

        return redirect()->route('basket.partidos')->with('mensaje', $mensaje);
    }

    public function galeria()
    {
        $tipoSeleccionado = request('tipo', 'todos');
        $tipos = ['todos' => 'Todos'] + Galeria::TIPOS;

        $galerias = Galeria::with('imagenes')
            ->withCount('imagenes')
            ->where('oculto', false)
            ->when($tipoSeleccionado !== 'todos', function ($query) use ($tipoSeleccionado) {
                $query->where('tipo', $tipoSeleccionado);
            })
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('basket.galeria', compact('galerias', 'tipos', 'tipoSeleccionado'));
    }

    public function galeriaDetalle($slug)
    {
        $galeria = Galeria::with('imagenes')
            ->where('oculto', false)
            ->where('slug', $slug)
            ->firstOrFail();

        return view('basket.galeria_detalle', compact('galeria'));
    }

    public function contacto()
    {
        return view('basket.contacto');
    }

    public function noticia()
    {
        $noticias = Noticia::where('oculto', false)
            ->latest()
            ->paginate(5);
        return view('basket.noticias', compact('noticias'));
    }

    public function noticia_detalle($slug)
    {
        $noticia = Noticia::where('oculto', false)
            ->where('slug', $slug)
            ->firstOrFail();

        return view('basket.noticias_detalle', compact('noticia'));
    }

    public function toggleOcultoNoticia(Noticia $noticia)
    {
        $noticia->oculto = !$noticia->oculto;
        $noticia->save();

        return redirect()->route('basket.noticias')->with('mensaje', $noticia->oculto ? 'Se ha ocultado correctamente.' : 'Se ha mostrado correctamente.');
    }

    public function toggleOcultoMerchandising(Merchandising $merchandising)
    {
        $merchandising->oculto = !$merchandising->oculto;
        $merchandising->save();

        return redirect()->route('basket.tienda')->with('mensaje', $merchandising->oculto ? 'Se ha ocultado correctamente.' : 'Se ha mostrado correctamente.');
    }

    public function toggleOcultoGaleria(Galeria $galeria)
    {
        $galeria->oculto = !$galeria->oculto;
        $galeria->save();

        return redirect()->route('basket.galeria')->with('mensaje', $galeria->oculto ? 'Se ha ocultado correctamente.' : 'Se ha mostrado correctamente.');
    }

    public function enviarInteresMerchan(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:merchandising,id',
            'talla' => 'nullable|string|max:20',
        ]);

        $producto = Merchandising::findOrFail($validated['producto_id']);
        $talla = $validated['talla'] ?? 'Sin talla especificada';
        $tallasDisponibles = $producto->talla
            ? array_map('trim', explode(',', $producto->talla))
            : [];

        if (!empty($tallasDisponibles) && !in_array($talla, $tallasDisponibles, true)) {
            return back()->withErrors([
                'talla' => 'Selecciona una talla valida para este producto.'
            ])->withInput();
        }

        $destinatario = env('MERCH_INTERES_EMAIL', 'info@bellreguardcb.com');
        $asunto = 'Interés ' . $producto->nombre;

        $mensaje = "Hola Bellreguard CB,\n\n"
            . "Estoy interesado en el producto: {$producto->nombre}.\n"
            . "Me gustaría pedirlo en la talla: {$talla}.\n\n"
            . "Gracias.";

        Mail::raw($mensaje, function ($mail) use ($destinatario, $asunto) {
            $mail->to($destinatario)->subject($asunto);
        });

        return back()->with('mensaje', 'Tu interés se ha enviado correctamente.');
    }
}
