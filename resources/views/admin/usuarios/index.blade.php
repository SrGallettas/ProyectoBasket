@extends('layouts.admin')

@section('title', __('admin.users.meta.index_title'))

@section('contenido_admin')
<x-admin-header
    :titulo="__('admin.users.index.title')"
    :subtitulo="__('admin.users.index.subtitle')"
    :rutaNuevo="route('usuarios.create')"
    :textoNuevo="__('admin.users.index.new')"
    :rutaIndex="route('usuarios.index')"
/>

@if(session('mensaje'))
    <div class="admin-alert-success">
        <i class="fas fa-check-circle"></i> {{ session('mensaje') }}
    </div>
@endif

@if($errors->any())
    <div class="cuadro-errores-admin">
        <strong>{{ __('admin.common.review_errors') }}</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    // Alterna asc/desc al volver a pulsar la misma cabecera.
    $nextDirection = function (string $column) use ($sort, $direction) {
        return $sort === $column && $direction === 'asc' ? 'desc' : 'asc';
    };

    // La flecha solo aparece si la vista ya tiene un sort activo en la URL.
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
                <th><a href="{{ route('usuarios.index', ['search' => $search, 'sort' => 'name', 'direction' => $nextDirection('name')]) }}" style="color: inherit; text-decoration: none; font: inherit;">{{ __('admin.users.fields.name') }}{!! $sortIndicator('name') !!}</a></th>
                <th><a href="{{ route('usuarios.index', ['search' => $search, 'sort' => 'email', 'direction' => $nextDirection('email')]) }}" style="color: inherit; text-decoration: none; font: inherit;">Email{!! $sortIndicator('email') !!}</a></th>
                <th><a href="{{ route('usuarios.index', ['search' => $search, 'sort' => 'role', 'direction' => $nextDirection('role')]) }}" style="color: inherit; text-decoration: none; font: inherit;">{{ __('admin.users.fields.role') }}{!! $sortIndicator('role') !!}</a></th>
                <th><a href="{{ route('usuarios.index', ['search' => $search, 'sort' => 'created_at', 'direction' => $nextDirection('created_at')]) }}" style="color: inherit; text-decoration: none; font: inherit;">{{ __('admin.users.fields.created') }}{!! $sortIndicator('created_at') !!}</a></th>
                <th><a href="{{ route('usuarios.index', ['search' => $search, 'sort' => 'created_at', 'direction' => $nextDirection('created_at')]) }}" style="color: inherit; text-decoration: none; font: inherit;">{{ __('admin.common.actions') }}{!! $sortIndicator('created_at') !!}</a></th>
            </tr>
        </thead>
        <tbody>
            @forelse($usuarios as $usuario)
                <tr>
                    <td><strong>{{ $usuario->name }}</strong></td>
                    <td>{{ $usuario->email }}</td>
                    <td>{{ \App\Models\User::ROLES[$usuario->role] ?? $usuario->role }}</td>
                    <td>{{ optional($usuario->created_at)->format('d/m/Y H:i') }}</td>
                    <td class="admin-actions-cell">
                        <div class="admin-row-actions">
                            <a href="{{ route('usuarios.edit', $usuario) }}" class="btn-accion editar admin-no-margin" title="{{ __('admin.users.actions.edit') }}">
                                <i class="fas fa-pen"></i>
                            </a>

                            <form action="{{ route('usuarios.destroy', $usuario) }}" method="POST" onsubmit="return confirm('{{ __('admin.users.actions.confirm_delete') }}')" class="admin-form-inline admin-form-inline-flex">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-accion borrar admin-no-margin" title="{{ __('admin.users.actions.delete') }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="admin-empty-cell">
                        <i class="fas fa-info-circle"></i> {{ __('admin.users.empty.index') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="contenedor-paginacion">
        {{ $usuarios->links() }}
    </div>
</div>
@endsection
