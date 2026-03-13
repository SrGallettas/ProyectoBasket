@props([
    'titulo',
    'subtitulo' => null,
    'backRoute',
    'backText' => 'Volver al listado',
    'backClass' => 'btn-nuevo',
    'backIcon' => 'fas fa-arrow-left',
])

<header class="header-ficha">
    <div>
        <h2>{{ $titulo }}</h2>
        @if($subtitulo)
            <p>{!! $subtitulo !!}</p>
        @endif
    </div>

    <a href="{{ $backRoute }}" class="{{ $backClass }}" title="{{ $backText }}">
        <i class="{{ $backIcon }}"></i> {{ $backText }}
    </a>
</header>
