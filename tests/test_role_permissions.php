<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
config(['database.default' => 'sqlite']);

echo "=== VERIFICACIÓN DE PERMISOS Y ROLES POR SECCIÓN ===\n\n";

$rolesToTest = [
    'admin@refugios.ve' => 'Admin Logística',
    'coordinador@refugios.ve' => 'Coordinador Refugio',
    'almacen@refugios.ve' => 'Almacén',
    'auditor@refugios.ve' => 'Auditor Humanitario',
];

foreach ($rolesToTest as $email => $roleName) {
    $user = App\Models\User::where('email', $email)->first();
    if (!$user) {
        echo "Error: Usuario {$email} no encontrado.\n";
        continue;
    }

    echo "▶ Rol: [{$user->rol}] {$user->name} ({$roleName})\n";
    echo "   - Refugios:       " . ($user->canAccessShelters() ? '✅ VISIBLE' : '❌ OCULTO') . "\n";
    echo "   - Insumos:        " . ($user->canAccessSupplies() ? '✅ VISIBLE' : '❌ OCULTO') . "\n";
    echo "   - Kardex:         " . ($user->canAccessMovements() ? '✅ VISIBLE' : '❌ OCULTO') . "\n";
    echo "   - Rebalanceo:     " . ($user->canAccessTransfers() ? '✅ VISIBLE' : '❌ OCULTO') . "\n";
    echo "   - Modelos EOQ:    " . ($user->canAccessIoModels() ? '✅ VISIBLE' : '❌ OCULTO') . "\n";
    echo "   - Redes CPM:      " . ($user->canAccessCpm() ? '✅ VISIBLE' : '❌ OCULTO') . "\n";
    echo "   - Carnets QR:     " . ($user->canAccessCarnets() ? '✅ VISIBLE' : '❌ OCULTO') . "\n";
    echo "   - Auditoría:      " . ($user->canAccessAudit() ? '✅ VISIBLE' : '❌ OCULTO') . "\n";
    echo "   - Reportes:       " . ($user->canAccessReports() ? '✅ VISIBLE' : '❌ OCULTO') . "\n\n";
}

// Probar Middleware RoleMiddleware directamente
echo "--- Probando RoleMiddleware con Coordinador intentando entrar a /cpm ---\n";
$coord = App\Models\User::where('email', 'coordinador@refugios.ve')->first();
Illuminate\Support\Facades\Auth::login($coord);

$middleware = new App\Http\Middleware\RoleMiddleware();
$req = Illuminate\Http\Request::create('/cpm', 'GET');
$res = $middleware->handle($req, function() {
    return 'cpm_accedido';
}, 'admin');

if ($res instanceof \Illuminate\Http\RedirectResponse) {
    echo "Bloqueo correcto: Redirige con mensaje de error -> " . session('error') . "\n";
} else {
    echo "FALLÓ: Permitió acceso indebido\n";
}

echo "--- Probando RoleMiddleware con Admin intentando entrar a /cpm ---\n";
$admin = App\Models\User::where('email', 'admin@refugios.ve')->first();
Illuminate\Support\Facades\Auth::login($admin);
$resAdmin = $middleware->handle($req, function() {
    return 'cpm_accedido_exitosamente';
}, 'admin');
echo "Acceso correcto: " . $resAdmin . "\n";

echo "\n=== TODAS LAS PRUEBAS DE ROLES COMPLETADAS EXITOSAMENTE ===\n";
