<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Noticia;
use App\Models\Merchandising;
use App\Models\Partido;
use App\Models\Galeria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class BasketController extends Controller
{
    public function inicio()
    {
        $noticias = Noticia::latest()->take(6)->get();
        return view('basket.inicio', compact("noticias"));
    }

    public function equipo(Request $request)
    {
        // 1. Obtenemos los equipos locales para los botones
        $equiposLocales = Equipo::where('es_local', true)->get();

        // 2. Filtro por categoría (por defecto 'Senior')
        $categoriaSel = $request->get('categoria', 'Senior');

        // 3. Buscamos el equipo específico con sus jugadores
        $equipoMostrar = Equipo::where('es_local', true)
            ->where('categoria', $categoriaSel)
            ->with('jugadores')
            ->first();

        return view('basket.equipo', compact('equiposLocales', 'equipoMostrar', 'categoriaSel'));
    }

    public function tienda()
    {
        $merchandising = Merchandising::orderBy('nombre', 'asc')->get();        
        return view('basket.merchandising', compact('merchandising'));
    }

    public function clasificacion()
    {
        return view('basket.clasificacion');
    }

    public function estadisticas()
    {
        return view('basket.estadisticas');
    }

    public function partidos()
    {
        $partidos = Partido::orderBy('created_at', 'asc')->paginate(10);
        return view('basket.partidos', compact('partidos'));
    }

    public function galeria()
    {
        $tipoSeleccionado = request('tipo', 'todos');
        $tipos = ['todos' => 'Todos'] + Galeria::TIPOS;

        $galerias = Galeria::with('imagenes')
            ->withCount('imagenes')
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
        $galeria = Galeria::with('imagenes')->where('slug', $slug)->firstOrFail();

        return view('basket.galeria_detalle', compact('galeria'));
    }

    public function contacto()
    {
        return view('basket.contacto');
    }

    public function noticia()
    {
        $noticias = Noticia::orderBy('created_at', 'asc')->paginate(10);
        return view('basket.noticias', compact('noticias'));
    }

    public function noticia_detalle($slug)
    {
        $noticia = Noticia::where('slug', $slug)->firstOrFail();

        return view('basket.noticias_detalle', compact('noticia'));
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
