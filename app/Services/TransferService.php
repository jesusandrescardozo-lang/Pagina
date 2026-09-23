<?php

namespace App\Services;

use App\Models\InventoryBatch;
use App\Models\InventoryMovement;
use App\Models\ShelterInventory;
use App\Models\SupplyTransfer;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;

/**
 * Máquina de estados de las transferencias inter-refugios.
 *
 * Transiciones permitidas:
 *   solicitada  -> aprobada | cancelada
 *   aprobada    -> en_transito | cancelada
 *   en_transito -> completada | cancelada (devuelve stock y lotes al origen)
 *   completada  -> (final)
 *   cancelada   -> (final)
 *
 * Reglas de integridad:
 *  - El stock solo se mueve en: despacho (sale del origen) y recepción (entra al destino).
 *  - El despacho descuenta lotes FEFO reales y los guarda en `lotes_despacho`.
 *  - La recepción recrea esos lotes con su vencimiento original en el destino.
 *  - Toda lectura-escritura de stock usa bloqueo pesimista (lockForUpdate).
 */
class TransferService
{
    public const TRANSICIONES = [
        'solicitada'  => ['aprobada', 'cancelada'],
        'aprobada'    => ['en_transito', 'cancelada'],
        'en_transito' => ['completada', 'cancelada'],
        'completada'  => [],
        'cancelada'   => [],
    ];

    /**
     * Valida que la transición sea legal. Lanza \RuntimeException si no lo es.
     */
    public function validarTransicion(SupplyTransfer $transfer, string $nuevoEstado): void
    {
        $permitidas = self::TRANSICIONES[$transfer->estado] ?? [];

        if (!in_array($nuevoEstado, $permitidas, true)) {
            $actual = $transfer->estado;
            throw new \RuntimeException(
                "Transición inválida: una transferencia '{$actual}' no puede pasar a '{$nuevoEstado}'."
                . (empty($permitidas) ? " El estado '{$actual}' es definitivo." : ' Estados permitidos: ' . implode(', ', $permitidas) . '.')
            );
        }
    }

    /**
     * solicitada -> aprobada (sin movimiento de stock).
     */
    public function aprobar(SupplyTransfer $transfer): void
    {
        $this->cambiarEstado($transfer, 'aprobada');
    }

    /**
     * aprobada -> en_transito: descuenta stock y lotes FEFO en el ORIGEN.
     */
    public function despachar(SupplyTransfer $transfer, ?string $vehiculo = null): void
    {
        DB::transaction(function () use ($transfer, $vehiculo) {
            $this->validarTransicion($transfer, 'en_transito');

            $supply = $transfer->supply;
            $origen = $transfer->refugioOrigen;
            $destino = $transfer->refugioDestino;
            $cantidad = (float) $transfer->cantidad;

            $inventario = $this->lockInventario($origen->id, $supply->id, $supply);

            if ($inventario->stock_actual < $cantidad) {
                throw new \RuntimeException(
                    "Stock insuficiente en {$origen->nombre}: hay {$inventario->stock_actual} {$supply->unidad_medida} y se requieren {$cantidad} {$supply->unidad_medida}."
                );
            }

            $stockAnt = (float) $inventario->stock_actual;
            $stockNue = $stockAnt - $cantidad;

            // Descuento FEFO sobre lotes reales + registro de lotes despachados
            [$lotesDespacho, $ultimoLoteId] = $this->descontarLotesFefo($inventario, $cantidad, $supply);

            $inventario->update(['stock_actual' => $stockNue]);

            InventoryMovement::create([
                'shelter_id' => $origen->id,
                'supply_id' => $supply->id,
                'inventory_batch_id' => $ultimoLoteId,
                'user_id' => auth()->id(),
                'tipo' => 'salida_transferencia',
                'cantidad' => $cantidad,
                'stock_anterior' => $stockAnt,
                'stock_nuevo' => $stockNue,
                'documento_referencia' => $transfer->codigo_transferencia,
                'motivo' => "Despacho por transferencia hacia {$destino->nombre}",
                'fecha_movimiento' => now(),
            ]);

            $transfer->update([
                'estado' => 'en_transito',
                'fecha_salida' => now(),
                'aprobado_por' => auth()->id(),
                'vehiculo_transporte' => $vehiculo ?? $transfer->vehiculo_transporte,
                'lotes_despacho' => $lotesDespacho,
            ]);
        });
    }

    /**
     * en_transito -> completada: entrega stock y recrea lotes en el DESTINO
     * conservando el vencimiento original de cada lote (FEFO correcto).
     */
    public function recibir(SupplyTransfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
            $this->validarTransicion($transfer, 'completada');

            $supply = $transfer->supply;
            $origen = $transfer->refugioOrigen;
            $destino = $transfer->refugioDestino;
            $cantidad = (float) $transfer->cantidad;

            $inventario = $this->lockInventario($destino->id, $supply->id, $supply);

            $stockAnt = (float) $inventario->stock_actual;
            $stockNue = $stockAnt + $cantidad;

            // Recrear lotes con su vencimiento original (o regla del insumo si no hay datos)
            $lotes = $transfer->lotes_despacho ?? [];
            $ultimoLoteId = null;

            if (empty($lotes)) {
                // Transferencia antigua (sin registro de lotes): aplica regla por defecto
                $lote = InventoryBatch::create([
                    'shelter_inventory_id' => $inventario->id,
                    'numero_lote' => 'TRF-REC-' . strtoupper(substr(uniqid(), -5)),
                    'cantidad_inicial' => $cantidad,
                    'cantidad_disponible' => $cantidad,
                    'fecha_ingreso' => now()->toDateString(),
                    'fecha_vencimiento' => $this->vencimientoPorDefecto($supply),
                    'estado' => 'vigente',
                ]);
                $ultimoLoteId = $lote->id;
            } else {
                foreach ($lotes as $loteDatos) {
                    $ultimoLoteId = $this->crearLoteRecepcion($inventario, $loteDatos);
                }
            }

            $inventario->update(['stock_actual' => $stockNue]);

            InventoryMovement::create([
                'shelter_id' => $destino->id,
                'supply_id' => $supply->id,
                'inventory_batch_id' => $ultimoLoteId,
                'user_id' => auth()->id(),
                'tipo' => 'entrada_transferencia',
                'cantidad' => $cantidad,
                'stock_anterior' => $stockAnt,
                'stock_nuevo' => $stockNue,
                'documento_referencia' => $transfer->codigo_transferencia,
                'motivo' => "Recepción de transferencia desde {$origen->nombre}",
                'fecha_movimiento' => now(),
            ]);

            $transfer->update([
                'estado' => 'completada',
                'fecha_llegada' => now(),
            ]);
        });
    }

    /**
     * Cualquier estado -> cancelada.
     * Si estaba EN TRÁNSITO, devuelve stock y lotes al origen (kardex inverso).
     */
    public function cancelar(SupplyTransfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
            $this->validarTransicion($transfer, 'cancelada');

            $estadoAnterior = $transfer->estado;

            if ($estadoAnterior === 'en_transito') {
                $supply = $transfer->supply;
                $origen = $transfer->refugioOrigen;
                $cantidad = (float) $transfer->cantidad;

                $inventario = $this->lockInventario($origen->id, $supply->id, $supply);

                $stockAnt = (float) $inventario->stock_actual;
                $stockNue = $stockAnt + $cantidad;

                // Devolver los lotes despachados a su estado original
                foreach ($transfer->lotes_despacho ?? [] as $loteDatos) {
                    $this->devolverLote($inventario, $loteDatos);
                }

                $inventario->update(['stock_actual' => $stockNue]);

                InventoryMovement::create([
                    'shelter_id' => $origen->id,
                    'supply_id' => $supply->id,
                    'user_id' => auth()->id(),
                    'tipo' => 'entrada_transferencia',
                    'cantidad' => $cantidad,
                    'stock_anterior' => $stockAnt,
                    'stock_nuevo' => $stockNue,
                    'documento_referencia' => $transfer->codigo_transferencia,
                    'motivo' => "Cancelación en tránsito: retorno de stock al origen desde {$transfer->refugioDestino->nombre}",
                    'fecha_movimiento' => now(),
                ]);
            }

            $transfer->update(['estado' => 'cancelada']);
        });
    }

    /**
     * Cambio de estado genérico (usa la misma máquina de estados).
     */
    public function cambiarEstado(SupplyTransfer $transfer, string $nuevoEstado, array $data = []): void
    {
        $this->validarTransicion($transfer, $nuevoEstado);

        if ($nuevoEstado === 'en_transito') {
            $this->despachar($transfer, $data['vehiculo_transporte'] ?? null);
            return;
        }
        if ($nuevoEstado === 'completada') {
            $this->recibir($transfer);
            return;
        }
        if ($nuevoEstado === 'cancelada') {
            $this->cancelar($transfer);
            return;
        }

        $transfer->update(['estado' => $nuevoEstado]);

        AuditService::log(
            'transferencias',
            'estado_actualizado',
            "Transferencia {$transfer->codigo_transferencia} cambió a '{$nuevoEstado}'"
        );
    }

    // ----------------------------------------------------------------
    // Helpers internos
    // ----------------------------------------------------------------

    /**
     * Obtiene el inventario con bloqueo pesimista (lo crea si no existe).
     */
    private function lockInventario(int $shelterId, int $supplyId, $supply): ShelterInventory
    {
        ShelterInventory::firstOrCreate(
            ['shelter_id' => $shelterId, 'supply_id' => $supplyId],
            [
                'stock_actual' => 0.00,
                'stock_minimo' => $supply->stock_seguridad_sugerido,
                'stock_maximo' => $supply->stock_seguridad_sugerido * 5,
                'punto_reorden' => $supply->stock_seguridad_sugerido * 1.5,
            ]
        );

        return ShelterInventory::where('shelter_id', $shelterId)
            ->where('supply_id', $supplyId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    /**
     * Descuenta FEFO los lotes del inventario y devuelve:
     *  - lista de lotes consumidos [numero_lote, cantidad, fecha_vencimiento]
     *  - id del último lote tocado (para el kardex)
     *
     * Si los lotes no cubren la cantidad (datos heredados desincronizados),
     * crea un lote de reconciliación para que lotes == stock.
     */
    private function descontarLotesFefo(ShelterInventory $inventario, float $cantidad, $supply): array
    {
        $lotes = $inventario->activeBatchesFefo()->lockForUpdate()->get();
        $sumaLotes = (float) $lotes->sum('cantidad_disponible');

        if ($sumaLotes + 0.001 < $cantidad) {
            $faltante = $cantidad - $sumaLotes;
            $reconciliacion = InventoryBatch::create([
                'shelter_inventory_id' => $inventario->id,
                'numero_lote' => 'REC-' . strtoupper(substr(uniqid(), -6)),
                'cantidad_inicial' => $faltante,
                'cantidad_disponible' => $faltante,
                'fecha_ingreso' => now()->toDateString(),
                'fecha_vencimiento' => null,
                'estado' => 'vigente',
            ]);
            $lotes = $lotes->prepend($reconciliacion);
        }

        $consumidos = [];
        $pendiente = $cantidad;
        $ultimoLoteId = null;

        foreach ($lotes as $lote) {
            if ($pendiente <= 0.001) {
                break;
            }

            $tomar = min((float) $lote->cantidad_disponible, $pendiente);
            if ($tomar <= 0) {
                continue;
            }

            $consumidos[] = [
                'numero_lote' => $lote->numero_lote,
                'cantidad' => round($tomar, 2),
                'fecha_vencimiento' => $lote->fecha_vencimiento ? $lote->fecha_vencimiento->toDateString() : null,
            ];

            $nuevaDisp = (float) $lote->cantidad_disponible - $tomar;
            $lote->update([
                'cantidad_disponible' => round($nuevaDisp, 2),
                'estado' => $nuevaDisp <= 0.001 ? 'agotado' : $lote->estado,
            ]);

            $ultimoLoteId = $lote->id;
            $pendiente -= $tomar;
        }

        return [$consumidos, $ultimoLoteId];
    }

    /**
     * Crea (o fusiona) el lote de recepción en el destino conservando el
     * vencimiento original del lote despachado.
     */
    private function crearLoteRecepcion(ShelterInventory $inventario, array $loteDatos): int
    {
        $existente = InventoryBatch::where('shelter_inventory_id', $inventario->id)
            ->where('numero_lote', $loteDatos['numero_lote'])
            ->where('estado', '!=', 'agotado')
            ->lockForUpdate()
            ->first();

        if ($existente) {
            $existente->update([
                'cantidad_disponible' => round((float) $existente->cantidad_disponible + (float) $loteDatos['cantidad'], 2),
                'cantidad_inicial' => round((float) $existente->cantidad_inicial + (float) $loteDatos['cantidad'], 2),
                'fecha_vencimiento' => $existente->fecha_vencimiento
                    ?? ($loteDatos['fecha_vencimiento'] ?? null),
                'estado' => 'vigente',
            ]);
            return $existente->id;
        }

        $vencimiento = $loteDatos['fecha_vencimiento'] ?? null;
        $lote = InventoryBatch::create([
            'shelter_inventory_id' => $inventario->id,
            'numero_lote' => $loteDatos['numero_lote'],
            'cantidad_inicial' => $loteDatos['cantidad'],
            'cantidad_disponible' => $loteDatos['cantidad'],
            'fecha_ingreso' => now()->toDateString(),
            'fecha_vencimiento' => $vencimiento,
            'estado' => ($vencimiento && strtotime($vencimiento) < time()) ? 'vencido' : 'vigente',
        ]);

        return $lote->id;
    }

    /**
     * Devuelve al origen un lote despachado (cancelación en tránsito).
     */
    private function devolverLote(ShelterInventory $inventario, array $loteDatos): void
    {
        $lote = InventoryBatch::where('shelter_inventory_id', $inventario->id)
            ->where('numero_lote', $loteDatos['numero_lote'])
            ->lockForUpdate()
            ->first();

        if ($lote) {
            $lote->update([
                'cantidad_disponible' => round((float) $lote->cantidad_disponible + (float) $loteDatos['cantidad'], 2),
                'estado' => 'vigente',
            ]);
            return;
        }

        InventoryBatch::create([
            'shelter_inventory_id' => $inventario->id,
            'numero_lote' => $loteDatos['numero_lote'],
            'cantidad_inicial' => $loteDatos['cantidad'],
            'cantidad_disponible' => $loteDatos['cantidad'],
            'fecha_ingreso' => now()->toDateString(),
            'fecha_vencimiento' => $loteDatos['fecha_vencimiento'] ?? null,
            'estado' => 'vigente',
        ]);
    }

    /**
     * Vencimiento por defecto para transferencias sin registro de lotes.
     */
    private function vencimientoPorDefecto($supply): ?string
    {
        if (!$supply->es_perecedero) {
            return null;
        }

        $dias = (int) $supply->dias_vida_util_promedio;
        return $dias > 0 ? now()->addDays($dias)->toDateString() : now()->addDays(90)->toDateString();
    }
}
