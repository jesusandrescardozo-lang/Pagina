<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CarnetController extends Controller
{
    /**
     * Lista de personal registrado y estado de sus carnets oficiales.
     */
    public function index()
    {
        $users = User::with('shelter')->orderBy('name')->get();
        return view('carnets.index', compact('users'));
    }

    /**
     * Muestra la credencial / carnet oficial de un empleado con su código QR imprimible.
     */
    public function show(User $user)
    {
        // Solo administradores pueden ver carnets de otros funcionarios
        if (!Auth::user()->isAdmin() && Auth::id() !== $user->id) {
            return redirect()->route('dashboard')->with('error', 'Acceso denegado: Solo tiene autorización para consultar su propia credencial.');
        }

        // Si no tiene token de carnet, generarlo
        if (empty($user->qr_token)) {
            $user->regenerateQrToken();
        }

        $user->load('shelter');
        return view('carnets.show', compact('user'));
    }

    /**
     * Regenera el token QR de un empleado (revoca carnets anteriores).
     */
    public function regenerate(User $user)
    {
        $oldToken = $user->qr_token;
        $newToken = $user->regenerateQrToken();

        AuditService::log(
            'Seguridad',
            'qr_badge_regenerated',
            'Regeneración de código QR de carnet para el usuario ' . $user->name . ' por ' . Auth::user()->name
        );

        return back()->with('success', "Se generó una nueva credencial QR para {$user->name}. El carnet físico anterior ha quedado revocado.");
    }

    /**
     * Activa o desactiva el carnet de un empleado (por extravío o suspensión).
     */
    public function toggle(User $user)
    {
        // Nadie puede suspender su propia credencial: se quedaría sin
        // acceso a su identificador oficial y no podría revertirlo.
        if (Auth::id() === $user->id) {
            return back()->with('error', 'No puede suspender su propia credencial. Solicite a otro administrador que lo haga.');
        }

        $user->qr_activo = !$user->qr_activo;
        $user->save();

        $estado = $user->qr_activo ? 'ACTIVADO' : 'SUSPENDIDO/BLOQUEADO';

        AuditService::log(
            'Seguridad',
            'qr_badge_status_changed',
            "Estado del carnet de {$user->name} cambiado a {$estado} por " . Auth::user()->name
        );

        return back()->with('success', "El carnet de {$user->name} ahora se encuentra {$estado}.");
    }
}
