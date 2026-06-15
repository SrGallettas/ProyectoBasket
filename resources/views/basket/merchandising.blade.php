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
        <i class="fa-solid fa-bag-shopping"></i>
    </div>
</x-public-news-hero>

{{-- Productos publicados en la tienda oficial --}}
<section class="productos-oficiales" data-cart-root data-cart-open="false">
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

            <button type="button" class="boton-cesta-flotante" data-open-cart data-cart-fab aria-label="{{ __('public.store.toolbar.cart') }}">
                <i class="fa-solid fa-bag-shopping boton-cesta-texto" aria-hidden="true"></i>
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
                        <input type="hidden" name="redirect_to" value="{{ route('basket.tienda') }}">

                        @if($tallas->count() > 1)
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
                        @elseif($tallas->count() === 1)
                            {{-- Talla unica: se fija automaticamente y se muestra como valor informativo --}}
                            <input type="hidden" name="talla" value="{{ $tallas->first() }}">
                            <label class="merch-field merch-field-placeholder">
                                <span>{{ __('public.store.form.size') }}</span>
                                <span class="merch-sin-talla merch-sin-talla-box">{{ $tallas->first() }}</span>
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
                            <form action="{{ route('basket.tienda.cesta.update', $cartItem['producto_id'] . '-' . md5($cartItem['talla'])) }}" method="POST" class="cesta-inline-form" data-cart-qty-form>
                                @csrf
                                @method('PATCH')
                                <label class="merch-field merch-field-cantidad merch-field-panel">
                                    <span>{{ __('public.store.form.quantity') }}</span>
                                    <input type="number" name="cantidad" min="1" max="20" value="{{ $cartItem['cantidad'] }}" class="selector-cantidad" data-auto-submit-qty>
                                </label>
                            </form>

                            {{-- Formulario para eliminar la línea --}}
                            <form action="{{ route('basket.tienda.cesta.remove', $cartItem['producto_id'] . '-' . md5($cartItem['talla'])) }}" method="POST" class="cesta-inline-form" data-cart-remove-form>
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="cesta-remove-button">{{ __('public.store.cart.remove') }}</button>
                            </form>
                        </div>

                        <div class="cesta-item-total" data-cart-line-total data-unit-price="{{ $cartItem['precio'] }}">
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
                    <strong data-cart-units>{{ $cartSummary['units'] }}</strong>
                </div>

                <div class="cesta-resumen-linea">
                    <span>{{ __('public.store.cart.subtotal') }}</span>
                    <strong data-cart-subtotal>{{ number_format($cartSummary['subtotal'], 2, ',', '.') }} €</strong>
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
    const floatingCartButton = document.querySelector('[data-cart-fab]');

    if (!root) {
        return;
    }

    function setCartState(isOpen) {
        root.classList.toggle('cart-open', isOpen);
        document.body.classList.toggle('cart-drawer-open', isOpen);
    }

    function bindCartUi() {
        root.querySelectorAll('[data-open-cart]').forEach((button) => {
            if (button.dataset.cartBound === 'true') {
                return;
            }

            button.dataset.cartBound = 'true';
            button.addEventListener('click', function () {
                setCartState(true);
            });
        });

        root.querySelectorAll('[data-cart-close]').forEach((button) => {
            if (button.dataset.cartBound === 'true') {
                return;
            }

            button.dataset.cartBound = 'true';
            button.addEventListener('click', function () {
                setCartState(false);
            });
        });

        root.querySelectorAll('[data-auto-submit-qty]').forEach((input) => {
            if (input.dataset.cartBound === 'true') {
                return;
            }

            input.dataset.cartBound = 'true';
            input.addEventListener('input', function () {
                syncLocalCartTotals();
            });
            input.addEventListener('change', function () {
                const form = input.closest('form');

                if (form) {
                    submitCartQuantityUpdate(form);
                }
            });
        });

        root.querySelectorAll('[data-cart-qty-form]').forEach((form) => {
            if (form.dataset.ajaxBound === 'true') {
                return;
            }

            form.dataset.ajaxBound = 'true';
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                submitCartQuantityUpdate(form);
            });
        });

        root.querySelectorAll('.merch-add-form').forEach((form) => {
            if (form.dataset.ajaxBound === 'true') {
                return;
            }

            form.dataset.ajaxBound = 'true';
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                submitAddToCart(form);
            });
        });

        root.querySelectorAll('[data-cart-remove-form]').forEach((form) => {
            if (form.dataset.ajaxBound === 'true') {
                return;
            }

            form.dataset.ajaxBound = 'true';
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                submitRemoveFromCart(form);
            });
        });
    }

    function bindCartScrollBehavior() {
        if (!floatingCartButton || window.innerWidth <= 768) {
            return;
        }
        const hideSections = Array.from(document.querySelectorAll('.contacto-pedido, .pie-pagina'));
        let ticking = false;

        function updateCartButtonPosition() {
            const isNearTop = window.scrollY < 220;
            let shouldHide = false;

            floatingCartButton.classList.toggle('is-pinned', !isNearTop);

            const buttonTop = window.innerHeight - floatingCartButton.offsetHeight - 32;
            const buttonBottom = buttonTop + floatingCartButton.offsetHeight;

            shouldHide = hideSections.some((section) => {
                const sectionRect = section.getBoundingClientRect();

                return sectionRect.top < buttonBottom && sectionRect.bottom > buttonTop;
            });

            floatingCartButton.classList.toggle('is-hidden', shouldHide);
            ticking = false;
        }

        window.addEventListener('scroll', function () {
            if (ticking) {
                return;
            }

            ticking = true;
            window.requestAnimationFrame(updateCartButtonPosition);
        }, { passive: true });

        updateCartButtonPosition();
    }

    function formatCurrency(value) {
        return new Intl.NumberFormat('es-ES', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(value) + ' €';
    }

    function syncLocalCartTotals() {
        let units = 0;
        let subtotal = 0;

        root.querySelectorAll('[data-cart-qty-form]').forEach((form) => {
            const input = form.querySelector('[data-auto-submit-qty]');
            const lineTotal = form.closest('.cesta-item')?.querySelector('[data-cart-line-total]');

            if (!input || !lineTotal) {
                return;
            }

            const quantity = Math.max(1, Math.min(20, parseInt(input.value || '1', 10) || 1));
            const unitPrice = parseFloat(lineTotal.dataset.unitPrice || '0');
            const total = quantity * unitPrice;

            units += quantity;
            subtotal += total;
            lineTotal.textContent = formatCurrency(total);
        });

        const unitsNode = root.querySelector('[data-cart-units]');
        const subtotalNode = root.querySelector('[data-cart-subtotal]');
        const countNode = root.querySelector('.boton-cesta-count');

        if (unitsNode) {
            unitsNode.textContent = units.toString();
        }

        if (subtotalNode) {
            subtotalNode.textContent = formatCurrency(subtotal);
        }

        if (countNode) {
            countNode.textContent = units.toString();
        }
    }

    function showAlert(type, message) {
        root.querySelectorAll('.public-alert-ok, .public-alert-error').forEach((alert) => alert.remove());

        if (!message) {
            return;
        }

        const alert = document.createElement('div');
        alert.className = type === 'error' ? 'public-alert-error' : 'public-alert-ok';
        alert.setAttribute('role', type === 'error' ? 'alert' : 'status');
        alert.textContent = message;
        root.prepend(alert);
    }

    function syncCartMarkup(doc) {
        const nextCount = doc.querySelector('.boton-cesta-count');
        const currentCount = root.querySelector('.boton-cesta-count');

        if (nextCount && currentCount) {
            currentCount.textContent = nextCount.textContent;
        }

        const nextOverlay = doc.querySelector('.cesta-overlay');
        const currentOverlay = root.querySelector('.cesta-overlay');

        if (nextOverlay && currentOverlay) {
            currentOverlay.replaceWith(nextOverlay);
        }

        const nextPanel = doc.querySelector('.panel-cesta');
        const currentPanel = root.querySelector('.panel-cesta');

        if (nextPanel && currentPanel) {
            currentPanel.replaceWith(nextPanel);
        }

        bindCartUi();
    }

    async function refreshCartMarkup() {
        const response = await fetch(window.location.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('refresh_failed');
        }

        const html = await response.text();
        const doc = new DOMParser().parseFromString(html, 'text/html');

        syncCartMarkup(doc);
    }

    async function submitAddToCart(form) {
        const submitButton = form.querySelector('button[type="submit"]');

        if (submitButton) {
            submitButton.disabled = true;
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: new FormData(form),
                credentials: 'same-origin',
            });

            if (response.ok) {
                const payload = await response.json();
                await refreshCartMarkup();
                showAlert('ok', payload.message || '');
                form.reset();
                return;
            }

            if (response.status === 422) {
                const payload = await response.json();
                const firstError = Object.values(payload.errors || {}).flat()[0] || 'No se ha podido añadir el producto.';
                showAlert('error', firstError);
                return;
            }

            showAlert('error', 'No se ha podido añadir el producto en este momento.');
        } catch (error) {
            showAlert('error', 'No se ha podido añadir el producto en este momento.');
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
            }
        }
    }

    async function submitCartQuantityUpdate(form) {
        const quantityInput = form.querySelector('[data-auto-submit-qty]');

        if (quantityInput) {
            quantityInput.disabled = true;
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: new FormData(form),
                credentials: 'same-origin',
            });

            if (response.ok) {
                await refreshCartMarkup();
                setCartState(true);
                return;
            }

            showAlert('error', 'No se ha podido actualizar la cantidad en este momento.');
        } catch (error) {
            showAlert('error', 'No se ha podido actualizar la cantidad en este momento.');
        } finally {
            if (quantityInput) {
                quantityInput.disabled = false;
            }
        }
    }

    async function submitRemoveFromCart(form) {
        const submitButton = form.querySelector('button[type="submit"]');

        if (submitButton) {
            submitButton.disabled = true;
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: new FormData(form),
                credentials: 'same-origin',
            });

            if (response.ok) {
                await refreshCartMarkup();
                setCartState(true);
                return;
            }

            showAlert('error', 'No se ha podido quitar el producto en este momento.');
        } catch (error) {
            showAlert('error', 'No se ha podido quitar el producto en este momento.');
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
            }
        }
    }

    bindCartUi();
    bindCartScrollBehavior();
});
</script>

@endsection
