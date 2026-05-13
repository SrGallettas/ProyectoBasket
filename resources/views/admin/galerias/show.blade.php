@extends('layouts.admin')

@section('title', __('admin.galleries.meta.show_title'))

@section('contenido_admin')
<div class="contenedor-ficha-noticia">
    <x-admin-show-header
        :titulo="__('admin.galleries.show.title')"
        :subtitulo="$tipos[$galeria->tipo] ?? ucfirst($galeria->tipo)"
        :editRoute="route('galerias.edit', $galeria->id)"
        :deleteRoute="route('galerias.destroy', $galeria->id)"
        :indexRoute="route('galerias.index')"
        :confirmMessage="__('admin.galleries.actions.confirm_delete_detail')"
    />

    <div class="pizarra-ficha">
        <h1 class="noticia-view-title">{{ $galeria->titulo }}</h1>

        <div class="noticia-view-footer noticia-view-footer-compact">
            <span class="meta-item"><i class="fas fa-tag"></i> <strong>{{ __('admin.galleries.fields.type') }}:</strong> {{ $tipos[$galeria->tipo] ?? ucfirst($galeria->tipo) }}</span>
            <span class="meta-item"><i class="fas fa-link"></i> <strong>Slug:</strong> {{ $galeria->slug }}</span>
            <span class="meta-item"><i class="fas fa-images"></i> <strong>{{ __('admin.galleries.fields.images') }}:</strong> {{ $galeria->imagenes->count() }}</span>
        </div>

        <p class="noticia-view-subtitle">{{ $galeria->descripcion }}</p>

        <div class="galeria-admin-grid galeria-admin-grid-show">
            @forelse($galeria->imagenes as $imagen)
                <div class="galeria-admin-card galeria-admin-card-static">
                    <img src="{{ $imagen->image_url }}" alt="{{ __('admin.galleries.fields.image_alt', ['title' => $galeria->titulo]) }}">
                </div>
            @empty
                <div class="noticia-placeholder">
                    <i class="fas fa-image"></i>
                    <span>{{ __('admin.galleries.empty.show') }}</span>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
