{{-- 1. EXTENSIÓN DEL LAYOUT Y TÍTULO --}}
@extends('layouts.app')

@section('title', 'Inicio - Bellreguard Club de Basket')

@section('contenido')
<section class="contenedor-login">
    <div class="caja-login">
        
        {{-- ==========================================================
             COLUMNA IZQUIERDA: FORMULARIO DE ACCESO
             ========================================================== --}}
        <div class="login-formulario">
            
            {{-- BLOQUE 1: CABECERA (Logo y Textos de Bienvenida) --}}
            <div class="login-header">
                <img src="{{ asset('img/logo.png') }}" alt="Logo Bellreguard CB" class="logo-login">
                <h1>Bienvenido de nuevo</h1>
                <p>Introduce tus credenciales para acceder al panel</p>
            </div>

            {{-- BLOQUE 2: FORMULARIO DE ENTRADA --}}
            {{-- La acción apunta a la ruta 'login' definida en web.php --}}
            <form action="{{ route('login') }}" method="POST">
                @csrf {{-- Token de seguridad obligatorio en Laravel --}}

                {{-- Campo: Email --}}
                <div class="grupo-input">
                    <label for="email">Email del Administrador</label>
                    <div class="input-icono">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="admin@bellreguard.com" value="{{ old('email') }}" required autofocus>
                    </div>
                    {{-- Validación: Muestra error si el email es incorrecto --}}
                    @error('email')
                        <span style="color: #e63000; font-size: 0.8rem; margin-top: 5px; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Campo: Contraseña --}}
                <div class="grupo-input">
                    <label for="password">Contraseña</label>
                    <div class="input-icono">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>

                {{-- Botón de Acción Principal --}}
                <button type="submit" class="btn-acceder">
                    Iniciar Sesión <i class="fas fa-sign-in-alt"></i>
                </button>
            </form>

            {{-- BLOQUE 3: PIE DEL FORMULARIO (Información adicional) --}}
            <div class="login-footer">
                <p>Si no tienes cuenta, contacta con el administrador del club.</p>
            </div>
        </div>

        {{-- ==========================================================
             COLUMNA DERECHA: SECCIÓN DECORATIVA (Branding)
             ========================================================== --}}
        <div class="login-decoracion">
            <div class="decoracion-contenido">
                <h2>Bellreguard CB</h2>
                <p>Gestión interna y panel de control</p>
            </div>
        </div>

    </div>
</section>
@endsection