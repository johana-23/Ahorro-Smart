<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sesión iniciada | Smart Ahorro</title>
</head>

<body>
    <main>
        <h1>Sesión iniciada correctamente</h1>
        <p>Tu identidad ha sido verificada por Smart Ahorro.</p>
        <p>Los módulos financieros permanecen temporalmente aislados durante la migración.</p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Cerrar sesión</button>
        </form>
    </main>
</body>

</html>