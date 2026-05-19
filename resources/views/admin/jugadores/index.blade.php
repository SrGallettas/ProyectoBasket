@extends('layouts.admin')

@use('Carbon\Carbon') 

@section('title', __('admin.players.meta.index_title'))

@section('contenido_admin')

{{-- Invocamos el componente con tus datos --}}
<x-admin-header 
    :titulo="__('admin.players.index.title')" 
    :subtitulo="__('admin.players.index.subtitle')"
    :rutaNuevo="route('jugadores.create')" 
    :textoNuevo="__('admin.players.index.new')"
    :rutaIndex="route('jugadores.index')"
/>

{{-- Mensaje de éxito --}}
@if(session('mensaje'))
    <div class="admin-alert-success">
        <i class="fas fa-check-circle"></i> {{ session('mensaje') }}
    </div>
@endif

@php
    // Alterna asc/desc al pulsar varias veces la misma cabecera.
    $nextDirection = function (string $column) use ($sort, $direction) {
        return $sort === $column && $direction === 'asc' ? 'desc' : 'asc';
    };

    // La flecha solo aparece cuando ya existe una ordenación activa en la URL.
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
        {{-- Tu tabla se queda EXACTAMENTE IGUAL que la tenías --}}
        <thead>
            <tr>
                <th>{{ __('admin.players.fields.photo') }}</th>
                <th>
                    <a
                        href="{{ route('jugadores.index', ['search' => $search, 'sort' => 'dorsal', 'direction' => $nextDirection('dorsal')]) }}"
                        style="color: inherit; text-decoration: none; font: inherit;"
                    >
                        {{ __('admin.players.fields.number') }}{!! $sortIndicator('dorsal') !!}
                    </a>
                </th>
                <th>
                    <a
                        href="{{ route('jugadores.index', ['search' => $search, 'sort' => 'nombre', 'direction' => $nextDirection('nombre')]) }}"
                        style="color: inherit; text-decoration: none; font: inherit;"
                    >
                        {{ __('admin.players.fields.name') }}{!! $sortIndicator('nombre') !!}
                    </a>
                </th>
                <th>
                    <a
                        href="{{ route('jugadores.index', ['search' => $search, 'sort' => 'apellido', 'direction' => $nextDirection('apellido')]) }}"
                        style="color: inherit; text-decoration: none; font: inherit;"
                    >
                        {{ __('admin.players.fields.surname') }}{!! $sortIndicator('apellido') !!}
                    </a>
                </th>
                <th>
                    <a
                        href="{{ route('jugadores.index', ['search' => $search, 'sort' => 'club', 'direction' => $nextDirection('club')]) }}"
                        style="color: inherit; text-decoration: none; font: inherit;"
                    >
                        {{ __('admin.players.fields.club') }}{!! $sortIndicator('club') !!}
                    </a>
                </th>
                <th>
                    <a
                        href="{{ route('jugadores.index', ['search' => $search, 'sort' => 'categoria', 'direction' => $nextDirection('categoria')]) }}"
                        style="color: inherit; text-decoration: none; font: inherit;"
                    >
                        {{ __('admin.players.fields.category') }}{!! $sortIndicator('categoria') !!}
                    </a>
                </th>
                <th>
                    <a
                        href="{{ route('jugadores.index', ['search' => $search, 'sort' => 'posicion', 'direction' => $nextDirection('posicion')]) }}"
                        style="color: inherit; text-decoration: none; font: inherit;"
                    >
                        {{ __('admin.players.fields.position') }}{!! $sortIndicator('posicion') !!}
                    </a>
                </th>
                <th>
                    <a
                        href="{{ route('jugadores.index', ['search' => $search, 'sort' => 'fecha_nacimiento', 'direction' => $nextDirection('fecha_nacimiento')]) }}"
                        style="color: inherit; text-decoration: none; font: inherit;"
                    >
                        {{ __('admin.players.fields.birth_date') }}{!! $sortIndicator('fecha_nacimiento') !!}
                    </a>
                </th>
                <th>
                    <a
                        href="{{ route('jugadores.index', ['search' => $search, 'sort' => 'oculto', 'direction' => $nextDirection('oculto')]) }}"
                        style="color: inherit; text-decoration: none; font: inherit;"
                    >
                        {{ __('admin.common.actions') }}{!! $sortIndicator('oculto') !!}
                    </a>
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse($jugadores as $jugador)
            <tr>
                {{-- 1. FOTO --}}
                <td>
                    <div class="contenedor-escudo admin-photo-frame">
                        @if($jugador->imagen_jugador)
                            <img src="{{ $jugador->imagen_jugador_url }}" alt="{{ __('admin.players.fields.photo') }}" class="admin-photo-fill" onerror="this.onerror=null;this.src='{{ asset('img/club_14.png') }}';">
                        @else
                            <img src="{{ asset('img/club_14.png') }}" alt="{{ __('admin.players.fields.no_photo') }}" class="admin-photo-fill">
                        @endif
                    </div>
                </td>
                {{-- 2. DORSAL --}}
                <td>
                    <span class="dorsal-badge">#{{ $jugador->dorsal }}</span>
                </td>            
                {{-- 3. NOMBRE --}}
                <td><strong>{{ $jugador->nombre }}</strong></td>

                {{-- 3.APELLIDO --}}
                <td><strong>{{ $jugador->apellido }}</strong></td>
                
                {{-- 4. CLUB --}}
                <td>
                    {{ $jugador->equipo->nombre ?? __('admin.players.fields.no_team') }}
                </td>

                {{-- 5. CATEGORÍA (Sacada del modelo Equipo) --}}
                <td>
                    <span class="admin-badge-light">
                        {{ $jugador->equipo->categoria ?? '-' }}
                    </span>
                </td>

                {{-- 6. POSICIÓN --}}
                <td>{{ $jugador->posicion_display }}</td>

                {{-- 4. FECHA DE NACIMIENTO --}}
                <td>
                    {{-- Usamos carbon para poner la fecha en formato día/mes/año --}}
                    {{ $jugador->fecha_nacimiento ? Carbon::parse($jugador->fecha_nacimiento)->format('d/m/Y') : __('admin.players.fields.not_assigned') }}
                </td>
                
                {{-- 7. ACCIONES (ALINEADAS) --}}
                <td class="admin-actions-cell">
                    <div class="admin-row-actions">
                        
                        {{-- Botón Ver --}}
                        <a href="{{ route('jugadores.show', $jugador->id) }}" class="btn-accion admin-view-btn" title="{{ __('admin.players.actions.view') }}">
                            <i class="fas fa-eye"></i>
                        </a>

                        {{-- Botón Editar --}}
                        <a href="{{ route ('jugadores.edit', $jugador->id)}}" class="btn-accion editar admin-no-margin" title="{{ __('admin.players.actions.edit') }}">
                            <i class="fas fa-pen"></i>
                        </a>

                        <form action="{{ route('jugadores.toggle-oculto', $jugador->id) }}" method="POST" class="admin-form-inline admin-form-inline-flex">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn-accion {{ $jugador->oculto ? 'mostrar' : 'ocultar' }} admin-no-margin" title="{{ $jugador->oculto ? __('admin.players.actions.show') : __('admin.players.actions.hide') }}">
                                <i class="fas {{ $jugador->oculto ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                            </button>
                        </form>
                        
                        {{-- Formulario Borrar --}}
                        <form action="{{ route ('jugadores.destroy', $jugador->id)}}" method="POST" onsubmit="return confirm('{{ __('admin.players.actions.confirm_delete') }}')" class="admin-form-inline admin-form-inline-flex">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-accion borrar admin-no-margin" title="{{ __('admin.players.actions.delete') }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>

                    </div>
                </td>
            </tr>
            @empty
            {{-- MENSAJE SI NO HAY JUGADORES --}}
            <tr>
                <td colspan="9" class="admin-empty-cell">
                    <i class="fas fa-info-circle"></i> {{ __('admin.players.empty.index') }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{--Esto es la paginación--}}
    <div class="contenedor-paginacion">
        {{ $jugadores->links() }}
    </div>

</div>
@endsection
