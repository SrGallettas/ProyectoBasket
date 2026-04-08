@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-ficha-noticia"> {{-- Ajustado a tu CSS --}}
    <x-admin-show-header
        titulo="Visualizar Noticia"
        subtitulo="Publicación editorial del club"
        :editRoute="route('noticias.edit', $noticia->id)"
        :deleteRoute="route('noticias.destroy', $noticia->id)"
        :indexRoute="route('noticias.index')"
        confirmMessage="¿Seguro que deseas eliminar esta noticia?"
    />

    <div class="pizarra-ficha">
        {{-- Contenedor de Imagen con el placeholder por si falla --}}
        <div class="noticia-hero-container">
            @if($noticia->imagen_noticia)
                <img src="{{ $noticia->imagen_noticia_url }}" class="noticia-img-main">
            @else
                <div class="noticia-placeholder">
                    <i class="fas fa-image"></i>
                    <span>Sin imagen de portada</span>
                </div>
            @endif
        </div>
        
        <h1 class="noticia-view-title">{{ $noticia->titulo }}</h1>

        {{-- NUEVO: Bloque de Autor y Fecha --}}
        <div class="noticia-view-footer noticia-view-footer-compact">
            <span class="meta-item"><i class="fas fa-user-pen"></i> <strong>Autor:</strong> {{ $noticia->autor }}</span>
            <span class="meta-item"><i class="fas fa-calendar-check"></i> <strong>Publicado:</strong> {{ $noticia->created_at->format('d/m/Y') }}</span>
        </div>
        
        <p class="noticia-view-subtitle">{{ $noticia->subtitulo }}</p>
        
        <div class="noticia-view-body">
            {{-- Sirve para que los saltos de línea (los "Enter") se respeten y el texto no se vea como un bloque pegado.--}}
            {{-- e(...) (Seguridad) --}}
            {!! nl2br(e($noticia->contenido)) !!}
        </div>
    </div>
</div>
@endsection
