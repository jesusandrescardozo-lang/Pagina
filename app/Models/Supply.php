<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supply extends Model
{
    use HasFactory;

    protected $table = 'supplies';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'categoria',
        'subcategoria',
        'unidad_medida',
        'clasificacion_abc',
        'es_perecedero',
        'dias_vida_util_promedio',
        'costo_unitario',
        'costo_pedido',
        'costo_mantenimiento_anual',
        'tiempo_entrega_dias',
        'consumo_diario_referencia',
        'stock_seguridad_sugerido',
        'costo_escasez_newsvendor',
        'costo_desecho_newsvendor',
    ];

    protected $casts = [
        'es_perecedero' => 'boolean',
        'dias_vida_util_promedio' => 'integer',
        'costo_unitario' => 'float',
        'costo_pedido' => 'float',
        'costo_mantenimiento_anual' => 'float',
        'tiempo_entrega_dias' => 'float',
        'consumo_diario_referencia' => 'float',
        'stock_seguridad_sugerido' => 'float',
        'costo_escasez_newsvendor' => 'float',
        'costo_desecho_newsvendor' => 'float',
    ];

    public function inventories()
    {
        return $this->hasMany(ShelterInventory::class, 'supply_id');
    }

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class, 'supply_id');
    }

    public function transfers()
    {
        return $this->hasMany(SupplyTransfer::class, 'supply_id');
    }

    public function getTotalStockAttribute(): float
    {
        return (float) $this->inventories()->sum('stock_actual');
    }

    public function getCategoriaBadgeAttribute(): array
    {
        return $this->categoria === 'medico'
            ? ['text' => 'Insumo Médico', 'icon' => 'fa-kit-medical', 'class' => 'bg-err']
            : ['text' => 'Alimento / Nutrición', 'icon' => 'fa-bowl-food', 'class' => 'bg-ok'];
    }

    public function getAbcBadgeAttribute(): array
    {
        return match($this->clasificacion_abc) {
            'A' => ['text' => 'Clase A (Crítico)', 'class' => 'bg-p1', 'desc' => 'Alto valor e impacto vital'],
            'B' => ['text' => 'Clase B (Moderado)', 'class' => 'bg-p2', 'desc' => 'Rotación media y valor estándar'],
            'C' => ['text' => 'Clase C (Bajo)', 'class' => 'bg-p3', 'desc' => 'Bajo costo y alta rotación'],
            default => ['text' => 'Clase ' . $this->clasificacion_abc, 'class' => 'bg-glass', 'desc' => ''],
        };
    }
}
