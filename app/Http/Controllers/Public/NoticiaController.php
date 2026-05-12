<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Noticia;

class NoticiaController extends Controller
{
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
        $noticia->oculto = ! $noticia->oculto;
        $noticia->save();

        return redirect()->route('basket.noticias')->with('mensaje', $noticia->oculto ? 'Se ha ocultado correctamente.' : 'Se ha mostrado correctamente.');
    }
}
