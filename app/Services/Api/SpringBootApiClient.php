<?php
namespace App\Services\Api;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class SpringBootApiClient
{
    private function baseUrl(): string
    {
        $url = rtrim((string) config('services.smart_ahorro_api.url', ''), '/');
        if ($url === '' || !preg_match('~^https?://~i', $url)) {
            throw new RuntimeException('SMART_AHORRO_API_URL no esta configurada correctamente');
        }
        return $url;
    }

    public function post(string $path, array $payload, ?string $token = null): Response
    {
        return $this->send('post', $path, $payload, $token);
    }

    public function get(string $path, ?string $token = null): Response
    {
        return $this->send('get', $path, [], $token);
    }

    private function send(string $method, string $path, array $payload, ?string $token): Response
    {
        // Evitar URL externas y rutas arbitrarias; el destino es siempre el backend configurado.
        if (!str_starts_with($path, '/api/v1/') || str_contains($path, '..') || str_contains($path, '?')) {
            throw new RuntimeException('Ruta API no permitida');
        }
        try {
            $request = Http::acceptJson()->asJson()->timeout(10)->connectTimeout(3);
            if ($token !== null) {
                $request = $request->withToken($token);
            }
            return $request->{$method}($this->baseUrl().$path, $payload);
        } catch (ConnectionException $e) {
            throw new RuntimeException('No fue posible conectar con el servicio de autenticacion', 0, $e);
        }
    }
}
