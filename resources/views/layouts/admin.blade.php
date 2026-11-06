<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') | Panel Econofibra JLINK</title>
    @vite(['resources/css/app.css'])
</head>
<body>
    <div class="admin-shell">
        <header class="admin-header">
            <p class="admin-brand">Econofibra JLINK</p>
            @auth
                <nav class="admin-nav" aria-label="Panel">
                    <a href="{{ route('admin.dashboard') }}">Inicio</a>
                    <a href="{{ route('admin.zones.index') }}">Zonas</a>
                    <a href="{{ route('admin.plans.index') }}">Planes</a>
                </nav>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="admin-button">Salir</button>
                </form>
            @endauth
        </header>
        <main class="admin-main">
            @yield('content')
        </main>
    </div>
</body>
</html>
