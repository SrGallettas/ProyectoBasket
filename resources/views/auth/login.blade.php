{{-- 1. EXTENSIÓN DEL LAYOUT Y TÍTULO --}}
@extends('layouts.app')

@push('styles')
<x-page-style file="login.css" />
@endpush

@section('title', __('admin.login.meta.title'))
@section('meta_description', __('admin.login.meta.description'))

@section('contenido')
<section class="contenedor-login">
    <div class="caja-login">
        
        {{-- ==========================================================
             COLUMNA IZQUIERDA: FORMULARIO DE ACCESO
             ========================================================== --}}
        <div class="login-formulario">
            
            {{-- BLOQUE 1: CABECERA (Logo y Textos de Bienvenida) --}}
            <div class="login-header">
                <img src="{{ asset('img/club_14.png') }}" alt="Logo Bellreguard CB" class="logo-login">
                <h1>{{ __('admin.login.header.title') }}</h1>
                <p>{{ __('admin.login.header.subtitle') }}</p>
            </div>

            {{-- BLOQUE 2: FORMULARIO DE ENTRADA --}}
            {{-- La acción apunta a la ruta 'login' definida en web.php --}}
            <form action="{{ route('login') }}" method="POST">
                @csrf {{-- Token de seguridad obligatorio en Laravel --}}

                {{-- Campo: Email --}}
                <div class="grupo-input">
                    <label for="email">{{ __('admin.login.fields.email') }}</label>
                    <div class="input-icono">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="{{ __('admin.login.fields.email_placeholder') }}" value="{{ old('email') }}" autofocus>
                    </div>
                    {{-- Validación: Muestra error si el email es incorrecto --}}
                    @error('email')
                        <span class="login-error-text">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Campo: Contraseña --}}
                <div class="grupo-input">
                    <label for="password">{{ __('admin.login.fields.password') }}</label>
                    <div class="input-icono">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="••••••••">
                    </div>
                </div>

                <div class="login-opciones">
                    <label class="recordarme-label" for="remember">
                        <input
                            type="checkbox"
                            id="remember"
                            name="remember"
                            value="1"
                            {{ old('remember') ? 'checked' : '' }}
                        >
                        {{ __('admin.login.fields.remember') }}
                    </label>
                </div>

                {{-- Botón de Acción Principal --}}
                <button type="submit" class="btn-acceder">
                    {{ __('admin.login.submit') }} <i class="fas fa-sign-in-alt"></i>
                </button>
            </form>

            {{-- BLOQUE 3: PIE DEL FORMULARIO (Información adicional) --}}
            <div class="login-footer">
                <p>{{ __('admin.login.footer') }}</p>
            </div>
        </div>

        {{-- ==========================================================
             COLUMNA DERECHA: SECCIÓN DECORATIVA (Branding)
             ========================================================== --}}
        <div class="login-decoracion">
            <div class="decoracion-contenido">
                <h2>Bellreguard CB</h2>
                <p>{{ __('admin.login.branding') }}</p>
            </div>
        </div>

    </div>
</section>
@endsection
