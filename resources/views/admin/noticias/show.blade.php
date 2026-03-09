@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-ficha-noticia"> {{-- Ajustado a tu CSS --}}
    
    <header class="header-ficha">
        <div>
            <h2>Visualizar Noticia</h2>
            <p>Publicación editorial del club</p>
        </div>
        <div class="acciones-header-ficha">
            
            <a href="{{ route('noticias.edit', $noticia->id) }}" class="btn-ficha-accion btn-ficha-edit">
                <i class="fas fa-edit"></i>&nbsp;EDITAR
            </a>

            <form action="{{ route('noticias.destroy', $noticia->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta noticia?')" class="admin-form-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-ficha-accion btn-ficha-delete">
                    <i class="fas fa-trash-alt"></i>&nbsp;ELIMINAR
                </button>
            </form>

            <a href="{{ route('noticias.index') }}" class="btn-ficha-accion btn-ficha-volver">
                <i class="fas fa-arrow-left"></i>&nbsp;VOLVER AL LISTADO
            </a>
        </div>
    </header>

    <div class="pizarra-ficha">
        {{-- Contenedor de Imagen con el placeholder por si falla --}}
        <div class="noticia-hero-container">
            @if($noticia->imagen_noticia)
                <img src="{{ asset('noticias/' . $noticia->imagen_noticia) }}" class="noticia-img-main">
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
