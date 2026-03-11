@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-ficha-noticia">
    <header class="header-ficha">
        <div>
            <h2>Visualizar Galería</h2>
            <p>{{ $tipos[$galeria->tipo] ?? ucfirst($galeria->tipo) }}</p>
        </div>
        <div class="acciones-header-ficha">
            <a href="{{ route('galerias.edit', $galeria->id) }}" class="btn-ficha-accion btn-ficha-edit">
                <i class="fas fa-edit"></i>&nbsp;EDITAR
            </a>

            <form action="{{ route('galerias.destroy', $galeria->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta galería?')" class="admin-form-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-ficha-accion btn-ficha-delete">
                    <i class="fas fa-trash-alt"></i>&nbsp;ELIMINAR
                </button>
            </form>

            <a href="{{ route('galerias.index') }}" class="btn-ficha-accion btn-ficha-volver">
                <i class="fas fa-arrow-left"></i>&nbsp;VOLVER AL LISTADO
            </a>
        </div>
    </header>

    <div class="pizarra-ficha">
        <h1 class="noticia-view-title">{{ $galeria->titulo }}</h1>

        <div class="noticia-view-footer noticia-view-footer-compact">
            <span class="meta-item"><i class="fas fa-tag"></i> <strong>Tipo:</strong> {{ $tipos[$galeria->tipo] ?? ucfirst($galeria->tipo) }}</span>
            <span class="meta-item"><i class="fas fa-link"></i> <strong>Slug:</strong> {{ $galeria->slug }}</span>
            <span class="meta-item"><i class="fas fa-images"></i> <strong>Imágenes:</strong> {{ $galeria->imagenes->count() }}</span>
        </div>

        <p class="noticia-view-subtitle">{{ $galeria->descripcion }}</p>

        <div class="galeria-admin-grid galeria-admin-grid-show">
            @forelse($galeria->imagenes as $imagen)
                <div class="galeria-admin-card galeria-admin-card-static">
                    <img src="{{ asset('galerias/' . $imagen->archivo) }}" alt="Imagen {{ $galeria->titulo }}">
                </div>
            @empty
                <div class="noticia-placeholder">
                    <i class="fas fa-image"></i>
                    <span>Sin imágenes en esta galería</span>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
