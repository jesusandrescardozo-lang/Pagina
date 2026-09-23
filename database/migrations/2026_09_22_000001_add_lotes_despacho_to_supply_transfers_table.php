<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Lotes FEFO consumidos en el despacho de la transferencia.
        // Permite: (a) recibir en destino respetando vencimientos reales,
        //          (b) devolver los lotes al origen si se cancela en tránsito.
        Schema::table('supply_transfers', function (Blueprint $table) {
            $table->json('lotes_despacho')->nullable()->after('observaciones');
        });
    }

    public function down(): void
    {
        Schema::table('supply_transfers', function (Blueprint $table) {
            $table->dropColumn('lotes_despacho');
        });
    }
};
