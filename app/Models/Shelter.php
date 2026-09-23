<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shelter extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'shelters';

    protected $fillable = [
        'codigo',
        'nombre',
        'departamento',
        'municipio',
        'direccion',
        'coordenadas',
        'capacidad_maxima',
        'poblacion_total',
        'poblacion_adultos',
        'poblacion_ninos',
        'poblacion_adultos_mayores',
        'poblacion_atencion_medica',
        'responsable_nombre',
        'responsable_telefono',
        'responsable_email',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'capacidad_maxima' => 'integer',
        'poblacion_total' => 'integer',
        'poblacion_adultos' => 'integer',
        'poblacion_ninos' => 'integer',
        'poblacion_adultos_mayores' => 'integer',
        'poblacion_atencion_medica' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public function inventories()
    {
        return $this->hasMany(ShelterInventory::class, 'shelter_id');
    }

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class, 'shelter_id');
    }

    public function outgoingTransfers()
    {
        return $this->hasMany(SupplyTransfer::class, 'refugio_origen_id');
    }

    public function incomingTransfers()
    {
        return $this->hasMany(SupplyTransfer::class, 'refugio_destino_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'shelter_id');
    }

    public function getOcupacionPorcentajeAttribute(): float
    {
        if ($this->capacidad_maxima <= 0) return 0.0;
        return round(($this->poblacion_total / $this->capacidad_maxima) * 100, 1);
    }

    public function getEstadoBadgeAttribute(): array
    {
        return match($this->estado) {
            'activo' => ['text' => 'Activo Operativo', 'class' => 'bg-ok'],
            'alerta_capacidad' => ['text' => 'Alerta de Capacidad', 'class' => 'bg-warn'],
            'evacuacion' => ['text' => 'En Evacuación', 'class' => 'bg-err'],
            'cerrado' => ['text' => 'Cerrado / Inactivo', 'class' => 'bg-glass'],
            default => ['text' => ucfirst($this->estado), 'class' => 'bg-glass'],
        };
    }
}
