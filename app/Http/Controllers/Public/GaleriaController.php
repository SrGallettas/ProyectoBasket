<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Galeria;

class GaleriaController extends Controller
{
    public function galeria()
    {
        $tipoSeleccionado = request('tipo', 'todos');
        $tipos = ['todos' => 'Todos'] + Galeria::TIPOS;

        $galerias = Galeria::with('portadaImagen')
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

    public function toggleOcultoGaleria(Galeria $galeria)
    {
        $galeria->oculto = ! $galeria->oculto;
        $galeria->save();

        return redirect()->route('basket.galeria')->with('mensaje', $galeria->oculto ? 'Se ha ocultado correctamente.' : 'Se ha mostrado correctamente.');
    }
}
