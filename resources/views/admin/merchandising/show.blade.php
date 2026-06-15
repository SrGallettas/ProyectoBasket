@extends('layouts.admin')

@section('title', __('admin.store.meta.show_title'))

@section('contenido_admin')
<div class="contenedor-ficha-merchan">
    <x-admin-show-header
        :titulo="__('admin.store.show.title')"
        :subtitulo="__('admin.store.show.subtitle')"
        :editRoute="route('merchandising.edit', $item->id)"
        :deleteRoute="route('merchandising.destroy', $item->id)"
        :indexRoute="route('merchandising.index')"
        :confirmMessage="__('admin.store.actions.confirm_delete_detail')"
        backClass="btn-ficha-volver-secondary"
    />

    <div class="pizarra-show">
        <div class="grid-show-merchan">
            
            {{-- Columna Izquierda: Imagen --}}
            <div class="columna-foto">
                <div class="marco-foto-show">
                    @if($item->imagen_merchan)
                        <img src="{{ $item->imagen_merchan_url }}" alt="{{ $item->nombre }}">
                    @else
                        <img src="{{ asset('img/club_14.png') }}" alt="{{ __('admin.store.images.missing_alt') }}">
                    @endif
                </div>
            </div>

            {{-- Columna Derecha: Información --}}
            <div class="columna-info">
                <div class="bloque-info">
                    <span class="label-ficha">{{ __('admin.store.show.article_name') }}</span>
                    <h3>{{ $item->nombre }}</h3>
                </div>

                <div class="bloque-info-doble">
                    <div class="dato">
                        <span class="label-ficha">{{ __('admin.store.show.official_price') }}</span>
                        <p class="precio-destacado">{{ number_format($item->precio, 2, ',', '.') }}€</p>
                    </div>
                    <div class="dato">
                        <span class="label-ficha">{{ __('admin.store.show.product_id') }}</span>
                        <p>#{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</p>
                    </div>
                </div>

                <div class="bloque-info">
                    <span class="label-ficha">{{ __('admin.store.show.available_sizes') }}</span>
                    <div class="contenedor-badges-tallas">
                        @forelse($tallas as $talla)
                            <span class="badge-talla-show">{{ $talla }}</span>
                        @empty
                            <span class="text-muted">{{ __('admin.store.show.no_sizes') }}</span>
                        @endforelse
                    </div>
                </div>

                <div class="bloque-info">
                    <span class="label-ficha">{{ __('admin.store.show.description') }}</span>
                    <p class="descripcion-texto">
                        {{ $item->descripcion ?? __('admin.store.show.description_fallback') }}
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
