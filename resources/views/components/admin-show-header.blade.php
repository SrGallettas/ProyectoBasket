@props([
    'titulo',
    'subtitulo' => null,
    'editRoute' => null,
    'deleteRoute' => null,
    'indexRoute',
    'confirmMessage' => null,
    'backText' => null,
    'backClass' => '',
])

@php($confirmMessage = $confirmMessage ?? __('admin.common.confirm_delete'))
@php($backText = $backText ?? __('admin.common.back_to_list_upper'))

<header class="header-ficha">
    <div>
        <h2>{{ $titulo }}</h2>
        @if($subtitulo)
            <p>{{ $subtitulo }}</p>
        @endif
    </div>

    <div class="acciones-header-ficha">
        @if($editRoute)
            <a href="{{ $editRoute }}" class="btn-ficha-accion btn-ficha-edit" title="{{ __('admin.common.edit_item') }}">
                <i class="fas fa-edit"></i>&nbsp;{{ __('admin.common.edit_upper') }}
            </a>
        @endif

        @if($deleteRoute)
            <form action="{{ $deleteRoute }}" method="POST" onsubmit="return confirm('{{ $confirmMessage }}')" class="admin-form-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-ficha-accion btn-ficha-delete" title="{{ __('admin.common.delete_item') }}">
                    <i class="fas fa-trash-alt"></i>&nbsp;{{ __('admin.common.delete_upper') }}
                </button>
            </form>
        @endif

        <a href="{{ $indexRoute }}" class="btn-ficha-accion btn-ficha-volver {{ $backClass }}" title="{{ __('admin.common.back_to_list') }}">
            <i class="fas fa-arrow-left"></i>&nbsp;{{ $backText }}
        </a>
    </div>
</header>
