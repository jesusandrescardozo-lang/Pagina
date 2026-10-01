<?php
// Verifica el LOGIN DIRECTO: con contraseña válida se entra al dashboard
// sin pasar por Google Authenticator ni código OTP (el 2FA fue retirado
// del flujo de autenticación; la ruta otp.view ya no existe).
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

echo "=== TEST: LOGIN DIRECTO SIN PASO DE GOOGLE AUTHENTICATOR ===" . PHP_EOL;

$usuario = User::where('email', 'admin@refugios.ve')->firstOrFail();
$resp = null;
$resp2 = null;

try {
    // 1) POST /login con credenciales correctas
    $req = Request::create('/login', 'POST', [
        'email' => 'admin@refugios.ve',
        'password' => 'Admin1234!',
    ]);
    // Nota: en consola la sesión no está iniciada, así que el token CSRF se
    // fija explícitamente (csrf_token() devolvería NULL antes de start()).
    $session = session();
    $session->put('_token', \Illuminate\Support\Str::random(40));
    $req->setLaravelSession($session);
    $req->setUserResolver(fn () => $usuario);
    $req->headers->set('X-CSRF-TOKEN', $session->token());

    $resp = $kernel->handle($req);
    $status = $resp->getStatusCode();
    $target = method_exists($resp, 'getTargetUrl') ? $resp->getTargetUrl() : '';

    echo "1. HTTP Status: {$status}" . PHP_EOL;
    echo "2. Redirect Target: {$target}" . PHP_EOL;

    $ok1 = $status === 302 && str_contains($target, 'dashboard');
    echo ($ok1 ? "[PASS]" : "[FAIL]") . " Login redirige DIRECTO al dashboard" . PHP_EOL;

    $ok2 = !str_contains($target, 'otp');
    echo ($ok2 ? "[PASS]" : "[FAIL]") . " Sin paso intermedio de OTP/2FA" . PHP_EOL;

    // 2) GET /dashboard con la sesión ya autenticada
    $dashReq = Request::create('/dashboard', 'GET');
    $dashReq->setLaravelSession($session);
    $dashRes = $kernel->handle($dashReq);
    $dashCode = $dashRes->getStatusCode();
    echo "3. Dashboard HTTP Status: {$dashCode}" . PHP_EOL;

    $ok3 = $dashCode === 200;
    echo ($ok3 ? "[PASS]" : "[FAIL]") . " El dashboard es accesible tras el login directo" . PHP_EOL;

    // 3) La ruta de verificación OTP ya no existe (2FA fuera del flujo)
    $ok4 = !Route::has('otp.view') && !Route::has('otp.verify');
    echo ($ok4 ? "[PASS]" : "[FAIL]") . " Las rutas otp.* ya no están registradas" . PHP_EOL;
} finally {
    if (isset($resp)) { $kernel->terminate($req, $resp); }
    if (isset($resp2)) { $kernel->terminate($dashReq, $resp2); }
    if (Auth::check()) { Auth::logout(); }
    session()->forget(['otp_verificado', '_token']);
}

$todo = isset($ok1, $ok2, $ok3, $ok4) && $ok1 && $ok2 && $ok3 && $ok4;
echo $todo
    ? "=== EXITO TOTAL: login directo al DASHBOARD sin Google Authenticator ni OTP ===" . PHP_EOL
    : "=== HUBO FALLOS ===" . PHP_EOL;
exit($todo ? 0 : 1);
