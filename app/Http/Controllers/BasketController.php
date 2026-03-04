<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Noticia;
use App\Models\Merchandising;
use App\Models\Partido;
use Illuminate\Http\Request;

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
        return view('basket.galeria');
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
}
