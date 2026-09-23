<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CpmActivity extends Model
{
    protected $fillable = [
        'project_id',
        'codigo',
        'nombre',
        'descripcion',
        'duracion',
        'predecesores',
        'es',
        'ef',
        'ls',
        'lf',
        'holgura',
        'es_critica',
    ];

    protected $casts = [
        'duracion' => 'integer',
        'predecesores' => 'array',
        'es' => 'decimal:2',
        'ef' => 'decimal:2',
        'ls' => 'decimal:2',
        'lf' => 'decimal:2',
        'holgura' => 'decimal:2',
        'es_critica' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(CpmProject::class, 'project_id');
    }
}
