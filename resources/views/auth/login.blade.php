<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script>
      document.documentElement.setAttribute('data-theme', localStorage.getItem('smart-ahorro-theme') || 'light');
    </script>
    <title>Iniciar sesión - Smart Ahorro</title>
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
  </head>
  <body>
    <div class="auth-theme-bar">
      <button type="button" class="theme-toggle" data-theme-toggle aria-label="Cambiar tema">
        <i class="fa-solid fa-moon" data-theme-icon></i>
        <span data-theme-text>Modo oscuro</span>
      </button>
    </div>

    <div class="auth-shell">
      <div class="auth-panel">
        <div class="auth-box">
          <div class="auth-brand">
            <img src="{{ asset('img/logo-pagina.png') }}" alt="logo-pagina" />
            <h2>Smart <span>Ahorro</span></h2>
            <p>Crea tu futuro, alcanza tus metas</p>
          </div>

          <h1>Iniciar sesión</h1>
          <p class="subtitulo">Accede a tu cuenta para continuar</p>

          <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="campo">
              <label for="email">Correo electrónico</label>
              <input type="email" id="email" name="email" placeholder="Correo electrónico" value="{{ old('email') }}" required />
            </div>

            <div class="campo campo-password">
              <label for="password">Contraseña</label>
              <input type="password" id="password" name="password" placeholder="Contraseña" required />
              <i class="bi bi-eye-fill toggle-password" data-target="password"></i>
            </div>

            <a href="{{ route('password.request') }}" class="auth-forgot">¿Olvidaste la contraseña?</a>

            @if ($errors->any())
              <x-alerta tipo="error">
                @foreach ($errors->all() as $error) {{ $error }}<br /> @endforeach
              </x-alerta>
            @endif

            <button type="submit" class="btn-primario" style="width:100%;">Iniciar sesión</button>
          </form>

          <a href="{{ route('register') }}" class="auth-link-secundario">¿No tienes cuenta? <strong>Regístrate</strong></a>
        </div>
      </div>

      <div class="auth-image">
        <img src="{{ asset('img/imagen-principal.png') }}" alt="Aplicación Smart ahorro" />
      </div>
    </div>

    <script src="{{ asset('js/theme-and-currency.js') }}"></script>
    <script>
      document.querySelectorAll('.toggle-password').forEach((icon) => {
        icon.addEventListener('click', () => {
          const input = document.getElementById(icon.dataset.target);
          const showing = input.type === 'text';
          input.type = showing ? 'password' : 'text';
          icon.classList.toggle('bi-eye-fill', showing);
          icon.classList.toggle('bi-eye-slash-fill', !showing);
        });
      });
    </script>
  </body>
</html>