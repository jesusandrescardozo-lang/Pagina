<?php

namespace App\Http\Requests;

use App\Models\Shelter;
use App\Models\Supply;
use App\Rules\StockSuficiente;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación de registro de movimientos de Kardex.
 *
 * Reglas de negocio validadas aquí:
 *  - El refugio no puede estar cerrado.
 *  - Las salidas no pueden superar el stock disponible (regla StockSuficiente).
 *  - Los ajustes fijan el nuevo nivel de stock (cantidad >= 0).
 *  - Entradas de perecederos exigen lote y vencimiento futuro.
 *  - Compras y transferencias entrantes exigen documento de referencia.
 *  - Mermas y ajustos exigen motivo.
 */
class StoreInventoryMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tipo = (string) $this->input('tipo');
        $esEntrada = str_starts_with($tipo, 'entrada');
        $esAjuste = $tipo === 'ajuste_inventario';

        $supply = Supply::find($this->input('supply_id'));
        $esPerecedero = (bool) ($supply?->es_perecedero ?? false);

        $rules = [
            'shelter_id' => [
                'required',
                'exists:shelters,id',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $shelter = Shelter::find($value);
                    if ($shelter && $shelter->estado === 'cerrado') {
                        $fail("El refugio '{$shelter->nombre}' está cerrado y no puede recibir movimientos de inventario.");
                    }
                },
            ],
            'supply_id' => 'required|exists:supplies,id',
            'tipo' => 'required|in:entrada_donacion,entrada_compra,entrada_transferencia,salida_consumo,salida_transferencia,salida_merma_caducidad,ajuste_inventario',
            'cantidad' => ['required', 'numeric', 'max:999999'],
            'documento_referencia' => 'nullable|string|max:100',
            'motivo' => 'nullable|string|max:1000',
            'numero_lote' => 'nullable|string|max:60',
            'fecha_vencimiento' => 'nullable|date',
        ];

        // Cantidad mínima según el tipo de movimiento.
        $rules['cantidad'][] = $esAjuste ? 'min:0' : 'min:0.01';

        // Las salidas no pueden superar el stock disponible del refugio.
        if (str_starts_with($tipo, 'salida')) {
            $rules['cantidad'][] = new StockSuficiente(
                (int) $this->input('shelter_id'),
                (int) $this->input('supply_id'),
                $supply?->unidad_medida ?? ''
            );
        }

        // Entradas de perecederos: lote y vencimiento obligatorio y futuro.
        if ($esEntrada && $esPerecedero) {
            $rules['numero_lote'] = 'required|string|max:60';
            $rules['fecha_vencimiento'] = 'required|date|after:today';
        }

        // Compras y transferencias entrantes exigen documento de referencia.
        if (in_array($tipo, ['entrada_compra', 'entrada_transferencia'], true)) {
            $rules['documento_referencia'] = 'required|string|max:100';
        }

        // Mermas y ajustos siempre exigen motivo.
        if (in_array($tipo, ['salida_merma_caducidad', 'ajuste_inventario'], true)) {
            $rules['motivo'] = 'required|string|max:1000';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'fecha_vencimiento.required' => 'Los insumos perecederos deben indicar la fecha de vencimiento del lote.',
            'fecha_vencimiento.after' => 'La fecha de vencimiento del lote debe ser posterior a hoy.',
            'numero_lote.required' => 'Los insumos perecederos deben indicar el número de lote.',
            'documento_referencia.required' => 'Las compras y transferencias entrantes deben indicar el documento de referencia (boleta, remisión, etc.).',
            'motivo.required' => 'Las mermas y los ajustes de inventario deben indicar un motivo.',
        ];
    }
}
