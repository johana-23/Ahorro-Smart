<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SpringAuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['services.smart_ahorro_api.url' => 'http://127.0.0.1:8080', 'session.block_store' => 'array']);
        Http::preventStrayRequests();
    }

    private function tokens(string $access, string $refresh): array
    {
        return ['accessToken' => $access, 'tokenType' => 'Bearer', 'expiresIn' => 900,
            'refreshToken' => $refresh, 'refreshExpiresIn' => 604800];
    }

    public function test_login_uses_spring_identity(): void
    {
        $refresh = str_repeat('a', 43);
        Http::fake([
            '*/api/v1/auth/login' => Http::response($this->tokens('access-prueba', $refresh)),
            '*/api/v1/auth/me' => Http::response(['userId' => 123]),
        ]);
        $this->post('/login', ['email' => 'prueba@example.com', 'password' => 'ClaveDePrueba'])
            ->assertRedirect(route('auth.session'))
            ->assertSessionHas('spring_auth.user_id', 123)
            ->assertSessionHas('spring_auth.refresh_token', $refresh);
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/me')
            && $request->hasHeader('Authorization', 'Bearer access-prueba'));
    }

    public function test_refresh_rotates_and_protects_session(): void
    {
        $old = str_repeat('a', 43); $new = str_repeat('b', 43);
        Http::fake([
            '*/api/v1/auth/refresh' => Http::response($this->tokens('nuevo-access', $new)),
            '*/api/v1/auth/me' => Http::response(['userId' => 123]),
        ]);
        $this->withSession(['spring_auth' => ['user_id' => 123, 'access_token' => 'viejo-access',
            'refresh_token' => $old, 'expires_at' => time() - 1, 'refresh_expires_at' => time() + 3600]])
            ->get('/auth/session')->assertOk()->assertSessionHas('spring_auth.refresh_token', $new);
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/refresh') && $request['refreshToken'] === $old);
    }

    public function test_revoked_identity_is_rejected(): void
    {
        Http::fake(['*/api/v1/auth/me' => Http::response([], 401)]);
        $this->withSession(['spring_auth' => ['user_id' => 123, 'access_token' => 'access',
            'refresh_token' => str_repeat('a', 43), 'expires_at' => time() + 500, 'refresh_expires_at' => time() + 3600]])
            ->get('/auth/session')->assertRedirect(route('login'))->assertSessionMissing('spring_auth.access_token');
    }

    public function test_logout_revokes_refresh_even_if_access_expired(): void
    {
        $refresh = str_repeat('a', 43);
        Http::fake(['*/api/v1/auth/logout' => Http::response(null, 204)]);
        $this->withSession(['spring_auth' => ['refresh_token' => $refresh, 'expires_at' => time() - 10]])
            ->post('/logout')->assertRedirect(route('login'))->assertSessionMissing('spring_auth.refresh_token');
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/logout') && $request['refreshToken'] === $refresh);
    }

    public function test_legacy_module_is_blocked(): void
    {
        $this->get('/dashboard')->assertStatus(503);
        Http::assertNothingSent();
    }
}
