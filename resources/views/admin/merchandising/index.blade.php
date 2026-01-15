@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-admin-principal"> 
    
    <header class="header-admin">
        <div>
            <h2>Gestión de Merchandising</h2>
            <p style="color: #777;">Productos y equipación oficial del club</p>
        </div>
        <a href="{{ route('merchandising.create') }}" class="btn-nuevo">
            <i class="fas fa-plus"></i> Nuevo Producto
        </a>
    </header>

    {{-- Buscador --}}
    <div class="contenedor-buscador">
        <form action="{{ route('merchandising.index') }}" method="GET" class="form-buscador">
            <div class="input-grupal">
                <input type="text" name="search" placeholder="Buscar producto..." value="{{ $search ?? '' }}" class="input-search">
                <button type="submit" class="btn-buscar">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            @if(isset($search) && $search != '')
                <a href="{{ route('merchandising.index') }}" class="btn-limpiar">
                    <i class="fas fa-times"></i>
                </a>
            @endif
        </form>
    </div>

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
                            <span class="nombre-producto">{{ $item->nombre }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge-talla">{{ $item->talla ?? 'N/A' }}</span>
                    </td>
                    <td>
                        <strong class="precio-texto">{{ number_format($item->precio, 2, ',', '.') }}€</strong>
                    </td>
                    <td>
                        <div class="acciones-flex">
                            <a href="{{ route('merchandising.edit', $item->id) }}" class="btn-accion editar" title="Editar">
                                <i class="fas fa-pen"></i>
                            </a>
                            
                            <form action="{{ route('merchandising.destroy', $item->id) }}" method="POST" onsubmit="return confirm('¿Eliminar producto?')">
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
                    <td colspan="5">
                        <div class="mensaje-vacio">
                            <i class="fas fa-box-open"></i>
                            <p>No se han encontrado productos de merchandising.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>


    </div>
</div>
@endsection