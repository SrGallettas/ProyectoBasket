<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin - Bellreguard CB</title>
   <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/paginas/admin.css') }}" />
    @stack('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="contenedor-admin">
        <aside class="sidebar-admin">
            <div class="admin-perfil">
                <img src="{{ asset('img/basket.jpeg') }}" alt="Logo Admin">
                <div class="admin-info">
                    <h4>Panel Admin</h4>
                    <span>Bellreguard CB</span>
                </div>
            </div>

            <nav class="menu-admin">
                <a href="{{ route('dashboard') }}" class="item-admin {{ request()->routeIs('dashboard') ? 'activo' : '' }}">
                    <i class="fas fa-home"></i> Inicio
                </a>

                <a href="{{ route('jugadores.index') }}" class="item-admin {{ request()->is('admin/jugadores*') ? 'activo' : '' }}">
                    <i class="fas fa-users"></i> Jugadores
                </a>

                <a href="{{ route('equipos.index') }}" class="item-admin {{ request()->is('admin/equipos*') ? 'activo' : '' }}">
                    <i class="fas fa-tshirt"></i> Equipos
                </a>

                <a href="{{ route('noticias.index') }}" class="item-admin {{ request()->is('admin/noticias*') ? 'activo' : '' }}">
                    <i class="fas fa-newspaper"></i> Noticias
                </a>

                <a href="{{ route('merchandising.index') }}" class="item-admin {{ request()->is('admin/merchandising*') ? 'activo' : '' }}">
                    <i class="fas fa-shopping-bag"></i> Merchandising
                </a>

                <a href="{{ route('partidos.index') }}" class="item-admin {{ request()->is('admin/partidos*') ? 'activo' : '' }}">
                    <i class="fas fa-calendar-alt"></i> Partidos
                </a>

                <a href="{{ route('galerias.index') }}" class="item-admin {{ request()->is('admin/galerias*') ? 'activo' : '' }}">
                    <i class="fas fa-images"></i> Galerías
                </a>

                <div class="separador-admin"></div>
    
                <a href="{{ url('/') }}" class="item-admin volver-web">
                    <i class="fas fa-arrow-left"></i> Volver a la Web
                </a>

                
</nav>
        </aside>

        <main class="contenido-admin">
            @yield('contenido_admin')
        </main>
    </div>
</body>
</html>
