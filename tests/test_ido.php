<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

config(['database.default' => 'sqlite']);

$service = app(\App\Services\InventoryOptimizationService::class);
$matriz = $service->getMatrizAutonomia();
echo "Refugios evaluados: " . count($matriz['refugios']) . PHP_EOL;
echo "Alertas críticas (< 2 días): " . $matriz['total_criticos'] . PHP_EOL;
echo "Alertas precaución (2-5 días): " . $matriz['total_precaucion'] . PHP_EOL;

$sugerencias = $service->sugerirTransferenciasRebalanceo();
echo "Sugerencias de rebalanceo generadas: " . count($sugerencias) . PHP_EOL;
foreach (array_slice($sugerencias, 0, 2) as $s) {
    echo " -> Transferir {$s['cantidad_sugerida']} {$s['unidad_medida']} de {$s['supply']->nombre} desde [{$s['refugio_origen']->nombre}] a [{$s['refugio_destino']->nombre}] ({$s['prioridad']})" . PHP_EOL;
}

$abc = $service->calcularClasificacionAbc();
echo "Resumen Clasificación ABC: A=" . $abc['resumen']['A'] . ", B=" . $abc['resumen']['B'] . ", C=" . $abc['resumen']['C'] . PHP_EOL;

$eoq = $service->calcularEoq(5000, 25.0, 3.5, 12.0, 4.0, 50.0);
echo "EOQ Q*: " . $eoq['q_optimo_entero'] . " | ROP: " . $eoq['rop'] . " | N pedidos/año: " . $eoq['num_ordenes_ano'] . PHP_EOL;

$newsvendor = $service->calcularNewsvendor(400, 80, 45.0, 15.0);
echo "Newsvendor Q*: " . $newsvendor['q_optimo_entero'] . " | CR: " . $newsvendor['razon_critica'] . " (" . $newsvendor['nivel_servicio_pct'] . "%)" . PHP_EOL;
