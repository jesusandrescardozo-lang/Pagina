<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Shelter;
use App\Models\Supply;
use App\Models\InventoryMovement;
use App\Models\SupplyTransfer;

echo "=== SHELTERS DIVERSIFICATION INSPECTION ===\n";
foreach (Shelter::all() as $s) {
    $itemCount = $s->inventories()->count();
    $critCount = $s->inventories()->whereColumn('stock_actual', '<=', 'stock_minimo')->count();
    $totalStock = $s->inventories()->sum('stock_actual');
    $sampleCrit = $s->inventories()->whereColumn('stock_actual', '<=', 'stock_minimo')->with('supply')->get()->pluck('supply.nombre')->take(2)->implode(', ');
    echo sprintf(
        "[%s] %s | Parroquia: %s | Pob: %d/%d (%.1f%%) [Niños: %d, Salud: %d] | Items: %d | Stock Total: %.0f | Críticos: %d (%s)\n",
        $s->codigo,
        $s->nombre,
        $s->municipio,
        $s->poblacion_total,
        $s->capacidad_maxima,
        $s->ocupacion_porcentaje,
        $s->poblacion_ninos,
        $s->poblacion_atencion_medica,
        $itemCount,
        $totalStock,
        $critCount,
        $sampleCrit ?: 'Ninguno'
    );
}

echo "\nTotal movimientos registrados: " . InventoryMovement::count() . "\n";
echo "Total transferencias registradas: " . SupplyTransfer::count() . "\n";
