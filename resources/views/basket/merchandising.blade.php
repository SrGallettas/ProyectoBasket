{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/paginas/merchandising.css') }}" />
@endpush

{{-- TÍTULO DE LA PESTAÑA --}}
@section('title', 'Inicio - Bellreguard Club de Basket')

{{-- CONTENIDO DE LA PÁGINA --}}
@section('contenido')

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

    <section class="productos-oficiales">
        @if(session('mensaje'))
            <div class="public-alert-ok">
                {{ session('mensaje') }}
            </div>
        @endif

        <div class="merch-intro">
            <span class="merch-kicker">Coleccion oficial</span>
            <h2>Productos Oficiales</h2>
            <p class="subtitulo merch-subtitulo">Encuentra toda la ropa y accesorios del equipo en una selección cuidada, pensada para acompañar al club dentro y fuera de la pista.</p>
        </div>

        <div class="rejilla-productos">
        @forelse($merchandising as $item)
            <div class="tarjeta-producto">
                {{-- Imagen: cargada desde la carpeta de merchandising --}}
                <img src="{{ asset('merchandising/' . $item->imagen_merchan) }}" alt="{{ $item->nombre }}" />
                
                <div class="info-producto">
                    <h3>{{ $item->nombre }}</h3>
                    <p class="descripcion-producto">{{ $item->descripcion }}</p>
                    
                    @php
                        $tallas = $item->talla ? explode(',', $item->talla) : [];
                    @endphp

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

                    @auth
                        <form action="{{ route('basket.tienda.toggle-oculto', $item->id) }}" method="POST" class="public-card-action merch-hide-action">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="public-hide-button">Ocultar en web</button>
                        </form>
                    @endauth
                </div>
            </div>
        @empty
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
    </section>


    <section class="contacto-pedido">
        <h2>¿Te gusta algún producto?</h2>
        <p>Contacta con nosotros para realizar tu pedido</p>
        <a href="mailto:info@bellreguardbasket.com" class="boton-contacto">
            <i class="fa-solid fa-envelope"></i> Contactar Ahora
        </a>
    </section>

</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const botones = document.querySelectorAll('.js-gmail-interes');

    botones.forEach(function (boton) {
        boton.addEventListener('click', function () {
            const contenedor = boton.closest('.interes-formulario');
            const tallaInput = contenedor ? contenedor.querySelector('.js-talla') : null;
            const talla = tallaInput ? tallaInput.value.trim() : 'Sin talla';

            if (tallaInput && tallaInput.tagName === 'SELECT' && !talla) {
                alert('Selecciona una talla antes de continuar.');
                return;
            }

            const producto = boton.dataset.producto || 'Producto';
            const asunto = `Interés ${producto}`;
            const cuerpo = `Hola Bellreguard CB,\n\nEstoy interesado en el producto: ${producto}.\nMe gustaría pedirlo en la talla: ${talla}.\n\nGracias.`;
            const gmailUrl = `https://mail.google.com/mail/?view=cm&fs=1&to=admin@bellreguard.com&su=${encodeURIComponent(asunto)}&body=${encodeURIComponent(cuerpo)}`;

            window.open(gmailUrl, '_blank');
        });
    });
});
</script>

@endsection
