<?php

namespace App\Http\Controllers;

use App\Services\Api\AuthApiService;
use App\Services\Api\InvalidResetTokenException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Presentación de la recuperación de contraseña. Tokens y contraseñas los administra Spring Boot.
 */
class PasswordResetController extends Controller
{
    public function __construct(
        private readonly AuthApiService $auth
    ) {}

    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255']]);

        try {
            $this->auth->forgotPassword($data['email']);
        } catch (Throwable $e) {
            Log::warning('Error al solicitar recuperación de contraseña', [
                'exception' => get_class($e),
            ]);

            return back()
                ->withErrors(['email' => 'El servicio no está disponible. Intenta más tarde.'])
                ->onlyInput('email');
        }

        // Respuesta idéntica exista o no la cuenta, para no revelar correos registrados.
        return back()->with(
            'status',
            'Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.'
        );
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
        ]);

        try {
            $this->auth->resetPassword($data['email'], $data['token'], $data['password']);
        } catch (InvalidResetTokenException $e) {
            return back()
                ->withErrors(['email' => 'El enlace de recuperación no es válido o ya expiró.'])
                ->onlyInput('email');
        } catch (Throwable $e) {
            Log::warning('Error al restablecer contraseña', [
                'exception' => get_class($e),
            ]);

            return back()
                ->withErrors(['email' => 'El servicio no está disponible. Intenta más tarde.'])
                ->onlyInput('email');
        }

        return redirect()
            ->route('login')
            ->with('status', 'Tu contraseña fue actualizada. Ya puedes iniciar sesión.');
    }
}
