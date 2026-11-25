<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Recuperar contraseña - Smart Ahorro</title>
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}" />
  </head>
  <body>
    <div class="auth-shell">
      <div class="auth-panel">
        <div class="auth-box">
          <div class="auth-brand">
            <img src="{{ asset('img/logo-pagina.png') }}" alt="logo-pagina" />
            <h2>Smart <span>Ahorro</span></h2>
          </div>

          <h1>Recuperar contraseña</h1>
          <p class="subtitulo">Ingresa tu correo y te enviaremos un enlace para restablecerla.</p>

          @if (session('status'))
            <x-alerta tipo="exito">{{ session('status') }}</x-alerta>
          @endif

          <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="campo">
              <label for="email">Correo electrónico</label>
              <input type="email" id="email" name="email" placeholder="Correo electrónico" value="{{ old('email') }}" required />
            </div>

            @if ($errors->any())
              <x-alerta tipo="error">
                @foreach ($errors->all() as $error) {{ $error }}<br /> @endforeach
              </x-alerta>
            @endif

            <button type="submit" class="btn-primario" style="width:100%;">Enviar enlace de recuperación</button>
          </form>

          <a href="{{ route('login') }}" class="auth-link-secundario">← Volver a iniciar sesión</a>
        </div>
      </div>

      <div class="auth-image">
        <img src="{{ asset('img/imagen-principal.png') }}" alt="Aplicación Smart ahorro" />
      </div>
    </div>
  </body>
</html>
