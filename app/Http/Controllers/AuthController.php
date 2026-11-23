<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\Api\AuthApiService;
use App\Services\Api\DuplicateApiEmailException;
use App\Services\Api\InvalidApiCredentialsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
            $result = $this->auth->login(
                $data['email'],
                $data['password']
            );

            $request->session()->regenerate();

            $request->session()->put(
                'spring_auth.access_token',
                $result['accessToken']
            );

            return redirect()->route('dashboard');

        } catch (InvalidApiCredentialsException $e) {
            return back()
                ->withErrors([
                    'email' => 'Correo o contraseña incorrectos.',
                ])
                ->onlyInput('email');

        } catch (Throwable $e) {
            Log::warning('Error en inicio de sesión API', [
                'exception' => get_class($e),
            ]);

            return back()
                ->withErrors([
                    'email' => 'El servicio de autenticación no está disponible.',
                ])
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
            $this->auth->register(
                $data['name'],
                $data['email'],
                $data['password']
            );

            return redirect()
                ->route('login')
                ->with(
                    'status',
                    'Cuenta creada correctamente. Inicia sesión.'
                );

        } catch (DuplicateApiEmailException $e) {
            return back()
                ->withErrors([
                    'email' => 'El correo electrónico ya está registrado.',
                ])
                ->onlyInput('name', 'email');

        } catch (Throwable $e) {
            Log::warning('Error en registro API', [
                'exception' => get_class($e),
            ]);

            return back()
                ->withErrors([
                    'email' => 'No fue posible completar el registro.',
                ])
                ->onlyInput('name', 'email');
        }
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
