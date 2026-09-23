<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registro - Smart Ahorro</title>
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
  </head>
  <body>
    <div class="auth-shell">
      <div class="auth-panel">
        <div class="auth-box">
          <div class="auth-brand">
            <img src="{{ asset('img/logo-pagina.png') }}" alt="logo-pagina" />
            <h2>Smart <span>Ahorro</span></h2>
            <p>Crea tu futuro, alcanza tus metas</p>
          </div>

          <h1>Registro</h1>
          <p class="subtitulo">Regístrate para acceder a tu cuenta</p>

          <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="campo">
              <label for="name">Nombre</label>
              <input type="text" id="name" name="name" placeholder="Nombre" value="{{ old('name') }}" required autocomplete="name" />
            </div>

            <div class="campo">
              <label for="email">Correo electrónico</label>
              <input type="email" id="email" name="email" placeholder="Correo electrónico" value="{{ old('email') }}" required autocomplete="email" />
            </div>

            <div class="campo campo-password">
              <label for="password">Contraseña</label>
              <input type="password" id="password" name="password" placeholder="Contraseña" required />
              <i class="bi bi-eye-fill toggle-password" data-target="password"></i>
            </div>

            <div class="campo campo-password">
              <label for="password_confirmation">Confirmar contraseña</label>
              <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirmar contraseña" required />
              <i class="bi bi-eye-fill toggle-password" data-target="password_confirmation"></i>
            </div>

            @if ($errors->any())
              <x-alerta tipo="error">
                @foreach ($errors->all() as $error) {{ $error }}<br /> @endforeach
              </x-alerta>
            @endif

            <button type="submit" class="btn-primario" style="width:100%;">Registrarse</button>
          </form>

          <a href="{{ route('login') }}" class="auth-link-secundario">¿Ya tienes cuenta? <strong>Iniciar sesión</strong></a>
        </div>
      </div>

      <div class="auth-image">
        <img src="{{ asset('img/imagen-principal.png') }}" alt="Aplicación Smart ahorro" />
      </div>
    </div>

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
