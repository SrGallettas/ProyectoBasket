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
        <div style="padding: 15px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 20px;">
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
                        <div style="display: flex; gap: 8px; align-items: center; justify-content: flex-start;">

                            <a href="{{ route('merchandising.show', $item->id) }}" class="btn-accion" title="Ver Detalles">
                                <i class="fas fa-eye"></i>
                            </a>

                            <a href="{{ route('merchandising.edit', $item->id) }}" class="btn-accion editar" title="Editar">
                                <i class="fas fa-pen"></i>
                            </a>
                            
                            <form action="{{ route('merchandising.destroy', $item->id) }}" method="POST" onsubmit="return confirm('¿Eliminar producto?')" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-accion borrar" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px; color: #777;">
                        <div class="mensaje-vacio">
                            <i class="fas fa-box-open" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
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