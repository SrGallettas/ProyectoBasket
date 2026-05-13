@extends('layouts.admin')

@section('title', __('admin.users.meta.edit_title'))

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <x-admin-edit-header
        :titulo="__('admin.users.edit.title')"
        :subtitulo="__('admin.users.edit.subtitle', ['name' => e($usuario->name)])"
        :backRoute="route('usuarios.index')"
    />

    @if ($errors->any())
        <div class="cuadro-errores-admin">
            <strong>{{ __('admin.common.form_error') }}</strong>
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
                    <h3><i class="fas fa-user-gear"></i> {{ __('admin.users.sections.access') }}</h3>

                    <div class="campo-ficha">
                        <label>{{ __('admin.users.fields.name') }}</label>
                        <input type="text" name="name" value="{{ old('name', $usuario->name) }}" class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.users.fields.email') }}</label>
                        <input type="email" name="email" value="{{ old('email', $usuario->email) }}" class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.users.fields.role') }}</label>
                        <select name="role" class="input-ficha input-white">
                            @foreach($roles as $value => $label)
                                <option value="{{ $value }}" {{ old('role', $usuario->role) === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="seccion-form-ficha seccion-deportiva">
                    <h3><i class="fas fa-lock"></i> {{ __('admin.users.fields.password') }}</h3>

                    <div class="campo-ficha">
                        <label>{{ __('admin.users.fields.new_password') }}</label>
                        <input type="password" name="password" class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.users.fields.confirm_new_password') }}</label>
                        <input type="password" name="password_confirmation" class="input-ficha">
                    </div>

                    <p style="color:#666; margin-top:12px;">{{ __('admin.users.edit.password_hint') }}</p>
                </div>
            </div>

            <div class="form-acciones-ficha">
                <a href="{{ route('usuarios.index') }}" class="btn-cancelar-ficha">{{ __('admin.common.cancel') }}</a>
                <button type="submit" class="btn-actualizar-ficha">
                    <i class="fas fa-sync-alt"></i> {{ __('admin.users.edit.submit') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
