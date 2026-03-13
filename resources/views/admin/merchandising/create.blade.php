@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-edit-jugador">
    <header class="header-ficha">
        <div>
            <h2>Nuevo Producto</h2>
            <p>Introduce los detalles del artículo para la tienda oficial</p>
        </div>
        <a href="{{ route('merchandising.index') }}" class="btn-nuevo" title="Volver al listado de productos">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
    </header>

     {{-- Bloque de errores (Estilo idéntico al de Merchandising) --}}
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
        <form action="{{ route('merchandising.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid-formulario-fichas">
                
                <div class="seccion-form-ficha seccion-producto">
                    <h3><i class="fas fa-tag"></i> Información General</h3>
                    <div class="campo-ficha">
                        <label for="nombre">Nombre del Producto</label>
                        <input type="text" name="nombre" id="nombre" class="input-ficha" value="{{ old('nombre') }}">
                    </div>
                    <div class="campo-ficha">
                        <label for="descripcion">Descripción</label>
                        <textarea name="descripcion" id="descripcion" rows="4" class="input-ficha">{{ old('descripcion') }}</textarea>
                    </div>
                </div>

                <div class="seccion-form-ficha seccion-logistica">
                    <h3><i class="fas fa-boxes"></i> Detalles y Precio</h3>
                    <div class="campo-ficha">
                        <label for="precio">Precio (€)</label>
                        <input type="number" name="precio" id="precio" step="0.01" class="input-ficha" value="{{ old('precio') }}">
                    </div>
                    <div class="campo-ficha">
                        <label>Tallas Disponibles</label>
                        <div class="merch-tallas-checkboxes">
                            {{-- Lógica para dejar marcados los checkboxes --}}
                            @foreach(['XS', 'S', 'M', 'L', 'XL', 'Unica', 'Sin Talla'] as $talla)
                                <label>
                                    <input type="checkbox" name="talla[]" value="{{ $talla }}" 
                                    {{ is_array(old('talla')) && in_array($talla, old('talla')) ? 'checked' : '' }}> 
                                    {{ $talla }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="campo-ficha">
                        <label>Visibilidad en la web</label>
                        <div class="checkbox-wrapper checkbox-wrapper-ficha">
                            <input type="checkbox" name="oculto" value="1" {{ old('oculto') ? 'checked' : '' }}>
                            <span>Ocultar producto de la web pública</span>
                        </div>
                    </div>
                </div>

                <div class="seccion-foto-ficha">
                    <div class="card-imagen-horizontal">
                        <div class="preview-foto-noticia">
                            <img src="{{ asset('img/basket.jpeg') }}" alt="Foto por defecto">
                        </div>

                        <div class="info-subida-noticia">
                            <h3>Imagen del Producto</h3>
                            <p>Para que luzca bien, usa una imagen cuadrada o vertical.</p>

                            <label for="imagen_merchan" class="btn-cambiar-foto">
                                <i class="fas fa-camera"></i> Seleccionar fotografía
                            </label>
                            <input type="file" name="imagen_merchan" id="imagen_merchan" class="hidden-file-input">

                            <span class="nota-subida">La foto es obligatoria y se guardará en public/merchandising.</span>
                        </div>
                    </div>
                </div>

            </div> 

            <div class="form-acciones-ficha">
                <a href="{{ route('merchandising.index') }}" class="btn-cancelar-ficha" title="Cancelar y volver al listado de productos">Cancelar</a>
                <button type="submit" class="btn-actualizar-ficha" title="Guardar producto">Guardar Producto</button>
            </div>
        </form>
    </div> 
</div>
@endsection
