@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-admin-noticias">
    <x-admin-header
        titulo="Gestión de Galerías"
        subtitulo="Crea álbumes, sube varias imágenes y organiza la galería por tipo."
        :rutaNuevo="route('galerias.create')"
        textoNuevo="Nueva Galería"
        :rutaIndex="route('galerias.index')"
    />

    @if(session('mensaje'))
        <div class="admin-alert-success">
            <i class="fas fa-check-circle"></i> {{ session('mensaje') }}
        </div>
    @endif

    <div class="pizarra-admin">
        <table class="tabla-admin">
            <thead>
                <tr>
                    <th>Portada</th>
                    <th>Título</th>
                    <th>Tipo</th>
                    <th>Imágenes</th>
                    <th>Slug</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($galerias as $galeria)
                    <tr>
                        <td class="td-img">
                            @if($galeria->imagenes->first())
                                <img src="{{ $galeria->imagenes->first()->image_url }}" alt="Portada {{ $galeria->titulo }}" class="admin-thumb-60">
                            @else
                                <div class="sin-foto"><i class="fas fa-image"></i></div>
                            @endif
                        </td>
                        <td>
                            <div class="info-principal">
                                <span class="titulo-row"><strong>{{ $galeria->titulo }}</strong></span><br>
                                <span class="admin-subtle-text">{{ \Illuminate\Support\Str::limit($galeria->descripcion, 70) }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="admin-badge admin-badge-neutral">{{ $tipos[$galeria->tipo] ?? ucfirst($galeria->tipo) }}</span>
                        </td>
                        <td>
                            <strong>{{ $galeria->imagenes_count }}</strong>
                        </td>
                        <td>
                            <span class="admin-code">{{ $galeria->slug }}</span>
                        </td>
                        <td>
                            <div class="admin-row-actions">
                                <a href="{{ route('galerias.show', $galeria->id) }}" class="btn-accion admin-view-btn" title="Ver detalles de la galeria">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('galerias.edit', $galeria->id) }}" class="btn-accion editar" title="Editar galeria">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('galerias.toggle-oculto', $galeria->id) }}" method="POST" class="admin-form-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-accion {{ $galeria->oculto ? 'mostrar' : 'ocultar' }}" title="{{ $galeria->oculto ? 'Mostrar galeria' : 'Ocultar galeria' }}">
                                        <i class="fas {{ $galeria->oculto ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('galerias.destroy', $galeria->id) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres borrar esta galería?')" class="admin-form-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-accion borrar" title="Eliminar galeria">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="admin-empty-cell">
                            <i class="fas fa-images"></i> No hay galerías creadas todavía.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="contenedor-paginacion">
            {{ $galerias->links() }}
        </div>
    </div>
</div>
@endsection
