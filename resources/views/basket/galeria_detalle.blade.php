@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/paginas/galeria.css') }}" />
@endpush

@section('title', $galeria->titulo . ' - Bellreguard Club de Basket')

@section('contenido')
<x-public-news-hero
    badge="ALBUM OFICIAL"
    title="{{ $galeria->titulo }}"
    subtitle="{{ $galeria->descripcion }}"
>
    <div class="galeria-detalle-nav">
        <a href="{{ route('basket.galeria') }}" class="galeria-breadcrumb">
            <i class="fas fa-arrow-left"></i> Volver a la galería
        </a>
    </div>
    <div class="stats-galeria">
        <span><i class="fas fa-images"></i> {{ $galeria->imagenes->count() }} fotos</span>
        <span><i class="fas fa-calendar-alt"></i> {{ $galeria->created_at->format('d/m/Y') }}</span>
    </div>
</x-public-news-hero>

<section class="contenedor-galeria contenedor-galeria-detalle">
    <div class="galeria-editorial-shell">
        <div class="galeria-editorial-intro">
            <span class="galeria-kicker">Album fotografico</span>
            <h2>Recorrido visual</h2>
            <p>Pulsa cualquier imagen para verla en grande y moverte por el album con las flechas laterales o el teclado.</p>
        </div>

        <div class="rejilla-fotos">
            @foreach($galeria->imagenes as $index => $imagen)
                <button
                    type="button"
                    class="foto-galeria-card"
                    data-galeria-item
                    data-index="{{ $index }}"
                    data-src="{{ $imagen->image_url }}"
                    data-alt="{{ $galeria->titulo }} - Foto {{ $index + 1 }}"
                >
                    <img src="{{ $imagen->image_url }}" alt="{{ $galeria->titulo }} - Foto {{ $index + 1 }}" onerror="this.onerror=null;this.src='{{ asset('img/club_14.png') }}';">
                    <span class="foto-galeria-overlay">
                        <i class="fas fa-expand"></i> Ver foto
                    </span>
                </button>
            @endforeach
        </div>
    </div>
</section>

<div class="lightbox-galeria" data-lightbox hidden>
    <button type="button" class="lightbox-cerrar" data-lightbox-cerrar aria-label="Cerrar visor">
        <i class="fas fa-times"></i>
    </button>

    <button type="button" class="lightbox-nav lightbox-nav-prev" data-lightbox-prev aria-label="Foto anterior">
        <i class="fas fa-chevron-left"></i>
    </button>

    <div class="lightbox-contenido">
        <img src="" alt="" data-lightbox-imagen>
        <div class="lightbox-meta">
            <strong>{{ $galeria->titulo }}</strong>
            <span data-lightbox-contador></span>
        </div>
    </div>

    <button type="button" class="lightbox-nav lightbox-nav-next" data-lightbox-next aria-label="Foto siguiente">
        <i class="fas fa-chevron-right"></i>
    </button>
</div>

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
        lightboxCounter.textContent = `Foto ${currentIndex + 1} de ${items.length}`;
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
