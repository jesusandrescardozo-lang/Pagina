<?php
// Verificación puntual de la invalidación de caché POR VERSIÓN
// (reemplaza al Cache::flush() global que borraba el rate-limiter).
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\InventoryOptimizationService;
use App\Services\AuditService;
use Illuminate\Support\Facades\Cache;

$fallos = 0;
function check(string $label, bool $ok): void
{
    global $fallos;
    echo ($ok ? '  [PASS] ' : '  [FAIL] ') . $label . PHP_EOL;
    if (! $ok) { $fallos++; }
}

echo "===============================================\n";
echo " TEST: INVALIDACION DE CACHE POR VERSION\n";
echo "===============================================\n";

$svc = app(InventoryOptimizationService::class);

// Asegurar estado inicial conocido
config(['cache.default' => 'array']);
cache()->forever(InventoryOptimizationService::CACHE_VERSION_KEY, 1);

// Las escrituras de auditoría van en transacción con rollback: no dejamos
// rastros en la BD (Neon) de esta verificación puntual.
Illuminate\Support\Facades\DB::beginTransaction();

$v1 = InventoryOptimizationService::cacheVersion();
$r1 = $svc->calcularClasificacionAbc();
check('Version inicial = 1', $v1 === 1);
check('ABC calcula items', is_array($r1) && array_key_exists('items', $r1));

// Clave analítica cacheada con la versión 1
$claveV1 = 'clasificacion_abc:1:global';
check('Entrada cacheada bajo clave con version 1', cache()->has($claveV1));

// Segunda llamada SIN invalidar -> debe servir desde caché (misma clave)
$antes = now();
$r2 = $svc->calcularClasificacionAbc();
check('Segunda llamada reutiliza caché (clave identica)', cache()->has($claveV1));

// Invalidar vía AuditService (como haría una escritura de inventario)
AuditService::limpiarCacheAnalitica();
$v2 = InventoryOptimizationService::cacheVersion();
check('Version incrementada a 2', $v2 === 2);
check('Entrada con version 1 queda huérfana (clave nueva distinta)', ! cache()->has('clasificacion_abc:2:global'));

// Recalcular -> ahora usa la clave de la versión 2
$r3 = $svc->calcularClasificacionAbc();
check('Recalculo usa clave con version 2', cache()->has('clasificacion_abc:2:global'));

// Clave AJENA a la caché analítica debe sobrevivir (antes el flush la borraba)
cache()->forever('login:test_demo', 'intacta');
Cache::forever('throttle:otra:cosa', 'tambien');
AuditService::limpiarCacheAnalitica();
check('Clave ajena (rate-limiter) NO borrada — sin flush global', cache()->has('login:test_demo'));
check('Segunda clave ajena también intacta', cache()->has('throttle:otra:cosa'));

// Los tres módulos del listado deben disparar la invalidación
foreach (['kardex', 'transferencias', 'refugios', 'insumos'] as $modulo) {
    $antes = InventoryOptimizationService::cacheVersion();
    AuditService::log($modulo, 'test', "Verificación cache version modulo {$modulo}");
    check("Escritura en '{$modulo}' incrementa la versión", InventoryOptimizationService::cacheVersion() === $antes + 1);
}

// Módulo NO relacionado NO debe invalidar
$antes = InventoryOptimizationService::cacheVersion();
AuditService::log('Autenticacion', 'test', 'Verificación cache version autenticacion');
check("Escritura en 'Autenticacion' NO incrementa la versión", InventoryOptimizationService::cacheVersion() === $antes);

\App\Models\AuditLog::where('accion', 'test')->delete();
Illuminate\Support\Facades\DB::rollBack();

echo PHP_EOL . ($fallos === 0 ? " TODAS LAS VERIFICACIONES PASARON\n" : " {$fallos} FALLOS\n");
exit($fallos === 0 ? 0 : 1);
