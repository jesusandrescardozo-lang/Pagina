<?php

namespace App\Http\Requests;

use App\Models\Shelter;
use App\Models\Supply;
use App\Rules\StockSuficiente;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación de solicitud de transferencias inter-refugios.
 *
 * Reglas de negocio validadas aquí:
 *  - Origen y destino distintos y no cerrados.
 *  - La cantidad no puede superar el stock disponible en el origen
 *    (regla StockSuficiente).
 */
class StoreSupplyTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $supply = Supply::find($this->input('supply_id'));

        return [
            'refugio_origen_id' => [
                'required',
                'exists:shelters,id',
                'different:refugio_destino_id',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $shelter = Shelter::find($value);
                    if ($shelter && $shelter->estado === 'cerrado') {
                        $fail("El refugio origen '{$shelter->nombre}' está cerrado y no puede despachar transferencias.");
                    }
                },
            ],
            'refugio_destino_id' => [
                'required',
                'exists:shelters,id',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $shelter = Shelter::find($value);
                    if ($shelter && $shelter->estado === 'cerrado') {
                        $fail("El refugio destino '{$shelter->nombre}' está cerrado y no puede recibir transferencias.");
                    }
                },
            ],
            'supply_id' => 'required|exists:supplies,id',
            'cantidad' => [
                'required',
                'numeric',
                'min:0.1',
                'max:999999',
                new StockSuficiente(
                    (int) $this->input('refugio_origen_id'),
                    (int) $this->input('supply_id'),
                    $supply?->unidad_medida ?? ''
                ),
            ],
            'prioridad' => 'required|in:urgente,alta,media,baja',
            'vehiculo_transporte' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'refugio_origen_id.different' => 'El refugio origen y destino deben ser distintos.',
        ];
    }
}
