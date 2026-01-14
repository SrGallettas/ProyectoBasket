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

            <div class="tarjeta-producto">
                <img src="./img/camiseta.jpg" alt="Camiseta Oficial" />
                <div class="info-producto">
                    <h3>Camiseta Oficial</h3>
                    <p class="descripcion-producto">Camiseta oficial del equipo en colores rojo y blanco</p>
                    <div class="precio-comprar">
                        <span class="precio">35€</span>
                        <button class="boton-comprar">¡Estoy Interesado!</button>
                    </div>
                </div>
            </div>

            <div class="tarjeta-producto">
                <img src="img/pantalon.jpg" alt="Pantalones de Juego" />
                <div class="info-producto">
                    <h3>Pantalones de Juego</h3>
                    <p class="descripcion-producto">Pantalones oficiales rojos con detalles blancos</p>
                    <div class="precio-comprar">
                        <span class="precio">28€</span>
                        <button class="boton-comprar">¡Estoy Interesado!</button>
                    </div>
                </div>
            </div>

            <div class="tarjeta-producto">
                <img src="img/gorra.jpg" alt="Gorra del Equipo" />
                <div class="info-producto">
                    <h3>Gorra del Equipo</h3>
                    <p class="descripcion-producto">Gorra oficial roja con logo bordado</p>
                    <div class="precio-comprar">
                        <span class="precio">22€</span>
                        <button class="boton-comprar">¡Estoy Interesado!</button>
                    </div>
                </div>
            </div>

            <div class="tarjeta-producto">
                <img src="img/sudadera.jpg" alt="Sudadera con Capucha" />
                <div class="info-producto">
                    <h3>Sudadera con Capucha</h3>
                    <p class="descripcion-producto">Sudadera roja con logo blanco del equipo</p>
                    <div class="precio-comprar">
                        <span class="precio">45€</span>
                        <button class="boton-comprar">¡Estoy Interesado!</button>
                    </div>
                </div>
            </div>

            <div class="tarjeta-producto">
                <img src="img/mochila.jpg" alt="Mochila del Equipo" />
                <div class="info-producto">
                    <h3>Mochila del Equipo</h3>
                    <p class="descripcion-producto">Mochila blanca con detalles rojos y logo</p>
                    <div class="precio-comprar">
                        <span class="precio">38€</span>
                        <button class="boton-comprar">¡Estoy Interesado!</button>
                    </div>
                </div>
            </div>

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