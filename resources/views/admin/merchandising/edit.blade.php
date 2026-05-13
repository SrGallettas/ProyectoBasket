@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <x-admin-edit-header
        :titulo="__('admin.store.edit.title', ['name' => $item->nombre])"
        :subtitulo="__('admin.store.edit.subtitle')"
        :backRoute="route('merchandising.index')"
        backClass="btn-nuevo"
    />

    @if ($errors->any())
    <div class="alerta-error">
        <strong>{{ __('admin.common.form_error') }}</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="pizarra-ficha">
        <form action="{{ route('merchandising.update', $item->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid-formulario-fichas">
                
                <div class="seccion-form-ficha seccion-producto">
                    <h3><i class="fas fa-tag"></i> {{ __('admin.store.sections.general') }}</h3>
                    <div class="campo-ficha">
                        <label for="nombre">{{ __('admin.store.fields.product_name') }}</label>
                        <input type="text" name="nombre" id="nombre" class="input-ficha" value="{{ old('nombre', $item->nombre) }}">
                    </div>
                    <div class="campo-ficha">
                        <label for="descripcion">{{ __('admin.store.fields.description') }}</label>
                        <textarea name="descripcion" id="descripcion" rows="4" class="input-ficha">{{ old('descripcion', $item->descripcion) }}</textarea>
                    </div>
                </div>

                <div class="seccion-form-ficha seccion-logistica">
                    <h3><i class="fas fa-boxes"></i> {{ __('admin.store.sections.details') }}</h3>
                    <div class="campo-ficha">
                        <label for="precio">{{ __('admin.store.fields.price_with_currency') }}</label>
                        <input type="number" name="precio" id="precio" step="0.01" class="input-ficha" value="{{ old('precio', $item->precio) }}">
                    </div>
                    <div class="campo-ficha">
                        <label>{{ __('admin.store.fields.available_sizes') }}</label>
                        <div class="contenedor-checkboxes">
                            @foreach(['XS', 'S', 'M', 'L', 'XL', 'Unica', 'Sin talla'] as $talla)
                                <label>
                                    <input type="checkbox" name="talla[]" value="{{ $talla }}" 
                                    {{ in_array($talla, old('talla', $tallasSeleccionadas)) ? 'checked' : '' }}> 
                                    {{ $talla }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="campo-ficha">
                        <label>{{ __('admin.store.fields.visibility') }}</label>
                        <div class="checkbox-wrapper checkbox-wrapper-ficha">
                            <input type="checkbox" name="oculto" value="1" {{ old('oculto', $item->oculto) ? 'checked' : '' }}>
                            <span>{{ __('admin.store.fields.hide_public') }}</span>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN DE FOTO UNIFICADA Y CENTRADA --}}
                <div class="seccion-foto-ficha">
                    <div class="card-imagen-horizontal">
                        {{-- Lado izquierdo: La miniatura --}}
                        <div class="preview-foto-noticia">
                            @if($item->imagen_merchan)
                                <img id="merch-edit-preview" src="{{ $item->imagen_merchan_url }}" alt="{{ __('admin.store.images.current_alt') }}">
                            @else
                                <img id="merch-edit-preview" src="{{ asset('img/club_14.png') }}" alt="{{ __('admin.store.images.default_alt') }}">
                            @endif
                        </div>

                        {{-- Lado derecho: Textos e Input --}}
                        <div class="info-subida-noticia">
                            <h3>{{ __('admin.store.images.title') }}</h3>
                            <p>{{ __('admin.store.images.help') }}</p>
                            
                            <label for="imagen_merchan" class="btn-cambiar-foto">
                                <i class="fas fa-camera"></i> {{ __('admin.store.images.change') }}
                            </label>
                            <input type="file" name="imagen_merchan" id="imagen_merchan" class="hidden-file-input" accept="image/*" data-image-input="#merch-edit-preview">
                            
                            <span class="nota-subida">{{ __('admin.store.images.note_edit') }}</span>
                        </div>
                    </div>
                </div>

            </div> 

            <div class="form-acciones-ficha">
                <a href="{{ route('merchandising.index') }}" class="btn-cancelar-ficha" title="{{ __('admin.store.actions.cancel_back') }}">{{ __('admin.common.cancel') }}</a>
                <button type="submit" class="btn-actualizar-ficha" title="{{ __('admin.store.actions.update') }}">
                    <i class="fas fa-sync-alt"></i> {{ __('admin.store.edit.submit') }}
                </button>
            </div>
        </form>
    </div> 
</div>
@endsection
