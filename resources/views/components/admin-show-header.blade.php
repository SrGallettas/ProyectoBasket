@props([
    'titulo',
    'subtitulo' => null,
    'editRoute' => null,
    'deleteRoute' => null,
    'indexRoute',
    'confirmMessage' => '¿Seguro que deseas eliminar este elemento?',
    'backText' => 'VOLVER AL LISTADO',
    'backClass' => '',
])

<header class="header-ficha">
    <div>
        <h2>{{ $titulo }}</h2>
        @if($subtitulo)
            <p>{{ $subtitulo }}</p>
        @endif
    </div>

    <div class="acciones-header-ficha">
        @if($editRoute)
            <a href="{{ $editRoute }}" class="btn-ficha-accion btn-ficha-edit" title="Editar elemento">
                <i class="fas fa-edit"></i>&nbsp;EDITAR
            </a>
        @endif

        @if($deleteRoute)
            <form action="{{ $deleteRoute }}" method="POST" onsubmit="return confirm('{{ $confirmMessage }}')" class="admin-form-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-ficha-accion btn-ficha-delete" title="Eliminar elemento">
                    <i class="fas fa-trash-alt"></i>&nbsp;ELIMINAR
                </button>
            </form>
        @endif

        <a href="{{ $indexRoute }}" class="btn-ficha-accion btn-ficha-volver {{ $backClass }}" title="Volver al listado">
            <i class="fas fa-arrow-left"></i>&nbsp;{{ $backText }}
        </a>
    </div>
</header>
