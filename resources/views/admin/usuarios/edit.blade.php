@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <x-admin-edit-header
        titulo="Editar Usuario"
        :subtitulo="'Actualizando la cuenta de <strong>' . e($usuario->name) . '</strong>'"
        :backRoute="route('usuarios.index')"
    />

    @if ($errors->any())
        <div class="cuadro-errores-admin">
            <strong>¡Vaya! Algo ha ido mal:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="pizarra-ficha">
        <form action="{{ route('usuarios.update', $usuario) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid-formulario-fichas">
                <div class="seccion-form-ficha seccion-personal">
                    <h3><i class="fas fa-user-gear"></i> Datos de acceso</h3>

                    <div class="campo-ficha">
                        <label>Nombre</label>
                        <input type="text" name="name" value="{{ old('name', $usuario->name) }}" required class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ old('email', $usuario->email) }}" required class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                        <label>Rol</label>
                        <select name="role" class="input-ficha input-white" required>
                            @foreach($roles as $value => $label)
                                <option value="{{ $value }}" {{ old('role', $usuario->role) === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="seccion-form-ficha seccion-deportiva">
                    <h3><i class="fas fa-lock"></i> Contraseña</h3>

                    <div class="campo-ficha">
                        <label>Nueva contraseña</label>
                        <input type="password" name="password" class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                        <label>Confirmar nueva contraseña</label>
                        <input type="password" name="password_confirmation" class="input-ficha">
                    </div>

                    <p style="color:#666; margin-top:12px;">Deja estos campos vacíos si no quieres cambiar la contraseña.</p>
                </div>
            </div>

            <div class="form-acciones-ficha">
                <a href="{{ route('usuarios.index') }}" class="btn-cancelar-ficha">Cancelar</a>
                <button type="submit" class="btn-actualizar-ficha">
                    <i class="fas fa-sync-alt"></i> ACTUALIZAR USUARIO
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
