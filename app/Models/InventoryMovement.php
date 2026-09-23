<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $table = 'inventory_movements';

    protected $fillable = [
        'shelter_id',
        'supply_id',
        'inventory_batch_id',
        'user_id',
        'tipo',
        'cantidad',
        'stock_anterior',
        'stock_nuevo',
        'documento_referencia',
        'motivo',
        'fecha_movimiento',
    ];

    protected $casts = [
        'cantidad' => 'float',
        'stock_anterior' => 'float',
        'stock_nuevo' => 'float',
        'fecha_movimiento' => 'datetime',
    ];

    public function shelter()
    {
        return $this->belongsTo(Shelter::class, 'shelter_id');
    }

    public function supply()
    {
        return $this->belongsTo(Supply::class, 'supply_id');
    }

    public function batch()
    {
        return $this->belongsTo(InventoryBatch::class, 'inventory_batch_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getEsEntradaAttribute(): bool
    {
        return str_starts_with($this->tipo, 'entrada');
    }

    public function getTipoBadgeAttribute(): array
    {
        return match($this->tipo) {
            'entrada_donacion' => ['text' => 'Donación Recibida', 'class' => 'bg-ok', 'icon' => 'fa-hand-holding-heart'],
            'entrada_compra' => ['text' => 'Compra / Adquisición', 'class' => 'bg-info2', 'icon' => 'fa-cart-shopping'],
            'entrada_transferencia' => ['text' => 'Transferencia Entrante', 'class' => 'bg-purple', 'icon' => 'fa-arrow-down-left-and-up-right-to-center'],
            'salida_consumo' => ['text' => 'Consumo Población', 'class' => 'bg-p2', 'icon' => 'fa-utensils'],
            'salida_transferencia' => ['text' => 'Transferencia Saliente', 'class' => 'bg-purple', 'icon' => 'fa-truck-arrow-right'],
            'salida_merma_caducidad' => ['text' => 'Merma / Caducidad', 'class' => 'bg-err', 'icon' => 'fa-trash-can'],
            'ajuste_inventario' => ['text' => 'Ajuste de Conteo', 'class' => 'bg-glass', 'icon' => 'fa-sliders'],
            default => ['text' => ucfirst($this->tipo), 'class' => 'bg-glass', 'icon' => 'fa-exchange-alt'],
        };
    }
}
