{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

{{-- CSS específico del listado de galerías --}}
@push('styles')
<x-page-style file="galeria.css" />
@endpush

@section('title', __('public.gallery.meta.title'))
@section('meta_description', __('public.gallery.meta.description'))

@section('contenido')
{{-- Hero principal de la galería pública --}}
<x-public-news-hero
    :badge="__('public.gallery.hero.badge')"
    :title="__('public.gallery.hero.title')"
    :subtitle="__('public.gallery.hero.subtitle')"
>
    <div class="stats-galeria">
        <span><i class="fas fa-folder-open"></i> {{ __('public.gallery.hero.albums_count', ['count' => $galerias->total()]) }}</span>
        <span><i class="fas fa-images"></i> {{ __('public.gallery.hero.photos_count', ['count' => $galerias->sum('imagenes_count')]) }}</span>
    </div>
</x-public-news-hero>

{{-- Contenedor principal con filtros y álbumes --}}
<section class="contenedor-galeria {{ $galerias->isEmpty() ? "estado-vacio-como-partidos estado-vacio-como-partidos-galeria" : "" }}">
    @if(session('mensaje'))
        <div class="public-alert-ok">
            {{ session('mensaje') }}
        </div>
    @endif

    {{-- Filtros por tipo de galería --}}
    <div class="filtros-galeria">
        @foreach($tipos as $clave => $label)
            <a href="{{ route('basket.galeria', ['tipo' => $clave !== 'todos' ? $clave : null]) }}"
               class="btn-filtro {{ $tipoSeleccionado === $clave ? 'activo' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if($galerias->isNotEmpty())
        {{-- Rejilla de álbumes publicados --}}
        <div class="rejilla-albumes">
            @foreach($galerias as $galeria)
            <div class="tarjeta-album">
                <div class="imagen-album">
                    @if($galeria->portada)
                        <img src="{{ $galeria->portada?->image_url ?? asset('img/club_14.png') }}" alt="{{ $galeria->titulo }}" onerror="this.onerror=null;this.src='{{ asset('img/club_14.png') }}';">
                    @else
                        <div class="album-placeholder">
                            <i class="fas fa-image"></i>
                        </div>
                    @endif
                    <span class="badge-fotos">{{ __('public.gallery.photos_badge', ['count' => $galeria->imagenes_count]) }}</span>
                </div>
                <div class="info-album">
                    <div class="album-meta-top">
                        <span class="tipo-album">{{ $tipos[$galeria->tipo] ?? ucfirst($galeria->tipo) }}</span>
                        <span class="fecha-album">{{ $galeria->created_at->translatedFormat('d/m/Y') }}</span>
                    </div>
                    <h3>{{ $galeria->titulo }}</h3>
                    <p>{{ \Illuminate\Support\Str::limit($galeria->descripcion, 120) }}</p>
                    <div class="footer-album">
                        <div class="public-inline-actions">
                            <a href="{{ route('basket.galeria.detalle', $galeria->slug) }}" class="btn-ver-album">{{ __('public.gallery.view_album') }}</a>
                            {{-- Acción de visibilidad solo para admin o editor --}}
                            @if(auth()->user()?->hasAnyRole([\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_EDITOR]))
                                <form action="{{ route('basket.galeria.toggle-oculto', $galeria->id) }}" method="POST" class="public-card-action">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="public-hide-button">{{ __('public.gallery.hide') }}</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Paginación del listado --}}
        <div class="contenedor-paginacion">
            {{ $galerias->links() }}
        </div>
    @else
        {{-- Estado vacío si no hay álbumes para el filtro actual --}}
        <div class="estado-vacio estado-vacio-merchan-gap estado-vacio-merchan-gap-galeria">
            <div class="estado-vacio-icono">
                <i class="fas fa-images"></i>
            </div>
            <h3>{{ __('public.gallery.empty.title') }}</h3>
            <p>{{ __('public.gallery.empty.text') }}</p>
        </div>
    @endif

</section>
@endsection
