<?php
// Flujo completo: registrar cuenta de empleado -> iniciar sesión con ella.
// Reproduce el fallo reportado: "Las credenciales proporcionadas no coinciden".
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$fallos = 0;
function check(string $label, bool $ok, string $detalle = ''): void
{
    global $fallos;
    echo ($ok ? '  [PASS] ' : '  [FAIL] ') . $label . ($ok ? '' : " — {$detalle}") . PHP_EOL;
    if (! $ok) { $fallos++; }
}

function http($kernel, string $uri, string $method = 'GET', array $data = []): array
{
    $req = Request::create($uri, $method, $data);
    $session = session();
    if (! $session->has('_token')) {
        $session->put('_token', Illuminate\Support\Str::random(40));
    }
    $token = $session->token();
    $session->forget('errors');
    $req->setLaravelSession($session);
    $req->headers->set('X-CSRF-TOKEN', $token);

    $status = 0; $errors = []; $body = ''; $location = '';
    try {
        $resp = $kernel->handle($req);
        $status = $resp->getStatusCode();
        $body = method_exists($resp, 'getContent') ? $resp->getContent() : '';
        $location = $resp->headers->get('Location') ?? '';
        $bag = session()->get('errors');
        if ($bag) { $errors = $bag->messages(); }
    } catch (\Throwable $e) {
        echo '       (excepción: ' . get_class($e) . ': ' . substr($e->getMessage(), 0, 160) . ")\n";
    } finally {
        $kernel->terminate($req, null);
    }

    return [$status, $errors, $body, $location];
}

echo "================================================\n";
echo " TEST: REGISTRO -> LOGIN (flujo completo)\n";
echo "================================================\n";

$email = 'flujo.login.' . uniqid() . '@refugios.ve';
$clave = 'Secre123!';

// 1) Registro
[$st, $errs] = http($kernel, '/register', 'POST', [
    'name' => 'Empleado Flujo Login',
    'email' => $email,
    'cargo' => 'Almacenista',
    'password' => $clave,
    'password_confirmation' => $clave,
]);
check('POST /register sin errores de validación', ! $errs, json_encode($errs));
$user = User::where('email', $email)->first();
check('Usuario creado en BD', $user !== null);

if ($user) {
    echo '       -> id=' . $user->id . ' rol=' . $user->rol . "\n";

    // 2) Diagnóstico del hash guardado
    $guardado = DB::table('users')->where('id', $user->id)->value('password');
    echo '       -> hash en BD (crudo): ' . substr($guardado, 0, 20) . "...\n";
    check('Hash::check directo sobre el modelo', Hash::check($clave, $user->password));
    check('Hash::check directo sobre valor crudo de BD', Hash::check($clave, $guardado));
    check('No está hasheado dos veces (no empieza por $2y$ tras otro hash)',
        ! str_starts_with($guardado, '$2y$') || substr_count($guardado, '$2y$') === 1);

    // 3) Intento de login real por HTTP (lo que ve el usuario)
    [$st, $errs, $body, $loc] = http($kernel, '/login', 'POST', [
        'email' => $email,
        'password' => $clave,
    ]);
    echo "       -> POST /login status={$st} Location={$loc}\n";
    check('POST /login NO devuelve error de credenciales', ! isset($errs['email']), json_encode($errs));
    check('POST /login redirige tras iniciar (302)', $st === 302, "status={$st}");
    check('Redirige al dashboard', str_contains($loc, '/dashboard'), $loc);

    // 4) Verificar también Auth::attempt directo
    $ok = Auth::attempt(['email' => $email, 'password' => $clave]);
    check('Auth::attempt directo', $ok, 'attempt devolvió false');
    Auth::logout();

    // 5) Comprobar contra la contraseña de los usuarios sembrados (control)
    $admin = User::where('rol', 'admin')->first();
    if ($admin) {
        $adminCrudo = DB::table('users')->where('id', $admin->id)->value('password');
        echo '       -> hash admin (crudo): ' . substr($adminCrudo, 0, 20) . "...\n";
        check('Usuarios sembrados: hash distinto al registrado',
            $adminCrudo !== $guardado, '¡mismo hash que el usuario nuevo!');
    }

    // Limpieza
    User::where('id', $user->id)->delete();
}

echo PHP_EOL . ($fallos === 0 ? " FLUJO COMPLETO OK\n" : " {$fallos} FALLOS\n");
exit($fallos === 0 ? 0 : 1);
