<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Smart Ahorro')</title>
    <script>
      (() => {
        const savedTheme = localStorage.getItem('smart-ahorro-theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
      })();
    </script>
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}" />
    @yield('extra-css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
  </head>

  <body>
    <div class="dashboard">
      <aside class="sidebar">
        <div class="logo">
          <img src="{{ asset('img/logo-pagina.png') }}" alt="logo-pagina" />
          <div>
            <h2>Smart Ahorro</h2>
            <p>Crea tu futuro, alcanza tus metas</p>
          </div>
        </div>

        <nav>
          <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fa-solid fa-house"></i> Dashboard
          </a>
          <a href="{{ route('perfil.edit') }}" class="{{ request()->routeIs('perfil.*') ? 'active' : '' }}">
            <i class="fa-regular fa-user"></i> Mi Perfil
          </a>
          <a href="{{ route('movimientos.index') }}" class="{{ request()->routeIs('movimientos.*', 'ingresos.*', 'gastos.*') ? 'active' : '' }}">
            <i class="fa-solid fa-right-left"></i> Movimientos
          </a>
          <a href="{{ route('metas.index') }}" class="{{ request()->routeIs('metas.*') ? 'active' : '' }}">
            <i class="fa-solid fa-bullseye"></i> Metas de ahorro
          </a>
          <a href="{{ route('reportes.index') }}" class="{{ request()->routeIs('reportes.*') ? 'active' : '' }}">
            <i class="fa-solid fa-chart-column"></i> Reportes
          </a>
          <a href="{{ route('historial.index') }}" class="{{ request()->routeIs('historial.*') ? 'active' : '' }}">
            <i class="fa-regular fa-clock"></i> Historial
          </a>
        </nav>
      </aside>

      <main class="content">
        <header class="header">
          <div>
            <h1>@yield('page-title')</h1>
            <p>@yield('page-subtitle')</p>
          </div>

          <div class="usuario">
            <button type="button" class="theme-toggle" data-theme-toggle aria-label="Cambiar tema">
              <i class="fa-solid fa-moon" data-theme-icon></i>
              <span data-theme-text>Modo oscuro</span>
            </button>
            <a href="{{ route('notificaciones.index') }}" style="color:var(--texto-secundario);"><i class="fa-solid fa-bell"></i></a>
            <div class="perfil">
              <i class="fa-solid fa-user"></i>
              <div>
                <h3>{{ Auth::user()->name }}</h3>
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button type="submit">Cerrar sesión</button>
                </form>
              </div>
            </div>
          </div>
        </header>

        @if (session('status'))
          <x-alerta tipo="exito">{{ session('status') }}</x-alerta>
        @endif

        @yield('content')
      </main>
    </div>

    <script src="{{ asset('js/theme-and-currency.js') }}"></script>
  </body>
</html>