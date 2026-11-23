<?php
namespace App\Http\Controllers;

use App\Services\Api\AuthApiService;
use App\Services\Api\DuplicateApiEmailException;
use App\Services\Api\InvalidApiCredentialsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/** Flujo aislado para validar HTTP/JWT. NO reemplaza aun AuthController ni el guard web. */
final class IntegrationAuthController extends Controller
{
    public function __construct(private readonly AuthApiService $auth) {}

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);
        try {
            $login = $this->auth->login($data['email'], $data['password']);
            $request->session()->regenerate();
            $request->session()->put('spring_auth.access_token', $login['accessToken']);
            return redirect()->route('integracion.me');
        } catch (InvalidApiCredentialsException $e) {
            return back()->withErrors(['email' => 'Credenciales invalidas'])->onlyInput('email');
        } catch (Throwable $e) {
            Log::warning('Fallo autenticacion API', ['exception' => get_class($e)]);
            return back()->withErrors(['email' => 'Servicio temporalmente no disponible'])->onlyInput('email');
        }
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:4', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        try {
            $this->auth->register($data['name'], $data['email'], $data['password']);
            return redirect()->route('integracion.login.form')->with('status', 'Cuenta creada. Inicia sesion.');
        } catch (DuplicateApiEmailException $e) {
            return back()->withErrors(['email' => 'El correo ya esta registrado'])->onlyInput('name', 'email');
        } catch (Throwable $e) {
            Log::warning('Fallo registro API', ['exception' => get_class($e)]);
            return back()->withErrors(['email' => 'Servicio temporalmente no disponible'])->onlyInput('name', 'email');
        }
    }

    public function me(Request $request)
    {
        $token = $request->session()->get('spring_auth.access_token');
        if (!is_string($token) || $token === '') {
            return redirect()->route('integracion.login.form');
        }
        try {
            $user = $this->auth->me($token);
            if ($user === null) {
                $request->session()->forget('spring_auth.access_token');
                return redirect()->route('integracion.login.form')->withErrors(['email' => 'Sesion expirada']);
            }
            return response()->json(['authenticated' => true, 'userId' => $user['userId']])
                ->header('Cache-Control', 'no-store');
        } catch (Throwable $e) {
            Log::warning('Fallo consulta usuario API', ['exception' => get_class($e)]);
            return response()->json(['message' => 'Servicio no disponible'], 503);
        }
    }

    public function logout(Request $request)
    {
        $request->session()->forget('spring_auth.access_token');
        $request->session()->regenerate();
        return redirect()->route('integracion.login.form');
    }
}
