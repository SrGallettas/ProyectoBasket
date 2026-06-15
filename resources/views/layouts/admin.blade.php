<!DOCTYPE html>
@php($htmlLocale = app()->getLocale() === 'val' ? 'ca' : str_replace('_', '-', app()->getLocale()))
<html lang="{{ $htmlLocale }}">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', __('admin.layout.title'))</title>
    <script>
        (function () {
            const key = 'bb_theme';
            const saved = localStorage.getItem(key);
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = saved || (prefersDark ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
   <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/paginas/admin.css') }}" />
    @stack('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    @php($adminUser = auth()->user())
    @php($currentAdminLocale = app()->getLocale() === 'val' ? 'val' : 'es')
    @php($nextAdminLocale = $currentAdminLocale === 'val' ? 'es' : 'val')
    <div class="contenedor-admin">
        @include('partials.admin.sidebar')

        <div class="admin-mobile-backdrop" id="admin-mobile-backdrop"></div>

        <main class="contenido-admin">
            @include('partials.admin.mobile-header')
            @yield('contenido_admin')
        </main>
    </div>

    @include('partials.admin.scripts')
    @vite('resources/js/app.js')
    @stack('scripts')
</body>
</html>
