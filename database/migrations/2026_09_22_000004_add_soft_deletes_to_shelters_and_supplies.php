<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Borrado lógico para refugios e insumos.
 *
 * Antes, eliminar un refugio/insumo hacía DELETE real con cascada:
 * borraba inventarios, lotes FEFO y todo el Kardex de movimientos
 * (pérdida irreversible de trazabilidad). Con softDeletes el registro
 * desaparece de las vistas pero sus datos siguen íntegros.
 *
 * Nota: los códigos únicos (REF-01, MED-001...) siguen reservados aunque
 * el registro esté borrado (la validación unique incluye los borrados),
 * evitando reutilizar un identificador ya emitido.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('shelters', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('supplies', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('shelters', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('supplies', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
