<?php
namespace App\Services\Api;

use RuntimeException;

final class AuthApiService
{
    public function __construct(private readonly SpringBootApiClient $client) {}

    public function login(string $email, string $password): array
    {
        $response = $this->client->post('/api/v1/auth/login', [
            'email' => $email, 'password' => $password,
        ]);
        return $this->parseTokens($response);
    }

    public function refresh(string $refreshToken): array
    {
        return $this->parseTokens($this->client->post('/api/v1/auth/refresh', [
            'refreshToken' => $refreshToken,
        ]));
    }

    private function parseTokens(\Illuminate\Http\Client\Response $response): array
    {
        $this->checkRateLimit($response);
        if ($response->status() === 401) {
            throw new InvalidApiCredentialsException();
        }
        if ($response->status() !== 200) {
            throw new RuntimeException('La autenticacion no esta disponible');
        }
        $data = $response->json();
        if (!is_array($data) || !is_string($data['accessToken'] ?? null)
            || trim($data['accessToken']) === ''
            || ($data['tokenType'] ?? null) !== 'Bearer'
            || ($data['expiresIn'] ?? null) !== 900
            || !is_string($data['refreshToken'] ?? null)
            || !preg_match('/\A[A-Za-z0-9_-]{43}\z/', $data['refreshToken'])
            || !is_int($data['refreshExpiresIn'] ?? null)
            || $data['refreshExpiresIn'] <= 0 || $data['refreshExpiresIn'] > 604800) {
            throw new RuntimeException('Respuesta de autenticacion invalida');
        }
        return $data;
    }

    private function checkRateLimit(\Illuminate\Http\Client\Response $response): void
    {
        if ($response->status() === 429) {
            throw new ApiRateLimitException();
        }
    }

    /** Devuelve ['userId' => int] o null si el token ya no es válido. */
    public function me(string $token): ?array
    {
        $response = $this->client->get('/api/v1/auth/me', $token);
        if ($response->status() === 401) {
            return null;
        }
        if (!$response->successful()) {
            throw new RuntimeException('No fue posible validar la sesion');
        }
        $data = $response->json();
        if (!is_array($data) || !is_int($data['userId'] ?? null) || $data['userId'] <= 0) {
            throw new RuntimeException('Respuesta de identidad invalida');
        }
        return ['userId' => (int) $data['userId']];
    }

    public function register(string $name, string $email, string $password): array
    {
        $response = $this->client->post('/api/v1/auth/register', [
            'name' => $name, 'email' => $email, 'password' => $password,
        ]);
        $this->checkRateLimit($response);
        if ($response->status() === 409) {
            throw new DuplicateApiEmailException();
        }
        if ($response->status() !== 201) {
            throw new RuntimeException('No fue posible registrar el usuario');
        }
        $data = $response->json();
        if (!is_array($data) || !is_int($data['userId'] ?? null) || $data['userId'] <= 0) {
            throw new RuntimeException('Respuesta de registro invalida');
        }
        return $data;
    }

    /** Revoca toda la familia aunque el JWT haya expirado. */
    public function logout(string $refreshToken): void
    {
        $response = $this->client->post('/api/v1/auth/logout', ['refreshToken' => $refreshToken]);
        $this->checkRateLimit($response);
        if ($response->status() !== 204) {
            throw new RuntimeException('No fue posible cerrar la sesion en la API');
        }
    }

    public function forgotPassword(string $email): void
    {
        $response = $this->client->post('/api/v1/auth/forgot-password', ['email' => $email]);
        $this->checkRateLimit($response);
        if ($response->status() !== 202) {
            throw new RuntimeException('No fue posible solicitar la recuperacion');
        }
    }

    public function resetPassword(string $email, string $token, string $password): void
    {
        $response = $this->client->post('/api/v1/auth/reset-password', [
            'email' => $email, 'token' => $token, 'password' => $password,
        ]);
        $this->checkRateLimit($response);
        if ($response->status() === 400) {
            throw new InvalidResetTokenException();
        }
        if ($response->status() !== 204) {
            throw new RuntimeException('No fue posible restablecer la contrasena');
        }
    }
}
