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
        if ($response->status() === 401) {
            throw new InvalidApiCredentialsException();
        }
        if (!$response->successful()) {
            throw new RuntimeException('La autenticacion no esta disponible');
        }
        $data = $response->json();
        if (!is_array($data) || !is_string($data['accessToken'] ?? null)
            || ($data['tokenType'] ?? null) !== 'Bearer'
            || !is_numeric($data['expiresIn'] ?? null)) {
            throw new RuntimeException('Respuesta de autenticacion invalida');
        }
        return $data;
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
        if (!is_array($data) || !is_numeric($data['userId'] ?? null)) {
            return null;
        }
        return ['userId' => (int) $data['userId']];
    }

    public function register(string $name, string $email, string $password): array
    {
        $response = $this->client->post('/api/v1/auth/register', [
            'name' => $name, 'email' => $email, 'password' => $password,
        ]);
        if ($response->status() === 409) {
            throw new DuplicateApiEmailException();
        }
        if (!$response->successful()) {
            throw new RuntimeException('No fue posible registrar el usuario');
        }
        return (array) $response->json();
    }

    /** Revoca el token en Spring Boot. 401 se considera ya cerrado. */
    public function logout(string $token): void
    {
        $response = $this->client->post('/api/v1/auth/logout', [], $token);
        if (!$response->successful() && $response->status() !== 401) {
            throw new RuntimeException('No fue posible cerrar la sesion en la API');
        }
    }

    public function forgotPassword(string $email): void
    {
        $response = $this->client->post('/api/v1/auth/forgot-password', ['email' => $email]);
        if (!$response->successful()) {
            throw new RuntimeException('No fue posible solicitar la recuperacion');
        }
    }

    public function resetPassword(string $email, string $token, string $password): void
    {
        $response = $this->client->post('/api/v1/auth/reset-password', [
            'email' => $email, 'token' => $token, 'password' => $password,
        ]);
        if ($response->status() === 400) {
            throw new InvalidResetTokenException();
        }
        if (!$response->successful()) {
            throw new RuntimeException('No fue posible restablecer la contrasena');
        }
    }
}
