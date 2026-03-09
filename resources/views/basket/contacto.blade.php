{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/paginas/contacto.css') }}" />
@endpush

{{-- TÍTULO DE LA PESTAÑA --}}
@section('title', 'Inicio - Bellreguard Club de Basket')

{{-- CONTENIDO DE LA PÁGINA --}}
@section('contenido')

<section class="seccion-contacto-header">
    <div class="header-contenido">
        <h1>Contacto</h1>
        <p>Ponte en contacto con el Bellreguard CB. Estamos aquí para ayudarte</p>
    </div>
    <div class="circulo-decorativo c1"></div>
    <div class="circulo-decorativo c2"></div>
    <div class="circulo-decorativo c3"></div>
</section>

<section class="contenedor-contacto">
    <div class="rejilla-contacto">
        
        <div class="bloque-sobre-nosotros">
            <div class="titulo-con-icono">
                <i class="fas fa-basketball-ball"></i>
                <h2>Sobre Nosotros</h2>
            </div>
            <p>El Bellreguard Club de Baloncesto es un referente deportivo en nuestra comunidad, dedicado a fomentar los valores del baloncesto y el trabajo en equipo. Fundado con la pasión por este deporte, nuestro club ha crecido hasta convertirse en una familia donde jugadores de todas las edades encuentran su lugar.</p>
            <p>Contamos con equipos en diversas categorías, desde formación hasta competición senior, y un cuerpo técnico altamente cualificado que trabaja día a día para desarrollar el talento de nuestros jugadores.</p>
            <p>Nuestras instalaciones modernas y nuestro compromiso con la excelencia deportiva nos posicionan como uno de los clubes más importantes de la región. Únete a nosotros y forma parte de esta gran familia del baloncesto.</p>
        </div>

        <div class="bloque-info-lateral">
            <div class="tarjeta-contacto-mini">
                <div class="icono-contacto-rojo"><i class="fas fa-phone-alt"></i></div>
                <div class="detalles-contacto">
                    <h4>Teléfono</h4>
                    <p>Llámanos en horario de oficina</p>
                    <span class="dato-principal">+34 962 834 567</span>
                    <small>Lun - Vie: 9:00 - 21:00</small>
                </div>
            </div>

            <div class="tarjeta-contacto-mini">
                <div class="icono-contacto-rojo"><i class="fas fa-envelope"></i></div>
                <div class="detalles-contacto">
                    <h4>Email</h4>
                    <p>Escríbenos y te responderemos pronto</p>
                    <span class="dato-principal">info@bellreguardcb.com</span>
                    <small>Respuesta en 24-48 horas</small>
                </div>
            </div>

            <div class="tarjeta-contacto-mini">
                <div class="icono-contacto-rojo"><i class="fas fa-map-marker-alt"></i></div>
                <div class="detalles-contacto">
                    <h4>Ubicación</h4>
                    <p>Visítanos en nuestras instalaciones</p>
                    <span class="dato-principal">Pabellón Municipal de Bellreguard</span>
                    <small>Calle del Deporte, 12. 46713 Bellreguard, Valencia</small>
                </div>
            </div>

            <div class="tarjeta-contacto-mini">
                <div class="icono-contacto-rojo"><i class="fas fa-share-alt"></i></div>
                <div class="detalles-contacto">
                    <h4>Redes Sociales</h4>
                    <p>Síguenos en nuestras redes</p>
                    <div class="iconos-redes-contacto">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="seccion-mapa">
        <div class="titulo-mapa-centrado">
            <h2>Cómo Llegar</h2>
            <p>Encuéntranos en el Pabellón Municipal de Bellreguard</p>
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
                <span>Pabellón Municipal de Bellreguard</span>
            </div>
        </div>
    </div>
</section>

@endsection
