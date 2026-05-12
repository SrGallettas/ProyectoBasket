{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

{{-- CSS específico de la tienda pública --}}
@push('styles')
<x-page-style file="merchandising.css" />
@endpush

{{-- TÍTULO DE LA PESTAÑA --}}
@section('title', 'Solicitar pedido - Bellreguard CB')

{{-- CONTENIDO DE LA PÁGINA --}}
@section('contenido')

{{-- Hero principal del formulario final --}}
<x-public-news-hero
    badge="SOLICITUD DE PEDIDO"
    title="Finaliza tu solicitud"
    subtitle="Revísalo todo y envíanos tus datos para que podamos gestionar el pedido contigo."
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
            <span class="merch-kicker">Paso final</span>
            <h2>Datos de contacto</h2>
            <p>Este formulario no cobra online. Nos llega tu solicitud y te contactamos para cerrar disponibilidad, entrega y pago.</p>

            <form action="{{ route('basket.tienda.pedido.enviar') }}" method="POST" class="pedido-form">
                @csrf

                <label class="pedido-field">
                    <span>Nombre</span>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required>
                </label>

                <label class="pedido-field">
                    <span>Apellidos</span>
                    <input type="text" name="apellidos" value="{{ old('apellidos') }}" required>
                </label>

                <label class="pedido-field">
                    <span>Teléfono</span>
                    <input type="text" name="telefono" value="{{ old('telefono') }}" required>
                </label>

                <label class="pedido-field">
                    <span>Correo electrónico</span>
                    <input type="email" name="correo" value="{{ old('correo') }}" required>
                </label>

                <label class="pedido-field pedido-field-full">
                    <span>Notas adicionales</span>
                    <textarea name="notas" placeholder="Ejemplo: prefiero recogerlo en entrenamiento, dudas con talla, etc.">{{ old('notas') }}</textarea>
                </label>

                <div class="pedido-field pedido-field-full">
                    <button type="submit" class="pedido-submit">Enviar solicitud de pedido</button>
                </div>
            </form>
        </div>

        {{-- Resumen lateral de la cesta --}}
        <aside class="pedido-summary">
            <span class="merch-kicker">Resumen</span>
            <h3>Tu pedido</h3>
            <p>Comprueba las líneas antes de enviarlo. El importe es orientativo y te lo confirmaremos al responder.</p>

            <div class="pedido-items">
                @foreach($cartItems as $item)
                    <article class="pedido-item">
                        <img src="{{ $item['imagen_url'] }}" alt="{{ $item['nombre'] }}">

                        <div>
                            <strong>{{ $item['nombre'] }}</strong>
                            <span>Talla: {{ $item['talla'] }}</span>
                            <span>Cantidad: {{ $item['cantidad'] }}</span>
                        </div>

                        <strong>{{ number_format($item['precio'] * $item['cantidad'], 2, ',', '.') }} €</strong>
                    </article>
                @endforeach
            </div>

            <div class="pedido-total">
                <span>{{ $cartSummary['units'] }} unidades</span>
                <strong>{{ number_format($cartSummary['subtotal'], 2, ',', '.') }} €</strong>
            </div>
        </aside>
    </div>
</section>

@endsection
