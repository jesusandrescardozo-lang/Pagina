<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GuestController extends Controller
{
    /**
     * Activa el Modo Invitado: guarda guest_mode en sesión y redirige al dashboard.
     */
    public function enter(Request $request)
    {
        // Si ya hay un usuario autenticado, ir directo al dashboard
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        $request->session()->put('guest_mode', true);

        return redirect()->route('dashboard')->with('info', '¡Bienvenido al Modo Invitado! Estás viendo el sistema en modo solo lectura.');
    }

    /**
     * Desactiva el Modo Invitado: limpia la sesión y redirige al login.
     */
    public function exit(Request $request)
    {
        $request->session()->forget('guest_mode');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Has salido del Modo Invitado. Inicia sesión para acceder al sistema completo.');
    }
}
