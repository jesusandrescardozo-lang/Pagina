<?php
// Diagnóstico rápido de integridad de la aplicación (CLI, no HTTP).

use Illuminate\Support\Facades\Cache;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "== Tablas ==\n";
$problemas = 0;
// El listado de tablas depende del motor: sqlite_master solo existe en SQLite,
// en Postgres/MySQL hay que leer las vistas del catálogo del sistema.
$driver = DB::connection()->getDriverName();
$tablasSql = match ($driver) {
    'sqlite' => "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'",
    'pgsql'  => "SELECT tablename AS name FROM pg_tables WHERE schemaname = 'public'",
    default  => "SELECT table_name AS name FROM information_schema.tables WHERE table_schema = DATABASE()",
};
$tables = collect(DB::select($tablasSql))
    ->map(fn ($r) => $r->name)->sort()->values()->all();
echo '  (' . $driver . ') ' . implode(', ', $tables) . "\n\n";

echo "== Claves de configuración críticas ==\n";
$keys = ['cache.default', 'logging.default', 'queue.default', 'filesystems.default',
         'mail.default', 'session.driver', 'database.default', 'cors.paths', 'hashing.driver'];
foreach ($keys as $k) {
    $v = config($k);
    if ($v === null) {
        $problemas++;
    }
    printf("  %-22s = %s\n", $k, $v === null ? '<< AUSENTE (null) >>' : (is_array($v) ? 'array(' . count($v) . ')' : var_export($v, true)));
}

echo "\n== Archivos de configuración presentes ==\n";
$expected = ['app','auth','cache','cors','database','filesystems','hashing','logging','mail','queue','services','session','view'];
foreach ($expected as $f) {
    $ok = file_exists(base_path("config/{$f}.php"));
    if (!$ok) {
        $problemas++;
    }
    printf("  %-14s %s\n", $f, $ok ? 'OK' : '<< FALTA >>');
}

echo "\n== Tablas requeridas por .env ==\n";
$requeridas = ['sessions' => 'SESSION_DRIVER=database', 'cache' => 'CACHE_STORE=database', 'jobs' => 'QUEUE_CONNECTION=database'];
foreach ($requeridas as $tabla => $motivo) {
    $ok = in_array($tabla, $tables, true);
    if (!$ok) {
        $problemas++;
    }
    printf("  %-14s %s  (%s)\n", $tabla, $ok ? 'OK' : '<< FALTA >>', $motivo);
}

echo "\n== Store de caché funcional ==\n";
try {
    Cache::put('__diag', 1, 10);
    $ok = Cache::get('__diag') === 1;
    Cache::forget('__diag');
    if (!$ok) {
        $problemas++;
    }
    echo '  ' . ($ok ? 'OK' : 'FALLO al leer') . "\n";
} catch (\Throwable $e) {
    $problemas++;
    echo '  << ERROR: ' . $e->getMessage() . " >>\n";
}

echo "\n== RateLimiter (usado por throttle:10,1 en /login) ==\n";
try {
    $rl = app(Illuminate\Cache\RateLimiter::class);
    echo '  OK ' . get_class($rl) . "\n";
} catch (\Throwable $e) {
    $problemas++;
    echo '  << ERROR: ' . $e->getMessage() . " >>\n";
}

echo "\n== Log funcional ==\n";
try {
    Illuminate\Support\Facades\Log::info('diagnostico-ok');
    echo "  OK\n";
} catch (\Throwable $e) {
    $problemas++;
    echo '  << ERROR: ' . $e->getMessage() . " >>\n";
}

echo "\n=================================================\n";
echo $problemas === 0
    ? " DIAGNÓSTICO: SIN PROBLEMAS DETECTADOS\n"
    : " DIAGNÓSTICO: {$problemas} PROBLEMA(S) DETECTADO(S)\n";
echo "=================================================\n";
exit($problemas === 0 ? 0 : 1);
