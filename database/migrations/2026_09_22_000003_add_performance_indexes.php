<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Índices de rendimiento para las consultas más pesadas del sistema:
 *  - Kardex: rangos de fecha, filtro por tipo, trazabilidad por documento.
 *  - Lotes FEFO: lookup por inventario + vencimiento.
 *  - Inventario por insumo (sin cobertura del unique compuesto).
 *  - Transferencias: filtro por estado ordenado por fecha.
 *  - Auditoría: listado ordenado por fecha.
 *
 * En MySQL los FK ya se auto-indexan (InnoDB); estos índices se crean solo
 * si no existen con ese nombre, evitando duplicados.
 */
return new class extends Migration {
    public function up(): void
    {
        // Kardex de movimientos
        $this->indexIfMissing('inventory_movements', ['fecha_movimiento'], 'inventory_movements_fecha_movimiento_index');
        $this->indexIfMissing('inventory_movements', ['tipo'], 'inventory_movements_tipo_index');
        $this->indexIfMissing('inventory_movements', ['documento_referencia'], 'inventory_movements_documento_referencia_index');
        $this->indexIfMissing('inventory_movements', ['shelter_id', 'supply_id', 'fecha_movimiento'], 'inventory_movements_shelter_supply_fecha_index');

        // Lotes FEFO (la columna de orden es fecha_vencimiento)
        $this->indexIfMissing('inventory_batches', ['shelter_inventory_id', 'fecha_vencimiento'], 'inventory_batches_inv_fecha_index');
        $this->indexIfMissing('inventory_batches', ['estado'], 'inventory_batches_estado_index');

        // Inventario por insumo (el unique [shelter_id, supply_id] solo cubre shelter_id)
        $this->indexIfMissing('shelter_inventories', ['supply_id'], 'shelter_inventories_supply_id_index');

        // Transferencias: índice + listado filtran por estado y ordenan por fecha
        $this->indexIfMissing('supply_transfers', ['estado', 'created_at'], 'supply_transfers_estado_created_at_index');

        // Auditoría: listado ordena por created_at
        $this->indexIfMissing('audit_logs', ['created_at'], 'audit_logs_created_at_index');
    }

    public function down(): void
    {
        $this->dropIndexIfMissing('inventory_movements', 'inventory_movements_fecha_movimiento_index');
        $this->dropIndexIfMissing('inventory_movements', 'inventory_movements_tipo_index');
        $this->dropIndexIfMissing('inventory_movements', 'inventory_movements_documento_referencia_index');
        $this->dropIndexIfMissing('inventory_movements', 'inventory_movements_shelter_supply_fecha_index');
        $this->dropIndexIfMissing('inventory_batches', 'inventory_batches_inv_fecha_index');
        $this->dropIndexIfMissing('inventory_batches', 'inventory_batches_estado_index');
        $this->dropIndexIfMissing('shelter_inventories', 'shelter_inventories_supply_id_index');
        $this->dropIndexIfMissing('supply_transfers', 'supply_transfers_estado_created_at_index');
        $this->dropIndexIfMissing('audit_logs', 'audit_logs_created_at_index');
    }

    private function indexIfMissing(string $table, array $columns, string $name): void
    {
        if (!$this->indexExists($table, $name)) {
            Schema::table($table, function (Blueprint $t) use ($columns, $name) {
                $t->index($columns, $name);
            });
        }
    }

    private function dropIndexIfMissing(string $table, string $name): void
    {
        if ($this->indexExists($table, $name)) {
            Schema::table($table, function (Blueprint $t) use ($name) {
                $t->dropIndex($name);
            });
        }
    }

    private function indexExists(string $table, string $name): bool
    {
        $conn = Schema::getConnection();

        if ($conn->getDriverName() === 'sqlite') {
            return (bool) $conn->selectOne(
                "SELECT 1 AS ok FROM sqlite_master WHERE type = 'index' AND name = ?",
                [$name]
            );
        }

        $row = $conn->selectOne(
            'SELECT 1 AS ok FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
            [$table, $name]
        );

        return $row !== null;
    }
};
