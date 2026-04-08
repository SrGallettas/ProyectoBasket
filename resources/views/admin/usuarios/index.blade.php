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

<div class="pizarra-admin">
    <table class="tabla-admin">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Creado</th>
                <th>Acciones</th>
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
