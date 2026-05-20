{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

{{-- CSS específico de la tienda pública --}}
@push('styles')
<x-page-style file="merchandising.css" />
@endpush

{{-- TÍTULO DE LA PESTAÑA --}}
@section('title', __('public.store.meta.title'))
@section('meta_description', __('public.store.meta.description'))

{{-- CONTENIDO DE LA PÁGINA --}}
@section('contenido')

{{-- Hero principal de merchandising --}}
<x-public-news-hero
    :badge="__('public.store.hero.badge')"
    :title="__('public.store.hero.title')"
    :subtitle="__('public.store.hero.subtitle')"
>
    <div class="iconos-merchandising">
        <i class="fa-solid fa-shirt"></i>
        <i class="fa-solid fa-hat-cap"></i>
        <i class="fa-solid fa-bag-shopping"></i>
    </div>
</x-public-news-hero>

{{-- Productos publicados en la tienda oficial --}}
<section class="productos-oficiales" data-cart-root data-cart-open="{{ $openCart ? 'true' : 'false' }}">
    @if(session('mensaje'))
        <div class="public-alert-ok">
            {{ session('mensaje') }}
        </div>
    @endif

    @if($errors->any())
        <div class="public-alert-error" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    @if($merchandising->isNotEmpty())
        {{-- Cabecera con resumen y acceso rápido a la cesta --}}
        <div class="merch-toolbar">
            <div class="merch-intro">
                <span class="merch-kicker">{{ __('public.store.toolbar.kicker') }}</span>
                <h2>{{ __('public.store.toolbar.title') }}</h2>
                <p class="subtitulo merch-subtitulo">{{ __('public.store.toolbar.subtitle') }}</p>
            </div>

            <button type="button" class="boton-cesta-flotante" data-open-cart>
                <span class="boton-cesta-texto">{{ __('public.store.toolbar.cart') }}</span>
                <span class="boton-cesta-count">{{ $cartSummary['units'] }}</span>
            </button>
        </div>

        {{-- Rejilla de productos publicados --}}
        <div class="rejilla-productos">
            @foreach($merchandising as $item)
            {{-- Conversión de la cadena de tallas a array para el selector --}}
            @php
                $tallas = collect(explode(',', (string) $item->talla))
                    ->map(fn ($talla) => trim($talla))
                    ->filter(fn ($talla) => $talla !== '' && strtolower($talla) !== 'sin talla')
                    ->values();
            @endphp

            <div class="tarjeta-producto">
                {{-- Imagen del producto --}}
                <img src="{{ $item->imagen_merchan_url }}" alt="{{ $item->nombre }}" />

                <div class="info-producto">
                    <h3>{{ $item->nombre }}</h3>
                    <p class="descripcion-producto">{{ $item->descripcion }}</p>

                    {{-- Resumen visual de tallas y precio --}}
                    <div class="merch-resumen-compra">
                        @if($tallas->isNotEmpty())
                            <div class="bloque-tallas merch-tallas-bloque">
                                <strong>{{ __('public.store.product.sizes') }}</strong>
                                <span class="tallas-texto merch-tallas-texto">
                                    {{ $tallas->implode(', ') }}
                                </span>
                            </div>
                        @else
                            <div class="bloque-tallas merch-tallas-bloque">
                                <strong>{{ __('public.store.product.format') }}</strong>
                                <span class="tallas-texto merch-tallas-texto">{{ __('public.store.product.no_size') }}</span>
                            </div>
                        @endif

                        <span class="precio">{{ number_format($item->precio, 0) }}€</span>
                    </div>

                    {{-- Formulario de alta en la cesta --}}
                    <form action="{{ route('basket.tienda.cesta.add') }}" method="POST" class="merch-add-form">
                        @csrf
                        <input type="hidden" name="producto_id" value="{{ $item->id }}">

                        @if($tallas->isNotEmpty())
                            {{-- Selector de talla para productos con variantes --}}
                            <label class="merch-field">
                                <span>{{ __('public.store.form.size') }}</span>
                                <select class="selector-talla" name="talla">
                                    <option value="">{{ __('public.store.form.select_size') }}</option>
                                    @foreach($tallas as $talla)
                                        <option value="{{ $talla }}">{{ $talla }}</option>
                                    @endforeach
                                </select>
                            </label>
                        @else
                            {{-- Valor oculto para productos sin tallaje --}}
                            <input type="hidden" name="talla" value="{{ __('public.store.product.no_size') }}">
                            <label class="merch-field merch-field-placeholder">
                                <span>{{ __('public.store.form.size') }}</span>
                                <span class="merch-sin-talla merch-sin-talla-box">{{ __('public.store.product.no_size') }}</span>
                            </label>
                        @endif

                        {{-- Selector de cantidad y CTA principal --}}
                        <div class="precio-comprar">
                            <label class="merch-field merch-field-cantidad">
                                <span>{{ __('public.store.form.quantity') }}</span>
                                <input type="number" name="cantidad" min="1" max="20" value="1" class="selector-cantidad">
                            </label>

                            <button type="submit" class="boton-comprar">
                                {{ __('public.store.form.add_to_cart') }}
                            </button>
                        </div>
                    </form>

                    {{-- Acción de visibilidad solo para admin o editor --}}
                    @if(auth()->user()?->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_EDITOR]))
                        <form action="{{ route('basket.tienda.toggle-oculto', $item->id) }}" method="POST" class="public-card-action merch-hide-action">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="public-hide-button">{{ __('public.store.hide') }}</button>
                        </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Paginación de productos --}}
        <div class="contenedor-paginacion">
            {{ $merchandising->links() }}
        </div>
    @else
        {{-- Estado vacío si no hay productos publicados --}}
        <div class="estado-vacio">
            <div class="estado-vacio-icono">
                <i class="fa-solid fa-bag-shopping"></i>
            </div>
            <h3>{{ __('public.store.empty.title') }}</h3>
            <p>{{ __('public.store.empty.text') }}</p>
            <a href="{{ route('basket.contacto') }}" class="estado-vacio-accion">{{ __('public.store.empty.cta') }}</a>
        </div>
    @endif

    @if($merchandising->isNotEmpty())
        {{-- Capa oscura del panel lateral --}}
        <div class="cesta-overlay" data-cart-close></div>

        {{-- Panel lateral con la cesta activa --}}
        <aside class="panel-cesta" aria-label="{{ __('public.store.cart.aria') }}">
            <div class="panel-cesta-header">
                <div>
                    <p class="panel-cesta-kicker">{{ __('public.store.cart.kicker') }}</p>
                    <h3>{{ __('public.store.cart.title') }}</h3>
                </div>

                <button type="button" class="panel-cesta-close" data-cart-close aria-label="{{ __('public.store.cart.close') }}">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="panel-cesta-body">
                @forelse($cartItems as $index => $cartItem)
                    {{-- Línea editable de la cesta --}}
                    <article class="cesta-item">
                        <img src="{{ $cartItem['imagen_url'] }}" alt="{{ $cartItem['nombre'] }}">

                        <div class="cesta-item-info">
                            <h4>{{ $cartItem['nombre'] }}</h4>
                            <p>{{ __('public.store.cart.size_line', ['size' => $cartItem['talla']]) }}</p>
                            <p>{{ __('public.store.cart.unit_price', ['price' => number_format($cartItem['precio'], 2, ',', '.')]) }}</p>

                            {{-- Formulario de cantidad por línea con envío automático --}}
                            <form action="{{ route('basket.tienda.cesta.update', $cartItem['producto_id'] . '-' . md5($cartItem['talla'])) }}" method="POST" class="cesta-inline-form">
                                @csrf
                                @method('PATCH')
                                <label class="merch-field merch-field-cantidad merch-field-panel">
                                    <span>{{ __('public.store.form.quantity') }}</span>
                                    <input type="number" name="cantidad" min="1" max="20" value="{{ $cartItem['cantidad'] }}" class="selector-cantidad" data-auto-submit-qty>
                                </label>
                            </form>

                            {{-- Formulario para eliminar la línea --}}
                            <form action="{{ route('basket.tienda.cesta.remove', $cartItem['producto_id'] . '-' . md5($cartItem['talla'])) }}" method="POST" class="cesta-inline-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="cesta-remove-button">{{ __('public.store.cart.remove') }}</button>
                            </form>
                        </div>

                        <div class="cesta-item-total">
                            {{ number_format($cartItem['precio'] * $cartItem['cantidad'], 2, ',', '.') }} €
                        </div>
                    </article>
                @empty
                    {{-- Estado vacío cuando todavía no se ha añadido nada --}}
                    <div class="cesta-vacia">
                        <i class="fa-solid fa-basket-shopping"></i>
                        <h4>{{ __('public.store.cart.empty_title') }}</h4>
                        <p>{{ __('public.store.cart.empty_text') }}</p>
                    </div>
                @endforelse
            </div>

            <div class="panel-cesta-footer">
                <div class="cesta-resumen-linea">
                    <span>{{ __('public.store.cart.units') }}</span>
                    <strong>{{ $cartSummary['units'] }}</strong>
                </div>

                <div class="cesta-resumen-linea">
                    <span>{{ __('public.store.cart.subtotal') }}</span>
                    <strong>{{ number_format($cartSummary['subtotal'], 2, ',', '.') }} €</strong>
                </div>

                @if($cartSummary['units'] > 0)
                    <a href="{{ route('basket.tienda.pedido') }}" class="cesta-submit-link">
                        {{ __('public.store.cart.request_order') }}
                    </a>
                @else
                    <button type="button" class="cesta-submit-link cesta-submit-link-disabled" disabled>
                        {{ __('public.store.cart.request_order') }}
                    </button>
                @endif
            </div>
        </aside>
    @endif
</section>

@if($merchandising->isNotEmpty())
    {{-- CTA final para pedidos o consultas directas --}}
    <section class="contacto-pedido">
        <h2>{{ __('public.store.support.title') }}</h2>
        <p>{{ __('public.store.support.text') }}</p>
        <a href="mailto:info@bellreguardcb.com" class="boton-contacto">
            <i class="fa-solid fa-envelope"></i> {{ __('public.store.support.cta') }}
        </a>
    </section>
@endif

{{-- Script del panel lateral de cesta --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.querySelector('[data-cart-root]');

    if (!root) {
        return;
    }

    const openButtons = root.querySelectorAll('[data-open-cart]');
    const closeButtons = root.querySelectorAll('[data-cart-close]');

    function setCartState(isOpen) {
        root.classList.toggle('cart-open', isOpen);
        document.body.classList.toggle('cart-drawer-open', isOpen);
    }

    openButtons.forEach((button) => {
        button.addEventListener('click', function () {
            setCartState(true);
        });
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', function () {
            setCartState(false);
        });
    });

    root.querySelectorAll('[data-auto-submit-qty]').forEach((input) => {
        input.addEventListener('change', function () {
            const form = input.closest('form');

            if (form) {
                form.submit();
            }
        });
    });

    if (root.dataset.cartOpen === 'true') {
        setCartState(true);
    }
});
</script>

@endsection
