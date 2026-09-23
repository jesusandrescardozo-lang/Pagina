<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación de creación de insumos.
 *
 * La autorización de escritura vive en los Gates (can:manage-supplies)
 * aplicados en routes/web.php; aquí solo se validan datos.
 */
class StoreSupplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas compartidas entre creación y edición
     * (UpdateSupplyRequest las reutiliza y solo ajusta `codigo`).
     */
    public function rules(): array
    {
        return [
            'codigo' => 'required|string|max:30|unique:supplies,codigo',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'categoria' => 'required|in:alimento,medico',
            'subcategoria' => 'required|string|max:100',
            'unidad_medida' => 'required|string|max:30',
            'clasificacion_abc' => 'required|in:A,B,C',
            'es_perecedero' => 'nullable|boolean',
            'dias_vida_util_promedio' => 'nullable|integer|min:1',
            'costo_unitario' => 'required|numeric|min:0.01',
            'costo_pedido' => 'required|numeric|min:0.01',
            'costo_mantenimiento_anual' => 'required|numeric|min:0.01',
            'tiempo_entrega_dias' => 'required|numeric|min:0.1',
            'consumo_diario_referencia' => 'required|numeric|min:0.001',
            'stock_seguridad_sugerido' => 'required|numeric|min:0',
            'costo_escasez_newsvendor' => 'nullable|numeric|min:0.01',
            'costo_desecho_newsvendor' => 'nullable|numeric|min:0.01',
        ];
    }
}
