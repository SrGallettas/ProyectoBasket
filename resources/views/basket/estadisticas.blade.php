@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/paginas/estadisticas.css') }}" />
@endpush

@section('title', 'Estadísticas - Bellreguard Club de Basket')

@section('contenido')
<x-public-news-hero
    badge="ESTADISTICAS OFICIALES"
    title="Estadisticas Bellreguard"
    subtitle="Consulta los datos completos entrando en cada partido."
/>

<section class="contenedor-estadisticas">
    <div class="estado-vacio">
        <div class="estado-vacio-icono">
            <i class="fas fa-chart-bar"></i>
        </div>
        <h3>Las estadisticas estan dentro del detalle de cada partido</h3>
        <p>Abre la seccion de partidos y pulsa en "Ver detalles del partido" para ver comparativa completa y puntos por cuartos.</p>
        <a href="{{ route('basket.partidos') }}" class="estado-vacio-accion">Ir a partidos</a>
    </div>
</section>
@endsection
