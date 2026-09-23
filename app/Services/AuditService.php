<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    public static function log(
        string $modulo,
        string $accion,
        string $descripcion,
        ?Model $modelo = null,
        array $valoresAntes = [],
        array $valoresDespues = []
    ): AuditLog {
        return AuditLog::create([
            'user_id' => auth()->id(),
            'modulo' => $modulo,
            'accion' => $accion,
            'descripcion' => $descripcion,
            'modelo_tipo' => $modelo ? get_class($modelo) : null,
            'modelo_id' => $modelo ? $modelo->getKey() : null,
            'valores_antes' => !empty($valoresAntes) ? $valoresAntes : null,
            'valores_despues' => !empty($valoresDespues) ? $valoresDespues : null,
            'ip_address' => request()->ip(),
            'user_agent' => substr(request()->userAgent() ?? '', 0, 500),
            'created_at' => now(),
        ]);
    }
}
