@extends('layouts.admin')

@section('contenido_admin')
<x-admin-header
    titulo="Gestión de Usuarios"
    subtitulo="Cuentas internas del panel y sus roles"
    :rutaNuevo="route('usuarios.create')"
    textoNuevo="Añadir Usuario"
    :rutaIndex="route('usuarios.index')"
/>

@if(session('mensaje'))
    <div class="admin-alert-success">
        <i class="fas fa-check-circle"></i> {{ session('mensaje') }}
    </div>
@endif

@if($errors->any())
    <div class="cuadro-errores-admin">
        <strong>Hay algo que revisar:</strong>
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
                <th><a href="{{ route('usuarios.index', ['search' => $search, 'sort' => 'name', 'direction' => $nextDirection('name')]) }}" style="color: inherit; text-decoration: none; font: inherit;">Nombre{!! $sortIndicator('name') !!}</a></th>
                <th><a href="{{ route('usuarios.index', ['search' => $search, 'sort' => 'email', 'direction' => $nextDirection('email')]) }}" style="color: inherit; text-decoration: none; font: inherit;">Email{!! $sortIndicator('email') !!}</a></th>
                <th><a href="{{ route('usuarios.index', ['search' => $search, 'sort' => 'role', 'direction' => $nextDirection('role')]) }}" style="color: inherit; text-decoration: none; font: inherit;">Rol{!! $sortIndicator('role') !!}</a></th>
                <th><a href="{{ route('usuarios.index', ['search' => $search, 'sort' => 'created_at', 'direction' => $nextDirection('created_at')]) }}" style="color: inherit; text-decoration: none; font: inherit;">Creado{!! $sortIndicator('created_at') !!}</a></th>
                <th><a href="{{ route('usuarios.index', ['search' => $search, 'sort' => 'created_at', 'direction' => $nextDirection('created_at')]) }}" style="color: inherit; text-decoration: none; font: inherit;">Acciones{!! $sortIndicator('created_at') !!}</a></th>
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
                            <a href="{{ route('usuarios.edit', $usuario) }}" class="btn-accion editar admin-no-margin" title="Editar usuario">
                                <i class="fas fa-pen"></i>
                            </a>

                            <form action="{{ route('usuarios.destroy', $usuario) }}" method="POST" onsubmit="return confirm('¿Eliminar usuario?')" class="admin-form-inline admin-form-inline-flex">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-accion borrar admin-no-margin" title="Eliminar usuario">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="admin-empty-cell">
                        <i class="fas fa-info-circle"></i> No se han encontrado usuarios.
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
