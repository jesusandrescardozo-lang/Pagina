<?php
// E2E por HTTP: login con contraseña y acceso DIRECTO al dashboard.
// El paso de Google Authenticator/OTP fue retirado: tras POST /login la
// respuesta debe redirigir a /dashboard y /auth/otp-verify ya no existe (404).
//
// REQUIERE el servidor local corriendo:  php artisan serve
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

const BASE = 'http://127.0.0.1:8000';

function curlReq(string $url, array $opts = []): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, $opts + [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, $body];
}

function testLoginDirecto(string $email, string $password, string $etiqueta): bool
{
    echo "\n>>> Login directo: {$etiqueta} ({$email})\n";
    $cookieFile = __DIR__ . '/cookie_login_' . md5($email) . '.txt';
    if (file_exists($cookieFile)) {
        unlink($cookieFile);
    }

    // 1. GET /login -> token CSRF
    [$code, $html] = curlReq(BASE . '/login', [
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
    ]);
    if ($code !== 200) {
        echo "  [FAIL] GET /login devolvió {$code} (¿servidor corriendo? php artisan serve)\n";
        return false;
    }
    preg_match('/name="_token" value="([^"]+)"/', $html, $m);
    $csrf = $m[1] ?? '';

    // 2. POST /login -> debe ir directo al dashboard (capturando la cabecera Location)
    $ch = curl_init(BASE . '/login');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            '_token' => $csrf,
            'email' => $email,
            'password' => $password,
        ]),
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER => true,
        CURLOPT_RETURNTRANSFER => true,
    ]);
    $raw = curl_exec($ch);
    $postCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    preg_match('/^Location:\s*(.+)$/mi', (string) $raw, $loc);
    $location = trim($loc[1] ?? '');

    if ($postCode !== 302 || !str_contains($location, '/dashboard')) {
        echo "  [FAIL] POST /login -> HTTP {$postCode}, Location: " . ($location ?: '(ninguna)') . "\n";
        if (file_exists($cookieFile)) { unlink($cookieFile); }
        return false;
    }
    if (str_contains($location, 'otp')) {
        echo "  [FAIL] El login redirigió a OTP: {$location}\n";
        if (file_exists($cookieFile)) { unlink($cookieFile); }
        return false;
    }

    // 3. GET /dashboard con la sesión recién creada -> 200
    $ch = curl_init(BASE . '/dashboard');
    curl_setopt_array($ch, [
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_RETURNTRANSFER => true,
    ]);
    curl_exec($ch);
    $dashCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 4. GET /auth/otp-verify -> 404 (ruta retirada)
    $ch = curl_init(BASE . '/auth/otp-verify');
    curl_setopt_array($ch, [
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_RETURNTRANSFER => true,
    ]);
    curl_exec($ch);
    $otpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (file_exists($cookieFile)) { unlink($cookieFile); }

    if ($dashCode === 200 && $otpCode === 404) {
        echo "  [PASS] Login directo al dashboard (200) y /auth/otp-verify inexistente (404) para {$etiqueta}\n";
        return true;
    }
    echo "  [FAIL] Dashboard HTTP {$dashCode}, /auth/otp-verify HTTP {$otpCode}\n";
    return false;
}

// ¿Está el servidor arriba?
[$probe] = curlReq(BASE . '/login');
if ($probe === 0) {
    echo "ERROR: no hay servidor en " . BASE . "\n";
    echo "Inicialo en otra terminal con:  php artisan serve\n";
    exit(1);
}

$r1 = testLoginDirecto('admin@refugios.ve', 'Admin1234!', 'Administrador');
$r2 = testLoginDirecto('coordinador@refugios.ve', 'Coordinador1234!', 'Coordinador');
$r3 = testLoginDirecto('almacen@refugios.ve', 'Almacen1234!', 'Almacenista');
$r4 = testLoginDirecto('auditor@refugios.ve', 'Auditor1234!', 'Auditora');

if ($r1 && $r2 && $r3 && $r4) {
    echo "\n=== LOGIN DIRECTO 100% FUNCIONAL PARA LOS 4 USUARIOS (SIN 2FA) ===\n";
    exit(0);
}
echo "\n=== AL MENOS UN USUARIO FALLÓ ===\n";
exit(1);
