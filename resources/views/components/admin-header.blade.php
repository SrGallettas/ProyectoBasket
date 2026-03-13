{{-- resources/views/components/admin-header.blade.php --}}
@props(['titulo', 'subtitulo', 'rutaNuevo' => null, 'textoNuevo' => 'Añadir', 'rutaIndex'])

<header class="header-admin">
    <div>
        <h2>{{ $titulo }}</h2>
        <p class="admin-header-subtitle">{{ $subtitulo }}</p>
    </div>
    
    @if($rutaNuevo)
        <a href="{{ $rutaNuevo }}" class="btn-nuevo" title="{{ $textoNuevo }}">
            <i class="fas fa-plus"></i> {{ $textoNuevo }}
        </a>
    @endif
</header>

{{-- Tu Buscador Original con tus estilos CSS --}}
<div class="contenedor-buscador">
    <form action="{{ url()->current() }}" method="GET" class="form-buscador">
        <div class="input-grupal">
            <input type="text" 
                   name="search" 
                   placeholder="Buscar por nombre..." 
                   value="{{ request('search') }}" 
                   class="input-search">
            <button type="submit" class="btn-buscar" title="Buscar">
                <i class="fas fa-search"></i>
            </button>
        </div>
        @if(request('search'))
            <a href="{{ $rutaIndex }}" class="btn-limpiar" title="Limpiar búsqueda">
                <i class="fas fa-times"></i>
            </a>
        @endif
    </form>
</div>
