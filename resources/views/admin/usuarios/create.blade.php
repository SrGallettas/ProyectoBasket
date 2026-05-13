@extends('layouts.admin')

@section('title', __('admin.users.meta.create_title'))

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <x-admin-edit-header
        :titulo="__('admin.users.create.title')"
        :subtitulo="__('admin.users.create.subtitle')"
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
        <form action="{{ route('usuarios.store') }}" method="POST">
            @csrf

            <div class="grid-formulario-fichas">
                <div class="seccion-form-ficha seccion-personal">
                    <h3><i class="fas fa-user"></i> {{ __('admin.users.sections.access') }}</h3>

                    <div class="campo-ficha">
                        <label>{{ __('admin.users.fields.name') }}</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.users.fields.email') }}</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.users.fields.role') }}</label>
                        <select name="role" class="input-ficha input-white">
                            @foreach($roles as $value => $label)
                                <option value="{{ $value }}" {{ old('role', \App\Models\User::ROLE_COACH) === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="seccion-form-ficha seccion-deportiva">
                    <h3><i class="fas fa-lock"></i> {{ __('admin.users.sections.security') }}</h3>

                    <div class="campo-ficha">
                        <label>{{ __('admin.users.fields.password') }}</label>
                        <input type="password" name="password" class="input-ficha">
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.users.fields.confirm_password') }}</label>
                        <input type="password" name="password_confirmation" class="input-ficha">
                    </div>
                </div>
            </div>

            <div class="form-acciones-ficha">
                <a href="{{ route('usuarios.index') }}" class="btn-cancelar-ficha">{{ __('admin.common.cancel') }}</a>
                <button type="submit" class="btn-actualizar-ficha btn-actualizar-azul">
                    <i class="fas fa-save"></i> {{ __('admin.users.create.submit') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
