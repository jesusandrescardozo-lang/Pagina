<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class InventoryBatch extends Model
{
    use HasFactory;

    protected $table = 'inventory_batches';

    protected $fillable = [
        'shelter_inventory_id',
        'numero_lote',
        'cantidad_inicial',
        'cantidad_disponible',
        'fecha_ingreso',
        'fecha_vencimiento',
        'estado',
    ];

    protected $casts = [
        'cantidad_inicial' => 'float',
        'cantidad_disponible' => 'float',
        'fecha_ingreso' => 'date',
        'fecha_vencimiento' => 'date',
    ];

    public function shelterInventory()
    {
        return $this->belongsTo(ShelterInventory::class, 'shelter_inventory_id');
    }

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class, 'inventory_batch_id');
    }

    public function getDiasParaVencerAttribute(): ?int
    {
        if (!$this->fecha_vencimiento) return null;
        return (int) Carbon::now()->diffInDays($this->fecha_vencimiento, false);
    }

    public function getEstadoBadgeAttribute(): array
    {
        if ($this->cantidad_disponible <= 0) {
            return ['text' => 'Agotado', 'class' => 'bg-glass'];
        }

        if ($this->fecha_vencimiento) {
            $dias = $this->dias_para_vencer;
            if ($dias < 0) {
                return ['text' => 'VENCIDO (' . abs($dias) . 'd atrás)', 'class' => 'bg-err'];
            } elseif ($dias <= 30) {
                return ['text' => 'Por vencer (' . $dias . 'd)', 'class' => 'bg-warn'];
            }
        }

        return ['text' => 'Vigente', 'class' => 'bg-ok'];
    }
}
