<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            [
                'name'              => 'Coordinador General de Logística',
                'email'             => 'admin@refugios.ve',
                'password'          => Hash::make('Admin1234!'),
                'rol'               => 'admin',
                'cargo'             => 'Coordinador General de Operaciones y Logística DC',
                'qr_activo'         => true,
                'google2fa_enabled' => true,
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Lic. Roberto Alvarado (Coordinador Albergue)',
                'email'             => 'coordinador@refugios.ve',
                'password'          => Hash::make('Coordinador1234!'),
                'rol'               => 'coordinador_refugio',
                'cargo'             => 'Coordinador de Albergue - Naciones Unidas',
                'qr_activo'         => true,
                'google2fa_enabled' => true,
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Ing. Karla Morales (Encargada Almacén Central)',
                'email'             => 'almacen@refugios.ve',
                'password'          => Hash::make('Almacen1234!'),
                'rol'               => 'almacenista',
                'cargo'             => 'Jefa de Almacén y Control de Suministros DC',
                'qr_activo'         => true,
                'google2fa_enabled' => true,
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Licda. Sofía Paz (Auditora Humanitaria)',
                'email'             => 'auditor@refugios.ve',
                'password'          => Hash::make('Auditor1234!'),
                'rol'               => 'auditor',
                'cargo'             => 'Auditora de Operaciones y Cadena Humanitaria',
                'qr_activo'         => true,
                'google2fa_enabled' => true,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($usuarios as $u) {
            $user = User::firstOrNew(['email' => $u['email']]);
            $user->forceFill($u);

            // Secretos 2FA y tokens QR se generan en tiempo de ejecución:
            // NUNCA se codifican en el repositorio (si estuvieran aquí, cualquiera
            // con acceso al código podría by-passar el segundo factor).
            // Si el usuario ya existe, se conservan sus secretos actuales.
            if (!$user->google2fa_secret) {
                $user->google2fa_secret = \App\Services\GoogleAuthenticatorService::generateSecret();
            }
            if (!$user->qr_token) {
                $user->qr_token = 'SEC-AUTH-' . strtoupper(\Illuminate\Support\Str::random(24));
            }

            $user->save();
        }
    }
}
