<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Valida si el usuario autenticado tiene uno de los roles autorizados.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Si el rol del usuario no está en los permitidos
        if (!in_array($user->rol, $roles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acceso denegado: Su perfil no tiene permisos para esta acción.',
                ], 403);
            }

            // Redirigir al dashboard con alerta de error
            return redirect()->route('dashboard')->with('error', 'Acceso no autorizado: Su rol (' . str_replace('_', ' ', $user->rol) . ') no tiene permiso para ingresar a esa sección.');
        }

        return $next($request);
    }
}
