<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== USERS ===\n";
foreach (\App\Models\User::all(['id', 'name', 'email', 'rol']) as $u) {
    echo "[{$u->id}] {$u->email} | {$u->rol} | {$u->name}\n";
}

echo "\n=== SHELTERS ===\n";
foreach (\App\Models\Shelter::orderBy('codigo')->get() as $s) {
    echo "[{$s->codigo}] ID={$s->id} | {$s->nombre} — {$s->municipio}\n";
}

echo "\n=== VERIFY NO DUPLICATE PARENTHESES ===\n";
$bad = \App\Models\Shelter::all()->filter(function($s) {
    return str_contains($s->nombre, '(') || str_contains($s->municipio . $s->nombre, ')(');
});
if ($bad->isEmpty()) {
    echo "✓ No duplicate parentheses in shelter names.\n";
} else {
    foreach ($bad as $b) {
        echo "✗ Check: [{$b->codigo}] {$b->nombre} | {$b->municipio}\n";
    }
}

echo "\n=== INVENTORY DIVERSITY CHECK ===\n";
foreach (\App\Models\Shelter::with('inventories.supply')->orderBy('codigo')->get() as $s) {
    $inv = $s->inventories;
    $criticals = $inv->filter(fn($i) => $i->stock_actual <= $i->stock_minimo)->count();
    $totalStock = $inv->sum('stock_actual');
    echo sprintf("[%s] Pob: %d/%d | Niños: %d | Items: %d | Stock: %.0f | Críticos: %d\n",
        $s->codigo, $s->poblacion_total, $s->capacidad_maxima,
        $s->poblacion_ninos, $inv->count(), $totalStock, $criticals
    );
}
