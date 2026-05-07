{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

{{-- CSS específico de la tienda pública --}}
@push('styles')
<link rel="stylesheet" href="{{ asset('css/paginas/merchandising.css') }}" />
@endpush

{{-- TÍTULO DE LA PESTAÑA --}}
@section('title', 'Inicio - Bellreguard Club de Basket')

{{-- CONTENIDO DE LA PÁGINA --}}
@section('contenido')

{{-- Hero principal de merchandising --}}
<x-public-news-hero
    badge="TIENDA OFICIAL"
    title="Merchandising Oficial"
    subtitle="Lleva los colores del Bellreguard CB contigo."
>
    <div class="iconos-merchandising">
        <i class="fa-solid fa-shirt"></i>
        <i class="fa-solid fa-hat-cap"></i>
        <i class="fa-solid fa-bag-shopping"></i>
    </div>
</x-public-news-hero>

{{-- Productos publicados en la tienda oficial --}}
<section class="productos-oficiales">
    @if(session('mensaje'))
        <div class="public-alert-ok">
            {{ session('mensaje') }}
        </div>
    @endif

    {{-- Introducción visual de la colección --}}
    <div class="merch-intro">
        <span class="merch-kicker">Coleccion oficial</span>
        <h2>Productos Oficiales</h2>
        <p class="subtitulo merch-subtitulo">Encuentra toda la ropa y accesorios del equipo en una selección cuidada, pensada para acompañar al club dentro y fuera de la pista.</p>
    </div>

    {{-- Rejilla de productos publicados --}}
    <div class="rejilla-productos">
        @forelse($merchandising as $item)
            <div class="tarjeta-producto">
                {{-- Imagen: cargada desde la carpeta de merchandising --}}
                <img src="{{ $item->imagen_merchan_url }}" alt="{{ $item->nombre }}" />
                
                <div class="info-producto">
                    <h3>{{ $item->nombre }}</h3>
                    <p class="descripcion-producto">{{ $item->descripcion }}</p>
                    
                    {{-- Conversión de la cadena de tallas a array para el selector --}}
                    @php
                        $tallas = $item->talla ? explode(',', $item->talla) : [];
                    @endphp

                    {{-- Resumen de tallas y precio --}}
                    <div class="merch-resumen-compra">
                        @if($item->talla)
                            <div class="bloque-tallas merch-tallas-bloque">
                                <strong>Tallas:</strong> 
                                <span class="tallas-texto merch-tallas-texto">
                                    {{ str_replace(',', ', ', $item->talla) }}
                                </span>
                            </div>
                        @endif

                        <span class="precio">{{ number_format($item->precio, 0) }}€</span>
                    </div>

                    {{-- Selector de talla y botón de interés por producto --}}
                    <div class="precio-comprar">
                        @if(count($tallas) > 0)
                            <select class="selector-talla js-talla" required>
                                <option value="">Selecciona talla</option>
                                @foreach($tallas as $talla)
                                    <option value="{{ trim($talla) }}">{{ trim($talla) }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="hidden" class="js-talla" value="Sin talla">
                            <span class="merch-sin-talla">Talla unica</span>
                        @endif

                        <button
                            type="button"
                            class="boton-comprar js-gmail-interes"
                            data-producto="{{ $item->nombre }}"
                        >
                            ¡Estoy Interesado!
                        </button>
                    </div>

                    {{-- Acción de visibilidad solo para admin o editor --}}
                    @if(auth()->user()?->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_EDITOR]))
                        <form action="{{ route('basket.tienda.toggle-oculto', $item->id) }}" method="POST" class="public-card-action merch-hide-action">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="public-hide-button">Ocultar en web</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            {{-- Estado vacío si no hay productos publicados --}}
            <div class="estado-vacio">
                <div class="estado-vacio-icono">
                    <i class="fa-solid fa-bag-shopping"></i>
                </div>
                <h3>Tienda temporalmente sin stock</h3>
                <p>Estamos preparando nuevos productos oficiales del club. Vuelve en unos dias para ver la nueva coleccion.</p>
                <a href="{{ route('basket.contacto') }}" class="estado-vacio-accion">Avisadme cuando haya stock</a>
            </div>
        @endforelse
    </div>

    {{-- Paginación de productos --}}
    <div class="contenedor-paginacion">
        {{ $merchandising->links() }}
    </div>
</section>

{{-- CTA final para pedidos o consultas directas --}}
<section class="contacto-pedido">
    <h2>¿Te gusta algún producto?</h2>
    <p>Contacta con nosotros para realizar tu pedido</p>
    <a href="mailto:info@bellreguardbasket.com" class="boton-contacto">
        <i class="fa-solid fa-envelope"></i> Contactar Ahora
    </a>
</section>

{{-- Script para abrir Gmail con el producto y la talla elegida --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const botones = document.querySelectorAll('.js-gmail-interes');

    botones.forEach(function (boton) {
        boton.addEventListener('click', function () {
            const contenedor = boton.closest('.precio-comprar');
            const tallaInput = contenedor ? contenedor.querySelector('.js-talla') : null;
            const talla = tallaInput ? tallaInput.value.trim() : 'Sin talla';

            if (tallaInput && tallaInput.tagName === 'SELECT' && !talla) {
                tallaInput.reportValidity();
                tallaInput.focus();
                return;
            }

            const producto = boton.dataset.producto || 'Producto';
            const asunto = `Interés ${producto}`;
            const cuerpo = `Hola Bellreguard CB,\n\nEstoy interesado en el producto: ${producto}.\nTalla: ${talla}.\n\nGracias.`;
            const gmailUrl = `https://mail.google.com/mail/?view=cm&fs=1&to=admin@bellreguard.com&su=${encodeURIComponent(asunto)}&body=${encodeURIComponent(cuerpo)}`;

            window.open(gmailUrl, '_blank');
        });
    });
});
</script>

@endsection
