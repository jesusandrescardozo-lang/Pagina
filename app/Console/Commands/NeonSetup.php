<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

/**
 * Verifica la conexión a Neon y deja la base de datos lista.
 *
 * Uso: php artisan neon:setup [--seed]
 */
class NeonSetup extends Command
{
    protected $signature = 'neon:setup {--seed : Ejecuta el seeder después de migrar}';

    protected $description = 'Verifica la conexión a Neon (Postgres) y ejecuta las migraciones';

    public function handle(): int
    {
        $this->info('Verificando conexión a Neon (Postgres)...');

        try {
            DB::connection()->getPdo();
            $driver = DB::connection()->getDriverName();
            $version = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION);
            $this->info("  ✓ Conexión exitosa ({$driver} {$version})");
        } catch (\Throwable $e) {
            $this->error('  ✗ No se pudo conectar a Neon:');
            $this->error('    ' . $e->getMessage());
            $this->newLine();
            $this->warn('Revisa que DATABASE_URL en .env tenga la cadena correcta de Neon:');
            $this->warn('  postgresql://usuario:contraseña@ep-xxxx.region.aws.neon.tech/hospital_triage?sslmode=require');
            return self::FAILURE;
        }

        $this->info('Ejecutando migraciones...');
        Artisan::call('migrate', ['--force' => true]);
        $this->line(Artisan::output());

        if ($this->option('seed')) {
            $this->info('Ejecutando seeder con datos demo...');
            Artisan::call('db:seed', ['--force' => true]);
            $this->line(Artisan::output());
        }

        $this->info('✓ Base de datos lista en Neon.');
        return self::SUCCESS;
    }
}
