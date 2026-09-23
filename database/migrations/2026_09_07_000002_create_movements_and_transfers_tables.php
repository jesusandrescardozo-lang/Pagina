<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Movimientos de Kardex (Trazabilidad estricta de Entradas / Salidas)
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shelter_id')->constrained('shelters')->onDelete('cascade');
            $table->foreignId('supply_id')->constrained('supplies')->onDelete('cascade');
            $table->foreignId('inventory_batch_id')->nullable()->constrained('inventory_batches')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('tipo', [
                'entrada_donacion',
                'entrada_compra',
                'entrada_transferencia',
                'salida_consumo',
                'salida_transferencia',
                'salida_merma_caducidad',
                'ajuste_inventario'
            ]);
            $table->decimal('cantidad', 14, 2);
            $table->decimal('stock_anterior', 14, 2);
            $table->decimal('stock_nuevo', 14, 2);
            $table->string('documento_referencia', 100)->nullable(); // Boleta de donación, remisión, etc.
            $table->text('motivo')->nullable();
            $table->dateTime('fecha_movimiento')->useCurrent();
            $table->timestamps();
        });

        // Transferencias y Rebalanceo Logístico entre Refugios
        Schema::create('supply_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_transferencia', 40)->unique();
            $table->foreignId('refugio_origen_id')->constrained('shelters')->onDelete('cascade');
            $table->foreignId('refugio_destino_id')->constrained('shelters')->onDelete('cascade');
            $table->foreignId('supply_id')->constrained('supplies')->onDelete('cascade');
            $table->decimal('cantidad', 14, 2);
            $table->enum('prioridad', ['urgente', 'alta', 'media', 'baja'])->default('alta');
            $table->enum('estado', ['solicitada', 'aprobada', 'en_transito', 'completada', 'cancelada'])->default('solicitada');
            $table->foreignId('solicitado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('aprobado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('vehiculo_transporte', 100)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha_salida')->nullable();
            $table->timestamp('fecha_llegada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supply_transfers');
        Schema::dropIfExists('inventory_movements');
    }
};
