@extends('layouts.admin')

@section('title', __('admin.news.meta.index_title'))

@section('contenido_admin')
<div class="contenedor-admin-noticias">
    
    {{-- 1. USAMOS EL COMPONENTE (Sustituye al header y al buscador viejo) --}}
    <x-admin-header 
        :titulo="__('admin.news.index.title')" 
        :subtitulo="__('admin.news.index.subtitle')"
        :rutaNuevo="route('noticias.create')" 
        :textoNuevo="__('admin.news.index.new')"
        :rutaIndex="route('noticias.index')"
    />

   {{-- Mensaje de éxito al crear, editar o borrar --}}
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
                    <th>{{ __('admin.news.fields.image') }}</th>
                    <th><a href="{{ route('noticias.index', ['search' => $search, 'sort' => 'titulo', 'direction' => $nextDirection('titulo')]) }}" style="color: inherit; text-decoration: none; font: inherit;">{{ __('admin.news.fields.title_author') }}{!! $sortIndicator('titulo') !!}</a></th>
                    <th><a href="{{ route('noticias.index', ['search' => $search, 'sort' => 'created_at', 'direction' => $nextDirection('created_at')]) }}" style="color: inherit; text-decoration: none; font: inherit;">{{ __('admin.news.fields.date') }}{!! $sortIndicator('created_at') !!}</a></th>
                    <th class="text-center"><a href="{{ route('noticias.index', ['search' => $search, 'sort' => 'oculto', 'direction' => $nextDirection('oculto')]) }}" style="color: inherit; text-decoration: none; font: inherit;">{{ __('admin.common.actions') }}{!! $sortIndicator('oculto') !!}</a></th>
                </tr>
            </thead>
            <tbody>
                @forelse($noticias as $noticia)
                <tr>
                    <td class="td-img">
                        <img src="{{ $noticia->imagen_noticia_url }}" alt="{{ __('admin.news.fields.image') }}" class="admin-thumb-60">
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
                            <a href="{{ route('noticias.show', $noticia->id) }}" class="btn-accion admin-view-btn" title="{{ __('admin.news.actions.view') }}">
                                <i class="fas fa-eye"></i>
                            </a>
                            {{-- Botón Editar --}}
                            <a href="{{ route('noticias.edit', $noticia->id) }}" class="btn-accion editar" title="{{ __('admin.news.actions.edit') }}">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('noticias.toggle-oculto', $noticia->id) }}" method="POST" class="admin-form-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-accion {{ $noticia->oculto ? 'mostrar' : 'ocultar' }}" title="{{ $noticia->oculto ? __('admin.news.actions.show') : __('admin.news.actions.hide') }}">
                                    <i class="fas {{ $noticia->oculto ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                </button>
                            </form>
                            {{-- Formulario Borrar --}}
                            <form action="{{ route('noticias.destroy', $noticia->id) }}" method="POST" onsubmit="return confirm('{{ __('admin.news.actions.confirm_delete') }}')" class="admin-form-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-accion borrar" title="{{ __('admin.news.actions.delete') }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="admin-empty-cell">
                        <i class="fas fa-info-circle"></i> {{ __('admin.news.empty.index') }}
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
