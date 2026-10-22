<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    /**
     * Módulos cuyo cálculo analítico depende del inventario.
     * Cualquier escritura sobre ellos invalida la caché de corta duración.
     */
    private const MODULOS_CON_CACHE = ['kardex', 'transferencias', 'refugios', 'insumos'];

    /**
     * Limpia la caché de los cálculos de I.O. (autonomía, rebalanceo, ABC).
     *
     * Usa invalidación POR VERSIÓN (ver InventoryOptimizationService):
     * solo incrementa un contador y las entradas anteriores quedan huérfanas.
     * Jamás se usa Cache::flush(), que borraría también claves ajenas
     * como el rate-limiter de login o las sesiones.
     */
    public static function limpiarCacheAnalitica(): void
    {
        InventoryOptimizationService::invalidarCacheAnalitica();
    }

    public static function log(
        string $modulo,
        string $accion,
        string $descripcion,
        ?Model $modelo = null,
        array $valoresAntes = [],
        array $valoresDespues = []
    ): AuditLog {
        $registro = AuditLog::create([
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

        // Se invalida DESPUÉS de registrar la auditoría: si el guardado
        // falla, no se tira la caché sin motivo.
        if (in_array($modulo, self::MODULOS_CON_CACHE, true)) {
            self::limpiarCacheAnalitica();
        }

        return $registro;
    }
}
