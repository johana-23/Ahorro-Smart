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
            || ($data['tokenType'] ?? null) !== 'Bearer') {
            throw new RuntimeException('Respuesta de autenticacion invalida');
        }
        return $data;
    }

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
       if (!is_array($data)) {
            return null;
        }

        $userId = $data['userId'] ?? $data['userID'] ?? null;

        if (!is_numeric($userId)) {
            return null;
        }

        return ['userId' => (int) $userId];
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
}
