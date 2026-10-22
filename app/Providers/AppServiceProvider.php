<?php

namespace App\Providers;

use App\Database\Connectors\NeonPostgresConnector;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Conector PDO para Postgres: añade options='endpoint=...' al DSN
        // (Neon exige SNI y el driver libpq antiguo no lo soporta solo).
        // Laravel resuelve el conector vía este binding ("db.connector.pgsql"),
        // por lo que no hace falta sobrescribir la clase Connection.
        $this->app->bind('db.connector.pgsql', fn () => new NeonPostgresConnector);
    }

    public function boot(): void
    {
        // Render termina TLS en el balanceador: en produccion forzamos HTTPS
        // para que redirecciones, formularios y assets salgan siempre seguros.
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        /*
        |--------------------------------------------------------------------------
        | Matriz única de permisos (Capability => Roles autorizados)
        |--------------------------------------------------------------------------
        | Esta es la ÚNICA fuente de verdad de los roles. Las rutas usan
        | `can:<capacidad>` y los métodos User::canX() delegan en estos Gates.
        */
        $permisos = [
            // Escritura de refugios (Admin, Coordinador de Refugio, Auditor)
            'manage-shelters'   => ['admin', 'coordinador_refugio', 'auditor'],
            // Escritura del catálogo de insumos (Admin, Almacenista)
            'manage-supplies'   => ['admin', 'almacenista'],
            // Escritura del Kardex de movimientos (Admin, Almacenista)
            'manage-kardex'     => ['admin', 'almacenista'],
            // Escritura de transferencias (Admin, Almacenista, Coordinador)
            'manage-transfers'  => ['admin', 'almacenista', 'coordinador_refugio'],
            // Consulta de la bitácora de auditoría (Admin, Auditor)
            'view-audit'        => ['admin', 'auditor'],
            // Gestión de carnets/credenciales (Solo Admin)
            'manage-carnets'    => ['admin'],
            // Accesos de solo lectura usados por las vistas (menús/botones)
            'access-supplies'   => ['admin', 'almacenista', 'coordinador_refugio', 'auditor'],
            'access-movements'  => ['admin', 'almacenista', 'auditor'],
            'access-reports'    => ['admin', 'almacenista', 'coordinador_refugio', 'auditor'],
        ];

        foreach ($permisos as $capacidad => $roles) {
            Gate::define($capacidad, fn (User $user) => in_array($user->rol, $roles, true));
        }
    }
}
