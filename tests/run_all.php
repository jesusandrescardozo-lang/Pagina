<?php
/**
 * EJECUTOR ÚNICO DE PRUEBAS — proyecto hospital-triage (NUTRILINK).
 *
 * Ejecuta todas las suites del proyecto en un solo comando y devuelve un
 * código de salida distinto de cero si ALGUNA falla:
 *
 *     php tests/run_all.php            # todo
 *     php tests/run_all.php --quick    # solo las suites rápidas
 *     php tests/run_all.php --verbose  # muestra la salida completa
 *
 * Las suites que necesitan `php artisan serve` se detectan y se marcan como
 * SKIP automático si el servidor no está levantado.
 */

require __DIR__ . '/../vendor/autoload.php';
$appBootstrap = require __DIR__ . '/../bootstrap/app.php';
$appBootstrap->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$suites = [
    // Pruebas de corrección de código (estáticas + runtime)
    'diagnostico.php'           => ['grupo' => 'Corrección de código', 'http' => false],
    'test_integrity.php'        => ['grupo' => 'Corrección de código', 'http' => false],
    'test_http_smoke.php'       => ['grupo' => 'Corrección de código', 'http' => false],
    'test_pages.php'            => ['grupo' => 'Corrección de código', 'http' => false],
    // Lógica de negocio
    'test_crud_forms.php'       => ['grupo' => 'Lógica de negocio',    'http' => false],
    'test_transfer_state_machine.php' => ['grupo' => 'Lógica de negocio', 'http' => false],
    'test_authorization_matrix.php'   => ['grupo' => 'Lógica de negocio', 'http' => false],
    'test_role_permissions.php' => ['grupo' => 'Lógica de negocio',    'http' => false],
    'test_direct_auth.php'      => ['grupo' => 'Lógica de negocio',    'http' => false],
    'test_shelter_switching.php' => ['grupo' => 'Lógica de negocio',   'http' => false],
    'test_google2fa_system.php' => ['grupo' => 'Lógica de negocio',    'http' => false],
    'test_totp_algo.php'        => ['grupo' => 'Lógica de negocio',    'http' => false],
    'test_ido.php'              => ['grupo' => 'Lógica de negocio',    'http' => false],
    'test_full_verification.php' => ['grupo' => 'Lógica de negocio',   'http' => false],
    // End-to-end por HTTP (requieren servidor)
    'test_http_flow.php'        => ['grupo' => 'End-to-end HTTP',      'http' => true],
    'test_e2e_login_http.php'   => ['grupo' => 'End-to-end HTTP',      'http' => true],
];

$suiteIds = array_keys($suites);
$argvList = $_SERVER['argv'] ?? [];
$verbose = in_array('--verbose', $argvList, true) || in_array('-v', $argvList, true);
$quick = in_array('--quick', $argvList, true) || in_array('-q', $argvList, true);

if ($quick) {
    $suiteIds = array_slice($suiteIds, 0, 3);
}

/** ¿Hay un servidor local en el puerto esperado? */
function servidorDisponible(string $url): bool
{
    if (!function_exists('curl_init')) {
        return false;
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_NOBODY         => true,
        CURLOPT_TIMEOUT        => 3,
        CURLOPT_CONNECTTIMEOUT => 2,
    ]);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $code > 0;
}

$servidorOk = servidorDisponible('http://127.0.0.1:8000/login');

$totales = ['pass' => 0, 'fail' => 0, 'skip' => 0];
$detalles = [];
$grupoActual = '';

echo "\n";
echo "==============================================================\n";
echo "  SUITE DE VERIFICACIÓN — NUTRILINK / hospital-triage\n";
echo "  PHP " . PHP_VERSION . ' · Laravel ' . app()->version() . "\n";
echo "  Servidor local : " . ($servidorOk ? 'detectado (127.0.0.1:8000)' : 'no detectado (suites HTTP → SKIP)') . "\n";
echo "==============================================================\n";

foreach ($suiteIds as $archivo) {
    $meta = $suites[$archivo];
    $ruta = __DIR__ . '/' . $archivo;

    if ($meta['grupo'] !== $grupoActual) {
        $grupoActual = $meta['grupo'];
        echo "\n--- {$grupoActual} ---\n";
    }

    if (!file_exists($ruta)) {
        printf("  [SKIP] %-38s archivo no encontrado\n", $archivo);
        $totales['skip']++;
        continue;
    }

    if ($meta['http'] && !$servidorOk) {
        printf("  [SKIP] %-38s requiere `php artisan serve`\n", $archivo);
        $totales['skip']++;
        continue;
    }

    $salida = [];
    $codigo = 0;
    $inicio = microtime(true);
    exec(sprintf('php %s 2>&1', escapeshellarg($ruta)), $salida, $codigo);
    $duracion = round((microtime(true) - $inicio) * 1000);

    $texto = implode("\n", $salida);
    $detalles[$archivo] = $texto;

    if ($codigo === 0) {
        $totales['pass']++;
        printf("  [PASS] %-38s %6d ms\n", $archivo, $duracion);
    } else {
        $totales['fail']++;
        printf("  [FAIL] %-38s %6d ms (código %d)\n", $archivo, $duracion, $codigo);
        $lineas = array_values(array_filter(array_map('trim', $salida)));
        foreach (array_slice($lineas, -6) as $linea) {
            echo "           └─ {$linea}\n";
        }
    }

    if ($verbose) {
        echo preg_replace('/^/m', '      │ ', $texto) . "\n";
    }
}

echo "\n==============================================================\n";
$fallidos = $totales['fail'];
$estado = $fallidos === 0 ? 'TODAS LAS PRUEBAS EN ORDEN' : "{$fallidos} SUITE(S) CON FALLO";
printf("  SUITES: %d correctas · %d con fallo · %d omitidas\n", $totales['pass'], $totales['fail'], $totales['skip']);
echo "  RESULTADO: {$estado}\n";
echo "==============================================================\n";

if ($fallidos > 0) {
    echo "\nSuites con fallo:\n";
    foreach ($detalles as $archivo => $texto) {
        // Solo las suites fallidas llegan aquí con errores visibles
        if (strpos($texto, '[FAIL]') !== false || strpos($texto, '✗') !== false) {
            echo "  · {$archivo}\n";
        }
    }
    echo "\nEjecuta `php tests/run_all.php --verbose` para ver la salida completa.\n";
}

exit($fallidos === 0 ? 0 : 1);
