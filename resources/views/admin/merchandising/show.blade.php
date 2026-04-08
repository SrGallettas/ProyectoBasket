@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-ficha-merchan">
    <x-admin-show-header
        titulo="Ficha del Producto"
        subtitulo="Detalles completos y stock de la tienda"
        :editRoute="route('merchandising.edit', $item->id)"
        :deleteRoute="route('merchandising.destroy', $item->id)"
        :indexRoute="route('merchandising.index')"
        confirmMessage="¿Seguro que deseas eliminar este producto?"
        backClass="btn-ficha-volver-secondary"
    />

    <div class="pizarra-show">
        <div class="grid-show-merchan">
            
            {{-- Columna Izquierda: Imagen --}}
            <div class="columna-foto">
                <div class="marco-foto-show">
                    @if($item->imagen_merchan)
                        <img src="{{ $item->imagen_merchan_url }}" alt="{{ $item->nombre }}">
                    @else
                        <img src="{{ asset('img/basket.jpeg') }}" alt="Sin imagen">
                    @endif
                </div>
            </div>

            {{-- Columna Derecha: Información --}}
            <div class="columna-info">
                <div class="bloque-info">
                    <span class="label-ficha">Nombre del artículo</span>
                    <h3>{{ $item->nombre }}</h3>
                </div>

                <div class="bloque-info-doble">
                    <div class="dato">
                        <span class="label-ficha">Precio oficial</span>
                        <p class="precio-destacado">{{ number_format($item->precio, 2, ',', '.') }}€</p>
                    </div>
                    <div class="dato">
                        <span class="label-ficha">ID Producto</span>
                        <p>#{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</p>
                    </div>
                </div>

                <div class="bloque-info">
                    <span class="label-ficha">Tallas disponibles</span>
                    <div class="contenedor-badges-tallas">
                        @forelse($tallas as $talla)
                            <span class="badge-talla-show">{{ $talla }}</span>
                        @empty
                            <span class="text-muted">Sin tallas definidas</span>
                        @endforelse
                    </div>
                </div>

                <div class="bloque-info">
                    <span class="label-ficha">Descripción del producto</span>
                    <p class="descripcion-texto">
                        {{ $item->descripcion ?? 'No hay descripción disponible para este artículo.' }}
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
