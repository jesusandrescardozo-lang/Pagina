<?php

namespace App\Http\Controllers;

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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:30|unique:supplies,codigo',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'categoria' => 'required|in:alimento,medico',
            'subcategoria' => 'required|string|max:100',
            'unidad_medida' => 'required|string|max:30',
            'clasificacion_abc' => 'required|in:A,B,C',
            'es_perecedero' => 'nullable|boolean',
            'dias_vida_util_promedio' => 'nullable|integer|min:1',
            'costo_unitario' => 'required|numeric|min:0.01',
            'costo_pedido' => 'required|numeric|min:0.01',
            'costo_mantenimiento_anual' => 'required|numeric|min:0.01',
            'tiempo_entrega_dias' => 'required|numeric|min:0.1',
            'consumo_diario_referencia' => 'required|numeric|min:0.001',
            'stock_seguridad_sugerido' => 'required|numeric|min:0',
            'costo_escasez_newsvendor' => 'nullable|numeric|min:0.01',
            'costo_desecho_newsvendor' => 'nullable|numeric|min:0.01',
        ]);

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

    public function update(Request $request, Supply $supply)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:30|unique:supplies,codigo,' . $supply->id,
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'categoria' => 'required|in:alimento,medico',
            'subcategoria' => 'required|string|max:100',
            'unidad_medida' => 'required|string|max:30',
            'clasificacion_abc' => 'required|in:A,B,C',
            'es_perecedero' => 'nullable|boolean',
            'dias_vida_util_promedio' => 'nullable|integer|min:1',
            'costo_unitario' => 'required|numeric|min:0.01',
            'costo_pedido' => 'required|numeric|min:0.01',
            'costo_mantenimiento_anual' => 'required|numeric|min:0.01',
            'tiempo_entrega_dias' => 'required|numeric|min:0.1',
            'consumo_diario_referencia' => 'required|numeric|min:0.001',
            'stock_seguridad_sugerido' => 'required|numeric|min:0',
            'costo_escasez_newsvendor' => 'nullable|numeric|min:0.01',
            'costo_desecho_newsvendor' => 'nullable|numeric|min:0.01',
        ]);

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

        AuditService::log('insumos', 'eliminado', "Insumo eliminado: {$nombre}", null, $antes, []);

        return redirect()->route('supplies.index')
            ->with('success', "Insumo '{$nombre}' eliminado del catálogo.");
    }

    /**
     * Calculadora interactiva y simulador de Modelos de Inventario I.O.
     */
    public function calculator(Request $request)
    {
        $supplies = Supply::all();
        $selectedSupply = null;

        if ($request->filled('supply_id')) {
            $selectedSupply = Supply::find($request->supply_id);
        }

        // Parámetros por defecto o del request
        $demanda = (float) ($request->get('demanda', $selectedSupply ? 5000 : 3600));
        $costoPedido = (float) ($request->get('costo_pedido', $selectedSupply ? $selectedSupply->costo_pedido : 25.0));
        $costoMant = (float) ($request->get('costo_mantenimiento', $selectedSupply ? $selectedSupply->costo_mantenimiento_anual : 3.5));
        $costoUnit = (float) ($request->get('costo_unitario', $selectedSupply ? $selectedSupply->costo_unitario : 12.0));
        $leadTime = (float) ($request->get('lead_time', $selectedSupply ? $selectedSupply->tiempo_entrega_dias : 4.0));
        $stockSeguridad = (float) ($request->get('stock_seguridad', $selectedSupply ? $selectedSupply->stock_seguridad_sugerido : 50.0));

        $eoq = $this->inventoryService->calcularEoq($demanda, $costoPedido, $costoMant, $costoUnit, $leadTime, $stockSeguridad);

        // Newsvendor
        $nvMedia = (float) ($request->get('nv_media', 400));
        $nvDesv = (float) ($request->get('nv_desv', 80));
        $nvEscasez = (float) ($request->get('nv_escasez', 45.0));
        $nvDesecho = (float) ($request->get('nv_desecho', 15.0));

        $newsvendor = $this->inventoryService->calcularNewsvendor($nvMedia, $nvDesv, $nvEscasez, $nvDesecho);

        return view('supplies.calculator', compact(
            'supplies',
            'selectedSupply',
            'demanda',
            'costoPedido',
            'costoMant',
            'costoUnit',
            'leadTime',
            'stockSeguridad',
            'eoq',
            'nvMedia',
            'nvDesv',
            'nvEscasez',
            'nvDesecho',
            'newsvendor'
        ));
    }
}
