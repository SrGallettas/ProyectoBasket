@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-admin-principal"> 
    
    {{-- 1. USAMOS EL COMPONENTE --}}
    <x-admin-header 
        titulo="Gestión de Merchandising" 
        subtitulo="Productos y equipación oficial del club"
        :rutaNuevo="route('merchandising.create')" 
        textoNuevo="Nuevo Producto"
        :rutaIndex="route('merchandising.index')"
    />

    {{-- Mensaje de éxito (Opcional, pero recomendado tenerlo) --}}
    @if(session('mensaje'))
        <div class="admin-alert-success">
            <i class="fas fa-check-circle"></i> {{ session('mensaje') }}
        </div>
    @endif

    <div class="pizarra-admin">
        <table class="tabla-admin">
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Producto</th>
                    <th>Talla</th>
                    <th>Precio</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($merchandising as $item)
                <tr>
                    <td>
                        <div class="contenedor-foto-tabla">
                            @if($item->imagen_merchan)
                                <img src="{{ asset('merchandising/' . $item->imagen_merchan) }}" alt="{{ $item->nombre }}" class="img-merchan-tabla">
                            @else
                                <div class="sin-foto-mini"><i class="fas fa-tshirt"></i></div>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="info-producto">
                            <span class="nombre-producto"><strong>{{ $item->nombre }}</strong></span>
                        </div>
                    </td>
                    <td>
                        <span class="badge-talla">{{ $item->talla ?? 'N/A' }}</span>
                    </td>
                    <td>
                        <strong class="precio-texto">{{ number_format($item->precio, 2, ',', '.') }}€</strong>
                    </td>
                    <td>
                        <div class="admin-row-actions">

                            <a href="{{ route('merchandising.show', $item->id) }}" class="btn-accion" title="Ver detalles del producto">
                                <i class="fas fa-eye"></i>
                            </a>

                            <a href="{{ route('merchandising.edit', $item->id) }}" class="btn-accion editar" title="Editar producto">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form action="{{ route('merchandising.toggle-oculto', $item->id) }}" method="POST" class="admin-form-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-accion {{ $item->oculto ? 'mostrar' : 'ocultar' }}" title="{{ $item->oculto ? 'Mostrar producto' : 'Ocultar producto' }}">
                                    <i class="fas {{ $item->oculto ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                </button>
                            </form>
                            
                            <form action="{{ route('merchandising.destroy', $item->id) }}" method="POST" onsubmit="return confirm('¿Eliminar producto?')" class="admin-form-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-accion borrar" title="Eliminar producto">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                        @if($item->oculto)
                            <span class="badge-oculto-admin">Oculto</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="admin-empty-cell">
                        <div class="mensaje-vacio">
                            <i class="fas fa-box-open merch-empty-icon"></i>
                            <p>No se han encontrado productos de merchandising.</p>
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
