<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Prueba login API</title></head><body>
<h1>Prueba aislada de login Spring Boot</h1>
@if(session('status'))<p>{{ session('status') }}</p>@endif
@if($errors->any())<p>{{ $errors->first() }}</p>@endif
<form method="POST" action="{{ route('integracion.login') }}">@csrf
<label>Correo <input type="email" name="email" value="{{ old('email') }}" required></label>
<label>Contraseña <input type="password" name="password" required></label>
<button type="submit">Entrar</button></form>
<a href="{{ route('integracion.register.form') }}">Registro de prueba</a>
</body></html>
