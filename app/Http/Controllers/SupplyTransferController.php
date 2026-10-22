<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplyTransferRequest;
use App\Models\InventoryMovement;
use App\Models\Shelter;
use App\Models\Supply;
use App\Models\SupplyTransfer;
use App\Services\AuditService;
use App\Services\InventoryOptimizationService;
use App\Services\TransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupplyTransferController extends Controller
{
    protected InventoryOptimizationService $inventoryService;
    protected TransferService $transferService;

    public function __construct(InventoryOptimizationService $inventoryService, TransferService $transferService)
    {
        $this->inventoryService = $inventoryService;
        $this->transferService = $transferService;
    }

    public function index(Request $request)
    {
        $query = SupplyTransfer::with(['refugioOrigen', 'refugioDestino', 'supply', 'solicitadoPor']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('prioridad')) {
            $query->where('prioridad', $request->prioridad);
        }

        $transfers = $query->latest()->paginate(15)->withQueryString();

        // Sugerencias heurísticas de rebalanceo inter-refugios
        $sugerencias = $this->inventoryService->sugerirTransferenciasRebalanceo();

        return view('transfers.index', compact('transfers', 'sugerencias'));
    }

    public function show(SupplyTransfer $transfer)
    {
        $transfer->load([
            'refugioOrigen',
            'refugioDestino',
            'supply',
            'solicitadoPor',
            'aprobadoPor',
        ]);

        $movimientos = InventoryMovement::with(['shelter', 'user'])
            ->where('documento_referencia', $transfer->codigo_transferencia)
            ->orderBy('fecha_movimiento')
            ->get();

        return view('transfers.show', compact('transfer', 'movimientos'));
    }

    public function create(Request $request)
    {
        $shelters = Shelter::where('estado', '!=', 'cerrado')->orderBy('codigo')->get();
        $supplies = Supply::orderBy('categoria')->orderBy('nombre')->get();

        $origenId = $request->get('origen_id');
        $destinoId = $request->get('destino_id');
        $supplyId = $request->get('supply_id');
        $cantidad = $request->get('cantidad', '');
        $prioridad = $request->get('prioridad', 'alta');

        return view('transfers.create', compact(
            'shelters',
            'supplies',
            'origenId',
            'destinoId',
            'supplyId',
            'cantidad',
            'prioridad'
        ));
    }

    public function store(StoreSupplyTransferRequest $request)
    {
        $validated = $request->validated();

        $validated['codigo_transferencia'] = 'TRF-' . strtoupper(Str::random(8));
        $validated['estado'] = 'solicitada';
        $validated['solicitado_por'] = auth()->id();

        $transfer = SupplyTransfer::create($validated);

        AuditService::log('transferencias', 'creado', "Solicitud de transferencia registrada: {$transfer->codigo_transferencia}", $transfer, [], $transfer->toArray());

        return redirect()->route('transfers.index')
            ->with('success', "Transferencia {$transfer->codigo_transferencia} solicitada con éxito.");
    }

    /**
     * solicitada -> aprobada (sin movimiento de stock).
     */
    public function approve(SupplyTransfer $transfer)
    {
        return $this->ejecutarTransicion($transfer, 'aprobada', 'Transferencia aprobada');
    }

    /**
     * aprobada -> en_transito (descuenta stock y lotes FEFO en el origen).
     */
    public function despachar(Request $request, SupplyTransfer $transfer)
    {
        $validated = $request->validate([
            'vehiculo_transporte' => 'nullable|string|max:100',
        ]);

        try {
            $this->transferService->despachar($transfer, $validated['vehiculo_transporte'] ?? null);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        AuditService::log(
            'transferencias',
            'estado_actualizado',
            "Transferencia {$transfer->codigo_transferencia} despachada (en tránsito)",
            $transfer
        );

        return redirect()->route('transfers.index')
            ->with('success', "Transferencia {$transfer->codigo_transferencia} despachada. Stock descontado del refugio origen.");
    }

    /**
     * en_transito -> completada (entrega stock y recrea lotes en el destino).
     */
    public function receive(SupplyTransfer $transfer)
    {
        return $this->ejecutarTransicion($transfer, 'completada', 'Transferencia completada');
    }

    /**
     * Cambio de estado genérico (respeta la máquina de estados).
     */
    public function updateStatus(Request $request, SupplyTransfer $transfer)
    {
        $validated = $request->validate([
            'estado' => 'required|in:aprobada,en_transito,completada,cancelada',
            'vehiculo_transporte' => 'nullable|string|max:100',
        ]);

        $nuevoEstado = $validated['estado'];

        if ($nuevoEstado === $transfer->estado) {
            return back();
        }

        try {
            $this->transferService->cambiarEstado($transfer, $nuevoEstado, $validated);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        AuditService::log(
            'transferencias',
            'estado_actualizado',
            "Transferencia {$transfer->codigo_transferencia} cambió a '{$nuevoEstado}'",
            $transfer
        );

        return redirect()->route('transfers.index')
            ->with('success', "Estado de la transferencia {$transfer->codigo_transferencia} actualizado a '{$nuevoEstado}'.");
    }

    /**
     * Ejecuta una transición sencilla (sin stock) con manejo de errores.
     */
    private function ejecutarTransicion(SupplyTransfer $transfer, string $nuevoEstado, string $exitoMensaje)
    {
        $estadoAnterior = $transfer->estado;

        try {
            $this->transferService->cambiarEstado($transfer, $nuevoEstado);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        AuditService::log(
            'transferencias',
            'estado_actualizado',
            "Transferencia {$transfer->codigo_transferencia} cambió de {$estadoAnterior} a {$nuevoEstado}",
            $transfer
        );

        return redirect()->route('transfers.index')
            ->with('success', "{$exitoMensaje}: {$transfer->codigo_transferencia}.");
    }
}
