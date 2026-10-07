<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver
    |--------------------------------------------------------------------------
    |
    | Laravel utiliza BCRYPT de forma predeterminada, considerado el
    | algoritmo recomendado para contraseñas en aplicaciones web.
    |
    */

    'driver' => env('HASH_DRIVER', 'bcrypt'),

    /*
    |--------------------------------------------------------------------------
    | Bcrypt Options
    |--------------------------------------------------------------------------
    |
    | El número de "rounds" define el costo de cómputo. Un valor más alto
    | significa mayor seguridad, pero también mayor tiempo de proceso.
    |
    */

    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 12),
        'verify' => env('BCRYPT_VERIFY', true),
        'limit' => env('BCRYPT_LIMIT', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Argon Options
    |--------------------------------------------------------------------------
    */

    'argon' => [
        'memory' => env('ARGON_MEMORY', 65536),
        'threads' => env('ARGON_THREADS', 1),
        'time' => env('ARGON_TIME', 4),
        'verify' => env('ARGON_VERIFY', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rehash On Login
    |--------------------------------------------------------------------------
    |
    | Si el costo de hash configurado cambió, Laravel volverá a generar el
    | hash del usuario automáticamente durante el próximo inicio de sesión.
    |
    */

    'rehash_on_login' => true,

];
