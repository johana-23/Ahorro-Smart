<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Smart Ahorro')</title>
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
          <a href="{{ route('ingresos.index') }}" class="{{ request()->routeIs('ingresos.*') ? 'active' : '' }}">
            <i class="fa-solid fa-circle-arrow-up"></i> Ingresos
          </a>
          <a href="{{ route('gastos.index') }}" class="{{ request()->routeIs('gastos.*') ? 'active' : '' }}">
            <i class="fa-solid fa-circle-arrow-down"></i> Gastos
          </a>
          <a href="{{ route('metas.index') }}" class="{{ request()->routeIs('metas.*') ? 'active' : '' }}">
            <i class="fa-solid fa-bullseye"></i> Metas de ahorro
          </a>
          <a href="{{ route('reportes.index') }}" class="{{ request()->routeIs('reportes.*') ? 'active' : '' }}">
            <i class="fa-solid fa-chart-column"></i> Reportes
          </a>
          <a href="{{ route('historial.index') }}" class="{{ request()->routeIs('historial.*') ? 'active' : '' }}">
            <i class="fa-regular fa-clock"></i> Historial</a>
        </nav>
      </aside>

      <main class="content">
        <header class="header">
          <div>
            <h1>@yield('page-title')</h1>
            <p>@yield('page-subtitle')</p>
          </div>

          <div class="usuario">
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
  </body>
</html>
