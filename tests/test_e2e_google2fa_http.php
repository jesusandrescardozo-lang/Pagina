<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\GoogleAuthenticatorService;

function testUserLoginAnd2fa(string $email, string $password, string $expectedName) {
    echo "\n>>> Probando autenticación Google Authenticator para: {$expectedName} ({$email})\n";
    $user = User::where('email', $email)->first();
    $cookieFile = __DIR__ . "/cookie_test_" . md5($email) . ".txt";
    if (file_exists($cookieFile)) unlink($cookieFile);

    // 1. GET /login
    $ch = curl_init('http://127.0.0.1:8000/login');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEJAR      => $cookieFile,
        CURLOPT_COOKIEFILE     => $cookieFile,
    ]);
    $html = curl_exec($ch);
    curl_close($ch);

    preg_match('/name="_token" value="([^"]+)"/', $html, $matches);
    $csrfToken = $matches[1] ?? '';

    // 2. POST /login
    $ch = curl_init('http://127.0.0.1:8000/login');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            '_token'   => $csrfToken,
            'email'    => $email,
            'password' => $password,
        ]),
        CURLOPT_COOKIEJAR      => $cookieFile,
        CURLOPT_COOKIEFILE     => $cookieFile,
        CURLOPT_FOLLOWLOCATION => false,
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code !== 302) {
        echo "  [FAIL] Login falló con HTTP {$code}\n";
        return false;
    }

    // 3. GET /auth/otp-verify
    $ch = curl_init('http://127.0.0.1:8000/auth/otp-verify');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEJAR      => $cookieFile,
        CURLOPT_COOKIEFILE     => $cookieFile,
    ]);
    $otpView = curl_exec($ch);
    curl_close($ch);

    preg_match('/name="csrf-token" content="([^"]+)"/', $otpView, $matches);
    $otpCsrf = $matches[1] ?? $csrfToken;

    // 4. POST TOTP Code
    $totpCode = GoogleAuthenticatorService::getCode($user->google2fa_secret);
    echo "  Clave Secreta: {$user->google2fa_secret}, Código TOTP actual: {$totpCode}\n";

    $ch = curl_init('http://127.0.0.1:8000/auth/otp-verify');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode(['code' => $totpCode]),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-CSRF-TOKEN: ' . $otpCsrf,
        ],
        CURLOPT_COOKIEJAR      => $cookieFile,
        CURLOPT_COOKIEFILE     => $cookieFile,
    ]);
    $otpRes = curl_exec($ch);
    $otpHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 5. GET /dashboard
    $ch = curl_init('http://127.0.0.1:8000/dashboard');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEJAR      => $cookieFile,
        CURLOPT_COOKIEFILE     => $cookieFile,
        CURLOPT_FOLLOWLOCATION => false,
    ]);
    curl_exec($ch);
    $dashCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (file_exists($cookieFile)) unlink($cookieFile);

    if ($otpHttpCode === 200 && $dashCode === 200) {
        echo "  [PASS] Verificación Google Authenticator y entrada al Dashboard exitosas para {$user->name}!\n";
        return true;
    } else {
        echo "  [FAIL] OTP HTTP {$otpHttpCode}, Dashboard HTTP {$dashCode}\n";
        return false;
    }
}

$r1 = testUserLoginAnd2fa('admin@refugios.ve', 'Admin1234!', 'Administrador');
$r2 = testUserLoginAnd2fa('coordinador@refugios.ve', 'Coordinador1234!', 'Coordinador');
$r3 = testUserLoginAnd2fa('almacen@refugios.ve', 'Almacen1234!', 'Almacenista');
$r4 = testUserLoginAnd2fa('auditor@refugios.ve', 'Auditor1234!', 'Auditora');

if ($r1 && $r2 && $r3 && $r4) {
    echo "\n=== TODOS LOS USUARIOS TIENEN GOOGLE AUTHENTICATOR FUNCIONANDO AL 100% ===\n";
    exit(0);
} else {
    echo "\n=== AL MENOS UN USUARIO FALLÓ ===\n";
    exit(1);
}
