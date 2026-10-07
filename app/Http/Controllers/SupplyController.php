<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplyRequest;
use App\Http\Requests\UpdateSupplyRequest;
use App\Models\Supply;
use App\Models\Shelter;
use App\Models\ShelterInventory;
use App\Services\AuditService;
use App\Services\InventoryOptimizationService;
use Illuminate\Http\Request;

class SupplyController extends Controller
{
    protected InventoryOptimizationService $inventoryService;

    public function __construct(InventoryOptimizationService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index(Request $request)
    {
        $query = Supply::with('inventories.shelter');

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        if ($request->filled('clasificacion_abc')) {
            $query->where('clasificacion_abc', $request->clasificacion_abc);
        }

        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $query->where(function($q) use ($term) {
                $q->where('nombre', 'like', $term)
                  ->orWhere('codigo', 'like', $term)
                  ->orWhere('subcategoria', 'like', $term);
            });
        }

        $supplies = $query->orderBy('categoria')->orderBy('nombre')->paginate(15)->withQueryString();

        $resumenCategorias = [
            'alimentos' => Supply::where('categoria', 'alimento')->count(),
            'medicos' => Supply::where('categoria', 'medico')->count(),
            'clase_a' => Supply::where('clasificacion_abc', 'A')->count(),
            'clase_b' => Supply::where('clasificacion_abc', 'B')->count(),
            'clase_c' => Supply::where('clasificacion_abc', 'C')->count(),
        ];

        return view('supplies.index', compact('supplies', 'resumenCategorias'));
    }

    public function create()
    {
        return view('supplies.create');
    }

    public function store(StoreSupplyRequest $request)
    {
        $validated = $request->validated();

        $validated['es_perecedero'] = $request->has('es_perecedero');

        $supply = Supply::create($validated);

        // Crear registro de inventario en cada refugio
        $shelters = Shelter::all();
        foreach ($shelters as $shelter) {
            ShelterInventory::create([
                'shelter_id' => $shelter->id,
                'supply_id' => $supply->id,
                'stock_actual' => 0.00,
                'stock_minimo' => $supply->stock_seguridad_sugerido,
                'stock_maximo' => $supply->stock_seguridad_sugerido * 5,
                'punto_reorden' => $supply->stock_seguridad_sugerido * 1.5,
            ]);
        }

        AuditService::log('insumos', 'creado', "Insumo registrado: {$supply->nombre} ({$supply->codigo})", $supply, [], $supply->toArray());

        return redirect()->route('supplies.show', $supply)
            ->with('success', "Insumo '{$supply->nombre}' registrado en el catálogo maestro.");
    }

    public function show(Supply $supply)
    {
        $supply->load(['inventories.shelter', 'inventories.activeBatchesFefo']);

        // Demanda anual agregada estimada
        $consumoDiarioTotal = 0.0;
        foreach ($supply->inventories as $inv) {
            $pob = $inv->shelter ? $inv->shelter->poblacion_total : 0;
            $consumoDiarioTotal += $pob * $supply->consumo_diario_referencia;
        }
        $demandaAnual = max(10.0, $consumoDiarioTotal * 365);

        // Cálculo EOQ
        $eoq = $this->inventoryService->calcularEoq(
            $demandaAnual,
            $supply->costo_pedido,
            $supply->costo_mantenimiento_anual,
            $supply->costo_unitario,
            $supply->tiempo_entrega_dias,
            $supply->stock_seguridad_sugerido
        );

        // Cálculo Newsvendor si aplica
        $newsvendor = null;
        if ($supply->costo_escasez_newsvendor && $supply->costo_desecho_newsvendor) {
            $mediaMensual = max(1.0, $demandaAnual / 12);
            $desviacion = max(1.0, $mediaMensual * 0.25); // Suposición de variabilidad 25%
            $newsvendor = $this->inventoryService->calcularNewsvendor(
                $mediaMensual,
                $desviacion,
                $supply->costo_escasez_newsvendor,
                $supply->costo_desecho_newsvendor
            );
        }

        return view('supplies.show', compact('supply', 'eoq', 'newsvendor', 'demandaAnual', 'consumoDiarioTotal'));
    }

    public function edit(Supply $supply)
    {
        return view('supplies.edit', compact('supply'));
    }

    public function update(UpdateSupplyRequest $request, Supply $supply)
    {
        $validated = $request->validated();

        $validated['es_perecedero'] = $request->has('es_perecedero');

        $antes = $supply->toArray();
        $supply->update($validated);

        AuditService::log('insumos', 'actualizado', "Insumo actualizado: {$supply->nombre}", $supply, $antes, $supply->toArray());

        return redirect()->route('supplies.show', $supply)
            ->with('success', "Parámetros del insumo '{$supply->nombre}' actualizados.");
    }

    public function destroy(Supply $supply)
    {
        $nombre = $supply->nombre;
        $antes = $supply->toArray();
        $supply->delete();

        AuditService::log('insumos', 'eliminado', "Insumo dado de baja (borrado lógico): {$nombre}", null, $antes, []);

        return redirect()->route('supplies.index')
            ->with('success', "Insumo '{$nombre}' dado de baja (registro conservado para trazabilidad).");
    }
}
