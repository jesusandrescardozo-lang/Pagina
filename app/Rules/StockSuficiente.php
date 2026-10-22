<?php

namespace App\Rules;

use App\Models\ShelterInventory;
use Illuminate\Contracts\Validation\Rule;

/**
 * Valida que la cantidad solicitada no supere el stock disponible
 * de un insumo en un refugio concreto.
 *
 * Se usa en Kardex (salidas) y en transferencias (origen).
 *
 * Nota sobre concurrencia (TOCTOU): esta regla es una validación "fría"
 * sin bloqueo; el descuento real del stock ocurre después dentro de una
 * transacción con lockForUpdate (ver TransferService e
 * InventoryMovementController), que es quien garantiza la integridad.
 */
class StockSuficiente implements Rule
{
    private float $disponible = 0.0;
    private bool $stockConsultado = false;

    public function __construct(
        private int $shelterId,
        private int $supplyId,
        private string $unidadMedida = '',
    ) {
    }

    public function passes($attribute, $value): bool
    {
        // La consulta se hace de forma perezosa aquí (y no en el constructor)
        // para no golpear la BD cuando la cantidad ni siquiera es numérica.
        $this->consultarStock();

        return is_numeric($value) && (float) $value <= $this->disponible + 0.0001;
    }

    private function consultarStock(): void
    {
        if ($this->stockConsultado) {
            return;
        }

        $inventario = ShelterInventory::where('shelter_id', $this->shelterId)
            ->where('supply_id', $this->supplyId)
            ->first();

        $this->disponible = $inventario ? (float) $inventario->stock_actual : 0.0;
        $this->stockConsultado = true;
    }

    public function message(): string
    {
        $unidad = $this->unidadMedida !== '' ? " {$this->unidadMedida}" : '';
        return "Stock insuficiente en el refugio: hay {$this->disponible}{$unidad} disponibles y la cantidad solicitada los supera.";
    }
}
