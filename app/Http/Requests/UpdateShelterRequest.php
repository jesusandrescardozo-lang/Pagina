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
        $rules['codigo'] = 'required|string|max:20|unique:shelters,codigo,' . $shelter->id;

        // En edición las poblaciones son obligatorias (en creación son opcionales).
        foreach (['poblacion_adultos', 'poblacion_ninos', 'poblacion_adultos_mayores', 'poblacion_atencion_medica'] as $campo) {
            $rules[$campo] = 'required|integer|min:0';
        }

        return $rules;
    }
}
