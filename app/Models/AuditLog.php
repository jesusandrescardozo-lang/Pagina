<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'modulo',
        'accion',
        'descripcion',
        'modelo_tipo',
        'modelo_id',
        'valores_antes',
        'valores_despues',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'valores_antes' => 'array',
        'valores_despues' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
