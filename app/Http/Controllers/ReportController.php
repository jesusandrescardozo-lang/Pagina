<?php

namespace App\Http\Controllers;

use App\Models\Shelter;
use App\Models\Supply;
use App\Models\ShelterInventory;
use App\Models\InventoryMovement;
use App\Services\InventoryOptimizationService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    protected InventoryOptimizationService $inventoryService;

    public function __construct(InventoryOptimizationService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index()
    {
        $shelters = Shelter::orderBy('codigo')->get();
        $supplies = Supply::orderBy('nombre')->get();
        return view('reports.index', compact('shelters', 'supplies'));
    }

    public function inventarioRefugios(Request $request)
    {
        $query = Shelter::with(['inventories.supply', 'inventories.activeBatchesFefo']);

        if ($request->filled('shelter_id')) {
            $query->where('id', $request->shelter_id);
        }

        $shelters = $query->where('estado', '!=', 'cerrado')->get();
        $matriz = $this->inventoryService->getMatrizAutonomia();

        $pdf = Pdf::loadView('pdf.inventario_refugios', [
            'shelters' => $shelters,
            'alertasCriticas' => $matriz['alertas_criticas'],
            'fecha' => now()->format('d/m/Y H:i'),
        ]);

        return $pdf->download('reporte_inventario_refugios.pdf');
    }

    public function kardexMovimientos(Request $request)
    {
        $query = InventoryMovement::with(['shelter', 'supply', 'batch', 'user']);

        if ($request->filled('shelter_id')) {
            $query->where('shelter_id', $request->shelter_id);
        }
        if ($request->filled('supply_id')) {
            $query->where('supply_id', $request->supply_id);
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_movimiento', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_movimiento', '<=', $request->fecha_hasta);
        }

        $movements = $query->latest('fecha_movimiento')->get();
        $shelter = $request->filled('shelter_id') ? Shelter::find($request->shelter_id) : null;
        $supply = $request->filled('supply_id') ? Supply::find($request->supply_id) : null;

        $pdf = Pdf::loadView('pdf.kardex_movimientos', [
            'movements' => $movements,
            'shelter' => $shelter,
            'supply' => $supply,
            'fecha' => now()->format('d/m/Y H:i'),
        ]);

        return $pdf->download('kardex_movimientos.pdf');
    }

    public function analisisOperaciones(Request $request)
    {
        $abcData = $this->inventoryService->calcularClasificacionAbc();
        $supplies = Supply::with('inventories')->get();
        $sugerencias = $this->inventoryService->sugerirTransferenciasRebalanceo();

        $pdf = Pdf::loadView('pdf.analisis_operaciones', [
            'abcData' => $abcData,
            'supplies' => $supplies,
            'sugerencias' => $sugerencias,
            'fecha' => now()->format('d/m/Y H:i'),
        ]);

        return $pdf->download('informe_optimizacion_inventarios_ido.pdf');
    }
}
