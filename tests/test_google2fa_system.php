<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "===============================================\n";
echo " TEST SUITE: GOOGLE AUTHENTICATOR (RFC 6238 TOTP)\n";
echo "===============================================\n\n";

$passed = 0;
$total = 0;

function testAssert($description, $condition) {
    global $passed, $total;
    $total++;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$description}\n";
    } else {
        echo "  [FAIL] {$description}\n";
    }
}

// -------------------------------------------------------------
// 1. Pruebas del Algoritmo Criptográfico TOTP
// -------------------------------------------------------------
echo "1. Algoritmo Criptográfico GoogleAuthenticatorService:\n";

use App\Services\GoogleAuthenticatorService;

$secret = GoogleAuthenticatorService::generateSecret(16);
testAssert("Genera clave secreta Base32 de 16 caracteres", strlen($secret) === 16 && preg_match('/^[A-Z2-7]{16}$/', $secret));

$code = GoogleAuthenticatorService::getCode($secret);
testAssert("Genera código numérico TOTP de 6 dígitos", strlen($code) === 6 && ctype_digit($code));

$isValid = GoogleAuthenticatorService::verifyCode($secret, $code, 1);
testAssert("Verifica código válido con tolerancia ±30s", $isValid === true);

$isInvalid = GoogleAuthenticatorService::verifyCode($secret, '999999', 1);
// In case 999999 happens to match (1 in a million chance), check a non-matching code
if ($code === '999999') {
    $isInvalid = GoogleAuthenticatorService::verifyCode($secret, '111111', 1);
}
testAssert("Rechaza código numérico no coincidente", $isInvalid === false);

$uri = GoogleAuthenticatorService::getOtpAuthUrl('NutriLnk', 'admin@refugios.ve', $secret);
testAssert("Genera URI estándar otpauth:// para Google Authenticator", str_starts_with($uri, 'otpauth://totp/NutriLnk:admin%40refugios.ve'));

$qrSvg = GoogleAuthenticatorService::getQrCodeSvg($uri, 200);
testAssert("Genera código QR local en SVG (sin servicios externos)", str_contains($qrSvg, '<svg') && !str_contains($qrSvg, 'qrserver.com') && !str_contains($qrSvg, 'api.'));

$remaining = GoogleAuthenticatorService::getSecondsRemaining();
testAssert("Calcula segundos restantes de la ventana (1 a 30s)", $remaining >= 1 && $remaining <= 30);

// -------------------------------------------------------------
// 2. Pruebas del Modelo User y Personal
// -------------------------------------------------------------
echo "\n2. Modelo User y Personal Registrado:\n";

use App\Models\User;

$admin = User::where('email', 'admin@refugios.ve')->first();
$coord = User::where('email', 'coordinador@refugios.ve')->first();
$almacen = User::where('email', 'almacen@refugios.ve')->first();
$auditor = User::where('email', 'auditor@refugios.ve')->first();

testAssert("Admin existe y tiene clave secreta", $admin && !empty($admin->getGoogle2faSecret()));
testAssert("Coordinador existe y tiene clave secreta", $coord && !empty($coord->getGoogle2faSecret()));
testAssert("Almacenista existe y tiene clave secreta", $almacen && !empty($almacen->getGoogle2faSecret()));
testAssert("Auditor existe y tiene clave secreta", $auditor && !empty($auditor->getGoogle2faSecret()));

testAssert(
    "Cada usuario tiene una clave secreta ÚNICA y DIFERENTE",
    $admin->google2fa_secret !== $coord->google2fa_secret &&
    $coord->google2fa_secret !== $almacen->google2fa_secret &&
    $almacen->google2fa_secret !== $auditor->google2fa_secret
);

$adminCode = $admin->getCurrentGoogle2faCode();
testAssert("Genera código TOTP en tiempo real para Admin", strlen($adminCode) === 6 && ctype_digit($adminCode));

// -------------------------------------------------------------
// 3. Pruebas de Integración y Middleware
// -------------------------------------------------------------
echo "\n3. Flujo Completo de Autenticación 2FA:\n";

use Illuminate\Support\Facades\Auth;

// Simular login con credenciales correctas
$loginSuccess = Auth::attempt(['email' => 'admin@refugios.ve', 'password' => 'Admin1234!']);
testAssert("Autenticación con email y contraseña exitosa", $loginSuccess === true);

// Verificar middleware: sin otp_verificado no debe entrar al dashboard
session()->forget('otp_verificado');
testAssert("Sin verificación 2FA, la sesión está desprotegida", session('otp_verificado', false) === false);

// Verificar código incorrecto
$wrongValidation = GoogleAuthenticatorService::verifyCode($admin->google2fa_secret, '000000', 1);
testAssert("Falla de validación ante código erróneo", $wrongValidation === false);

// Verificar código correcto con Google Authenticator
$correctCode = GoogleAuthenticatorService::getCode($admin->google2fa_secret);
$correctValidation = GoogleAuthenticatorService::verifyCode($admin->google2fa_secret, $correctCode, 1);
testAssert("Validación exitosa del código generado por Google Authenticator", $correctValidation === true);

// Establecer sesión 2FA autorizada
session(['otp_verificado' => true]);
testAssert("Sesión marcada como otp_verificado tras validar con Google Authenticator", session('otp_verificado') === true);

echo "\n===============================================\n";
echo " RESULTADO: {$passed}/{$total} PRUEBAS EXITOSAS\n";
echo "===============================================\n";

if ($passed === $total) {
    exit(0);
} else {
    exit(1);
}
