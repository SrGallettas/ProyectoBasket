@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <x-admin-edit-header
        titulo="Añadir Usuario"
        subtitulo="Crear una nueva cuenta de acceso al panel"
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
        <form action="{{ route('usuarios.store') }}" method="POST">
            @csrf

            <div class="grid-formulario-fichas">
                <div class="seccion-form-ficha seccion-personal">
                    <h3><i class="fas fa-user"></i> Datos de acceso</h3>

                    <div class="campo-ficha">
                        <label>Nombre</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                        <label>Rol</label>
                        <select name="role" class="input-ficha input-white" required>
                            @foreach($roles as $value => $label)
                                <option value="{{ $value }}" {{ old('role', \App\Models\User::ROLE_COACH) === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="seccion-form-ficha seccion-deportiva">
                    <h3><i class="fas fa-lock"></i> Seguridad</h3>

                    <div class="campo-ficha">
                        <label>Contraseña</label>
                        <input type="password" name="password" required class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                        <label>Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" required class="input-ficha">
                    </div>
                </div>
            </div>

            <div class="form-acciones-ficha">
                <a href="{{ route('usuarios.index') }}" class="btn-cancelar-ficha">Cancelar</a>
                <button type="submit" class="btn-actualizar-ficha btn-actualizar-azul">
                    <i class="fas fa-save"></i> CREAR USUARIO
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
