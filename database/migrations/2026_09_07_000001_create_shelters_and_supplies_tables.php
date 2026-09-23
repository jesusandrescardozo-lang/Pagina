<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Refugios de Emergencia / Albergues
        Schema::create('shelters', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique(); // Ej: REF-01, REF-02
            $table->string('nombre');
            $table->string('departamento')->default('Distrito Capital');
            $table->string('municipio')->default('Libertador');
            $table->string('direccion');
            $table->string('coordenadas')->nullable(); // Lat, Lng
            $table->unsignedInteger('capacidad_maxima');
            $table->unsignedInteger('poblacion_total')->default(0);
            $table->unsignedInteger('poblacion_adultos')->default(0);
            $table->unsignedInteger('poblacion_ninos')->default(0);
            $table->unsignedInteger('poblacion_adultos_mayores')->default(0);
            $table->unsignedInteger('poblacion_atencion_medica')->default(0); // Heridos o pacientes crónicos
            $table->string('responsable_nombre');
            $table->string('responsable_telefono');
            $table->string('responsable_email')->nullable();
            $table->enum('estado', ['activo', 'alerta_capacidad', 'evacuacion', 'cerrado'])->default('activo');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        // Catálogo Maestro de Insumos Médicos y Alimentos
        Schema::create('supplies', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique(); // Ej: MED-001, ALI-001
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->enum('categoria', ['alimento', 'medico']);
            $table->string('subcategoria'); // Granos, Enlatados, Lácteos, Agua, Antibióticos, Curación, etc.
            $table->string('unidad_medida', 30); // kg, litros, raciones, cajas, unidades, frascos
            $table->enum('clasificacion_abc', ['A', 'B', 'C'])->default('B');
            $table->boolean('es_perecedero')->default(false);
            $table->unsignedInteger('dias_vida_util_promedio')->nullable();

            // Parámetros Cuantitativos de Investigación de Operaciones (I.O.)
            $table->decimal('costo_unitario', 12, 2)->default(0.00); // C (Costo de adquisición por unidad)
            $table->decimal('costo_pedido', 12, 2)->default(15.00);   // S o K (Costo fijo por emitir una orden)
            $table->decimal('costo_mantenimiento_anual', 12, 2)->default(2.50); // H (Costo de almacenamiento por unidad/año)
            $table->decimal('tiempo_entrega_dias', 8, 2)->default(3.00);        // L (Lead Time en días de reabastecimiento)
            $table->decimal('consumo_diario_referencia', 10, 3)->default(1.000); // Tasa consumo estimado persona/día
            $table->decimal('stock_seguridad_sugerido', 12, 2)->default(10.00);  // SS
            
            // Parámetros para Modelo Newsvendor / Período Único
            $table->decimal('costo_escasez_newsvendor', 12, 2)->nullable(); // Cu (Costo de subestimación/escasez en crisis)
            $table->decimal('costo_desecho_newsvendor', 12, 2)->nullable();  // Co (Costo de sobreestimación/pérdida por vencimiento)

            $table->timestamps();
        });

        // Inventario consolidado por Refugio
        Schema::create('shelter_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shelter_id')->constrained('shelters')->onDelete('cascade');
            $table->foreignId('supply_id')->constrained('supplies')->onDelete('cascade');
            $table->decimal('stock_actual', 14, 2)->default(0.00);
            $table->decimal('stock_minimo', 14, 2)->default(0.00);
            $table->decimal('stock_maximo', 14, 2)->default(0.00);
            $table->decimal('punto_reorden', 14, 2)->default(0.00); // ROP calculado
            $table->timestamps();

            $table->unique(['shelter_id', 'supply_id']);
        });

        // Lotes de Inventario y Control FEFO (First Expired, First Out)
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shelter_inventory_id')->constrained('shelter_inventories')->onDelete('cascade');
            $table->string('numero_lote', 60);
            $table->decimal('cantidad_inicial', 14, 2);
            $table->decimal('cantidad_disponible', 14, 2);
            $table->date('fecha_ingreso');
            $table->date('fecha_vencimiento')->nullable();
            $table->enum('estado', ['vigente', 'proximo_a_vencer', 'vencido', 'agotado'])->default('vigente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_batches');
        Schema::dropIfExists('shelter_inventories');
        Schema::dropIfExists('supplies');
        Schema::dropIfExists('shelters');
    }
};
