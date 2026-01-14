<?php

namespace App\Http\Controllers;

use App\Models\Equipo; // No olvides importar el modelo para que funcione la página de equipo
use Illuminate\Http\Request;

class BasketController extends Controller
{
    public function inicio()
    {
        return view('basket.inicio');
    }

    public function equipo(Request $request)
    {
        // 1. Obtenemos los equipos locales para los botones
        $equiposLocales = \App\Models\Equipo::where('es_local', true)->get();

        // 2. Filtro por categoría (por defecto 'Senior')
        $categoriaSel = $request->get('categoria', 'Senior');

        // 3. Buscamos el equipo específico con sus jugadores
        $equipoMostrar = \App\Models\Equipo::where('es_local', true)
            ->where('categoria', $categoriaSel)
            ->with('jugadores')
            ->first();

        return view('basket.equipo', compact('equiposLocales', 'equipoMostrar', 'categoriaSel'));
    }

    public function merchandising()
    {
        return view('basket.merchandising');
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
        return view('basket.partidos');
    }

    public function galeria()
    {
        return view('basket.galeria');
    }

    public function contacto()
    {
        return view('basket.contacto');
    }
}
