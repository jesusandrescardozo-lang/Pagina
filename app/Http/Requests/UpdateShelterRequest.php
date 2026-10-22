<?php

namespace App\Http\Requests;

/**
 * Validación de edición de refugios.
 * Reutiliza las reglas de StoreShelterRequest y solo flexibiliza `codigo`
 * para permitir conservar el código propio del registro.
 */
class UpdateShelterRequest extends StoreShelterRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $shelter = $this->route('shelter');
        // Igual que en creación, pero relajando `unique` para conservar el propio código.
        $rules['codigo'] = 'required|string|min:3|max:20|regex:/^[A-Za-z0-9-]+$/|unique:shelters,codigo,' . $shelter->id;

        // En edición las poblaciones son obligatorias (en creación son opcionales).
        foreach (['poblacion_adultos', 'poblacion_ninos', 'poblacion_adultos_mayores', 'poblacion_atencion_medica'] as $campo) {
            $rules[$campo] = 'required|integer|min:0|max:1000000';
        }

        return $rules;
    }
}
