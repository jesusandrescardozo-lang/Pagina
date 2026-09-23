<?php
// Prueba integral de la máquina de estados de transferencias:
// - transiciones inválidas rechazadas
// - despacho con descuento FEFO real de lotes
// - recepción recreando lotes con vencimiento original
// - cancelación en tránsito devolviendo stock y lotes al origen
// Todo se ejecuta en una transacción que se revierte (no ensucia la BD).
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\InventoryBatch;
use App\Models\Shelter;
use App\Models\ShelterInventory;
use App\Models\Supply;
use App\Models\SupplyTransfer;
use App\Services\TransferService;
use Illuminate\Support\Facades\DB;

$service = $app->make(TransferService::class);
$pasados = 0;
$total = 0;

function check(string $label, bool $ok): void
{
    global $pasados, $total;
    $total++;
    if ($ok) { $pasados++; echo "  [PASS] $label\n"; }
    else { echo "  [FAIL] $label\n"; }
}

echo "===============================================\n";
echo " TEST: MÁQUINA DE ESTADOS DE TRANSFERENCIAS\n";
echo "===============================================\n";

DB::beginTransaction();

try {
    // ---------- Fixtures ----------
    $origen = Shelter::create([
        'codigo' => 'REF-TST-A', 'nombre' => 'Refugio Origen TST',
        'departamento' => 'DT', 'municipio' => 'MU', 'direccion' => 'Dir 1',
        'capacidad_maxima' => 100, 'poblacion_total' => 50,
        'responsable_nombre' => 'Test', 'responsable_telefono' => '000',
        'estado' => 'activo',
    ]);
    $destino = Shelter::create([
        'codigo' => 'REF-TST-B', 'nombre' => 'Refugio Destino TST',
        'departamento' => 'DT', 'municipio' => 'MU', 'direccion' => 'Dir 2',
        'capacidad_maxima' => 100, 'poblacion_total' => 50,
        'responsable_nombre' => 'Test', 'responsable_telefono' => '000',
        'estado' => 'activo',
    ]);
    $supply = Supply::create([
        'codigo' => 'TST-001', 'nombre' => 'Insumo TST', 'categoria' => 'alimento',
        'subcategoria' => 'Granos', 'unidad_medida' => 'kg', 'clasificacion_abc' => 'A',
        'es_perecedero' => true, 'dias_vida_util_promedio' => 30,
        'costo_unitario' => 10, 'costo_pedido' => 5, 'costo_mantenimiento_anual' => 2,
        'tiempo_entrega_dias' => 3, 'consumo_diario_referencia' => 1,
        'stock_seguridad_sugerido' => 10,
    ]);

    $invOrigen = ShelterInventory::create([
        'shelter_id' => $origen->id, 'supply_id' => $supply->id,
        'stock_actual' => 100, 'stock_minimo' => 10, 'stock_maximo' => 50, 'punto_reorden' => 15,
    ]);
    $invDestino = ShelterInventory::create([
        'shelter_id' => $destino->id, 'supply_id' => $supply->id,
        'stock_actual' => 0, 'stock_minimo' => 10, 'stock_maximo' => 50, 'punto_reorden' => 15,
    ]);

    // 2 lotes FEFO: primero vence el más viejo
    $loteViejo = InventoryBatch::create([
        'shelter_inventory_id' => $invOrigen->id, 'numero_lote' => 'LOT-VIEJO',
        'cantidad_inicial' => 20, 'cantidad_disponible' => 20,
        'fecha_ingreso' => now()->subDays(10)->toDateString(),
        'fecha_vencimiento' => now()->addDays(5)->toDateString(), 'estado' => 'vigente',
    ]);
    $loteNuevo = InventoryBatch::create([
        'shelter_inventory_id' => $invOrigen->id, 'numero_lote' => 'LOT-NUEVO',
        'cantidad_inicial' => 80, 'cantidad_disponible' => 80,
        'fecha_ingreso' => now()->toDateString(),
        'fecha_vencimiento' => now()->addDays(60)->toDateString(), 'estado' => 'vigente',
    ]);

    $transfer = SupplyTransfer::create([
        'codigo_transferencia' => 'TRF-TST-0001',
        'refugio_origen_id' => $origen->id, 'refugio_destino_id' => $destino->id,
        'supply_id' => $supply->id, 'cantidad' => 30, 'prioridad' => 'alta',
        'estado' => 'solicitada',
    ]);

    // ---------- 1. Transiciones inválidas ----------
    try {
        $service->validarTransicion($transfer, 'completada'); // solicitada -> completada NO permitida
        check('Rechaza transición solicitada -> completada', false);
    } catch (\RuntimeException $e) {
        check('Rechaza transición solicitada -> completada', true);
    }

    try {
        $service->validarTransicion($transfer, 'en_transito'); // solicitada -> en_transito NO permitida
        check('Rechaza transición solicitada -> en_transito', false);
    } catch (\RuntimeException $e) {
        check('Rechaza transición solicitada -> en_transito', true);
    }

    // ---------- 2. Flujo feliz: aprobar -> despachar -> recibir ----------
    $service->aprobar($transfer->fresh());
    check('Aprueba: solicitada -> aprobada', $transfer->fresh()->estado === 'aprobada');

    $service->despachar($transfer->fresh(), 'Camioneta TST');
    $transfer->refresh();
    $invOrigen->refresh();
    check('Despacha: aprobada -> en_transito', $transfer->estado === 'en_transito');
    check('Despacha: descuenta stock en origen (100 -> 70)', abs($invOrigen->stock_actual - 70) < 0.01);
    check('Despacha: descuenta lote más viejo primero (FEFO: LOT-VIEJO agotado)', $loteViejo->fresh()->cantidad_disponible == 0 && $loteViejo->fresh()->estado === 'agotado');
    check('Despacha: resto sale del lote nuevo (80 -> 70)', abs($loteNuevo->fresh()->cantidad_disponible - 70) < 0.01);
    check('Despacha: guarda lotes_despacho con vencimientos originales',
        is_array($transfer->lotes_despacho)
        && count($transfer->lotes_despacho) === 2
        && $transfer->lotes_despacho[0]['fecha_vencimiento'] === $loteViejo->fecha_vencimiento->toDateString()
    );
    check('Despacha: registra kardex salida', \App\Models\InventoryMovement::where('documento_referencia', 'TRF-TST-0001')->where('tipo', 'salida_transferencia')->exists());

    $service->recibir($transfer->fresh());
    $transfer->refresh();
    $invDestino->refresh();
    check('Recibe: en_transito -> completada', $transfer->estado === 'completada');
    check('Recibe: abona stock en destino (0 -> 30)', abs($invDestino->stock_actual - 30) < 0.01);
    $loteRecibido = InventoryBatch::where('shelter_inventory_id', $invDestino->id)->where('numero_lote', 'LOT-VIEJO')->first();
    $loteRecibido2 = InventoryBatch::where('shelter_inventory_id', $invDestino->id)->where('numero_lote', 'LOT-NUEVO')->first();
    check('Recibe: recrea lotes con vencimiento original (FEFO destino)',
        $loteRecibido && $loteRecibido2
        && $loteRecibido->fecha_vencimiento->toDateString() === $loteViejo->fecha_vencimiento->toDateString()
        && $loteRecibido2->fecha_vencimiento->toDateString() === $loteNuevo->fecha_vencimiento->toDateString()
    );
    check('Recibe: suma de lotes destino == stock (20 + 10 = 30)',
        abs(InventoryBatch::where('shelter_inventory_id', $invDestino->id)->where('estado', '!=', 'agotado')->sum('cantidad_disponible') - 30) < 0.01
    );

    try {
        $service->recibir($transfer->fresh()); // completada es definitivo
        check('Bloquea transición desde estado final completada', false);
    } catch (\RuntimeException $e) {
        check('Bloquea transición desde estado final completada', true);
    }

    // ---------- 3. Cancelación en tránsito devuelve stock y lotes ----------
    $transfer2 = SupplyTransfer::create([
        'codigo_transferencia' => 'TRF-TST-0002',
        'refugio_origen_id' => $origen->id, 'refugio_destino_id' => $destino->id,
        'supply_id' => $supply->id, 'cantidad' => 15, 'prioridad' => 'media',
        'estado' => 'solicitada',
    ]);
    $service->aprobar($transfer2->fresh());
    $service->despachar($transfer2->fresh());
    $invOrigen->refresh();
    check('Segunda transferencia despachada (70 -> 55)', abs($invOrigen->stock_actual - 55) < 0.01);

    $service->cancelar($transfer2->fresh());
    $invOrigen->refresh();
    check('Cancela en tránsito: estado cancelada', $transfer2->fresh()->estado === 'cancelada');
    check('Cancela en tránsito: devuelve stock al origen (55 -> 70)', abs($invOrigen->stock_actual - 70) < 0.01);
    check('Cancela en tránsito: devuelve cantidades al lote consumido (FEFO: LOT-NUEVO vuelve a 70)',
        abs($loteNuevo->fresh()->cantidad_disponible - 70) < 0.01
        && abs($loteViejo->fresh()->cantidad_disponible - 0) < 0.01
    );
    check('Cancela en tránsito: registra kardex inverso',
        \App\Models\InventoryMovement::where('documento_referencia', 'TRF-TST-0002')->where('tipo', 'entrada_transferencia')->exists()
    );

    try {
        $service->aprobar($transfer2->fresh()); // cancelada es definitivo
        check('Bloquea reactivación de transferencia cancelada', false);
    } catch (\RuntimeException $e) {
        check('Bloquea reactivación de transferencia cancelada', true);
    }

    // ---------- 4. Despacho con stock insuficiente ----------
    $transfer3 = SupplyTransfer::create([
        'codigo_transferencia' => 'TRF-TST-0003',
        'refugio_origen_id' => $origen->id, 'refugio_destino_id' => $destino->id,
        'supply_id' => $supply->id, 'cantidad' => 9999, 'prioridad' => 'baja',
        'estado' => 'solicitada',
    ]);
    $service->aprobar($transfer3->fresh());
    try {
        $service->despachar($transfer3->fresh());
        check('Rechaza despacho con stock insuficiente', false);
    } catch (\RuntimeException $e) {
        check('Rechaza despacho con stock insuficiente', true);
    }
    check('Despacho fallido no modifica el estado', $transfer3->fresh()->estado === 'aprobada');
    check('Despacho fallido no modifica el stock', abs($invOrigen->fresh()->stock_actual - 70) < 0.01);

} finally {
    DB::rollBack(); // nada queda en la base de datos
}

echo "\n RESULTADO: $pasados/$total PRUEBAS EXITOSAS\n";
exit($pasados === $total ? 0 : 1);
