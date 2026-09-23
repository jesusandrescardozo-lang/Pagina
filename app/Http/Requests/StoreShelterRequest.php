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
            'codigo' => 'required|string|max:20|unique:shelters,codigo',
            'nombre' => 'required|string|max:255',
            'departamento' => 'required|string|max:100',
            'municipio' => 'required|string|max:100',
            'direccion' => 'required|string|max:255',
            'coordenadas' => 'nullable|string|max:100',
            'capacidad_maxima' => 'required|integer|min:1',
            'poblacion_adultos' => 'nullable|integer|min:0',
            'poblacion_ninos' => 'nullable|integer|min:0',
            'poblacion_adultos_mayores' => 'nullable|integer|min:0',
            'poblacion_atencion_medica' => 'nullable|integer|min:0',
            'responsable_nombre' => 'required|string|max:255',
            'responsable_telefono' => 'required|string|max:50',
            'responsable_email' => 'nullable|email|max:100',
            'estado' => 'required|in:activo,alerta_capacidad,evacuacion,cerrado',
            'observaciones' => 'nullable|string',
        ];
    }
}
