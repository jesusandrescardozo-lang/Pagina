<?php

namespace App\Services;

use App\Models\CpmProject;
use App\Models\CpmActivity;

class CpmCalculatorService
{
    /**
     * Calcula CPM (Ida, Vuelta, Holguras y Ruta Crítica) para un proyecto dado.
     */
    public function calculate(CpmProject $project): array
    {
        $activities = $project->activities()->get();
        if ($activities->isEmpty()) {
            return [
                'activities' => [],
                'ruta_critica' => [],
                'duracion_proyecto' => 0,
                'graph_data' => ['nodes' => [], 'edges' => []],
            ];
        }

        // Mapeo indexado por código
        $actMap = [];
        $successors = [];
        foreach ($activities as $act) {
            $actMap[$act->codigo] = $act;
            $successors[$act->codigo] = [];
        }

        // Construir lista de sucesores
        foreach ($activities as $act) {
            $preds = is_array($act->predecesores) ? $act->predecesores : (json_decode($act->predecesores, true) ?? []);
            foreach ($preds as $p) {
                if (isset($successors[$p])) {
                    $successors[$p][] = $act->codigo;
                }
            }
        }

        // Orden topológico simplificado
        $inDegree = [];
        foreach ($activities as $act) {
            $preds = is_array($act->predecesores) ? $act->predecesores : (json_decode($act->predecesores, true) ?? []);
            $inDegree[$act->codigo] = count($preds);
        }

        $queue = [];
        foreach ($inDegree as $code => $deg) {
            if ($deg === 0) {
                $queue[] = $code;
            }
        }

        $topoOrder = [];
        while (!empty($queue)) {
            $curr = array_shift($queue);
            $topoOrder[] = $curr;

            foreach ($successors[$curr] as $succ) {
                $inDegree[$succ]--;
                if ($inDegree[$succ] === 0) {
                    $queue[] = $succ;
                }
            }
        }

        // Si hubiera ciclos o nodos aislados, incluir los que falten
        foreach ($activities as $act) {
            if (!in_array($act->codigo, $topoOrder)) {
                $topoOrder[] = $act->codigo;
            }
        }

        // 1. PASO ADELANTE (Forward Pass): ES & EF
        foreach ($topoOrder as $code) {
            $act = $actMap[$code];
            $preds = is_array($act->predecesores) ? $act->predecesores : (json_decode($act->predecesores, true) ?? []);

            if (empty($preds)) {
                $act->es = 0;
            } else {
                $maxEf = 0;
                foreach ($preds as $p) {
                    if (isset($actMap[$p])) {
                        $maxEf = max($maxEf, $actMap[$p]->ef ?? 0);
                    }
                }
                $act->es = $maxEf;
            }
            $act->ef = $act->es + $act->duracion;
        }

        // Duración total del proyecto = Máximo EF
        $duracionProyecto = 0;
        foreach ($activities as $act) {
            $duracionProyecto = max($duracionProyecto, $act->ef ?? 0);
        }

        // 2. PASO ATRÁS (Backward Pass): LF & LS
        $reverseTopo = array_reverse($topoOrder);
        foreach ($reverseTopo as $code) {
            $act = $actMap[$code];
            $succs = $successors[$code];

            if (empty($succs)) {
                $act->lf = $duracionProyecto;
            } else {
                $minLs = PHP_INT_MAX;
                foreach ($succs as $s) {
                    if (isset($actMap[$s])) {
                        $minLs = min($minLs, $actMap[$s]->ls ?? $duracionProyecto);
                    }
                }
                $act->lf = ($minLs === PHP_INT_MAX) ? $duracionProyecto : $minLs;
            }
            $act->ls = $act->lf - $act->duracion;
            $act->holgura = $act->ls - $act->es;
            $act->es_critica = abs($act->holgura) < 0.0001;

            // Guardar en base de datos
            $act->save();
        }

        // Recopilar ruta crítica y datos de grafos para Vis.js
        $rutaCritica = [];
        $nodes = [];
        $edges = [];

        foreach ($activities as $act) {
            if ($act->es_critica) {
                $rutaCritica[] = $act->codigo;
            }

            $color = $act->es_critica ? '#dc3545' : '#0d6efd';
            $nodes[] = [
                'id' => $act->codigo,
                'label' => "{$act->codigo}\n{$act->nombre}\nDur: {$act->duracion}m\n[ES:{$act->es} EF:{$act->ef}]\n[LS:{$act->ls} LF:{$act->lf}]",
                'color' => [
                    'background' => $color,
                    'border' => '#000',
                    'highlight' => ['background' => '#ffc107', 'border' => '#000']
                ],
                'font' => ['color' => '#ffffff'],
                'shape' => 'box',
            ];

            $preds = is_array($act->predecesores) ? $act->predecesores : (json_decode($act->predecesores, true) ?? []);
            foreach ($preds as $p) {
                $edges[] = [
                    'from' => $p,
                    'to' => $act->codigo,
                    'arrows' => 'to',
                    'color' => ($act->es_critica && isset($actMap[$p]) && $actMap[$p]->es_critica) ? '#dc3545' : '#848484',
                    'width' => ($act->es_critica && isset($actMap[$p]) && $actMap[$p]->es_critica) ? 3 : 1,
                ];
            }
        }

        return [
            'activities' => $activities,
            'ruta_critica' => $rutaCritica,
            'duracion_proyecto' => $duracionProyecto,
            'graph_data' => [
                'nodes' => $nodes,
                'edges' => $edges,
            ],
        ];
    }
}
