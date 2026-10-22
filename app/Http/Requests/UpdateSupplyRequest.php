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
        // Igual que en creación, pero relajando `unique` para conservar el propio código.
        $rules['codigo'] = 'required|string|min:3|max:30|regex:/^[A-Za-z0-9-]+$/|unique:supplies,codigo,' . $supply->id;

        return $rules;
    }
}
