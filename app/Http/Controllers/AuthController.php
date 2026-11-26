<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\Api\AuthApiService;
use App\Services\Api\ApiRateLimitException;
use App\Services\Api\DuplicateApiEmailException;
use App\Services\Api\InvalidApiCredentialsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthApiService $auth
    ) {}

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        try {
            $result = $this->auth->login($data['email'], $data['password']);

            $me = $this->auth->me($result['accessToken']);
            if ($me === null || $me['userId'] <= 0) {
                throw new RuntimeException('Identidad no válida');
            }

            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $request->session()->put([
                'spring_auth.user_id' => $me['userId'],
                'spring_auth.access_token' => $result['accessToken'],
                'spring_auth.refresh_token' => $result['refreshToken'],
                'spring_auth.refresh_expires_at' => time() + $result['refreshExpiresIn'] - 10,
                'spring_auth.expires_at' => time() + max((int) $result['expiresIn'] - 10, 0),
            ]);

            return redirect()->route('auth.session');

        } catch (InvalidApiCredentialsException $e) {
            return back()
                ->withErrors(['email' => 'Correo o contraseña incorrectos.'])
                ->onlyInput('email');

        } catch (ApiRateLimitException $e) {
            return back()->withErrors(['email' => 'Demasiados intentos. Intenta más tarde.'])->onlyInput('email');
        } catch (Throwable $e) {
            Log::warning('Error en inicio de sesión API', [
                'exception' => get_class($e),
            ]);

            return back()
                ->withErrors(['email' => 'El servicio de autenticación no está disponible.'])
                ->onlyInput('email');
        }
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        try {
            $this->auth->register($data['name'], $data['email'], $data['password']);

            return redirect()
                ->route('login')
                ->with('status', 'Cuenta creada correctamente. Inicia sesión.');

        } catch (DuplicateApiEmailException $e) {
            return back()
                ->withErrors(['email' => 'El correo electrónico ya está registrado.'])
                ->onlyInput('name', 'email');

        } catch (ApiRateLimitException $e) {
            return back()->withErrors(['email' => 'Demasiados intentos. Intenta más tarde.'])->onlyInput('name', 'email');
        } catch (Throwable $e) {
            Log::warning('Error en registro API', [
                'exception' => get_class($e),
            ]);

            return back()
                ->withErrors(['email' => 'No fue posible completar el registro.'])
                ->onlyInput('name', 'email');
        }
    }

    public function logout(Request $request)
    {
        $token = $request->session()->get('spring_auth.refresh_token');
        $revoked = true;

        if (is_string($token) && $token !== '') {
            try {
                $this->auth->logout($token);
            } catch (Throwable $e) {
                $revoked = false;
                Log::warning('No se pudo revocar el token en la API', [
                    'exception' => get_class($e),
                ]);
            }
        }

        // invalidate() elimina de la sesión el JWT y los datos de usuario.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $revoked ? redirect()->route('login') : redirect()->route('login')->withErrors([
            'email' => 'Sesión local cerrada. No fue posible confirmar la revocación remota.',
        ]);
    }
}
