<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nueva contraseña - Smart Ahorro</title>
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
          </div>

          <h1>Nueva contraseña</h1>
          <p class="subtitulo">Define tu nueva contraseña para continuar.</p>

          <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}" />

            <div class="campo">
              <label for="email">Correo electrónico</label>
              <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required />
            </div>

            <div class="campo campo-password">
              <label for="password">Nueva contraseña</label>
              <input type="password" id="password" name="password" placeholder="Nueva contraseña" required />
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

            <button type="submit" class="btn-primario" style="width:100%;">Restablecer contraseña</button>
          </form>
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
