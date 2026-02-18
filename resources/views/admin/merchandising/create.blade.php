@extends('layouts.admin')

@section('contenido_admin')
<div class="contenedor-edit-merchan">
    <header class="header-ficha">
        <div>
            <h2>Nuevo Producto</h2>
            <p>Introduce los detalles del artículo para la tienda oficial</p>
        </div>
        <a href="{{ route('merchandising.index') }}" class="btn-nuevo">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
    </header>

    @if ($errors->any())
    <div style="background: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <strong>¡Vaya! Algo ha ido mal:</strong>
        <ul style="margin-top: 5px; margin-bottom: 0; list-style: none; padding-left: 0;">
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
                        <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 10px;">
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
                </div>

                <div class="seccion-foto-ficha">
                    <div class="info-subida-ficha">
                        <label>Imagen del Producto</label>
                        <input type="file" name="imagen_merchan">
                    </div>
                </div>

            </div> 

            <div class="form-acciones-ficha">
                <a href="{{ route('merchandising.index') }}" class="btn-cancelar-ficha">Cancelar</a>
                <button type="submit" class="btn-actualizar-ficha">Guardar Producto</button>
            </div>
        </form>
    </div> 
</div> 
@endsection