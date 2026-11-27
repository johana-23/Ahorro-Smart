<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script>
      document.documentElement.setAttribute('data-theme', localStorage.getItem('smart-ahorro-theme') || 'light');
    </script>
    <title>Smart Ahorro — Ahorra con hábito, no con fuerza de voluntad</title>
    <meta name="description" content="Registra tus ingresos y gastos, define metas de ahorro y deja que el semáforo financiero y la planta de ahorro te muestren si vas por buen camino." />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}" />
    <style>
      h1, h2, .landing-nav-logo span { font-family: 'Sora', 'Segoe UI', sans-serif; letter-spacing: -0.02em; }
    </style>
  </head>
  <body>

    <div class="landing-wrap">
      <nav class="landing-nav">
        <div class="landing-nav-logo">
          <img src="{{ asset('img/logo-pagina.png') }}" alt="Smart Ahorro" />
          <span>Smart Ahorro</span>
        </div>
        <div class="landing-nav-links">
          <button type="button" class="theme-toggle" data-theme-toggle aria-label="Cambiar tema">
            <i class="fa-solid fa-moon" data-theme-icon></i>
            <span data-theme-text>Modo oscuro</span>
          </button>
          <a href="{{ route('login') }}" class="link">Iniciar sesión</a>
          <a href="{{ route('register') }}" class="btn-primario">Crear cuenta</a>
        </div>
      </nav>

      <section class="landing-hero">
        <div>
          <h1>El ahorro no falla por falta de plata. Falla por falta de hábito.</h1>
          <p class="sub">
            Smart Ahorro registra tus ingresos y gastos, calcula cuánto debes aportar a
            cada meta, y usa un semáforo financiero y una planta que crece con tu
            constancia — para que ahorrar se sienta como un hábito, no como una tarea pendiente.
          </p>
          <div class="landing-cta-group">
            <a href="{{ route('register') }}" class="btn-primario">Crear cuenta gratis</a>
            <a href="{{ route('login') }}" class="btn-secundario">Ya tengo cuenta</a>
          </div>
          <p class="landing-nota">No gestionamos tu dinero real — es una herramienta de orientación financiera.</p>
        </div>

        <div>
          <div class="landing-grafico">
            <div class="landing-barra b1"></div>
            <div class="landing-barra b2"></div>
            <div class="landing-barra b3"></div>
            <div class="landing-barra b4"><span class="landing-brote">🌱</span></div>
          </div>
        </div>
      </section>
    </div>

    <div class="landing-wrap">
      <section class="landing-section">
        <h2>De registrar un gasto a cumplir una meta, en tres pasos</h2>

        <div class="landing-pasos">
          <div class="landing-paso">
            <p class="num">Paso 1</p>
            <h3>Registra ingresos y gastos</h3>
            <p>Lleva el control de tu dinero en minutos, clasificando cada gasto como fijo o variable.</p>
          </div>
          <div class="landing-paso">
            <p class="num">Paso 2</p>
            <h3>Define una meta</h3>
            <p>Dinos cuánto quieres ahorrar y en cuántos meses. Nosotros calculamos lo que debes aportar.</p>
          </div>
          <div class="landing-paso">
            <p class="num">Paso 3</p>
            <h3>Cuida tu constancia</h3>
            <p>Tu planta de ahorro y tu semáforo financiero te avisan si vas por buen camino, antes de que sea tarde.</p>
          </div>
        </div>
      </section>

      <section class="landing-section">
        <h2>Lo que hace distinto a Smart Ahorro</h2>
        <p class="intro">La mayoría de apps de finanzas te muestran números. Esta también te muestra si mantienes el hábito.</p>

        <div style="margin-top:32px;">
          <div class="landing-feature">
            <div class="barra" style="background:var(--semaforo-verde);"></div>
            <div>
              <h3>Semáforo financiero</h3>
              <p>Un vistazo te dice si tu ahorro acumulado cubre tus próximos meses de gastos: verde, amarillo o rojo.</p>
            </div>
          </div>
          <div class="landing-feature">
            <div class="barra" style="background:var(--acento);"></div>
            <div>
              <h3>Planta de ahorro</h3>
              <p>Aporta seguido y tu planta florece. Déjala unos días sin cuidar y se marchita — notas la falta de constancia antes de que se vuelva costumbre.</p>
            </div>
          </div>
          <div class="landing-feature">
            <div class="barra" style="background:var(--semaforo-amarillo);"></div>
            <div>
              <h3>Metas con cálculo automático</h3>
              <p>Define cuánto y para cuándo. Smart Ahorro calcula cuánto debes aportar cada mes para lograrlo.</p>
            </div>
          </div>
          <div class="landing-feature">
            <div class="barra" style="background:var(--texto-principal);"></div>
            <div>
              <h3>Reportes y recomendaciones</h3>
              <p>Gráficas de tus hábitos de gasto y sugerencias basadas en tu comportamiento financiero real, no en reglas genéricas.</p>
            </div>
          </div>
        </div>
      </section>

      <section class="landing-cta-final">
        <div>
          <h2>Empieza a ahorrar con constancia</h2>
          <p>Crear tu cuenta toma menos de un minuto.</p>
        </div>
        <a href="{{ route('register') }}" class="btn-primario">Crear cuenta gratis</a>
      </section>

      <footer class="landing-footer">
        <span>Smart Ahorro — Proyecto académico, SENA · ADSO</span>
        <span>&copy; {{ date('Y') }}</span>
      </footer>
    </div>

    <script src="{{ asset('js/theme-and-currency.js') }}"></script>
  </body>
</html>