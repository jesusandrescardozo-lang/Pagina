<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryMovementRequest;
use App\Models\Shelter;
use App\Models\Supply;
use App\Models\ShelterInventory;
use App\Models\InventoryBatch;
use App\Models\InventoryMovement;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryMovement::with(['shelter', 'supply', 'batch', 'user']);

        $shelterId = $request->has('shelter_id') ? $request->input('shelter_id') : session('shelter_id');
        if ($shelterId) {
            $query->where('shelter_id', $shelterId);
        }

        if ($request->filled('supply_id')) {
            $query->where('supply_id', $request->supply_id);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('categoria')) {
            $query->whereHas('supply', fn($q) => $q->where('categoria', $request->categoria));
        }

        $movements = $query->latest('fecha_movimiento')->paginate(20)->withQueryString();

        $shelters = Shelter::orderBy('codigo')->get();
        $supplies = Supply::orderBy('nombre')->get();

        return view('movements.index', compact('movements', 'shelters', 'supplies'));
    }

    public function create(Request $request)
    {
        $shelters = Shelter::where('estado', '!=', 'cerrado')->orderBy('codigo')->get();
        $supplies = Supply::orderBy('categoria')->orderBy('nombre')->get();

        $selectedShelterId = $request->get('shelter_id');
        $selectedSupplyId = $request->get('supply_id');
        $defaultTipo = $request->get('tipo', 'entrada_donacion');

        return view('movements.create', compact(
            'shelters',
            'supplies',
            'selectedShelterId',
            'selectedSupplyId',
            'defaultTipo'
        ));
    }

    public function store(StoreInventoryMovementRequest $request)
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($validated) {
            $shelter = Shelter::findOrFail($validated['shelter_id']);
            $supply = Supply::findOrFail($validated['supply_id']);

            ShelterInventory::firstOrCreate(
                ['shelter_id' => $shelter->id, 'supply_id' => $supply->id],
                [
                    'stock_actual' => 0.00,
                    'stock_minimo' => $supply->stock_seguridad_sugerido,
                    'stock_maximo' => $supply->stock_seguridad_sugerido * 5,
                    'punto_reorden' => $supply->stock_seguridad_sugerido * 1.5,
                ]
            );

            // Bloqueo pesimista: evita condiciones de carrera entre movimientos simultáneos
            $inventory = ShelterInventory::where('shelter_id', $shelter->id)
                ->where('supply_id', $supply->id)
                ->lockForUpdate()
                ->firstOrFail();

            $cantidad = (float) $validated['cantidad'];
            $stockAnterior = (float) $inventory->stock_actual;
            $batchId = null;

            if (str_starts_with($validated['tipo'], 'entrada')) {
                // ENTRADA: Crear o actualizar lote
                $loteNum = !empty($validated['numero_lote']) 
                    ? $validated['numero_lote'] 
                    : 'LOT-' . strtoupper(substr(uniqid(), -6));

                $batch = InventoryBatch::create([
                    'shelter_inventory_id' => $inventory->id,
                    'numero_lote' => $loteNum,
                    'cantidad_inicial' => $cantidad,
                    'cantidad_disponible' => $cantidad,
                    'fecha_ingreso' => now()->toDateString(),
                    'fecha_vencimiento' => $validated['fecha_vencimiento'] ?? null,
                    'estado' => 'vigente',
                ]);
                $batchId = $batch->id;

                $stockNuevo = $stockAnterior + $cantidad;
            } elseif ($validated['tipo'] === 'ajuste_inventario') {
                // AJUSTE DIRECTO
                $stockNuevo = $cantidad;
            } else {
                // SALIDA (Consumo, Merma o Transferencia)
                if ($stockAnterior < $cantidad) {
                    return back()->withInput()->with('error', "Stock insuficiente en {$shelter->nombre}. Stock actual: {$stockAnterior} {$supply->unidad_medida}, solicitado: {$cantidad} {$supply->unidad_medida}.");
                }

                $stockNuevo = $stockAnterior - $cantidad;

                // Aplicar deducción FEFO (First Expired, First Out) sobre lotes
                $lotes = $inventory->activeBatchesFefo()->lockForUpdate()->get();
                $pendienteDeducir = $cantidad;

                foreach ($lotes as $lote) {
                    if ($pendienteDeducir <= 0) break;

                    if ($lote->cantidad_disponible <= $pendienteDeducir) {
                        $pendienteDeducir -= $lote->cantidad_disponible;
                        $lote->update([
                            'cantidad_disponible' => 0,
                            'estado' => 'agotado',
                        ]);
                    } else {
                        $lote->update([
                            'cantidad_disponible' => $lote->cantidad_disponible - $pendienteDeducir,
                        ]);
                        $pendienteDeducir = 0;
                    }
                    $batchId = $lote->id;
                }
            }

            // Actualizar stock consolidado
            $inventory->update(['stock_actual' => $stockNuevo]);

            // Registrar movimiento en Kardex
            $movimiento = InventoryMovement::create([
                'shelter_id' => $shelter->id,
                'supply_id' => $supply->id,
                'inventory_batch_id' => $batchId,
                'user_id' => auth()->id(),
                'tipo' => $validated['tipo'],
                'cantidad' => $cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockNuevo,
                'documento_referencia' => $validated['documento_referencia'] ?? null,
                'motivo' => $validated['motivo'] ?? null,
                'fecha_movimiento' => now(),
            ]);

            AuditService::log(
                'kardex',
                'movimiento',
                "Movimiento Kardex ({$validated['tipo']}): {$cantidad} {$supply->unidad_medida} de {$supply->nombre} en {$shelter->nombre}",
                $movimiento,
                ['stock_anterior' => $stockAnterior],
                ['stock_nuevo' => $stockNuevo]
            );

            return redirect()->route('movements.index')
                ->with('success', "Movimiento registrado exitosamente en Kardex. Nuevo stock: {$stockNuevo} {$supply->unidad_medida}.");
        });
    }
}
