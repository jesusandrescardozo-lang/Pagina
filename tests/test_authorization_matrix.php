<?php
// Prueba la matriz de permisos (Gates + middleware can:) y el QR SVG en carnets.
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

$pasados = 0; $total = 0;
function check(string $label, bool $ok): void
{
    global $pasados, $total;
    $total++;
    if ($ok) { $pasados++; echo "  [PASS] $label\n"; }
    else { echo "  [FAIL] $label\n"; }
}

echo "===============================================\n";
echo " TEST: MATRIZ DE PERMISOS (Gates + can:)\n";
echo "===============================================\n";

$admin      = User::where('rol', 'admin')->first();
$coordinador = User::where('rol', 'coordinador_refugio')->first();
$almacenista = User::where('rol', 'almacenista')->first();
$auditor    = User::where('rol', 'auditor')->first();

check('Existen los 4 usuarios de prueba', $admin && $coordinador && $almacenista && $auditor);

// ---------- Matriz de Gates ----------
check('admin puede manage-supplies',          Gate::forUser($admin)->allows('manage-supplies'));
check('almacenista puede manage-supplies',     Gate::forUser($almacenista)->allows('manage-supplies'));
check('auditor NO puede manage-supplies',      !Gate::forUser($auditor)->allows('manage-supplies'));
check('coordinador NO puede manage-supplies',  !Gate::forUser($coordinador)->allows('manage-supplies'));
check('auditor puede view-audit',              Gate::forUser($auditor)->allows('view-audit'));
check('almacenista NO puede view-audit',       !Gate::forUser($almacenista)->allows('view-audit'));
check('solo admin manage-carnets',             Gate::forUser($admin)->allows('manage-carnets') && !Gate::forUser($auditor)->allows('manage-carnets'));
check('coordinador puede manage-transfers',    Gate::forUser($coordinador)->allows('manage-transfers'));
check('auditor NO puede manage-transfers',     !Gate::forUser($auditor)->allows('manage-transfers'));
check('auditor puede access-reports',          Gate::forUser($auditor)->allows('access-reports'));
check('coordinador NO puede manage-kardex',    !Gate::forUser($coordinador)->allows('manage-kardex'));

// Coherencia User::canX() <-> Gates (las vistas usan los métodos)
check('User::canManageSupplies() coherente con Gate',
    $auditor->canManageSupplies() === false && $almacenista->canManageSupplies() === true);
check('User::canAccessAudit() coherente con Gate',
    $auditor->canAccessAudit() === true && $coordinador->canAccessAudit() === false);

// ---------- HTTP con middleware can: ----------
function httpAs($kernel, $user, string $uri, string $method = 'GET', array $data = []): int {
    $req = Request::create($uri, $method, $data);
    if ($user) {
        \Illuminate\Support\Facades\Auth::login($user);
        $session = session();
        $session->put('otp_verificado', true);
        $session->put('_token', csrf_token());
        $req->setLaravelSession($session);
        $req->setUserResolver(fn() => $user);
        $req->headers->set('X-CSRF-TOKEN', csrf_token());
    }
    try {
        $resp = $kernel->handle($req);
        return $resp->getStatusCode();
    } catch (\Throwable $e) {
        return 0;
    } finally {
        $kernel->terminate($req, $resp ?? null);
        \Illuminate\Support\Facades\Auth::logout();
        session()->forget('otp_verificado');
    }
}

echo "\n-- HTTP --\n";
check('Auditor GET /supplies/create -> 403',   httpAs($kernel, $auditor, '/supplies/create') === 403);
check('Admin GET /supplies/create -> 200',     httpAs($kernel, $admin, '/supplies/create') === 200);
check('Auditor GET /audit -> 200',             httpAs($kernel, $auditor, '/audit') === 200);
check('Almacenista GET /audit -> 403',         httpAs($kernel, $almacenista, '/audit') === 403);
check('Admin GET /carnets -> 200',             httpAs($kernel, $admin, '/carnets') === 200);
check('Auditor GET /carnets -> 403',           httpAs($kernel, $auditor, '/carnets') === 403);

// ---------- QR SVG en carnets (sin servicios externos) ----------
$auditorSinSesion = null;
$admin->regenerateQrToken(); // asegura token
$code = null;
$resp = null;
$req = Request::create('/carnets/' . $admin->id, 'GET');
\Auth::login($admin);
$session = session();
$session->put('otp_verificado', true);
$req->setLaravelSession($session);
$req->setUserResolver(fn() => $admin);
$resp = $kernel->handle($req);
$html = $resp->getContent();
check('GET /carnets/{admin} -> 200', $resp->getStatusCode() === 200);
// El 2FA fue retirado del flujo: el carnet ya no muestra QR de Google
// Authenticator ni secretos; conserva el token físico de credencial.
check('Carnet sin rastros de Google Authenticator/2FA',
    !str_contains($html, 'Authenticator') && !str_contains($html, 'google2fa'));
check('Carnet muestra el token de credencial', str_contains($html, $admin->qr_token));
\Auth::logout();
$kernel->terminate($req, $resp);

echo "\n RESULTADO: $pasados/$total PRUEBAS EXITOSAS\n";
exit($pasados === $total ? 0 : 1);
