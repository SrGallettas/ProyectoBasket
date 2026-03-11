@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-ficha-noticia">
    <x-admin-show-header
        titulo="Visualizar Galería"
        :subtitulo="$tipos[$galeria->tipo] ?? ucfirst($galeria->tipo)"
        :editRoute="route('galerias.edit', $galeria->id)"
        :deleteRoute="route('galerias.destroy', $galeria->id)"
        :indexRoute="route('galerias.index')"
        confirmMessage="¿Seguro que deseas eliminar esta galería?"
    />

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
