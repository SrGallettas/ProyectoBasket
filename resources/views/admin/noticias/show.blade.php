@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-edit-noticia"> {{-- Cambiado para seguir tu estructura --}}
    
    <header class="header-ficha">
        <div>
            <h2>Visualizar Noticia</h2>
            <p>Publicación editorial del club</p>
        </div>
        <div class="acciones-header-ficha">
            
            {{-- Botón EDITAR --}}
            <a href="{{ route('noticias.edit', $noticia->id) }}" class="btn-ficha-accion btn-ficha-edit">
                <i class="fas fa-edit"></i>&nbsp;EDITAR
            </a>

            {{-- Botón ELIMINAR --}}
            <form action="{{ route('noticias.destroy', $noticia->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta noticia?')" style="margin: 0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-ficha-accion btn-ficha-delete">
                    <i class="fas fa-trash-alt"></i>&nbsp;ELIMINAR
                </button>
            </form>

            {{-- Botón VOLVER --}}
            <a href="{{ route('noticias.index') }}" class="btn-ficha-accion btn-ficha-volver">
                <i class="fas fa-arrow-left"></i>&nbsp;VOLVER AL LISTADO
            </a>
        </div>
    </header>

    <div class="pizarra-ficha">
        {{-- Aquí va el resto del contenido de la noticia que ya tienes --}}
        <div class="noticia-hero-img" style="height: 350px; overflow: hidden; border-radius: 12px; margin-bottom: 25px;">
            <img src="{{ asset('noticias/' . $noticia->imagen_noticia) }}" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        
        <h1 class="noticia-title" style="font-size: 2.2rem; font-weight: 800;">{{ $noticia->titulo }}</h1>
        <p class="noticia-subtitle" style="font-size: 1.2rem; color: #666; border-left: 4px solid #fb8500; padding-left: 15px; margin: 20px 0;">{{ $noticia->subtitulo }}</p>
        
        <div class="noticia-body" style="font-size: 1.1rem; line-height: 1.8; color: #334155;">
            {!! nl2br(e($noticia->contenido)) !!}
        </div>
    </div>
</div>
@endsection