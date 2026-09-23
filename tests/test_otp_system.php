<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
config(['database.default' => 'sqlite']);

echo "========================================================\n";
echo "--- INICIANDO PRUEBAS DEL SISTEMA DE VERIFICACIÓN OTP ---\n";
echo "========================================================\n\n";

// 1. Obtener usuario de prueba
$user = App\Models\User::where('email', 'admin@refugios.ve')->first();
if (!$user) {
    $user = App\Models\User::first();
}
echo "1. Usuario de prueba seleccionado: {$user->name} ({$user->email})\n";
Illuminate\Support\Facades\Auth::login($user);

// 2. El código correcto es el TOTP vigente de Google Authenticator del usuario
$initialCode = App\Services\GoogleAuthenticatorService::getCode($user->getGoogle2faSecret());
session([
    'otp_attempts' => 0,
    'otp_last_attempt_at' => null,
]);
session()->forget('otp_verificado');

echo "2. Código TOTP vigente del usuario: {$initialCode} (ventana de 30s)\n";

// 3. Probar Middleware EnsureOtpVerified SIN otp_verificado
$middleware = new App\Http\Middleware\EnsureOtpVerified();
$req = Illuminate\Http\Request::create('/dashboard', 'GET');
$req->setLaravelSession(session());

$res = $middleware->handle($req, function() {
    return 'dashboard_accedido';
});

if ($res instanceof \Illuminate\Http\RedirectResponse && $res->getTargetUrl() === route('otp.view')) {
    echo "3. Test Middleware Bloqueo: PASÓ -> Redirige correctamente a " . $res->getTargetUrl() . "\n";
} else {
    echo "3. Test Middleware Bloqueo: FALLÓ -> Permitió acceso no autorizado\n";
    exit(1);
}

// 4. Probar OtpAuthController@verify con código INCORRECTO
$controller = new App\Http\Controllers\Auth\OtpAuthController();
$reqInvalid = Illuminate\Http\Request::create('/auth/otp-verify', 'POST', ['code' => '000000']);
$reqInvalid->setLaravelSession(session());
$reqInvalid->headers->set('Accept', 'application/json');

$resInvalid = $controller->verify($reqInvalid);
$dataInvalid = json_decode($resInvalid->getContent(), true);

if ($resInvalid->getStatusCode() === 422 && empty($dataInvalid['success'])) {
    echo "4. Test Código Erróneo: PASÓ -> Rechazado con mensaje: '{$dataInvalid['message']}'\n";
} else {
    echo "4. Test Código Erróneo: FALLÓ\n";
    exit(1);
}

// 5. Probar OtpAuthController@verify con código CORRECTO
$reqValid = Illuminate\Http\Request::create('/auth/otp-verify', 'POST', ['code' => $initialCode]);
$reqValid->setLaravelSession(session());
$reqValid->headers->set('Accept', 'application/json');

$resValid = $controller->verify($reqValid);
$dataValid = json_decode($resValid->getContent(), true);

if ($resValid->getStatusCode() === 200 && !empty($dataValid['success']) && session('otp_verificado') === true) {
    echo "5. Test Código Correcto: PASÓ -> '{$dataValid['message']}' | otp_verificado en sesión = SI\n";
} else {
    echo "5. Test Código Correcto: FALLÓ\n";
    exit(1);
}

// 6. Probar Middleware EnsureOtpVerified TRAS validación exitosa
$reqAfter = Illuminate\Http\Request::create('/dashboard', 'GET');
$reqAfter->setLaravelSession(session());

$resAfter = $middleware->handle($reqAfter, function() {
    return 'ACCESO_CONCEDIDO_AL_DASHBOARD';
});

if ($resAfter === 'ACCESO_CONCEDIDO_AL_DASHBOARD') {
    echo "6. Test Middleware Tras OTP: PASÓ -> Acceso permitido al Dashboard\n";
} else {
    echo "6. Test Middleware Tras OTP: FALLÓ\n";
    exit(1);
}

// 7. Probar límite de intentos y su ventana de recuperación (60 s)
session()->forget('otp_verificado');
session(['otp_attempts' => 5, 'otp_last_attempt_at' => time()]); // bloqueo activo

$reqLimited = Illuminate\Http\Request::create('/auth/otp-verify', 'POST', ['code' => '000000']);
$reqLimited->setLaravelSession(session());
$reqLimited->headers->set('Accept', 'application/json');
$resLimited = $controller->verify($reqLimited);

if ($resLimited->getStatusCode() === 429) {
    echo "7. Test Límite de 5 intentos: PASÓ -> Bloqueado con 429\n";
} else {
    echo "7. Test Límite de 5 intentos: FALLÓ (status {$resLimited->getStatusCode()})\n";
    exit(1);
}

// Tras 60 s sin intentos, el contador debe reiniciarse
session(['otp_attempts' => 5, 'otp_last_attempt_at' => time() - 61]);

$reqWindow = Illuminate\Http\Request::create('/auth/otp-verify', 'POST', ['code' => '000000']);
$reqWindow->setLaravelSession(session());
$reqWindow->headers->set('Accept', 'application/json');
$resWindow = $controller->verify($reqWindow);
$dataWindow = json_decode($resWindow->getContent(), true);

if ($resWindow->getStatusCode() === 422 && str_contains($dataWindow['message'] ?? '', 'Intento 1')) {
    echo "8. Test Ventana de recuperación (60 s): PASÓ -> Contador reiniciado, vuelve a intento 1\n";
} else {
    echo "8. Test Ventana de recuperación: FALLÓ (status {$resWindow->getStatusCode()}, msg: " . ($dataWindow['message'] ?? 'n/a') . ")\n";
    exit(1);
}

echo "\n========================================================\n";
echo "--- TODAS LAS PRUEBAS DE VERIFICACIÓN OTP EXITOSAS ---\n";
echo "========================================================\n";
