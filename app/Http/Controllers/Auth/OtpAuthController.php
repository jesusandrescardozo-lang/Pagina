<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\GoogleAuthenticatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OtpAuthController extends Controller
{
    /**
     * Muestra la pantalla de verificación 2FA mediante Google Authenticator.
     */
    public function view()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Si ya está verificado, entrar directamente al dashboard
        if (session('otp_verificado', false)) {
            return redirect()->route('dashboard');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $secondsRemaining = GoogleAuthenticatorService::getSecondsRemaining();

        return view('auth.otp-verify', compact('user', 'secondsRemaining'));
    }

    /**
     * Valida el código TOTP de 6 dígitos ingresado por el usuario usando Google Authenticator.
     */
    public function verify(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Obtener el código completo (acepta 'code' directo o concatenación de 'digit_1'..'digit_6')
        $code = $request->input('code');
        if (empty($code)) {
            $digits = [
                $request->input('digit_1'),
                $request->input('digit_2'),
                $request->input('digit_3'),
                $request->input('digit_4'),
                $request->input('digit_5'),
                $request->input('digit_6'),
            ];
            $code = implode('', $digits);
        }

        $code = trim((string)$code);

        if (strlen($code) !== 6 || !ctype_digit($code)) {
            $msg = 'Por favor ingrese los 6 dígitos numéricos del código de Google Authenticator.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->withErrors(['code' => $msg]);
        }

        $attempts = session('otp_attempts', 0);

        // Validar límite de intentos (la ventana se reinicia tras 60 s sin intentos)
        $ultimoIntento = (int) session('otp_last_attempt_at', 0);
        if ($ultimoIntento > 0 && (time() - $ultimoIntento) > 60) {
            $attempts = 0;
            session(['otp_attempts' => 0]);
        }

        if ($attempts >= 5) {
            AuditService::log('Seguridad', 'google2fa_max_attempts', 'Límite de intentos 2FA superado por ' . $user->name);
            $msg = 'Ha superado el límite de 5 intentos. Espere 1 minuto sin intentar y vuelva a intentarlo con el código vigente.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 429);
            }
            return back()->withErrors(['code' => $msg]);
        }

        $secret = $user->getGoogle2faSecret();
        $isValid = GoogleAuthenticatorService::verifyCode($secret, $code, 1);

        if (!$isValid) {
            $attempts++;
            session([
                'otp_attempts' => $attempts,
                'otp_last_attempt_at' => time(),
            ]);

            AuditService::log(
                'Seguridad',
                'google2fa_mismatch',
                "Código de Google Authenticator incorrecto (Intento {$attempts}/5) para {$user->name}"
            );

            $msg = "Código incorrecto o expirado (Intento {$attempts} de 5). Asegúrese de ingresar el código actual de su app Google Authenticator.";
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->withErrors(['code' => $msg]);
        }

        // Verificación exitosa
        session(['otp_verificado' => true]);
        session()->forget(['otp_attempts', 'otp_last_attempt_at']);

        AuditService::log(
            'Seguridad',
            'google2fa_access_granted',
            'Acceso 2FA con Google Authenticator verificado exitosamente para ' . $user->name . ' [' . ($user->cargo ?? $user->rol) . ']'
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Código de Google Authenticator verificado con éxito. Acceso concedido.',
                'redirect' => route('dashboard'),
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Bienvenido(a) a NutriLnk. Acceso verificado correctamente.');
    }

    /**
     * Regenera la clave secreta de Google Authenticator para el usuario (si extravió su dispositivo).
     */
    public function regenerateSecret(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->google2fa_secret = GoogleAuthenticatorService::generateSecret();
        $user->save();

        session()->forget('otp_attempts');

        AuditService::log(
            'Seguridad',
            'google2fa_secret_regenerated',
            'Clave secreta de Google Authenticator regenerada para ' . $user->name
        );

        $msg = 'Se ha generado una nueva clave secreta de Google Authenticator. Debe volver a escanear el código QR en su teléfono.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'secret' => $user->google2fa_secret,
                'qr_svg' => $user->getGoogle2faQrSvg(220),
                'current_code' => $user->getCurrentGoogle2faCode(),
            ]);
        }

        return back()->with('success', $msg);
    }
}
