<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplyTransfer extends Model
{
    use HasFactory;

    protected $table = 'supply_transfers';

    protected $fillable = [
        'codigo_transferencia',
        'refugio_origen_id',
        'refugio_destino_id',
        'supply_id',
        'cantidad',
        'prioridad',
        'estado',
        'solicitado_por',
        'aprobado_por',
        'vehiculo_transporte',
        'observaciones',
        'lotes_despacho',
        'fecha_salida',
        'fecha_llegada',
    ];

    protected $casts = [
        'cantidad' => 'float',
        'lotes_despacho' => 'array',
        'fecha_salida' => 'datetime',
        'fecha_llegada' => 'datetime',
    ];

    public function refugioOrigen()
    {
        return $this->belongsTo(Shelter::class, 'refugio_origen_id');
    }

    public function refugioDestino()
    {
        return $this->belongsTo(Shelter::class, 'refugio_destino_id');
    }

    public function supply()
    {
        return $this->belongsTo(Supply::class, 'supply_id');
    }

    public function solicitadoPor()
    {
        return $this->belongsTo(User::class, 'solicitado_por');
    }

    public function aprobadoPor()
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    public function getFechaSolicitudAttribute()
    {
        return $this->created_at;
    }

    public function getPrioridadBadgeAttribute(): array
    {
        return match($this->prioridad) {
            'urgente' => ['text' => 'URGENTE', 'class' => 'bg-err'],
            'alta' => ['text' => 'Alta', 'class' => 'bg-warn'],
            'media' => ['text' => 'Media', 'class' => 'bg-info2'],
            'baja' => ['text' => 'Baja', 'class' => 'bg-glass'],
            default => ['text' => ucfirst($this->prioridad), 'class' => 'bg-glass'],
        };
    }

    public function getEstadoBadgeAttribute(): array
    {
        return match($this->estado) {
            'solicitada' => ['text' => 'Solicitada', 'class' => 'bg-warn'],
            'aprobada' => ['text' => 'Aprobada en Almacén', 'class' => 'bg-info2'],
            'en_transito' => ['text' => 'En Tránsito (Convoy)', 'class' => 'bg-purple'],
            'completada' => ['text' => 'Entregada y Recibida', 'class' => 'bg-ok'],
            'cancelada' => ['text' => 'Cancelada', 'class' => 'bg-err'],
            default => ['text' => ucfirst($this->estado), 'class' => 'bg-glass'],
        };
    }
}
