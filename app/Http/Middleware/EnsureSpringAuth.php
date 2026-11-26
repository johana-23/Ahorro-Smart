<?php

namespace App\Http\Middleware;

use App\Services\Api\AuthApiService;
use App\Services\Api\ApiRateLimitException;
use App\Services\Api\InvalidApiCredentialsException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class EnsureSpringAuth
{
    public function __construct(private readonly AuthApiService $auth) {}

    public function handle(Request $request, Closure $next): Response
    {
        $session = $request->session();
        $token = $session->get('spring_auth.access_token');
        $refresh = $session->get('spring_auth.refresh_token');
        $expiresAt = $session->get('spring_auth.expires_at');
        $refreshExpiresAt = $session->get('spring_auth.refresh_expires_at');
        $userId = $session->get('spring_auth.user_id');
        if (!is_string($token) || $token === '' || !is_string($refresh) || $refresh === ''
            || !is_int($expiresAt) || !is_int($refreshExpiresAt) || $refreshExpiresAt <= time()
            || !is_int($userId) || $userId <= 0) {
            return $this->invalid($request);
        }

        try {
            if ($expiresAt <= time()) {
                // Rotación única: la ruta mantiene un bloqueo de sesión durante esta operación.
                $result = $this->auth->refresh($refresh);
                $token = $result['accessToken'];
                $session->put([
                    'spring_auth.access_token' => $token,
                    'spring_auth.refresh_token' => $result['refreshToken'],
                    'spring_auth.expires_at' => time() + $result['expiresIn'] - 10,
                    'spring_auth.refresh_expires_at' => time() + $result['refreshExpiresIn'] - 10,
                ]);
            }
            $me = $this->auth->me($token);
            if ($me === null || $me['userId'] !== $userId) {
                return $this->invalid($request);
            }
        } catch (InvalidApiCredentialsException $e) {
            return $this->invalid($request);
        } catch (ApiRateLimitException $e) {
            return response('Demasiadas solicitudes. Intenta más tarde.', 429);
        } catch (Throwable $e) {
            Log::warning('No se pudo verificar la sesión API', ['exception' => get_class($e)]);
            return response('Servicio de autenticación no disponible.', 503);
        }
        return $next($request);
    }

    private function invalid(Request $request): Response
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Sesión inválida o expirada'], 401);
        }
        return redirect()->route('login')->withErrors([
            'email' => 'Tu sesión expiró o no es válida. Inicia sesión nuevamente.',
        ]);
    }
}
