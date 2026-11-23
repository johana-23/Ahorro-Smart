<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Prueba registro API</title></head><body>
<h1>Prueba aislada de registro Spring Boot</h1>
@if($errors->any())<p>{{ $errors->first() }}</p>@endif
<form method="POST" action="{{ route('integracion.register') }}">@csrf
<label>Nombre <input name="name" value="{{ old('name') }}" required></label>
<label>Correo <input type="email" name="email" value="{{ old('email') }}" required></label>
<label>Contraseña <input type="password" name="password" required></label>
<label>Confirmación <input type="password" name="password_confirmation" required></label>
<button type="submit">Registrar</button></form>
</body></html>
