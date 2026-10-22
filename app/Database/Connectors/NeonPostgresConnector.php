<?php

namespace App\Database\Connectors;

use Illuminate\Database\Connectors\PostgresConnector;

/**
 * Conector de Postgres con soporte para Neon (SNI).
 *
 * Neon identifica el proyecto de dos formas: por SNI (el hostname del DSN)
 * o por la opción de libpq `endpoint=<endpoint-id>` dentro del DSN.
 * Si se envían las dos y no coinciden, el proxy de Neon rechaza la conexión:
 *     ERROR: Nombre de proyecto inconsistente inferido de SNI (...) y opción de proyecto (...)
 *
 * Por eso esta regla: si el host ya trae el endpoint (cualquier hostname
 * `ep-xxxx...neon.tech`, con o sin `-pooler`), el ID viaja por SNI y la
 * opción NO se añade. Solo se añade cuando el host es una IP u otro host
 * sin endpoint (SNI no puede identificar el proyecto en ese caso).
 *
 * Laravel no ofrece un hook para añadir opciones al DSN, y además pasa
 * `config['options']` como opciones PDO (donde no sirven), por eso este
 * conector reescribe el DSN y limpia la opción sobrante.
 */
class NeonPostgresConnector extends PostgresConnector
{
    /**
     * Reconstruye el DSN del padre (que ya incluye sslmode) y le añade
     * `options='endpoint=...'` solo si el host no ya ya identifica el
     * proyecto vía SNI.
     */
    protected function getDsn(array $config): string
    {
        $dsn = parent::getDsn($config);

        $endpoint = $this->endpointFromConfig($config);
        if ($endpoint !== null
            && ! $this->hostIdentifiesProject($config)
            && ! str_contains($dsn, 'options=')) {
            $dsn .= ";options='endpoint={$endpoint}'";
        }

        return $dsn;
    }

    /**
     * true cuando el host ya lleva el endpoint id en el hostname, así que
     * Neon resuelve el proyecto por SNI y cualquier opción añadida entraría
     * en conflicto con ella.
     */
    private function hostIdentifiesProject(array $config): bool
    {
        $host = strtolower((string) ($config['host'] ?? ''));

        // Primer label del hostname: "ep-snowy-lab-b47a57pb-pooler"
        $label = explode('.', $host, 2)[0] ?? '';

        // Hostname estándar de Neon: ep-<id> (directo) o ep-<id>-pooler
        return str_ends_with($host, '.neon.tech') && str_starts_with($label, 'ep-');
    }

    /**
     * Las opciones de PDO no aceptan `endpoint`: se eliminan para que
     * PDO::__construct no las interprete como una opción inválida.
     */
    public function getOptions(array $config): array
    {
        $options = parent::getOptions($config);

        unset($options['endpoint']);

        return $options;
    }

    /**
     * Extrae el endpoint ID de la configuración (soporta el token `endpoint=`
     * por si viene escrito como opción libpq completa).
     */
    private function endpointFromConfig(array $config): ?string
    {
        $value = $config['options']['endpoint'] ?? $config['options'] ?? null;

        if (is_array($value)) {
            $value = reset($value) ?: null;
        }

        if (!is_string($value) || $value === '') {
            return null;
        }

        return str_contains($value, '=') ? explode('=', $value, 2)[1] : $value;
    }
}