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

    @php
        // Alterna asc/desc cuando se vuelve a pulsar la misma cabecera.
        $nextDirection = function (string $column) use ($sort, $direction) {
            return $sort === $column && $direction === 'asc' ? 'desc' : 'asc';
        };

        // La flecha solo aparece cuando ya hay una ordenación activa.
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
                    <th>Portada</th>
                    <th><a href="{{ route('galerias.index', ['search' => $search, 'sort' => 'titulo', 'direction' => $nextDirection('titulo')]) }}" style="color: inherit; text-decoration: none; font: inherit;">Título{!! $sortIndicator('titulo') !!}</a></th>
                    <th><a href="{{ route('galerias.index', ['search' => $search, 'sort' => 'tipo', 'direction' => $nextDirection('tipo')]) }}" style="color: inherit; text-decoration: none; font: inherit;">Tipo{!! $sortIndicator('tipo') !!}</a></th>
                    <th><a href="{{ route('galerias.index', ['search' => $search, 'sort' => 'imagenes_count', 'direction' => $nextDirection('imagenes_count')]) }}" style="color: inherit; text-decoration: none; font: inherit;">Imágenes{!! $sortIndicator('imagenes_count') !!}</a></th>
                    <th><a href="{{ route('galerias.index', ['search' => $search, 'sort' => 'slug', 'direction' => $nextDirection('slug')]) }}" style="color: inherit; text-decoration: none; font: inherit;">Slug{!! $sortIndicator('slug') !!}</a></th>
                    <th class="text-center"><a href="{{ route('galerias.index', ['search' => $search, 'sort' => 'oculto', 'direction' => $nextDirection('oculto')]) }}" style="color: inherit; text-decoration: none; font: inherit;">Acciones{!! $sortIndicator('oculto') !!}</a></th>
                </tr>
            </thead>
            <tbody>
                @forelse($galerias as $galeria)
                    <tr>
                        <td class="td-img">
                            @if($galeria->portada)
                                <img src="{{ $galeria->portada->image_url }}" alt="Portada {{ $galeria->titulo }}" class="admin-thumb-60">
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
