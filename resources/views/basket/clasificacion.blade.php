{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/paginas/clasificacion.css') }}" />
@endpush

{{-- TÍTULO DE LA PESTAÑA --}}
@section('title', 'Inicio - Bellreguard Club de Basket')

{{-- CONTENIDO DE LA PÁGINA --}}
@section('contenido')

<x-public-news-hero
    badge="CLASIFICACIONES OFICIALES"
    title="Tabla de Posiciones"
    subtitle="Consulta la posición de nuestros equipos en todas las categorías."
>
    <div class="icono-trofeo">
        <i class="fas fa-trophy"></i>
    </div>
</x-public-news-hero>

<section class="contenedor-clasificacion">
    <div class="selector-categorias">
        <button class="btn-cat activo">Senior</button>
        <button class="btn-cat">Junior</button>
        <button class="btn-cat">Cadete</button>
        <button class="btn-cat">Infantil</button>
        <button class="btn-cat">Alevín</button>
    </div>

    <div class="tabla-contenedor">
        <div class="tabla-header">
            <i class="fas fa-trophy"></i> Liga Senior - Temporada 2023/24
        </div>
        <div class="tabla-scroll">
            <table class="tabla-clasificacion">
                <thead>
                    <tr>
                        <th>Pos</th>
                        <th>Equipo</th>
                        <th>PJ</th>
                        <th>PG</th>
                        <th>PP</th>
                        <th>PF</th>
                        <th>PC</th>
                        <th>Pts</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>1</strong></td>
                        <td>Valencia BC</td>
                        <td>18</td>
                        <td>15</td>
                        <td>3</td>
                        <td>1456</td>
                        <td>1298</td>
                        <td><strong>33</strong></td>
                    </tr>
                    <tr class="fila-destacada">
                        <td><strong>2</strong></td>
                        <td>Bellreguard CB</td>
                        <td>18</td>
                        <td>13</td>
                        <td>5</td>
                        <td>1389</td>
                        <td>1267</td>
                        <td><strong>31</strong></td>
                    </tr>
                    <tr>
                        <td><strong>3</strong></td>
                        <td>Alicante Club</td>
                        <td>18</td>
                        <td>12</td>
                        <td>6</td>
                        <td>1334</td>
                        <td>1289</td>
                        <td><strong>30</strong></td>
                    </tr>
                    <tr>
                        <td><strong>4</strong></td>
                        <td>Castellón Basket</td>
                        <td>18</td>
                        <td>10</td>
                        <td>8</td>
                        <td>1278</td>
                        <td>1301</td>
                        <td><strong>28</strong></td>
                    </tr>
                    <tr>
                        <td><strong>5</strong></td>
                        <td>Gandia Sports</td>
                        <td>18</td>
                        <td>8</td>
                        <td>10</td>
                        <td>1234</td>
                        <td>1356</td>
                        <td><strong>26</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

@endsection
