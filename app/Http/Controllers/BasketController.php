<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Public\ClasificacionController as PublicClasificacionController;
use App\Http\Controllers\Public\EquipoController as PublicEquipoController;
use App\Http\Controllers\Public\GaleriaController as PublicGaleriaController;
use App\Http\Controllers\Public\HomeController as PublicHomeController;
use App\Http\Controllers\Public\NoticiaController as PublicNoticiaController;
use App\Http\Controllers\Public\PartidoController as PublicPartidoController;
use App\Http\Controllers\Public\TiendaController as PublicTiendaController;
use App\Models\Equipo;
use App\Models\Jugador;
use App\Models\Noticia;
use App\Models\Partido;
use Illuminate\Http\Request;

class BasketController extends Controller
{
    public function inicio()
    {
        return app(PublicHomeController::class)->inicio();
    }

    public function equipo(Request $request)
    {
        return app(PublicEquipoController::class)->equipo($request);
    }

    public function jugadorDetalle(Jugador $jugador)
    {
        return app(PublicEquipoController::class)->jugadorDetalle($jugador);
    }

    public function tienda()
    {
        return app(PublicTiendaController::class)->tienda();
    }

    public function mostrarFormularioPedido()
    {
        return app(PublicTiendaController::class)->mostrarFormularioPedido();
    }

    public function anadirProductoCesta(Request $request)
    {
        return app(PublicTiendaController::class)->anadirProductoCesta($request);
    }

    public function actualizarProductoCesta(Request $request, string $itemKey)
    {
        return app(PublicTiendaController::class)->actualizarProductoCesta($request, $itemKey);
    }

    public function eliminarProductoCesta(string $itemKey)
    {
        return app(PublicTiendaController::class)->eliminarProductoCesta($itemKey);
    }

    public function enviarPedidoMerchan(Request $request)
    {
        return app(PublicTiendaController::class)->enviarPedidoMerchan($request);
    }

    public function clasificacion()
    {
        return app(PublicClasificacionController::class)->clasificacion();
    }

    public function estadisticas(Request $request)
    {
        return app(PublicClasificacionController::class)->estadisticas($request);
    }

    public function partidos()
    {
        return app(PublicPartidoController::class)->partidos();
    }

    public function partidoDetalle($id)
    {
        return app(PublicPartidoController::class)->partidoDetalle($id);
    }

    public function toggleOcultoEquipo(Equipo $equipo)
    {
        return app(PublicEquipoController::class)->toggleOcultoEquipo($equipo);
    }

    public function toggleOcultoJugador(Jugador $jugador)
    {
        return app(PublicEquipoController::class)->toggleOcultoJugador($jugador);
    }

    public function toggleOcultoPartido(Partido $partido)
    {
        return app(PublicPartidoController::class)->toggleOcultoPartido($partido);
    }

    public function galeria()
    {
        return app(PublicGaleriaController::class)->galeria();
    }

    public function galeriaDetalle($slug)
    {
        return app(PublicGaleriaController::class)->galeriaDetalle($slug);
    }

    public function contacto()
    {
        return app(PublicHomeController::class)->contacto();
    }

    public function noticia()
    {
        return app(PublicNoticiaController::class)->noticia();
    }

    public function noticia_detalle($slug)
    {
        return app(PublicNoticiaController::class)->noticia_detalle($slug);
    }

    public function toggleOcultoNoticia(Noticia $noticia)
    {
        return app(PublicNoticiaController::class)->toggleOcultoNoticia($noticia);
    }

    public function toggleOcultoMerchandising(Merchandising $merchandising)
    {
        return app(PublicTiendaController::class)->toggleOcultoMerchandising($merchandising);
    }

    public function toggleOcultoGaleria(Galeria $galeria)
    {
        return app(PublicGaleriaController::class)->toggleOcultoGaleria($galeria);
    }
}
