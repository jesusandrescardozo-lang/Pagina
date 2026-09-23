<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware heredado mantenido para compatibilidad.
 * Delega en EnsureOtpVerified.
 */
class EnsureQrVerified
{
    public function handle(Request $request, Closure $next)
    {
        $otpMiddleware = new EnsureOtpVerified();
        return $otpMiddleware->handle($request, $next);
    }
}
