@extends('layouts.admin')

@section('title', __('admin.store.meta.create_title'))

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <header class="header-ficha">
        <div>
            <h2>{{ __('admin.store.create.title') }}</h2>
            <p>{{ __('admin.store.create.subtitle') }}</p>
        </div>
        <a href="{{ route('merchandising.index') }}" class="btn-nuevo" title="{{ __('admin.store.actions.cancel_back') }}">
            <i class="fas fa-arrow-left"></i> {{ __('admin.common.back_to_list') }}
        </a>
    </header>

     {{-- Bloque de errores (Estilo idéntico al de Merchandising) --}}
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
        <form action="{{ route('merchandising.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid-formulario-fichas">
                
                <div class="seccion-form-ficha seccion-producto">
                    <h3><i class="fas fa-tag"></i> {{ __('admin.store.sections.general') }}</h3>
                    <div class="campo-ficha">
                        <label for="idioma_origen">{{ __('admin.store.fields.source_language') }}</label>
                        <select name="idioma_origen" id="idioma_origen" class="input-ficha">
                            <option value="es" {{ old('idioma_origen', 'es') === 'es' ? 'selected' : '' }}>{{ __('admin.store.fields.source_language_es') }}</option>
                            <option value="val" {{ old('idioma_origen') === 'val' ? 'selected' : '' }}>{{ __('admin.store.fields.source_language_val') }}</option>
                        </select>
                    </div>
                    <div class="campo-ficha">
                        <label for="nombre">{{ __('admin.store.fields.product_name_input') }}</label>
                        <input type="text" name="nombre" id="nombre" class="input-ficha" value="{{ old('nombre') }}">
                    </div>
                    <div class="campo-ficha">
                        <label for="descripcion">{{ __('admin.store.fields.description_input') }}</label>
                        <textarea name="descripcion" id="descripcion" rows="4" class="input-ficha">{{ old('descripcion') }}</textarea>
                    </div>
                </div>

                <div class="seccion-form-ficha seccion-logistica">
                    <h3><i class="fas fa-boxes"></i> {{ __('admin.store.sections.details') }}</h3>
                    <div class="campo-ficha">
                        <label for="precio">{{ __('admin.store.fields.price_with_currency') }}</label>
                        <input type="number" name="precio" id="precio" step="0.01" class="input-ficha" value="{{ old('precio') }}">
                    </div>
                    <div class="campo-ficha">
                        <label>{{ __('admin.store.fields.available_sizes') }}</label>
                        <div class="merch-tallas-checkboxes">
                            {{-- Lógica para dejar marcados los checkboxes --}}
                            @foreach(['XS', 'S', 'M', 'L', 'XL', 'Unica', 'Sin talla'] as $talla)
                                <label>
                                    <input type="checkbox" name="talla[]" value="{{ $talla }}" 
                                    {{ is_array(old('talla')) && in_array($talla, old('talla')) ? 'checked' : '' }}> 
                                    {{ $talla }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.store.fields.visibility') }}</label>
                        <div class="checkbox-wrapper checkbox-wrapper-ficha">
                            <input type="checkbox" name="oculto" value="1" {{ old('oculto') ? 'checked' : '' }}>
                            <span>{{ __('admin.store.fields.hide_public') }}</span>
                        </div>
                    </div>
                </div>

                <div class="seccion-foto-ficha">
                    <div class="card-imagen-horizontal">
                        <div class="preview-foto-noticia">
                            <img id="merch-create-preview" src="{{ asset('img/club_14.png') }}" alt="{{ __('admin.store.images.default_alt') }}">
                        </div>

                        <div class="info-subida-noticia">
                            <h3>{{ __('admin.store.images.title') }}</h3>
                            <p>{{ __('admin.store.images.help') }}</p>

                            <label for="imagen_merchan" class="btn-cambiar-foto">
                                <i class="fas fa-camera"></i> {{ __('admin.store.images.select') }}
                            </label>
                            <input type="file" name="imagen_merchan" id="imagen_merchan" class="hidden-file-input" accept="image/*" data-image-input="#merch-create-preview">

                            <span class="nota-subida">{{ __('admin.store.images.note_create') }}</span>
                        </div>
                    </div>
                </div>

            </div> 

            <div class="form-acciones-ficha">
                <a href="{{ route('merchandising.index') }}" class="btn-nuevo" title="{{ __('admin.store.actions.cancel_back') }}">{{ __('admin.common.cancel') }}</a>
                <button type="submit" class="btn-actualizar-ficha" title="{{ __('admin.store.actions.save') }}">{{ __('admin.store.create.submit') }}</button>
            </div>
        </form>
    </div> 
</div>
@endsection
