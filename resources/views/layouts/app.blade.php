<!DOCTYPE html>
@php($htmlLocale = app()->getLocale() === 'val' ? 'ca' : str_replace('_', '-', app()->getLocale()))
@php($ogLocale = app()->getLocale() === 'val' ? 'ca_ES' : 'es_ES')
<html lang="{{ $htmlLocale }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Bellreguard CB')</title>
    <meta name="description" content="@yield('meta_description', __('ui.meta_description'))" />
    <link rel="canonical" href="@yield('canonical', url()->current())" />

    <meta property="og:locale" content="{{ $ogLocale }}" />
    <meta property="og:type" content="@yield('og_type', 'website')" />
    <meta property="og:site_name" content="Bellreguard CB" />
    <meta property="og:title" content="@yield('og_title', View::yieldContent('title', 'Bellreguard CB'))" />
    <meta property="og:description" content="@yield('og_description', View::yieldContent('meta_description', __('ui.meta_description')))" />
    <meta property="og:url" content="@yield('og_url', url()->current())" />
    <meta property="og:image" content="@yield('og_image', asset('img/principal-1.png'))" />
    <meta property="og:image:alt" content="@yield('og_image_alt', 'Bellreguard CB')" />

    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="@yield('twitter_title', View::yieldContent('og_title', View::yieldContent('title', 'Bellreguard CB')))" />
    <meta name="twitter:description" content="@yield('twitter_description', View::yieldContent('og_description', View::yieldContent('meta_description', __('ui.meta_description'))))" />
    <meta name="twitter:image" content="@yield('twitter_image', View::yieldContent('og_image', asset('img/principal-1.png')))" />
    <meta name="twitter:image:alt" content="@yield('twitter_image_alt', View::yieldContent('og_image_alt', 'Bellreguard CB'))" />

    <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
    @stack('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="{{ request()->routeIs('basket.inicio') ? 'pagina-inicio' : '' }}">

    @include('partials.nav')

    <main>
        @yield('contenido')
    </main>

    @include('partials.footer')
</body>
</html>
