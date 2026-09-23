<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CpmProject;
use App\Models\CpmActivity;
use App\Models\User;
use App\Services\CpmCalculatorService;

/**
 * Seeder CPM para Cadena Logística de Suministros Humanitarios
 */
class CpmDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $adminId = $admin ? $admin->id : 1;
        $calculator = new CpmCalculatorService();

        // Proyecto 1: Reabastecimiento Humanitario de Emergencia
        $proyecto1 = CpmProject::updateOrCreate(
            ['nombre' => 'Cadena Logística de Reabastecimiento a Refugios'],
            [
                'descripcion' => 'Modelo CPM de la red de suministro humanitario desde la alerta de déficit en el refugio hasta la entrega y consumo final.',
                'tipo'        => 'reabastecimiento_emergencia',
                'user_id'     => $adminId,
            ]
        );

        $actividades1 = [
            ['codigo' => 'A', 'nombre' => 'Alerta de déficit y consolidación de demanda', 'duracion' => 2, 'predecesores' => []],
            ['codigo' => 'B', 'nombre' => 'Aprobación de orden y asignación presupuestaria', 'duracion' => 4, 'predecesores' => ['A']],
            ['codigo' => 'C', 'nombre' => 'Preparación de insumos médicos en Almacén Central', 'duracion' => 6, 'predecesores' => ['B']],
            ['codigo' => 'D', 'nombre' => 'Alistamiento de alimentos y granos básicos', 'duracion' => 8, 'predecesores' => ['B']],
            ['codigo' => 'E', 'nombre' => 'Inspección bromatológica y verificación FEFO', 'duracion' => 3, 'predecesores' => ['C', 'D']],
            ['codigo' => 'F', 'nombre' => 'Consolidación de carga y estiba en convoy', 'duracion' => 4, 'predecesores' => ['E']],
            ['codigo' => 'G', 'nombre' => 'Coordinación de ruta segura y escolta vial', 'duracion' => 3, 'predecesores' => ['B']],
            ['codigo' => 'H', 'nombre' => 'Transporte terrestre hacia red de refugios', 'duracion' => 7, 'predecesores' => ['F', 'G']],
            ['codigo' => 'I', 'nombre' => 'Recepción, verificación y pesaje en refugio', 'duracion' => 2, 'predecesores' => ['H']],
            ['codigo' => 'J', 'nombre' => 'Registro en Kardex y almacenamiento en bodega', 'duracion' => 2, 'predecesores' => ['I']],
            ['codigo' => 'K', 'nombre' => 'Distribución y racionamiento a población albergada', 'duracion' => 3, 'predecesores' => ['J']],
        ];

        foreach ($actividades1 as $act) {
            CpmActivity::updateOrCreate(
                ['project_id' => $proyecto1->id, 'codigo' => $act['codigo']],
                [
                    'nombre'       => $act['nombre'],
                    'duracion'     => $act['duracion'],
                    'predecesores' => $act['predecesores'],
                ]
            );
        }
        $calculator->calculate($proyecto1);

        // Proyecto 2: Operativo Diario de Alimentación y Salud en Refugio
        $proyecto2 = CpmProject::updateOrCreate(
            ['nombre' => 'Operativo Diario de Racionamiento y Dispensación Médica'],
            [
                'descripcion' => 'Ruta crítica para las actividades de cocina comunitaria y atención médica matutina en el refugio.',
                'tipo'        => 'distribucion_alimentos',
                'user_id'     => $adminId,
            ]
        );

        $actividades2 = [
            ['codigo' => 'A', 'nombre' => 'Censo matutino y triage de pacientes vulnerables', 'duracion' => 1, 'predecesores' => []],
            ['codigo' => 'B', 'nombre' => 'Despacho de granos e ingredientes desde bodega', 'duracion' => 1, 'predecesores' => ['A']],
            ['codigo' => 'C', 'nombre' => 'Preparación y cocción de raciones calóricas', 'duracion' => 3, 'predecesores' => ['B']],
            ['codigo' => 'D', 'nombre' => 'Preparación de kits de medicación y sueros orales', 'duracion' => 2, 'predecesores' => ['A']],
            ['codigo' => 'E', 'nombre' => 'Servido de alimentos en comedor comunitario', 'duracion' => 2, 'predecesores' => ['C']],
            ['codigo' => 'F', 'nombre' => 'Ronda médica y dispensación de fármacos', 'duracion' => 2, 'predecesores' => ['D']],
            ['codigo' => 'G', 'nombre' => 'Actualización de Kardex y registro de consumos', 'duracion' => 1, 'predecesores' => ['E', 'F']],
        ];

        foreach ($actividades2 as $act) {
            CpmActivity::updateOrCreate(
                ['project_id' => $proyecto2->id, 'codigo' => $act['codigo']],
                [
                    'nombre'       => $act['nombre'],
                    'duracion'     => $act['duracion'],
                    'predecesores' => $act['predecesores'],
                ]
            );
        }
        $calculator->calculate($proyecto2);
    }
}
