<?php

use App\Models\User;
use App\Services\GoogleAuthenticatorService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

/**
 * Rota los secretos 2FA y tokens QR que estaban codificados en el repositorio.
 * Cualquiera con acceso al código fuente podría haberlos usado para by-passar
 * el segundo factor, por lo que se regeneran (los usuarios vuelven a escanear
 * el QR desde su carnet).
 */
return new class extends Migration {
    private const SECRETOS_FILTRADOS = [
        'JBSWY3DPEHPK3PXP',
        'KRSXG5CTMVRXEZLU',
        'MVXXEZLTOBXXE33N',
        'ONSWG4TFOR2HI2DF',
    ];

    private const TOKENS_FILTRADOS = [
        'SEC-VE-ADMIN-7701-X89',
        'SEC-VE-COORD-8802-K42',
        'SEC-VE-ALMAC-9903-M15',
        'SEC-VE-AUDIT-6604-P91',
    ];

    public function up(): void
    {
        User::whereIn('google2fa_secret', self::SECRETOS_FILTRADOS)->get()->each(function (User $user) {
            $user->google2fa_secret = GoogleAuthenticatorService::generateSecret();
            $user->save();
        });

        User::whereIn('qr_token', self::TOKENS_FILTRADOS)->get()->each(function (User $user) {
            $user->qr_token = 'SEC-AUTH-' . strtoupper(Str::random(24));
            $user->save();
        });
    }

    public function down(): void
    {
        // Rotación irreversible por seguridad (no se restauran secretos filtrados).
    }
};
