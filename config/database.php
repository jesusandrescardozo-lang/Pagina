<?php

return [
    'default' => env('DB_CONNECTION', 'sqlite'),

    // Instrumentación de consultas (diagnóstico de lentitud).
    // Se lee vía config() porque env() NO funciona con `artisan config:cache`.
    'query_log' => env('DB_QUERY_LOG', false),

    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => database_path('database.sqlite'),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],
        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'hospital_triage'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'hospital_triage'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', ''),
            // charset y search_path en NULL = NO se ejecutan los
            // `set names 'utf8'` / `set search_path to ...` que Laravel
            // manda al conectar. Son 2 consultas de ida y vuelta por
            // PETICIÓN (~450 ms con Neon) y sus valores por defecto ya
            // coinciden: Postgres arranca con client_encoding=UTF8 y
            // search_path=public. Si algún día cambias de base, define
            // DB_CHARSET / DB_SEARCH_PATH en .env para volver a fijarlos.
            'charset' => env('DB_CHARSET') ?: null,
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => env('DB_SEARCH_PATH') ?: null,
            'sslmode' => env('DB_SSLMODE', 'prefer'),
            // Conexión persistente de PDO: reutiliza el socket TLS ya
            // negociado entre peticiones y ahorra el handshake completo
            // (~500 ms con Neon) en CADA request. Funciona con
            // `artisan serve`, php-fpm y Apache (se reutiliza por proceso).
            // Pon DB_PERSISTENT=false si algún conductor lo rechaza.
            //
            // Neon identifica el proyecto por SNI cuando DB_HOST es un
            // hostname ep-xxxx...neon.tech; en ese caso la opción "endpoint"
            // NO se envía (enviarla si difiere del host provoca el error
            // "Nombre de proyecto inconsistente inferido de SNI ... y opción
            // de proyecto ..."). Solo se usa si te conectas por IP u otro host.
            'options' => array_filter([
                'endpoint' => env('DB_ENDPOINT_ID'),
                PDO::ATTR_PERSISTENT => env('DB_PERSISTENT', false) ? true : null,
            ]),
        ],
    ],
    'migrations' => 'migrations',
];
