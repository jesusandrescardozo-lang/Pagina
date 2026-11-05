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
        // Los datos de la página se cachean 60 s y se invalidan solos en
        // cada escritura (versión de caché): ahorra ~5 consultas de ida y
        // vuelta a Neon en cada visita al listado.
        $datos = $this->cacheaPagina($request, function () use ($request) {
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

            // UNA sola consulta con FILTER (Postgres) en vez de 5 count()
            // separados: con Neon cada consulta extra son ~200 ms de ida y vuelta.
            $resumen = Supply::selectRaw("
                    count(*) filter (where categoria = 'alimento')              as alimentos,
                    count(*) filter (where categoria = 'medico')                as medicos,
                    count(*) filter (where clasificacion_abc = 'A')             as clase_a,
                    count(*) filter (where clasificacion_abc = 'B')             as clase_b,
                    count(*) filter (where clasificacion_abc = 'C')             as clase_c
                ")
                ->first();

            $resumenCategorias = [
                'alimentos' => (int) ($resumen->alimentos ?? 0),
                'medicos'   => (int) ($resumen->medicos ?? 0),
                'clase_a'   => (int) ($resumen->clase_a ?? 0),
                'clase_b'   => (int) ($resumen->clase_b ?? 0),
                'clase_c'   => (int) ($resumen->clase_c ?? 0),
            ];

            return compact('supplies', 'resumenCategorias');
        });

        return view('supplies.index', $datos);
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
        // (lista cacheada del menú, se invalida al escribir en refugios)
        $shelters = Shelter::listaParaMenu();
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
        // 'supply' es imprescindible: los accessors consumo_diario_estimado /
        // dias_autonomia / nivel_alerta (que usa la vista en CADA fila) lo
        // consultan si no está cargado (N+1 sobre supplies).
        $supply->load(['inventories.shelter', 'inventories.supply', 'inventories.activeBatchesFefo']);

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
