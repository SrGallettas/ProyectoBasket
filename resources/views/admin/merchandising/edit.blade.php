@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <x-admin-edit-header
        :titulo="'Editar Producto: ' . $item->nombre"
        subtitulo="Introduce los detalles del artículo para la tienda oficial"
        :backRoute="route('merchandising.index')"
        backClass="btn-nuevo"
    />

    @if ($errors->any())
    <div class="alerta-error">
        <strong>¡Vaya! Algo ha ido mal:</strong>
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
                    <h3><i class="fas fa-tag"></i> Información General</h3>
                    <div class="campo-ficha">
                        <label for="nombre">Nombre del Producto</label>
                        <input type="text" name="nombre" id="nombre" class="input-ficha" value="{{ old('nombre', $item->nombre) }}">
                    </div>
                    <div class="campo-ficha">
                        <label for="descripcion">Descripción</label>
                        <textarea name="descripcion" id="descripcion" rows="4" class="input-ficha">{{ old('descripcion', $item->descripcion) }}</textarea>
                    </div>
                </div>

                <div class="seccion-form-ficha seccion-logistica">
                    <h3><i class="fas fa-boxes"></i> Detalles y Precio</h3>
                    <div class="campo-ficha">
                        <label for="precio">Precio (€)</label>
                        <input type="number" name="precio" id="precio" step="0.01" class="input-ficha" value="{{ old('precio', $item->precio) }}">
                    </div>
                    <div class="campo-ficha">
                        <label>Tallas Disponibles</label>
                        <div class="contenedor-checkboxes">
                            @foreach(['XS', 'S', 'M', 'L', 'XL', 'Unica'] as $talla)
                                <label>
                                    <input type="checkbox" name="talla[]" value="{{ $talla }}" 
                                    {{ in_array($talla, old('talla', $tallasSeleccionadas)) ? 'checked' : '' }}> 
                                    {{ $talla }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="campo-ficha">
                        <label>Visibilidad en la web</label>
                        <div class="checkbox-wrapper checkbox-wrapper-ficha">
                            <input type="checkbox" name="oculto" value="1" {{ old('oculto', $item->oculto) ? 'checked' : '' }}>
                            <span>Ocultar producto de la web pública</span>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN DE FOTO UNIFICADA Y CENTRADA --}}
                <div class="seccion-foto-ficha">
                    <div class="card-imagen-horizontal">
                        {{-- Lado izquierdo: La miniatura --}}
                        <div class="preview-foto-noticia">
                            @if($item->imagen_merchan)
                                <img id="merch-edit-preview" src="{{ $item->imagen_merchan_url }}" alt="Foto actual">
                            @else
                                <img id="merch-edit-preview" src="{{ asset('img/basket.jpeg') }}" alt="Foto por defecto">
                            @endif
                        </div>

                        {{-- Lado derecho: Textos e Input --}}
                        <div class="info-subida-noticia">
                            <h3>Imagen del Producto</h3>
                            <p>Para que luzca bien, usa una imagen cuadrada o vertical.</p>
                            
                            <label for="imagen_merchan" class="btn-cambiar-foto">
                                <i class="fas fa-camera"></i> Cambiar fotografía
                            </label>
                            <input type="file" name="imagen_merchan" id="imagen_merchan" class="hidden-file-input" accept="image/*" data-image-input="#merch-edit-preview">
                            
                            <span class="nota-subida">Si no seleccionas ninguna, se mantendrá la actual.</span>
                        </div>
                    </div>
                </div>

            </div> 

            <div class="form-acciones-ficha">
                <a href="{{ route('merchandising.index') }}" class="btn-cancelar-ficha" title="Cancelar y volver al listado de productos">Cancelar</a>
                <button type="submit" class="btn-actualizar-ficha" title="Actualizar producto">
                    <i class="fas fa-sync-alt"></i> ACTUALIZAR FICHA
                </button>
            </div>
        </form>
    </div> 
</div>
@endsection
