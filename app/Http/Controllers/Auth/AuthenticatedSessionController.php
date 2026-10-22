<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Shelter;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        $shelters = Shelter::orderBy('codigo')->get();
        return view('auth.login', compact('shelters'));
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'shelter_id' => ['nullable', 'exists:shelters,id'],
        ]);

        // Protección de fuerza bruta: máximo 5 intentos por minuto por IP.
        $throttleKey = 'login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'email' => "Demasiados intentos de inicio de sesión. Inténtelo de nuevo en {$seconds} segundos.",
            ])->onlyInput('email');
        }
        RateLimiter::hit($throttleKey, 60);

        $remember = $request->boolean('remember');

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $remember)) {
            $request->session()->regenerate();
            RateLimiter::clear($throttleKey);

            $user = Auth::user();

            if ($request->filled('shelter_id')) {
                $shelter = Shelter::find($request->input('shelter_id'));
                if ($shelter) {
                    session([
                        'shelter_id' => $shelter->id,
                        'shelter_nombre' => $shelter->nombre,
                        'shelter_codigo' => $shelter->codigo,
                    ]);
                }
            } else {
                session()->forget(['shelter_id', 'shelter_nombre', 'shelter_codigo']);
            }

            // Inicio de sesión directo sin Google Authenticator ni OTP
            session(['otp_verificado' => true]);

            AuditService::log('Autenticación', 'login_ok', 'Inicio de sesión exitoso para ' . $user->name);

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    public function destroy(Request $request)
    {
        $usuario = Auth::user();
        if ($usuario) {
            AuditService::log('Autenticación', 'logout', 'Cierre de sesión por ' . $usuario->name);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}