@extends('layouts.admin') {{-- O tu layout de admin si tienes otro --}}

@section('contenido_admin')
<div class="contenedor-admin-noticias">
    
    <div class="header-admin">
        <div>
            <h1>Gestión de Noticias</h1>
            <p>Aquí puedes crear, editar o eliminar las noticias del club.</p>
        </div>
        <a href="{{ route('noticias.create') }}" class="btn-nuevo">
            <i class="fas fa-plus"></i> Nueva Noticia
        </a>
    </div>

    {{-- Buscador de Equipos --}}
    <div class="contenedor-buscador">
        <form action="{{ route('noticias.search') }}" method="GET" class="form-buscador">
            <div class="input-grupal">
                <input type="text" name="search" placeholder="Buscar por nombre..." value="{{ $search ?? '' }}" lass="input-search">
                <button type="submit" class="btn-buscar">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            {{-- Si hay una búsqueda activa, mostramos un botón para limpiar --}}
            @if(isset($search) && $search != '')
                <a href="{{ route('noticias.index') }}" class="btn-limpiar" title="Limpiar búsqueda">
                    <i class="fas fa-times"></i>
                </a>
            @endif
        </form>
    </div>


   {{-- Mensaje de éxito al crear, editar o borrar --}}
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
                    <th>Título / Autor</th>
                    <th>Fecha</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($noticias as $noticia)
                <tr>
                    <td class="td-img">
                        @if($noticia->imagen_noticia)
                            <img src="{{ asset('noticias/' . $noticia->imagen_noticia) }}" alt="Imagen Noticia">
                        @else
                            <div class="sin-foto"><i class="fas fa-image" alt = 'imagen_noticia' ></i></div>
                        @endif
                    </td>
                    <td>
                        <div class="info-principal">
                            <span class="titulo-row">{{ $noticia->titulo }}</span>
                            <span class="autor-row"><i class="fas fa-user"></i> {{ $noticia->autor }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="fecha-admin">{{ $noticia->created_at->format('d/m/Y') }}</span>
                    </td>
                    <td>
                        <div class="acciones-admin">
                            <a href="{{ route('noticias.show', $noticia->id) }}" class="btn-accion btn-ver" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('noticias.edit', $noticia->id) }}" class="btn-accion btn-editar" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('noticias.destroy', $noticia->id) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres borrar esta noticia?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-accion btn-borrar" title="Eliminar">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                {{-- MENSAJE CUANDO ESTÁ VACÍO --}}
                <tr>
                    <td colspan="4" style="text-align: center; padding: 40px; color: #777;">
                        <i class="fas fa-info-circle"></i> No se han encontrado noticias disponibles.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{--Esto es la paginación--}}
        <div class="contenedor-paginacion">
            {{ $noticias->links() }}
        </div>
    </div>
</div>
@endsection