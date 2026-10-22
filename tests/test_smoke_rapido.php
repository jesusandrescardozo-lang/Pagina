<?php
// Smoke test RÁPIDO: verifica que las páginas principales rendericen (200)
// y no devuelvan 500. Usa el kernel directamente, sin servidor.
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$fallos = 0;
function check(string $label, bool $ok, string $detalle = ''): void
{
    global $fallos;
    echo ($ok ? '  [PASS] ' : '  [FAIL] ') . $label . ($ok ? '' : " — {$detalle}") . PHP_EOL;
    if (! $ok) { $fallos++; }
}

function get($kernel, ?User $user, string $uri): array
{
    $req = Request::create($uri, 'GET');
    if ($user) {
        Auth::login($user);
        $session = session();
        $session->put('otp_verificado', true);
        $req->setLaravelSession($session);
        $req->setUserResolver(fn () => $user);
    }
    try {
        $resp = $kernel->handle($req);
        return [$resp->getStatusCode(), (string) $resp->getContent()];
    } catch (\Throwable $e) {
        return [0, get_class($e) . ': ' . substr($e->getMessage(), 0, 120)];
    } finally {
        $kernel->terminate($req, null);
        Auth::logout();
    }
}

echo "===============================================\n";
echo " TEST: SMOKE RAPIDO DE PAGINAS\n";
echo "===============================================\n";

$admin = User::where('rol', 'admin')->firstOrFail();

// Públicas
[$st] = get($kernel, null, '/login');
check('GET /login -> 200', $st === 200, "status={$st}");

// Autenticadas (admin)
$paginas = [
    '/dashboard' => 'Dashboard',
    '/shelters'  => 'Refugios (índice)',
    '/supplies'  => 'Insumos (índice)',
    '/movements' => 'Kardex (índice)',
    '/transfers' => 'Transferencias (índice)',
    '/reports'   => 'Reportes',
    '/audit'     => 'Auditoría',
    '/carnets'   => 'Carnets',
];
foreach ($paginas as $uri => $nombre) {
    [$st, $body] = get($kernel, $admin, $uri);
    $hayError = str_contains($body, 'Server Error') || str_contains($body, 'Whoops') || str_contains($body, 'Exception');
    check("GET {$uri} -> 200 sin errores ({$nombre})", $st === 200 && ! $hayError, "status={$st}");
}

echo PHP_EOL . ($fallos === 0 ? " TODAS LAS PAGINAS OK\n" : " {$fallos} FALLOS\n");
exit($fallos === 0 ? 0 : 1);
