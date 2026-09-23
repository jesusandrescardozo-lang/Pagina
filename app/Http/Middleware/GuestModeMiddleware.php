<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuestModeMiddleware
{
    /**
     * Permite el acceso a usuarios autenticados con OTP verificado
     * O a visitantes con sesión de modo invitado activa (guest_mode = true).
     *
     * Los invitados solo pueden realizar operaciones de lectura (GET/HEAD).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $isAuthenticated = Auth::check() && $request->session()->get('otp_verificado', false);
        $isGuest        = !Auth::check() && $request->session()->get('guest_mode', false);

        // Autenticado pero SIN verificar OTP: mandarlo a la pantalla de 2FA
        // (evita el bucle login <-> dashboard)
        if (Auth::check() && !$request->session()->get('otp_verificado', false)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acceso denegado: se requiere verificación de 2 factores.',
                    'redirect' => route('otp.view'),
                ], 403);
            }
            return redirect()->route('otp.view');
        }

        // Si no está autenticado ni es invitado, redirigir al login
        if (!$isAuthenticated && !$isGuest) {
            return redirect()->route('login');
        }

        // Los invitados solo pueden hacer GET / HEAD (solo lectura), excepto cambiar el filtro de refugio en sesión
        if ($isGuest && !in_array($request->method(), ['GET', 'HEAD']) && !$request->routeIs('shelter.switch')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Modo Invitado: Solo se permiten operaciones de lectura.',
                ], 403);
            }
            return redirect()->back()->with('error', 'Modo Invitado: No tienes permiso para realizar esta acción. Por favor inicia sesión.');
        }

        return $next($request);
    }
}
