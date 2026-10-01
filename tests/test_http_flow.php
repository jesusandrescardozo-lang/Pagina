<?php

$cookieJar = tempnam(sys_get_temp_dir(), 'nutri_cookie');

// 1. GET /login para obtener el CSRF token
$ch = curl_init('http://127.0.0.1:8000/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
$html = curl_exec($ch);
preg_match('/name="_token" value="([^"]+)"/', $html, $matches);
$token = $matches[1] ?? null;

echo "1. GET /login => CSRF Token: " . ($token ? 'OK (' . substr($token, 0, 8) . '...)' : 'MISSING') . PHP_EOL;

// 2. POST /login con credenciales
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    '_token' => $token,
    'email' => 'admin@refugios.ve',
    'password' => 'password',
]));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_HEADER, true);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

preg_match('/Location: (.+)\r\n/', $res, $loc);
$location = trim($loc[1] ?? 'None');

echo "2. POST /login => HTTP Status: {$httpCode}, Location: {$location}" . PHP_EOL;

if (str_contains($location, 'dashboard') && !str_contains($location, 'otp')) {
    echo ">>> EXITO ROTUNDO: El login va DIRECTO a /dashboard sin pedir OTP ni Google Authenticator!" . PHP_EOL;
} else {
    echo ">>> ALERTA: Redirigió a {$location}" . PHP_EOL;
}

// 3. Entrar a /dashboard usando la cookie de sesión
curl_setopt($ch, CURLOPT_URL, $location);
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_HEADER, false);
$dashHtml = curl_exec($ch);
$dashCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "3. GET {$location} => HTTP Code: {$dashCode}" . PHP_EOL;
echo "   - Contiene Topbar NutriLnk: " . (str_contains($dashHtml, 'NutriLnk') ? 'SI' : 'NO') . PHP_EOL;
echo "   - Contiene Botón Modo Oscuro/Claro (themeSwitchBtn): " . (str_contains($dashHtml, 'themeSwitchBtn') ? 'SI' : 'NO') . PHP_EOL;
echo "   - Contiene Dots estilo macOS (Image 2): " . (str_contains($dashHtml, 'macos-window-dots') ? 'SI' : 'NO') . PHP_EOL;

// 4. Probar que no hay rastro de Google Authenticator en el HTML del carnet ni en el login
echo "4. Verificando ausencia de Google Authenticator:" . PHP_EOL;
echo "   - Login tiene mención de 2FA/OTP: " . (str_contains($html, '2FA OTP') || str_contains($html, 'Google Authenticator') ? 'SI' : 'NO') . PHP_EOL;
echo "   - Dashboard tiene mención de OTP: " . (str_contains($dashHtml, 'otp.view') || str_contains($dashHtml, 'otp_verificado') ? 'SI' : 'NO') . PHP_EOL;

// 5. GET /carnets
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/carnets');
$carnetsHtml = curl_exec($ch);
echo "   - Carnets tiene columna Google Authenticator: " . (str_contains($carnetsHtml, 'Google Authenticator 2FA') ? 'SI' : 'NO') . PHP_EOL;

curl_close($ch);
@unlink($cookieJar);
echo "=== FIN DE VALIDACIÓN ===" . PHP_EOL;
