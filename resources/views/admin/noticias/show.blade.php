@extends('layouts.admin')

@section('title', __('admin.news.meta.show_title'))

@section('contenido_admin')
<div class="contenedor-ficha-noticia"> {{-- Ajustado a tu CSS --}}
    <x-admin-show-header
        :titulo="__('admin.news.show.title')"
        :subtitulo="__('admin.news.show.subtitle')"
        :editRoute="route('noticias.edit', $noticia->id)"
        :deleteRoute="route('noticias.destroy', $noticia->id)"
        :indexRoute="route('noticias.index')"
        :confirmMessage="__('admin.news.actions.confirm_delete_detail')"
    />

    <div class="pizarra-ficha">
        <div class="noticia-hero-container">
            <img src="{{ $noticia->imagen_noticia_url }}" class="noticia-img-main" alt="{{ $noticia->titulo }}">
        </div>
        
        <h1 class="noticia-view-title">{{ $noticia->titulo }}</h1>

        {{-- NUEVO: Bloque de Autor y Fecha --}}
        <div class="noticia-view-footer noticia-view-footer-compact">
            <span class="meta-item"><i class="fas fa-user-pen"></i> <strong>{{ __('admin.news.fields.author') }}:</strong> {{ $noticia->autor }}</span>
            <span class="meta-item"><i class="fas fa-calendar-check"></i> <strong>{{ __('admin.news.show.published') }}:</strong> {{ $noticia->created_at->format('d/m/Y') }}</span>
        </div>
        
        <p class="noticia-view-subtitle">{{ $noticia->subtitulo }}</p>
        
        <div class="noticia-view-body">
            {{-- Sirve para que los saltos de línea (los "Enter") se respeten y el texto no se vea como un bloque pegado.--}}
            {{-- e(...) (Seguridad) --}}
            {!! nl2br(e($noticia->contenido)) !!}
        </div>
    </div>
</div>
@endsection
