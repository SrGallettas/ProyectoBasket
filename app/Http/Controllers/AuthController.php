<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// Auth se usa para intentar iniciar sesión y también para cerrarla.
use Illuminate\Support\Facades\Auth;

// RateLimiter protege el login frente a muchos intentos seguidos.
use Illuminate\Support\Facades\RateLimiter;

// Str ayuda a normalizar el email al construir la clave del limitador.
use Illuminate\Support\Str;

// Esta excepción permite devolver errores de validación al formulario.
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Máximo de intentos fallidos permitidos antes de bloquear temporalmente.
    private const LOGIN_MAX_ATTEMPTS = 5;

    // Tiempo del bloqueo en segundos: 900 = 15 minutos.
    private const LOGIN_DECAY_SECONDS = 900;

    /*
     * FORMULARIO DE LOGIN
     *
     * Este método solo devuelve la vista de acceso.
     * No valida datos ni toca la sesión.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /*
     * PROCESAR LOGIN
     *
     * Este método recibe el formulario de acceso y controla los intentos fallidos.
     *
     * Flujo:
     * 1. valida email y contraseña
     * 2. genera una clave única para el limitador
     * 3. comprueba si el usuario está temporalmente bloqueado
     * 4. intenta autenticar
     * 5. si entra, regenera la sesión y limpia el bloqueo
     * 6. si falla, suma un intento y devuelve error
     */
    public function login(Request $request)
    {
        // Primero validamos los dos campos mínimos del formulario.
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // "remember" indica si se debe mantener la sesión iniciada.
        $remember = $request->boolean('remember');

        // Esta clave identifica de forma única al usuario + IP para limitar ataques.
        $throttleKey = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, self::LOGIN_MAX_ATTEMPTS)) {
            // Si ya superó el máximo, calculamos cuánto tiempo queda de espera.
            $segundosRestantes = RateLimiter::availableIn($throttleKey);
            $minutosRestantes = (int) ceil($segundosRestantes / 60);

            throw ValidationException::withMessages([
                'email' => "Demasiados intentos de acceso. Vuelve a intentarlo en {$minutosRestantes} minuto(s).",
            ]);
        }

        if (Auth::attempt($credentials, $remember)) {
            // Al entrar correctamente, se limpia el contador de fallos.
            RateLimiter::clear($throttleKey);

            // Regenerar sesión evita problemas de session fixation.
            $request->session()->regenerate();

            // Si había una URL protegida pendiente se respeta; si no, va al panel admin.
            return redirect()->intended('admin');
        }

        // Si falla el login, sumamos un intento consumido.
        RateLimiter::hit($throttleKey, self::LOGIN_DECAY_SECONDS);

        // Volvemos atrás con error y conservamos email + remember.
        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->withInput($request->only('email', 'remember'));
    }

    /*
     * CERRAR SESIÓN
     *
     * Este método desconecta al usuario actual y deja la sesión limpia.
     */
    public function logout(Request $request)
    {
        // Se cierra la autenticación actual.
        Auth::logout();

        // Se invalida la sesión para que no pueda reutilizarse.
        $request->session()->invalidate();

        // Se crea un nuevo token CSRF para la siguiente navegación.
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /*
     * CLAVE DEL LIMITADOR
     *
     * Combina email e IP para que el control de intentos
     * no dependa solo de uno de los dos valores.
     */
    private function throttleKey(Request $request): string
    {
        // El email se pasa a minúsculas para evitar claves duplicadas.
        return Str::lower((string) $request->input('email')) . '|' . $request->ip();
    }
}
