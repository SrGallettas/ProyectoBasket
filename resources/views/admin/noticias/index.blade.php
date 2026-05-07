@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-admin-noticias">
    
    {{-- 1. USAMOS EL COMPONENTE (Sustituye al header y al buscador viejo) --}}
    <x-admin-header 
        titulo="Gestión de Noticias" 
        subtitulo="Aquí puedes crear, editar o eliminar las noticias del club."
        :rutaNuevo="route('noticias.create')" 
        textoNuevo="Nueva Noticia"
        :rutaIndex="route('noticias.index')"
    />

   {{-- Mensaje de éxito al crear, editar o borrar --}}
    @if(session('mensaje'))
        <div class="admin-alert-success">
            <i class="fas fa-check-circle"></i> {{ session('mensaje') }}
        </div>
    @endif

    @php
        // Alterna asc/desc cuando el usuario pulsa varias veces la misma cabecera.
        $nextDirection = function (string $column) use ($sort, $direction) {
            return $sort === $column && $direction === 'asc' ? 'desc' : 'asc';
        };

        // La flecha solo se enseña cuando ya hay una ordenación activa.
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
                    <th>Imagen</th>
                    <th><a href="{{ route('noticias.index', ['search' => $search, 'sort' => 'titulo', 'direction' => $nextDirection('titulo')]) }}" style="color: inherit; text-decoration: none; font: inherit;">Título / Autor{!! $sortIndicator('titulo') !!}</a></th>
                    <th><a href="{{ route('noticias.index', ['search' => $search, 'sort' => 'created_at', 'direction' => $nextDirection('created_at')]) }}" style="color: inherit; text-decoration: none; font: inherit;">Fecha{!! $sortIndicator('created_at') !!}</a></th>
                    <th class="text-center"><a href="{{ route('noticias.index', ['search' => $search, 'sort' => 'oculto', 'direction' => $nextDirection('oculto')]) }}" style="color: inherit; text-decoration: none; font: inherit;">Acciones{!! $sortIndicator('oculto') !!}</a></th>
                </tr>
            </thead>
            <tbody>
                @forelse($noticias as $noticia)
                <tr>
                    <td class="td-img">
                        @if($noticia->imagen_noticia)
                            <img src="{{ $noticia->imagen_noticia_url }}" alt="Imagen Noticia" class="admin-thumb-60">
                        @else
                            <div class="sin-foto"><i class="fas fa-image"></i></div>
                        @endif
                    </td>
                    <td>
                        <div class="info-principal">
                            <span class="titulo-row"><strong>{{ $noticia->titulo }}</strong></span><br>
                            <span class="autor-row admin-subtle-text"><i class="fas fa-user"></i> {{ $noticia->autor }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="fecha-admin">{{ $noticia->created_at->format('d/m/Y') }}</span>
                    </td>
                    <td>
                        <div class="admin-row-actions">
                            {{-- Botón Ver --}}
                            <a href="{{ route('noticias.show', $noticia->id) }}" class="btn-accion admin-view-btn" title="Ver detalles de la noticia">
                                <i class="fas fa-eye"></i>
                            </a>
                            {{-- Botón Editar --}}
                            <a href="{{ route('noticias.edit', $noticia->id) }}" class="btn-accion editar" title="Editar noticia">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('noticias.toggle-oculto', $noticia->id) }}" method="POST" class="admin-form-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-accion {{ $noticia->oculto ? 'mostrar' : 'ocultar' }}" title="{{ $noticia->oculto ? 'Mostrar noticia' : 'Ocultar noticia' }}">
                                    <i class="fas {{ $noticia->oculto ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                </button>
                            </form>
                            {{-- Formulario Borrar --}}
                            <form action="{{ route('noticias.destroy', $noticia->id) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres borrar esta noticia?')" class="admin-form-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-accion borrar" title="Eliminar noticia">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="admin-empty-cell">
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
