<?php
/**
 * Flujo HTTP real de inicio de sesión (requiere `php artisan serve` en :8000).
 *
 * Valida que el login vaya DIRECTO al panel (sin 2FA/OTP), que el dashboard
 * renderice y que no queden rastros de Google Authenticator en la interfaz.
 */

const BASE = 'http://127.0.0.1:8000';

$cookieJar = tempnam(sys_get_temp_dir(), 'nutri_cookie');
$failures = 0;

function check(string $label, bool $ok): void
{
    global $failures;
    if ($ok) {
        echo "  [PASS] $label\n";
    } else {
        echo "  [FAIL] $label\n";
        $failures++;
    }
}

// 1. GET /login para obtener la cookie de sesión y el token CSRF
$ch = curl_init(BASE . '/login');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEJAR      => $cookieJar,
    CURLOPT_COOKIEFILE     => $cookieJar,
    CURLOPT_TIMEOUT        => 15,
]);
$html = curl_exec($ch);
$statusLogin = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "1. GET /login => HTTP {$statusLogin}\n";
check('GET /login responde 200', $statusLogin === 200);

preg_match('/name="_token" value="([^"]+)"/', (string) $html, $m);
$token = $m[1] ?? null;
check('Se obtiene el token CSRF', $token !== null);

// 2. POST /login con las credenciales REALES del seeder
curl_setopt_array($ch, [
    CURLOPT_URL      => BASE . '/login',
    CURLOPT_POST     => true,
    CURLOPT_POSTFIELDS => http_build_query([
        '_token'    => $token,
        'email'     => 'admin@refugios.ve',
        'password'  => 'Admin1234!',
    ]),
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_HEADER         => true,
]);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

preg_match('/^Location:\s*(.+)$/mi', (string) $res, $loc);
$location = trim($loc[1] ?? 'None');

echo "2. POST /login => HTTP {$httpCode}, Location: {$location}\n";
check('El login devuelve 302 (redirección)', $httpCode === 302);
check('Redirige DIRECTO a /dashboard', str_contains($location, '/dashboard'));
check('NO redirige a un paso OTP/2FA', !str_contains($location, 'otp'));

// 3. GET /dashboard con la sesión recién creada
curl_setopt_array($ch, [
    CURLOPT_URL      => $location,
    CURLOPT_POST     => false,
    CURLOPT_HEADER   => false,
    CURLOPT_FOLLOWLOCATION => true,
]);
$dashHtml = curl_exec($ch);
$dashCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "3. GET {$location} => HTTP {$dashCode}\n";
check('El dashboard responde 200', $dashCode === 200);
check('Contiene el topbar NutriLnk', str_contains((string) $dashHtml, 'NutriLnk'));
check('Contiene el conmutador de tema (themeSwitchBtn)', str_contains((string) $dashHtml, 'themeSwitchBtn'));
check('Sin errores visibles en el HTML', !preg_match('/Whoops|Symfony\\\\Component\\\\HttpKernel|Internal Server Error|Undefined variable|ErrorException/', (string) $dashHtml));

// 4. Rutas protegidas y de solo lectura
foreach (['/shelters', '/supplies', '/movements', '/transfers', '/reports', '/audit', '/carnets'] as $uri) {
    curl_setopt($ch, CURLOPT_URL, BASE . $uri);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    check("GET {$uri} => 200", $code === 200 && !preg_match('/Whoops|Internal Server Error/', (string) $body));
}

// 5. Sin rastro de Google Authenticator / 2FA en la interfaz
echo "4. Verificando ausencia de Google Authenticator:\n";
check('Login sin mención de 2FA/OTP',
    !str_contains((string) $html, '2FA OTP') && !str_contains((string) $html, 'Google Authenticator'));
check('Dashboard sin mención de OTP',
    !str_contains((string) $dashHtml, 'otp.view') && !str_contains((string) $dashHtml, 'otp_verificado'));

curl_setopt($ch, CURLOPT_URL, BASE . '/carnets');
$carnetsHtml = (string) curl_exec($ch);
check('Carnets sin columna "Google Authenticator 2FA"', !str_contains($carnetsHtml, 'Google Authenticator 2FA'));

curl_close($ch);
@unlink($cookieJar);

echo $failures === 0
    ? "\n=== FLUJO HTTP COMPLETO: 100% EXITOSO ===\n"
    : "\n=== FLUJO HTTP: {$failures} FALLO(S) ===\n";
exit($failures === 0 ? 0 : 1);
