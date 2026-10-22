<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación de creación de refugios.
 *
 * La autorización de escritura vive en los Gates (can:manage-shelters)
 * aplicados en routes/web.php; aquí solo se validan datos.
 */
class StoreShelterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas compartidas entre creación y edición
     * (UpdateShelterRequest las reutiliza y solo ajusta `codigo`).
     */
    public function rules(): array
    {
        return [
            'codigo' => 'required|string|min:3|max:20|regex:/^[A-Za-z0-9-]+$/|unique:shelters,codigo',
            'nombre' => 'required|string|min:3|max:255',
            'departamento' => 'required|string|max:100',
            'municipio' => 'required|string|max:100',
            'direccion' => 'required|string|max:255',
            'coordenadas' => 'nullable|string|max:100|regex:/^-?\d{1,3}(\.\d+)?\s*,\s*-?\d{1,3}(\.\d+)?$/',
            'capacidad_maxima' => 'required|integer|min:1|max:1000000',
            'poblacion_adultos' => 'nullable|integer|min:0|max:1000000',
            'poblacion_ninos' => 'nullable|integer|min:0|max:1000000',
            'poblacion_adultos_mayores' => 'nullable|integer|min:0|max:1000000',
            'poblacion_atencion_medica' => 'nullable|integer|min:0|max:1000000',
            'responsable_nombre' => 'required|string|max:255',
            'responsable_telefono' => 'required|string|max:50|regex:/^[0-9+\-\s()]{7,20}$/',
            'responsable_email' => 'nullable|email|max:100',
            'estado' => 'required|in:activo,alerta_capacidad,evacuacion,cerrado',
            'observaciones' => 'nullable|string|max:2000',
        ];
    }

    /**
     * Reglas de coherencia entre campos que no se pueden expresar
     * como reglas simples.
     *
     * Criterio del sistema (idéntico al cálculo del controlador):
     *   poblacion_total = adultos + niños + mayores.
     * La atención médica NO se suma: es un subconjunto del total
     * (personas albergadas que además requieren atención prioritaria).
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $albergados = (int) ($this->input('poblacion_adultos') ?? 0)
                    + (int) ($this->input('poblacion_ninos') ?? 0)
                    + (int) ($this->input('poblacion_adultos_mayores') ?? 0);
            $medica = (int) ($this->input('poblacion_atencion_medica') ?? 0);

            $total = $this->input('poblacion_total');
            if ($total !== null && (int) $total !== $albergados) {
                $validator->errors()->add(
                    'poblacion_total',
                    "La población total ({$total}) no coincide con la suma de los grupos etarios ({$albergados})."
                );
            }

            if ($medica > $albergados) {
                $validator->errors()->add(
                    'poblacion_atencion_medica',
                    'La población bajo atención médica no puede superar la población albergada (adultos + niños + mayores).'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'codigo.regex' => 'El código solo puede contener letras, números y guiones (ej. REF-01).',
            'coordenadas.regex' => 'Las coordenadas deben tener el formato "latitud, longitud" (ej. 10.4806, -66.9036).',
            'responsable_telefono.regex' => 'El teléfono solo puede contener números, espacios y los caracteres + ( ) -.',
            'capacidad_maxima.max' => 'La capacidad máxima no puede superar 1.000.000 de personas.',
        ];
    }
}
