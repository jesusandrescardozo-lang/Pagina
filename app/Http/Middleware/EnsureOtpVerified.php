<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureOtpVerified
{
    /**
     * Maneja la verificación de doble factor OTP requerida antes de acceder a cualquier módulo del sistema.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Si la sesión no tiene otp_verificado activo, redirigir a la pantalla de validación OTP
        if (!$request->session()->get('otp_verificado', false)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acceso denegado: Se requiere verificación mediante código OTP de 6 dígitos.',
                    'redirect' => route('otp.view')
                ], 403);
            }

            return redirect()->route('otp.view');
        }

        return $next($request);
    }
}
