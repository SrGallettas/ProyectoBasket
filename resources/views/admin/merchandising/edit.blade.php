@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-edit-merchan">
    <header class="header-ficha">
        <div>
            <h2>Editar Producto: {{ $item->nombre }}</h2>
            <p>Introduce los detalles del artículo para la tienda oficial</p>
        </div>
        <a href="{{ route('merchandising.index') }}" class="btn-nuevo">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
    </header>

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
                </div>

                {{-- SECCIÓN DE FOTO UNIFICADA Y CENTRADA --}}
                <div class="seccion-foto-ficha">
                    <div class="card-imagen-horizontal">
                        {{-- Lado izquierdo: La miniatura --}}
                        <div class="preview-foto-noticia">
                            @if($item->imagen_merchan)
                                <img src="{{ asset('merchandising/' . $item->imagen_merchan) }}" alt="Foto actual">
                            @else
                                <img src="{{ asset('img/basket.jpeg') }}" alt="Foto por defecto">
                            @endif
                        </div>

                        {{-- Lado derecho: Textos e Input --}}
                        <div class="info-subida-noticia">
                            <h3>Imagen del Producto</h3>
                            <p>Para que luzca bien, usa una imagen cuadrada o vertical.</p>
                            
                            <label for="imagen_merchan" class="btn-cambiar-foto">
                                <i class="fas fa-camera"></i> Cambiar fotografía
                            </label>
                            <input type="file" name="imagen_merchan" id="imagen_merchan" style="display: none;">
                            
                            <span class="nota-subida">Si no seleccionas ninguna, se mantendrá la actual.</span>
                        </div>
                    </div>
                </div>

            </div> 

            <div class="form-acciones-ficha">
                <a href="{{ route('merchandising.index') }}" class="btn-cancelar-ficha">Cancelar</a>
                <button type="submit" class="btn-actualizar-ficha">Guardar Cambios</button>
            </div>
        </form>
    </div> 
</div> 
@endsection