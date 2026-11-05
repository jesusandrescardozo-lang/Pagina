<?php

namespace App\Http\Controllers;

use App\Services\InventoryOptimizationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Duración de la caché de datos de páginas de solo lectura (segundos).
     * Es un respaldo: la invalidación real es por versión (ver abajo).
     */
    public const PAGINA_CACHE_TTL = 60;

    /**
     * Caché de corta duración para el DATOS que alimenta una vista.
     *
     * Con Neon (Postgres serverless en us-east-2) cada consulta cuesta
     * ~200 ms de ida y vuelta: una página de listado con 5-6 consultas
     * tardaba 1,5-2 s. Aquí se cachea el resultado del resolver durante
     * PAGINA_CACHE_TTL segundos.
     *
     * La clave incluye:
     *  - la URL completa (filtros, búsqueda, paginación);
     *  - el refugio activo en sesión (filtra el kardex);
     *  - el usuario (ninguna vista mezcla datos entre usuarios);
     *  - la versión de caché analítica, que InventoryOptimizationService
     *    incrementa en CADA escritura auditada. Al escribir, todas las
     *    páginas cacheadas quedan huérfanas y se recalculan al instante,
     *    así que nunca se sirven datos viejos tras un movimiento.
     *
     * Solo se cachean respuestas GET de lectura: nunca se cachean
     * POST/redirect ni HTML con tokens CSRF (esta caché guarda datos, no
     * el HTML renderizado).
     */
    protected function cacheaPagina(Request $request, callable $resolver, int $ttl = self::PAGINA_CACHE_TTL)
    {
        $clave = sprintf(
            'pagina:%s:v%d',
            sha1(implode('|', [
                $request->method(),
                $request->fullUrl(),
                (string) session('shelter_id'),
                (string) (auth()->id() ?? 'invitado'),
            ])),
            InventoryOptimizationService::cacheVersion()
        );

        return cache()->remember($clave, $ttl, $resolver);
    }
}
