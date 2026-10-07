<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | Este es el mailer por defecto para el envío de mensajes. Laravel
    | incluye "smtp", "sendmail", "array" y "log" entre otros.
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'auth_mode' => null,
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => ['smtp', 'log'],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | Todas las notificaciones generadas por la aplicación utilizarán
    | estas direcciones por defecto.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'sistema@refugios.ve'),
        'name' => env('MAIL_FROM_NAME', 'NutriLnk'),
    ],

];
