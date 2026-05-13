{{-- LAYOUT PRINCIPAL --}}
@extends('layouts.app')

{{-- CSS específico del detalle de galería --}}
@push('styles')
<x-page-style file="galeria.css" />
@endpush

@section('title', $galeria->titulo . ' - Bellreguard CB')
@section('meta_description', $galeria->descripcion ?: __('public.gallery_detail.meta.description'))

@section('contenido')
{{-- Hero con datos básicos del álbum --}}
<x-public-news-hero
    :badge="__('public.gallery_detail.hero.badge')"
    title="{{ $galeria->titulo }}"
    subtitle="{{ $galeria->descripcion }}"
>
    <div class="galeria-detalle-nav">
        <a href="{{ route('basket.galeria') }}" class="galeria-breadcrumb">
            <i class="fas fa-arrow-left"></i> {{ __('public.gallery_detail.hero.back') }}
        </a>
    </div>
    <div class="stats-galeria">
        <span><i class="fas fa-images"></i> {{ __('public.gallery.photos_badge', ['count' => $galeria->imagenes->count()]) }}</span>
        <span><i class="fas fa-calendar-alt"></i> {{ $galeria->created_at->translatedFormat('d/m/Y') }}</span>
    </div>
</x-public-news-hero>

{{-- Zona principal del álbum con la rejilla de imágenes --}}
<section class="contenedor-galeria contenedor-galeria-detalle">
    <div class="galeria-editorial-shell">
        {{-- Texto introductorio del álbum --}}
        <div class="galeria-editorial-intro">
            <span class="galeria-kicker">{{ __('public.gallery_detail.intro.kicker') }}</span>
            <h2>{{ __('public.gallery_detail.intro.title') }}</h2>
            <p>{{ __('public.gallery_detail.intro.text') }}</p>
        </div>

        {{-- Rejilla de fotos que alimenta el lightbox --}}
        <div class="rejilla-fotos">
            @foreach($galeria->imagenes as $index => $imagen)
                <button
                    type="button"
                    class="foto-galeria-card"
                    data-galeria-item
                    data-index="{{ $index }}"
                    data-src="{{ $imagen->image_url }}"
                    data-alt="{{ __('public.gallery_detail.photo_alt', ['title' => $galeria->titulo, 'number' => $index + 1]) }}"
                >
                    <img src="{{ $imagen->image_url }}" alt="{{ __('public.gallery_detail.photo_alt', ['title' => $galeria->titulo, 'number' => $index + 1]) }}" onerror="this.onerror=null;this.src='{{ asset('img/club_14.png') }}';">
                    <span class="foto-galeria-overlay">
                        <i class="fas fa-expand"></i> {{ __('public.gallery_detail.view_photo') }}
                    </span>
                </button>
            @endforeach
        </div>
    </div>
</section>

{{-- Lightbox para abrir una foto en grande y navegar por el álbum --}}
<div class="lightbox-galeria" data-lightbox hidden>
    <button type="button" class="lightbox-cerrar" data-lightbox-cerrar aria-label="{{ __('public.gallery_detail.lightbox.close') }}">
        <i class="fas fa-times"></i>
    </button>

    <button type="button" class="lightbox-nav lightbox-nav-prev" data-lightbox-prev aria-label="{{ __('public.gallery_detail.lightbox.previous') }}">
        <i class="fas fa-chevron-left"></i>
    </button>

    <div class="lightbox-contenido">
        <img src="" alt="" data-lightbox-imagen>
        <div class="lightbox-meta">
            <strong>{{ $galeria->titulo }}</strong>
            <span data-lightbox-contador></span>
        </div>
    </div>

    <button type="button" class="lightbox-nav lightbox-nav-next" data-lightbox-next aria-label="{{ __('public.gallery_detail.lightbox.next') }}">
        <i class="fas fa-chevron-right"></i>
    </button>
</div>

{{-- Script del lightbox: abrir, cerrar y navegar entre imágenes --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const items = Array.from(document.querySelectorAll('[data-galeria-item]'));
    const lightbox = document.querySelector('[data-lightbox]');
    const lightboxImage = document.querySelector('[data-lightbox-imagen]');
    const lightboxCounter = document.querySelector('[data-lightbox-contador]');
    const closeButton = document.querySelector('[data-lightbox-cerrar]');
    const prevButton = document.querySelector('[data-lightbox-prev]');
    const nextButton = document.querySelector('[data-lightbox-next]');

    if (!items.length || !lightbox || !lightboxImage || !lightboxCounter) {
        return;
    }

    let currentIndex = 0;

    const render = () => {
        const item = items[currentIndex];
        lightboxImage.src = item.dataset.src;
        lightboxImage.alt = item.dataset.alt;
        lightboxCounter.textContent = @json(__('public.gallery_detail.lightbox.counter_prefix')) + ' ' + (currentIndex + 1) + ' ' + @json(__('public.gallery_detail.lightbox.counter_separator')) + ' ' + items.length;
    };

    const openLightbox = (index) => {
        currentIndex = index;
        render();
        lightbox.hidden = false;
        document.body.classList.add('lightbox-open');
    };

    const closeLightbox = () => {
        lightbox.hidden = true;
        document.body.classList.remove('lightbox-open');
    };

    const showNext = () => {
        currentIndex = (currentIndex + 1) % items.length;
        render();
    };

    const showPrev = () => {
        currentIndex = (currentIndex - 1 + items.length) % items.length;
        render();
    };

    items.forEach((item, index) => {
        item.addEventListener('click', () => openLightbox(index));
    });

    closeButton.addEventListener('click', closeLightbox);
    nextButton.addEventListener('click', showNext);
    prevButton.addEventListener('click', showPrev);

    lightbox.addEventListener('click', function (event) {
        if (event.target === lightbox) {
            closeLightbox();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (lightbox.hidden) {
            return;
        }

        if (event.key === 'Escape') {
            closeLightbox();
        }

        if (event.key === 'ArrowRight') {
            showNext();
        }

        if (event.key === 'ArrowLeft') {
            showPrev();
        }
    });
});
</script>
@endsection
