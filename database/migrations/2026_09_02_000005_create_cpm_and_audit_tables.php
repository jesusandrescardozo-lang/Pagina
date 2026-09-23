<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Proyectos CPM (Cadena Logística y Reabastecimiento Humanitario)
        Schema::create('cpm_projects', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('tipo', 50)->default('reabastecimiento_emergencia'); // reabastecimiento_emergencia, distribucion_alimentos, cadena_suministro_medica, proceso_logistico, personalizado
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Actividades del CPM
        Schema::create('cpm_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('cpm_projects')->onDelete('cascade');
            $table->string('codigo', 10);           // A, B, C...
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->unsignedSmallInteger('duracion'); // Horas o días según proyecto
            // Predecesores: array JSON de códigos de actividades previas
            $table->json('predecesores')->nullable();
            // Resultados del cálculo CPM (Investigación de Operaciones)
            $table->decimal('es', 8, 2)->nullable();   // Early Start
            $table->decimal('ef', 8, 2)->nullable();   // Early Finish
            $table->decimal('ls', 8, 2)->nullable();   // Late Start
            $table->decimal('lf', 8, 2)->nullable();   // Late Finish
            $table->decimal('holgura', 8, 2)->nullable(); // Slack / Float
            $table->boolean('es_critica')->default(false);
            $table->timestamps();

            $table->unique(['project_id', 'codigo']);
        });

        // Log de auditoría
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('modulo', 50);
            $table->string('accion', 30);            // creado, actualizado, eliminado, inventario, transferencia
            $table->text('descripcion');
            $table->string('modelo_tipo', 100)->nullable();
            $table->unsignedBigInteger('modelo_id')->nullable();
            $table->json('valores_antes')->nullable();
            $table->json('valores_despues')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('cpm_activities');
        Schema::dropIfExists('cpm_projects');
    }
};
