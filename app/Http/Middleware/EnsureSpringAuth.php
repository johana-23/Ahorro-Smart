<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exige que la sesión web tenga un JWT de Spring Boot vigente.
 * Spring Boot valida firma, emisor, audiencia y expiración en cada llamada protegida;
 * aquí solo se evita operar con un token ausente o vencido (decisión: nuevo login al expirar).
 */
final class EnsureSpringAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->session()->get('spring_auth.access_token');
        $expiresAt = $request->session()->get('spring_auth.expires_at');

        if (is_string($token) && $token !== '' && is_int($expiresAt) && $expiresAt > time()) {
            return $next($request);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Sesión expirada'], 401);
        }

        return redirect()
            ->route('login')
            ->withErrors(['email' => 'Tu sesión expiró. Inicia sesión nuevamente.']);
    }
}
