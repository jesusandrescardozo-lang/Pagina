<?php
/**
 * PRUEBAS DE CORRECCIÓN DE CÓDIGO (estáticas + entorno de ejecución).
 *
 * Verifica que el proyecto no tenga errores "silenciosos" que aparezcan
 * recién al ejecutarlo:
 *
 *   1. Sintaxis PHP de todo el código propio (sin incluir vendor/).
 *   2. Archivos de configuración de Laravel (los ausentes provocan
 *      "Log [] is not defined" / "Cache store [] is not defined").
 *   3. Claves de configuración críticas con valor.
 *   4. Comandos de artisan (optimize, view:cache, route:list).
 *   5. Caché, logging y RateLimiter operativos.
 *   6. Esquema de base de datos (tablas cache/jobs/sessions).
 *   7. Ausencia de código de depuración olvidado en producción.
 */

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
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

$raiz = dirname(__DIR__);
$dirs = ['app', 'config', 'routes', 'database', 'resources', 'public', 'tests', 'bootstrap'];

// ---------------------------------------------------------------
// 1. Sintaxis PHP
// ---------------------------------------------------------------
echo "== 1. Sintaxis PHP ==\n";
$errores = [];
$archivos = 0;
foreach ($dirs as $dir) {
    $ruta = "{$raiz}/{$dir}";
    if (!is_dir($ruta)) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($ruta, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $archivos++;
        $salida = [];
        $codigo = 0;
        exec(sprintf('php -l %s 2>&1', escapeshellarg($file->getPathname())), $salida, $codigo);
        if ($codigo !== 0) {
            $errores[] = basename($file->getPathname()) . ': ' . implode(' ', $salida);
        }
    }
}
check("Se analizaron {$archivos} archivos PHP", $archivos > 0);
check('Ningún archivo con error de sintaxis', $errores === [], implode(' | ', array_slice($errores, 0, 3)));

// ---------------------------------------------------------------
// 2. Archivos de configuración
// ---------------------------------------------------------------
echo "\n== 2. Archivos de configuración ==\n";
$requeridos = ['app', 'auth', 'cache', 'cors', 'database', 'filesystems', 'hashing',
               'logging', 'mail', 'queue', 'services', 'session', 'view'];
$faltantes = [];
foreach ($requeridos as $cfg) {
    if (!file_exists("{$raiz}/config/{$cfg}.php")) {
        $faltantes[] = $cfg;
    }
}
check('Los ' . count($requeridos) . ' archivos de config existen', $faltantes === [], 'faltan: ' . implode(', ', $faltantes));

$claves = [
    'cache.default' => null, 'logging.default' => null, 'queue.default' => null,
    'filesystems.default' => null, 'mail.default' => null, 'session.driver' => null,
    'database.default' => null, 'hashing.driver' => null, 'cors.paths' => null,
];
$nulas = [];
foreach ($claves as $clave => $_) {
    if (config($clave) === null) {
        $nulas[] = $clave;
    }
}
check('Las claves de config críticas tienen valor', $nulas === [], 'null: ' . implode(', ', $nulas));

// ---------------------------------------------------------------
// 3. Comandos de artisan
// ---------------------------------------------------------------
echo "\n== 3. Comandos de artisan ==\n";
$comandos = [
    'route:list'  => ['--except-vendor' => true],
    'view:cache'  => [],
    'route:cache' => [],
];
foreach ($comandos as $comando => $params) {
    try {
        $codigo = Artisan::call($comando, $params);
        check("php artisan {$comando}", $codigo === 0, trim(Artisan::output()));
    } catch (\Throwable $e) {
        check("php artisan {$comando}", false, $e->getMessage());
    }
}
Artisan::call('route:clear');

$okOptimize = true;
$detalleOptimize = '';
try {
    $okOptimize = Artisan::call('optimize') === 0;
    $detalleOptimize = trim(Artisan::output());
} catch (\Throwable $e) {
    $okOptimize = false;
    $detalleOptimize = $e->getMessage();
} finally {
    Artisan::call('optimize:clear');
}
check('php artisan optimize (config + routes)', $okOptimize, $detalleOptimize);

// ---------------------------------------------------------------
// 4. Servicios de ejecución
// ---------------------------------------------------------------
echo "\n== 4. Servicios en tiempo de ejecución ==\n";
try {
    Cache::put('__integridad', 'ok', 30);
    check('Caché: escritura + lectura', Cache::get('__integridad') === 'ok');
    Cache::forget('__integridad');
    check('Caché: borrado', Cache::get('__integridad') === null);
} catch (\Throwable $e) {
    check('Caché operativa', false, $e->getMessage());
}

try {
    Log::info('integridad-ok');
    check('Logging: registro sin excepción', true);
} catch (\Throwable $e) {
    check('Logging operativo', false, $e->getMessage());
}

try {
    $rl = app(Illuminate\Cache\RateLimiter::class);
    $rl->clear('__integridad_rl');
    $rl->hit('__integridad_rl', 60);
    $unintento = $rl->attempts('__integridad_rl') === 1;
    for ($i = 0; $i < 4; $i++) {
        $rl->hit('__integridad_rl', 60);
    }
    $bloqueado = $rl->tooManyAttempts('__integridad_rl', 5) === true;
    $rl->clear('__integridad_rl');
    check('RateLimiter (throttle:10,1 del login)', $unintento && $bloqueado,
        'intentos=' . var_export($unintento, true) . ', bloqueo=' . var_export($bloqueado, true));
} catch (\Throwable $e) {
    check('RateLimiter operativo', false, $e->getMessage());
}

// ---------------------------------------------------------------
// 5. Esquema de base de datos
// ---------------------------------------------------------------
echo "\n== 5. Esquema de base de datos ==\n";
$tablas = ['users', 'sessions', 'cache', 'jobs', 'shelters', 'supplies',
           'inventory_movements', 'supply_transfers', 'audit_logs'];
$faltan = [];
foreach ($tablas as $tabla) {
    if (!Schema::hasTable($tabla)) {
        $faltan[] = $tabla;
    }
}
check('Tablas esenciales creadas', $faltan === [], 'faltan: ' . implode(', ', $faltan));
check('Conexión activa', DB::connection()->getDatabaseName() !== '');

// ---------------------------------------------------------------
// 6. Código de depuración olvidado
// ---------------------------------------------------------------
echo "\n== 6. Código de depuración en producción ==\n";
$patrones = ['/\bdd\s*\(/', '/\bdump\s*\(/', '/\bvar_dump\s*\(/', '/\bprint_r\s*\(/'];
$sucios = [];
foreach (['app', 'routes', 'resources', 'config', 'database'] as $dir) {
    $ruta = "{$raiz}/{$dir}";
    if (!is_dir($ruta)) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($ruta, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $contenido = file_get_contents($file->getPathname());
        foreach ($patrones as $patron) {
            if (preg_match($patron, $contenido)) {
                $sucios[] = str_replace($raiz . DIRECTORY_SEPARATOR, '', $file->getPathname());
                break;
            }
        }
    }
}
check('Sin dd()/dump()/var_dump()/print_r() en el código propio', $sucios === [], implode(', ', array_unique($sucios)));

// ---------------------------------------------------------------
echo "\n=======================================================\n";
echo " RESULTADO: {$pasados}/" . ($pasados + $fallidos) . " CORRECCIONES VERIFICADAS";
echo $fallidos === 0 ? " — TODO EN ORDEN\n" : " — {$fallidos} PENDIENTE(S)\n";
echo "=======================================================\n";
exit($fallidos === 0 ? 0 : 1);
