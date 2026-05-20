@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-admin-principal"> 
    
    {{-- 1. USAMOS EL COMPONENTE --}}
    <x-admin-header 
        :titulo="__('admin.store.index.title')" 
        :subtitulo="__('admin.store.index.subtitle')"
        :rutaNuevo="route('merchandising.create')" 
        :textoNuevo="__('admin.store.index.new')"
        :rutaIndex="route('merchandising.index')"
    />

    {{-- Mensaje de éxito (Opcional, pero recomendado tenerlo) --}}
    @if(session('mensaje'))
        <div class="admin-alert-success">
            <i class="fas fa-check-circle"></i> {{ session('mensaje') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="admin-alert-warning">
            <i class="fas fa-triangle-exclamation"></i> {{ session('warning') }}
        </div>
    @endif

    @php
        // Alterna asc/desc desde las cabeceras clicables.
        $nextDirection = function (string $column) use ($sort, $direction) {
            return $sort === $column && $direction === 'asc' ? 'desc' : 'asc';
        };

        // La flecha solo aparece cuando ya existe una ordenación activa.
        $hasActiveSort = request()->has('sort');

        $sortIndicator = function (string $column) use ($sort, $direction, $hasActiveSort) {
            if (!$hasActiveSort || $sort !== $column) {
                return '';
            }

            return $direction === 'asc' ? ' ▲' : ' ▼';
        };
    @endphp

    <div class="pizarra-admin">
        <table class="tabla-admin">
            <thead>
                <tr>
                    <th>{{ __('admin.store.fields.image') }}</th>
                    <th><a href="{{ route('merchandising.index', ['search' => $search, 'sort' => 'nombre', 'direction' => $nextDirection('nombre')]) }}" style="color: inherit; text-decoration: none; font: inherit;">{{ __('admin.store.fields.product') }}{!! $sortIndicator('nombre') !!}</a></th>
                    <th><a href="{{ route('merchandising.index', ['search' => $search, 'sort' => 'talla', 'direction' => $nextDirection('talla')]) }}" style="color: inherit; text-decoration: none; font: inherit;">{{ __('admin.store.fields.size') }}{!! $sortIndicator('talla') !!}</a></th>
                    <th><a href="{{ route('merchandising.index', ['search' => $search, 'sort' => 'precio', 'direction' => $nextDirection('precio')]) }}" style="color: inherit; text-decoration: none; font: inherit;">{{ __('admin.store.fields.price') }}{!! $sortIndicator('precio') !!}</a></th>
                    <th class="text-center"><a href="{{ route('merchandising.index', ['search' => $search, 'sort' => 'oculto', 'direction' => $nextDirection('oculto')]) }}" style="color: inherit; text-decoration: none; font: inherit;">{{ __('admin.store.fields.actions') }}{!! $sortIndicator('oculto') !!}</a></th>
                </tr>
            </thead>
            <tbody>
                @forelse($merchandising as $item)
                <tr>
                    <td>
                        <div class="contenedor-foto-tabla">
                            @if($item->imagen_merchan)
                                <img src="{{ $item->imagen_merchan_url }}" alt="{{ $item->nombre }}" class="img-merchan-tabla">
                            @else
                                <div class="sin-foto-mini" aria-label="{{ __('admin.store.images.missing_alt') }}"><i class="fas fa-tshirt"></i></div>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="info-producto">
                            <span class="nombre-producto"><strong>{{ $item->nombre }}</strong></span>
                        </div>
                    </td>
                    <td>
                        <span class="badge-talla">{{ $item->talla ?? __('admin.store.fields.not_available') }}</span>
                    </td>
                    <td>
                        <strong class="precio-texto">{{ number_format($item->precio, 2, ',', '.') }}€</strong>
                    </td>
                    <td>
                        <div class="admin-row-actions">

                            <a href="{{ route('merchandising.show', $item->id) }}" class="btn-accion" title="{{ __('admin.store.actions.view') }}">
                                <i class="fas fa-eye"></i>
                            </a>

                            <a href="{{ route('merchandising.edit', $item->id) }}" class="btn-accion editar" title="{{ __('admin.store.actions.edit') }}">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form action="{{ route('merchandising.toggle-oculto', $item->id) }}" method="POST" class="admin-form-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-accion {{ $item->oculto ? 'mostrar' : 'ocultar' }}" title="{{ $item->oculto ? __('admin.store.actions.show') : __('admin.store.actions.hide') }}">
                                    <i class="fas {{ $item->oculto ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                </button>
                            </form>
                            
                            <form action="{{ route('merchandising.destroy', $item->id) }}" method="POST" onsubmit="return confirm('{{ __('admin.store.actions.confirm_delete') }}')" class="admin-form-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-accion borrar" title="{{ __('admin.store.actions.delete') }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="admin-empty-cell">
                        <div class="mensaje-vacio">
                            <i class="fas fa-box-open merch-empty-icon"></i>
                            <p>{{ __('admin.store.empty.index') }}</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        {{-- Paginación si la usas --}}
        @if(method_exists($merchandising, 'links'))
            <div class="contenedor-paginacion">
                {{ $merchandising->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
