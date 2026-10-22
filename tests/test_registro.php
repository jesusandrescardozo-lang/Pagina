<?php
// Verificación del registro de empleados:
// - Dominio no institucional -> rechazado.
// - Dominio institucional -> aceptado (rol operador, cuenta creada).
// - Pinta de login renderiza OK.
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$fallos = 0;
function check(string $label, bool $ok): void
{
    global $fallos;
    echo ($ok ? '  [PASS] ' : '  [FAIL] ') . $label . PHP_EOL;
    if (! $ok) { $fallos++; }
}

function httpForm($kernel, string $uri, string $method = 'GET', array $data = [], ?User $user = null): array
{
    $req = Request::create($uri, $method, $data);
    $session = session();
    if (! $session->has('_token')) {
        $session->put('_token', \Illuminate\Support\Str::random(40));
    }
    $token = $session->token();
    $session->forget('errors');
    $req->setLaravelSession($session);
    if ($user) {
        $req->setUserResolver(fn () => $user);
    }
    $req->headers->set('X-CSRF-TOKEN', $token);

    $status = 0; $errors = []; $body = '';
    try {
        $resp = $kernel->handle($req);
        $status = $resp->getStatusCode();
        $body = method_exists($resp, 'getContent') ? $resp->getContent() : '';
        $errorsBag = session()->get('errors');
        if ($errorsBag) { $errors = $errorsBag->messages(); }
    } catch (\Throwable $e) {
        echo '       (excepción: ' . get_class($e) . ': ' . substr($e->getMessage(), 0, 140) . ")\n";
    } finally {
        $kernel->terminate($req, null);
    }

    return [$status, $errors, $body];
}

echo "===============================================\n";
echo " TEST: REGISTRO INSTITUCIONAL\n";
echo "===============================================\n";

// Pinta de login debe renderizar (verifica Blade + config de dominios)
[$st, , $body] = httpForm($kernel, '/login', 'GET');
check('GET /login -> 200', $st === 200);
check('Login muestra enlace de registro (dominios configurados)', str_contains($body, "switchMode('register')"));
check('Login indica el dominio institucional permitido', str_contains($body, 'refugios.ve'));

$dominios = config('app.dominios_registro');
check('Config app.dominios_registro parsea lista', is_array($dominios) && in_array('refugios.ve', $dominios, true));

DB::beginTransaction();

try {
    $emailPermiso = 'prueba.registro.' . uniqid() . '@refugios.ve';
    $emailBloqueo = 'intruso.' . uniqid() . '@gmail.com';
    $password = 'Secre123!';

    // 1) Dominio no institucional -> rechazado
    [$st, $errs] = httpForm($kernel, '/register', 'POST', [
        'name' => 'Usuario Externo',
        'email' => $emailBloqueo,
        'cargo' => 'Otro',
        'password' => $password,
        'password_confirmation' => $password,
    ]);
    check('Registro con dominio gmail -> rechazado (302)', $st === 302);
    check('Error apunta al campo email', isset($errs['email']));
    check('Mensaje menciona dominios institucionales', str_contains(implode(' ', $errs['email'] ?? ''), 'institucionales'));
    check('Cuenta NO creada con dominio bloqueado', ! User::where('email', $emailBloqueo)->exists());

    // 2) Dominio institucional -> aceptado
    [$st, $errs] = httpForm($kernel, '/register', 'POST', [
        'name' => 'Empleado Registro Test',
        'email' => $emailPermiso,
        'cargo' => 'Almacenista',
        'password' => $password,
        'password_confirmation' => $password,
    ]);
    $user = User::where('email', $emailPermiso)->first();
    check('Registro con dominio refugios.ve -> sin errores de validación', !$errs);
    check('Cuenta creada', $user !== null);
    check('Rol asignado es operador (solo lectura)', $user && $user->rol === 'operador');
    check('Password hasheado (no en claro)', $user && Hash::check($password, $user->password));

    // 3) Case-insensitive: el dominio en mayúsculas también pasa
    $emailCase = 'prueba.case.' . uniqid() . '@REFUGIOS.VE';
    [$st, $errs] = httpForm($kernel, '/register', 'POST', [
        'name' => 'Empleado Case Test',
        'email' => $emailCase,
        'cargo' => 'Auditor',
        'password' => $password,
        'password_confirmation' => $password,
    ]);
    check('Dominio en mayúsculas aceptado (case-insensitive)', !$errs && User::where('email', $emailCase)->exists());

    // 4) Contraseña débil -> rechazada por Password::min(8)
    [$st, $errs] = httpForm($kernel, '/register', 'POST', [
        'name' => 'Empleado Pass Test',
        'email' => 'prueba.pass.' . uniqid() . '@refugios.ve',
        'cargo' => 'Otro',
        'password' => '12345678',
        'password_confirmation' => '12345678',
    ]);
    check('Contraseña débil (sin letras/símbolos) -> rechazada', isset($errs['password']));
} finally {
    DB::rollBack();
}

echo PHP_EOL . ($fallos === 0 ? " TODAS LAS VERIFICACIONES PASARON\n" : " {$fallos} FALLOS\n");
exit($fallos === 0 ? 0 : 1);
