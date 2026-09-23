<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShelterInventory extends Model
{
    use HasFactory;

    protected $table = 'shelter_inventories';

    protected $fillable = [
        'shelter_id',
        'supply_id',
        'stock_actual',
        'stock_minimo',
        'stock_maximo',
        'punto_reorden',
    ];

    protected $casts = [
        'stock_actual' => 'float',
        'stock_minimo' => 'float',
        'stock_maximo' => 'float',
        'punto_reorden' => 'float',
    ];

    public function shelter()
    {
        return $this->belongsTo(Shelter::class, 'shelter_id');
    }

    public function supply()
    {
        return $this->belongsTo(Supply::class, 'supply_id');
    }

    public function batches()
    {
        return $this->hasMany(InventoryBatch::class, 'shelter_inventory_id');
    }

    public function activeBatchesFefo()
    {
        return $this->hasMany(InventoryBatch::class, 'shelter_inventory_id')
            ->where('cantidad_disponible', '>', 0)
            ->orderByRaw('CASE WHEN fecha_vencimiento IS NULL THEN 1 ELSE 0 END, fecha_vencimiento ASC');
    }

    public function getConsumoDiarioEstimadoAttribute(): float
    {
        $poblacion = $this->shelter ? $this->shelter->poblacion_total : 0;
        $tasa = $this->supply ? $this->supply->consumo_diario_referencia : 1.0;
        return round($poblacion * $tasa, 2);
    }

    public function getDiasAutonomiaAttribute(): float
    {
        $consumoDiario = $this->consumo_diario_estimado;
        if ($consumoDiario <= 0) {
            return 999.0;
        }
        return round($this->stock_actual / $consumoDiario, 1);
    }

    public function getNivelAlertaAttribute(): array
    {
        $dias = $this->dias_autonomia;
        if ($dias < 2.0) {
            return [
                'estado' => 'CRÍTICO',
                'badge' => 'bg-err',
                'color' => '#f43f5e',
                'mensaje' => 'Riesgo inminente de desabastecimiento (< 2 días)'
            ];
        } elseif ($dias <= 5.0) {
            return [
                'estado' => 'PRECAUCIÓN',
                'badge' => 'bg-warn',
                'color' => '#f59e0b',
                'mensaje' => 'Reabastecimiento requerido en breve (2 a 5 días)'
            ];
        } else {
            return [
                'estado' => 'ÓPTIMO',
                'badge' => 'bg-ok',
                'color' => '#10b981',
                'mensaje' => 'Stock suficiente (> 5 días de cobertura)'
            ];
        }
    }
}
