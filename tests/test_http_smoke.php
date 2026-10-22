<?php
/**
 * PRUEBA DE HUMO HTTP — recorre todas las rutas del sistema.
 *
 * Se ejecuta dentro del propio kernel de Laravel (no necesita servidor):
 * comprueba que NINGUNA vista devuelva 500 ni muestre rastros de error,
 * y que la matriz de permisos se respete en cada rol.
 */

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pasados = 0;
$fallidos = 0;

function check(string $label, bool $ok, string $detalle = ''): void
{
    global $pasados, $fallidos;
    if ($ok) {
        $pasados++;
        echo "  [PASS] $label\n";
    } else {
        $fallidos++;
        echo "  [FAIL] $label" . ($detalle !== '' ? " — {$detalle}" : '') . "\n";
    }
}

/** Ejecuta una petición en el kernel y devuelve [status, body]. */
function requestAs($kernel, ?User $user, string $uri, string $method = 'GET', bool $guestMode = false): array
{
    $req = Request::create($uri, $method);
    $resp = null;

    if ($user) {
        Auth::login($user);
        $session = session();
        $session->put('otp_verificado', true);
        $session->put('_token', csrf_token());
        $req->setLaravelSession($session);
        $req->setUserResolver(fn () => $user);
        $req->headers->set('X-CSRF-TOKEN', csrf_token());
    } elseif ($guestMode) {
        $session = session();
        $session->put('guest_mode', true);
        $req->setLaravelSession($session);
    }

    try {
        $resp = $kernel->handle($req);
        return [$resp->getStatusCode(), (string) $resp->getContent()];
    } catch (\Throwable $e) {
        return [0, get_class($e) . ': ' . $e->getMessage()];
    } finally {
        $kernel->terminate($req, $resp);
        if ($user) {
            Auth::logout();
            session()->forget('otp_verificado');
        }
        if ($guestMode) {
            session()->forget('guest_mode');
        }
    }
}

$marcadores = '/Whoops|Internal Server Error|Undefined variable|ErrorException|Illuminate\\\\Exception|Stack trace|SQLSTATE/';

$usuarios = [
    'admin'          => User::where('rol', 'admin')->first(),
    'coordinador'    => User::where('rol', 'coordinador_refugio')->first(),
    'almacenista'    => User::where('rol', 'almacenista')->first(),
    'auditor'        => User::where('rol', 'auditor')->first(),
];
check('Existen los 4 usuarios del seeder', !in_array(null, $usuarios, true));

// ---------------------------------------------------------------
// 1. Visitante sin sesión
// ---------------------------------------------------------------
echo "\n== 1. Visitante sin sesión ==\n";
[$st] = requestAs($kernel, null, '/login');
check('GET /login -> 200', $st === 200, "obtuvo {$st}");

[$st, $body] = requestAs($kernel, null, '/');
check('GET / redirige', $st === 302, "obtuvo {$st}");

foreach (['/dashboard', '/shelters', '/supplies', '/audit', '/carnets'] as $uri) {
    [$st] = requestAs($kernel, null, $uri);
    check("GET {$uri} -> 302 (exige sesión)", $st === 302, "obtuvo {$st}");
}

[$st] = requestAs($kernel, null, '/esta-ruta-no-existe');
check('GET /ruta inexistente -> 404', $st === 404, "obtuvo {$st}");

// ---------------------------------------------------------------
// 2. Rutas de solo lectura con cualquier rol autenticado
// ---------------------------------------------------------------
echo "\n== 2. Rutas de solo lectura (4 roles) ==\n";
$lectura = ['/dashboard', '/shelters', '/supplies', '/movements', '/transfers',
            '/reports', '/reports/inventario-refugios',
            '/reports/kardex-movimientos', '/reports/analisis-operaciones'];

// Las URLs de detalle solo se añaden si existe al menos un registro:
// con tablas vacías, el fallback anterior generaba rutas inválidas
// (p. ej. /transfers/0) que devuelven 404 y falsaban el fallo.
$shelter = \App\Models\Shelter::orderBy('id')->first();
$supply = \App\Models\Supply::orderBy('id')->first();
$transfer = \App\Models\SupplyTransfer::orderBy('id')->first();

if ($shelter) {
    $lectura[] = "/shelters/{$shelter->id}";
}
if ($supply) {
    $lectura[] = "/supplies/{$supply->id}";
}
if ($transfer) {
    $lectura[] = "/transfers/{$transfer->id}";
}

foreach ($usuarios as $rol => $user) {
    $malas = [];
    foreach ($lectura as $uri) {
        [$st, $body] = requestAs($kernel, $user, $uri);
        if ($st !== 200) {
            $malas[] = "{$uri}=>{$st}";
        } elseif (preg_match($marcadores, $body)) {
            $malas[] = "{$uri}=>error en HTML";
        }
    }
    check("Rol '{$rol}' lee las " . count($lectura) . ' secciones sin errores', $malas === [], implode(', ', $malas));
}

// Cambiar de refugio sin parámetro redirige (nunca debe fallar con 500)
foreach ($usuarios as $rol => $user) {
    [$st, $body] = requestAs($kernel, $user, '/shelter/switch');
    check("Rol '{$rol}': GET /shelter/switch redirige sin error", in_array($st, [200, 302], true) && !preg_match($marcadores, $body), "obtuvo {$st}");
}

// ---------------------------------------------------------------
// 3. Modo invitado (solo lectura, sin autenticar)
// ---------------------------------------------------------------
echo "\n== 3. Modo invitado ==\n";
$guestRutas = ['/dashboard', '/shelters', '/supplies', '/movements', '/transfers', '/reports'];
$malas = [];
foreach ($guestRutas as $uri) {
    [$st, $body] = requestAs($kernel, null, $uri, 'GET', true);
    if ($st !== 200) {
        $malas[] = "{$uri}=>{$st}";
    } elseif (preg_match($marcadores, $body)) {
        $malas[] = "{$uri}=>error en HTML";
    }
}
check('Invitado accede a las ' . count($guestRutas) . ' secciones de lectura', $malas === [], implode(', ', $malas));

// El invitado no debe poder entrar a auditoría ni carnets
foreach (['/audit', '/carnets'] as $uri) {
    [$st] = requestAs($kernel, null, $uri, 'GET', true);
    check("Invitado NO entra a {$uri}", $st !== 200, "obtuvo {$st}");
}

// ---------------------------------------------------------------
// 4. Matriz de permisos en rutas de escritura
// ---------------------------------------------------------------
echo "\n== 4. Matriz de permisos (puertas de escritura) ==\n";
$puertas = [
    '/shelters/create'   => 'manage-shelters',
    '/supplies/create'   => 'manage-supplies',
    '/movements/create'  => 'manage-kardex',
    '/transfers/create'  => 'manage-transfers',
    '/audit'             => 'view-audit',
    '/carnets'           => 'manage-carnets',
];

foreach ($usuarios as $rol => $user) {
    $malas = [];
    foreach ($puertas as $uri => $gate) {
        $esperado = Gate::forUser($user)->allows($gate) ? 200 : 403;
        [$st] = requestAs($kernel, $user, $uri);
        if ($st !== $esperado) {
            $malas[] = "{$uri} esperaba {$esperado}, obtuvo {$st}";
        }
    }
    check("Rol '{$rol}' respeta la matriz de permisos", $malas === [], implode(' | ', $malas));
}

// ---------------------------------------------------------------
echo "\n=======================================================\n";
echo " RESULTADO: {$pasados}/" . ($pasados + $fallidos) . ' PETICIONES VERIFICADAS';
echo $fallidos === 0 ? " — SIN ERRORES\n" : " — {$fallidos} FALLO(S)\n";
echo "=======================================================\n";
exit($fallidos === 0 ? 0 : 1);
