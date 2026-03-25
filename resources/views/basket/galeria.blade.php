@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/paginas/galeria.css') }}" />
@endpush

@section('title', 'Galería - Bellreguard Club de Basket')

@section('contenido')
<x-public-news-hero
    badge="ARCHIVO OFICIAL"
    title="Galería Bellreguard CB"
    subtitle="Álbumes del club organizados por tipo para que puedas filtrar cada momento."
>
    <div class="stats-galeria">
        <span><i class="fas fa-folder-open"></i> {{ $galerias->total() }} álbumes</span>
        <span><i class="fas fa-images"></i> {{ $galerias->sum('imagenes_count') }} fotos en esta página</span>
    </div>
</x-public-news-hero>

<section class="contenedor-galeria">
    @if(session('mensaje'))
        <div class="public-alert-ok">
            {{ session('mensaje') }}
        </div>
    @endif

    <div class="filtros-galeria">
        @foreach($tipos as $clave => $label)
            <a href="{{ route('basket.galeria', ['tipo' => $clave !== 'todos' ? $clave : null]) }}"
               class="btn-filtro {{ $tipoSeleccionado === $clave ? 'activo' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="rejilla-albumes">
        @forelse($galerias as $galeria)
            <div class="tarjeta-album">
                <div class="imagen-album">
                    @if($galeria->portada)
                        <img src="{{ asset('galerias/' . $galeria->portada->archivo) }}" alt="{{ $galeria->titulo }}" onerror="this.onerror=null;this.src='{{ asset('img/club_14.png') }}';">
                    @else
                        <div class="album-placeholder">
                            <i class="fas fa-image"></i>
                        </div>
                    @endif
                    <span class="badge-fotos">{{ $galeria->imagenes_count }} fotos</span>
                </div>
                <div class="info-album">
                    <span class="tipo-album">{{ $tipos[$galeria->tipo] ?? ucfirst($galeria->tipo) }}</span>
                    <h3>{{ $galeria->titulo }}</h3>
                    <p>{{ \Illuminate\Support\Str::limit($galeria->descripcion, 120) }}</p>
                    <div class="footer-album">
                        <span class="fecha-album">{{ $galeria->created_at->format('d/m/Y') }}</span>
                        <div class="public-inline-actions">
                            <a href="{{ route('basket.galeria.detalle', $galeria->slug) }}" class="btn-ver-album">Ver álbum</a>
                            @auth
                                <form action="{{ route('basket.galeria.toggle-oculto', $galeria->id) }}" method="POST" class="public-card-action">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="public-hide-button">Ocultar en web</button>
                                </form>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="galeria-empty-state">
                <i class="fas fa-images"></i>
                <h3>No hay álbumes para este filtro</h3>
                <p>Cuando se creen galerías desde el panel de administración aparecerán aquí.</p>
            </div>
        @endforelse
    </div>

        <div class="contenedor-paginacion">
            {{ $galerias->links() }}
        </div>

</section>
@endsection
