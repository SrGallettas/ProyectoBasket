{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

{{-- CSS específico de la tienda pública --}}
@push('styles')
<x-page-style file="merchandising.css" />
@endpush

{{-- TÍTULO DE LA PESTAÑA --}}
@section('title', __('public.order.meta.title'))
@section('meta_description', __('public.order.meta.description'))

{{-- CONTENIDO DE LA PÁGINA --}}
@section('contenido')

{{-- Hero principal del formulario final --}}
<x-public-news-hero
    :badge="__('public.order.hero.badge')"
    :title="__('public.order.hero.title')"
    :subtitle="__('public.order.hero.subtitle')"
/>

{{-- Contenido principal del formulario y resumen --}}
<section class="productos-oficiales">
    @if($errors->any())
        <div class="public-alert-error" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="pedido-layout">
        {{-- Formulario de datos del cliente --}}
        <div class="pedido-card">
            <span class="merch-kicker">{{ __('public.order.form.kicker') }}</span>
            <h2>{{ __('public.order.form.title') }}</h2>
            <p>{{ __('public.order.form.text') }}</p>

            <form action="{{ route('basket.tienda.pedido.enviar') }}" method="POST" class="pedido-form">
                @csrf

                <label class="pedido-field">
                    <span>{{ __('public.order.fields.name') }}</span>
                    <input type="text" name="nombre" value="{{ old('nombre') }}">
                </label>

                <label class="pedido-field">
                    <span>{{ __('public.order.fields.surnames') }}</span>
                    <input type="text" name="apellidos" value="{{ old('apellidos') }}">
                </label>

                <label class="pedido-field">
                    <span>{{ __('public.order.fields.phone') }}</span>
                    <input type="text" name="telefono" value="{{ old('telefono') }}">
                </label>

                <label class="pedido-field">
                    <span>{{ __('public.order.fields.email') }}</span>
                    <input type="email" name="correo" value="{{ old('correo') }}">
                </label>

                <label class="pedido-field pedido-field-full">
                    <span>{{ __('public.order.fields.notes') }}</span>
                    <textarea name="notas" placeholder="{{ __('public.order.fields.notes_placeholder') }}">{{ old('notas') }}</textarea>
                </label>

                <div class="pedido-field pedido-field-full">
                    <button type="submit" class="pedido-submit">{{ __('public.order.form.submit') }}</button>
                </div>
            </form>
        </div>

        {{-- Resumen lateral de la cesta --}}
        <aside class="pedido-summary">
            <span class="merch-kicker">{{ __('public.order.summary.kicker') }}</span>
            <h3>{{ __('public.order.summary.title') }}</h3>
            <p>{{ __('public.order.summary.text') }}</p>

            <div class="pedido-items">
                @foreach($cartItems as $item)
                    <article class="pedido-item">
                        <img src="{{ $item['imagen_url'] }}" alt="{{ $item['nombre'] }}">

                        <div>
                            <strong>{{ $item['nombre'] }}</strong>
                            <span>{{ __('public.order.summary.size', ['size' => $item['talla']]) }}</span>
                            <span>{{ __('public.order.summary.quantity', ['quantity' => $item['cantidad']]) }}</span>
                        </div>

                        <strong>{{ number_format($item['precio'] * $item['cantidad'], 2, ',', '.') }} €</strong>
                    </article>
                @endforeach
            </div>

            <div class="pedido-total">
                <span>{{ __('public.order.summary.units', ['count' => $cartSummary['units']]) }}</span>
                <strong>{{ number_format($cartSummary['subtotal'], 2, ',', '.') }} €</strong>
            </div>
        </aside>
    </div>
</section>

@endsection
