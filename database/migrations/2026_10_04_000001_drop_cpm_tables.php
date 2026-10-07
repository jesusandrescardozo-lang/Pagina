<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Retira por completo el módulo CPM (Método de la Ruta Crítica) del proyecto:
 * se eliminan las tablas de proyectos y actividades CPM. La bitácora de
 * auditoría (audit_logs) creada en la misma migración original se conserva.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('cpm_activities');
        Schema::dropIfExists('cpm_projects');
    }

    public function down(): void
    {
        // No reversible: los datos de los proyectos CPM fueron eliminados.
    }
};
