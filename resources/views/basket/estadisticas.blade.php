{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

{{-- TÍTULO DE LA PESTAÑA --}}
@section('title', 'Inicio - Bellreguard Club de Basket')

{{-- CONTENIDO DE LA PÁGINA --}}
@section('contenido')

<section class="seccion-estadisticas-header">
    <div class="header-contenido">
        <h1>Estadísticas del Equipo</h1>
        <p>Datos y métricas de rendimiento por temporada</p>
        <div class="temporada-actual">
            <i class="fas fa-calendar-alt"></i> Temporada 2023-2024
        </div>
    </div>
</section>

<section class="contenedor-estadisticas">
    <div class="selector-temporadas">
        <button class="btn-temp activo">2023-2024</button>
        <button class="btn-temp">2022-2023</button>
        <button class="btn-temp">2021-2022</button>
        <button class="btn-temp">2020-2021</button>
    </div>

    <div class="rejilla-stats-top">
        <div class="card-stat">
            <div class="icon-box red"><i class="fas fa-target-cell"></i></div>
            <div class="stat-info">
                <h3>847</h3>
                <p>Puntos Totales</p>
            </div>
        </div>
        <div class="card-stat">
            <div class="icon-box orange"><i class="fas fa-redo"></i></div>
            <div class="stat-info">
                <h3>312</h3>
                <p>Rebotes</p>
            </div>
        </div>
        <div class="card-stat">
            <div class="icon-box red-dark"><i class="fas fa-hand-paper"></i></div>
            <div class="stat-info">
                <h3>156</h3>
                <p>Asistencias</p>
            </div>
        </div>
        <div class="card-stat">
            <div class="icon-box red-soft"><i class="fas fa-shield-alt"></i></div>
            <div class="stat-info">
                <h3>89</h3>
                <p>Robos</p>
            </div>
        </div>
    </div>

    <div class="rejilla-detalles">
        <div class="caja-detalle">
            <div class="detalle-header">
                <i class="fas fa-basketball-ball icon-red"></i>
                <h4>Tiros de Campo</h4>
            </div>
            <div class="fila-detalle">
                <span>Tiros de 2 Puntos</span>
                <div class="valor">
                    <strong>234/456</strong>
                    <small>51.3%</small>
                </div>
            </div>
            <div class="fila-detalle">
                <span>Tiros de 3 Puntos</span>
                <div class="valor">
                    <strong>89/234</strong>
                    <small>38.0%</small>
                </div>
            </div>
            <div class="fila-detalle">
                <span>Tiros Libres</span>
                <div class="valor">
                    <strong>156/198</strong>
                    <small>78.8%</small>
                </div>
            </div>
        </div>

        <div class="caja-detalle">
            <div class="detalle-header">
                <i class="fas fa-chart-line icon-red"></i>
                <h4>Rendimiento Defensivo</h4>
            </div>
            <div class="fila-detalle">
                <span>Rebotes Defensivos</span>
                <div class="valor">
                    <strong>234</strong>
                    <small>8.9 por partido</small>
                </div>
            </div>
            <div class="fila-detalle">
                <span>Rebotes Ofensivos</span>
                <div class="valor">
                    <strong>78</strong>
                    <small>2.9 por partido</small>
                </div>
            </div>
            <div class="fila-detalle">
                <span>Tapones</span>
                <div class="valor">
                    <strong>34</strong>
                    <small>1.3 por partido</small>
                </div>
            </div>
        </div>
    </div>

    <div class="rejilla-resumen-final">
        <div class="card-resumen red">
            <div class="resumen-head">
                <i class="fas fa-trophy"></i>
                <span>Partidos</span>
            </div>
            <div class="resumen-body">
                <h2>26</h2>
                <p>Partidos Jugados</p>
            </div>
        </div>
        <div class="card-resumen green">
            <div class="resumen-head">
                <i class="fas fa-check-circle"></i>
                <span>Victorias</span>
            </div>
            <div class="resumen-body">
                <h2>18</h2>
                <p>Partidos Ganados</p>
            </div>
        </div>
        <div class="card-resumen dark">
            <div class="resumen-head">
                <i class="fas fa-times-circle"></i>
                <span>Derrotas</span>
            </div>
            <div class="resumen-body">
                <h2>8</h2>
                <p>Partidos Perdidos</p>
            </div>
        </div>
    </div>
</section>

@endsection