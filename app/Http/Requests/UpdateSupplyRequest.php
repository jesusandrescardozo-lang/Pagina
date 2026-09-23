<?php

namespace App\Http\Requests;

/**
 * Validación de edición de insumos.
 * Reutiliza las reglas de StoreSupplyRequest y solo flexibiliza `codigo`
 * para permitir conservar el código propio del registro.
 */
class UpdateSupplyRequest extends StoreSupplyRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $supply = $this->route('supply');
        $rules['codigo'] = 'required|string|max:30|unique:supplies,codigo,' . $supply->id;

        return $rules;
    }
}
