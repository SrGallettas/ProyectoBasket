{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

{{-- CSS específico de la página de contacto --}}
@push('styles')
<x-page-style file="contacto.css" />
@endpush

{{-- TÍTULO DE LA PESTAÑA --}}
@section('title', __('public.contact.meta.title'))
@section('meta_description', __('public.contact.meta.description'))

{{-- CONTENIDO DE LA PÁGINA --}}
@section('contenido')

{{-- Hero principal de la página --}}
<x-public-news-hero
    :badge="__('public.contact.hero.badge')"
    :title="__('public.contact.hero.title')"
    :subtitle="__('public.contact.hero.subtitle')"
/>

{{-- Bloque principal con información del club y datos de contacto --}}
<section class="contenedor-contacto">
    <div class="rejilla-contacto">
        {{-- Presentación general del club --}}
        <div class="bloque-sobre-nosotros">
            <div class="titulo-con-icono">
                <i class="fas fa-basketball-ball"></i>
                <h2>{{ __('public.contact.about.title') }}</h2>
            </div>
            <p>{{ __('public.contact.about.p1') }}</p>
            <p>{{ __('public.contact.about.p2') }}</p>
            <p>{{ __('public.contact.about.p3') }}</p>
        </div>

        {{-- Tarjetas laterales con vías de contacto rápidas --}}
        <div class="bloque-info-lateral">
            <div class="tarjeta-contacto-mini">
                <div class="icono-contacto-rojo"><i class="fas fa-phone-alt"></i></div>
                <div class="detalles-contacto">
                    <h4>{{ __('public.contact.cards.phone.title') }}</h4>
                    <p>{{ __('public.contact.cards.phone.text') }}</p>
                    <span class="dato-principal">+34 962 834 567</span>
                    <small>{{ __('public.contact.cards.phone.hours') }}</small>
                </div>
            </div>

            <div class="tarjeta-contacto-mini">
                <div class="icono-contacto-rojo"><i class="fas fa-envelope"></i></div>
                <div class="detalles-contacto">
                    <h4>{{ __('public.contact.cards.email.title') }}</h4>
                    <p>{{ __('public.contact.cards.email.text') }}</p>
                    <span class="dato-principal">info@bellreguardcb.com</span>
                    <small>{{ __('public.contact.cards.email.response') }}</small>
                </div>
            </div>

            <div class="tarjeta-contacto-mini">
                <div class="icono-contacto-rojo"><i class="fas fa-map-marker-alt"></i></div>
                <div class="detalles-contacto">
                    <h4>{{ __('public.contact.cards.location.title') }}</h4>
                    <p>{{ __('public.contact.cards.location.text') }}</p>
                    <span class="dato-principal">{{ __('public.contact.cards.location.venue') }}</span>
                    <small>{{ __('public.contact.cards.location.address') }}</small>
                </div>
            </div>

            <div class="tarjeta-contacto-mini">
                <div class="icono-contacto-rojo"><i class="fas fa-share-alt"></i></div>
                <div class="detalles-contacto">
                    <h4>{{ __('public.contact.cards.social.title') }}</h4>
                    <p>{{ __('public.contact.cards.social.text') }}</p>
                    <div class="iconos-redes-contacto">
                        <a href="https://www.instagram.com/basquetbellreguard/" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Mapa e indicación de la ubicación física del pabellón --}}
    <div class="seccion-mapa">
        <div class="titulo-mapa-centrado">
            <h2>{{ __('public.contact.map.title') }}</h2>
            <p>{{ __('public.contact.map.subtitle') }}</p>
        </div>
        <div class="mapa-placeholder">
            {{-- Mapa Real de Google Maps --}}
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3092.115479007358!2d-0.1678986234394086!3d38.94492107171408!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd61e88a6595614d%3A0x727823800999a75a!2sPavell%C3%B3%20Municipal%20de%20Bellreguard!5e0!3m2!1ses!2ses!4v1705000000000!5m2!1ses!2ses" 
                width="100%" 
                height="100%" 
                class="mapa-iframe-no-border"
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>

            {{-- Etiqueta flotante para dar estilo --}}
            <div class="info-flotante-mapa">
                <i class="fas fa-basketball-ball"></i>
                <span>{{ __('public.contact.cards.location.venue') }}</span>
            </div>
        </div>
    </div>
</section>

@endsection
