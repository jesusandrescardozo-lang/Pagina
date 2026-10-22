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
            'codigo' => 'required|string|min:3|max:30|regex:/^[A-Za-z0-9-]+$/|unique:supplies,codigo',
            'nombre' => 'required|string|min:3|max:255',
            'descripcion' => 'nullable|string|max:2000',
            'categoria' => 'required|in:alimento,medico',
            'subcategoria' => 'required|string|max:100',
            'unidad_medida' => 'required|string|max:30',
            'clasificacion_abc' => 'required|in:A,B,C',
            'es_perecedero' => 'nullable|boolean',
            'dias_vida_util_promedio' => 'nullable|required_if:es_perecedero,1|integer|min:1|max:3650',
            'costo_unitario' => 'required|numeric|min:0.01|max:99999999',
            'costo_pedido' => 'required|numeric|min:0.01|max:99999999',
            'costo_mantenimiento_anual' => 'required|numeric|min:0.01|max:99999999',
            'tiempo_entrega_dias' => 'required|numeric|min:0.1|max:365',
            'consumo_diario_referencia' => 'required|numeric|min:0.001|max:1000',
            'stock_seguridad_sugerido' => 'required|numeric|min:0|max:99999999',
            // Los costos del modelo Newsvendor van en pareja: ambos o ninguno.
            'costo_escasez_newsvendor' => [
                'nullable',
                'numeric',
                'min:0.01',
                'max:99999999',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (filled($value) && blank($this->input('costo_desecho_newsvendor'))) {
                        $fail('Si define el costo de escasez (Cu) también debe definir el costo de desecho (Co).');
                    }
                },
            ],
            'costo_desecho_newsvendor' => [
                'nullable',
                'numeric',
                'min:0.01',
                'max:99999999',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (filled($value) && blank($this->input('costo_escasez_newsvendor'))) {
                        $fail('Si define el costo de desecho (Co) también debe definir el costo de escasez (Cu).');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.regex' => 'El código solo puede contener letras, números y guiones (ej. MED-001, ALI-001).',
            'dias_vida_util_promedio.required_if' => 'Los insumos perecederos deben indicar sus días de vida útil promedio.',
            'tiempo_entrega_dias.max' => 'El tiempo de entrega no puede superar 365 días.',
        ];
    }
}
