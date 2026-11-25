<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\Api\AuthApiService;
use App\Services\Api\DuplicateApiEmailException;
use App\Services\Api\InvalidApiCredentialsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

            // Spring Boot confirma la identidad; Laravel solo la refleja en su sesión web.
            $me = $this->auth->me($result['accessToken']);
            $user = $me !== null ? User::find($me['userId']) : null;

            if ($user === null) {
                throw new RuntimeException('El usuario autenticado no existe en la base de datos');
            }

            Auth::guard('web')->login($user);
            $request->session()->regenerate();

            $request->session()->put([
                'spring_auth.access_token' => $result['accessToken'],
                // Margen de 10 s para no usar un token a punto de vencer.
                'spring_auth.expires_at' => time() + max((int) $result['expiresIn'] - 10, 0),
            ]);

            return redirect()->intended(route('dashboard'));

        } catch (InvalidApiCredentialsException $e) {
            return back()
                ->withErrors(['email' => 'Correo o contraseña incorrectos.'])
                ->onlyInput('email');

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
        $token = $request->session()->get('spring_auth.access_token');

        if (is_string($token) && $token !== '') {
            try {
                $this->auth->logout($token);
            } catch (Throwable $e) {
                Log::warning('No se pudo revocar el token en la API', [
                    'exception' => get_class($e),
                ]);
            }
        }

        // invalidate() elimina de la sesión el JWT y los datos de usuario.
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
