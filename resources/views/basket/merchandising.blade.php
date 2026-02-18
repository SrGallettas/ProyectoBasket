{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

{{-- TÍTULO DE LA PESTAÑA --}}
@section('title', 'Inicio - Bellreguard Club de Basket')

{{-- CONTENIDO DE LA PÁGINA --}}
@section('contenido')

<section class="seccion-merchandising-header">
        <h1>Merchandising Oficial</h1>
        <p>Lleva los colores del Bellreguard CB contigo</p>
        <div class="iconos-merchandising">
            <i class="fa-solid fa-shirt"></i>
            <i class="fa-solid fa-hat-cap"></i>
            <i class="fa-solid fa-bag-shopping"></i>
        </div>
    </section>

    <section class="productos-oficiales">
        <h2>Productos Oficiales</h2>
        <p class="subtitulo">Encuentra toda la ropa y accesorios del equipo</p>

        <div class="rejilla-productos">
        @forelse($merchandising as $item)
            <div class="tarjeta-producto">
                {{-- Imagen: cargada desde la carpeta de merchandising --}}
                <img src="{{ asset('merchandising/' . $item->imagen_merchan) }}" alt="{{ $item->nombre }}" />
                
                <div class="info-producto">
                    <h3>{{ $item->nombre }}</h3>
                    <p class="descripcion-producto">{{ $item->descripcion }}</p>
                    
                    {{-- Tallas: se muestran solo si el item las tiene --}}
                    @if($item->talla)
                        <div class="bloque-tallas" style="margin-bottom: 15px; font-size: 0.9rem;">
                            <strong>Tallas:</strong> 
                            <span class="tallas-texto" style="background: #eee; padding: 2px 6px; border-radius: 4px;">
                                {{ str_replace(',', ', ', $item->talla) }}
                            </span>
                        </div>
                    @endif

                    <div class="precio-comprar">
                        {{-- Precio: formateado con el símbolo € --}}
                        <span class="precio">{{ number_format($item->precio, 0) }}€</span>
                        <button class="boton-comprar">¡Estoy Interesado!</button>
                    </div>
                </div>
            </div>
        @empty
            <p>No hay productos disponibles en este momento.</p>
        @endforelse
        </div>
    </section>


    <section class="contacto-pedido">
        <h2>¿Te gusta algún producto?</h2>
        <p>Contacta con nosotros para realizar tu pedido</p>
        <a href="mailto:info@bellreguardbasket.com" class="boton-contacto">
            <i class="fa-solid fa-envelope"></i> Contactar Ahora
        </a>
    </section>

</section>

@endsection