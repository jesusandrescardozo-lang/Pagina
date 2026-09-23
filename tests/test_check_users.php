<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

foreach (App\Models\User::all(['name', 'email', 'google2fa_secret']) as $u) {
    echo $u->email . ' => ' . $u->google2fa_secret . PHP_EOL;
}
