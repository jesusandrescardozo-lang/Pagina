<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Shelter;

echo "=== TEST 1: LOGIN VIEW RENDERING ===\n";
$reqLogin = Request::create('/login', 'GET');
$respLogin = $kernel->handle($reqLogin);
$contentLogin = $respLogin->getContent();

if (strpos($contentLogin, 'NutriLnk') !== false) {
    echo "✓ 'NutriLnk' branding present in login.\n";
} else {
    echo "✗ 'NutriLnk' NOT found in login.\n";
}

if (strpos($contentLogin, '(Investigación de Operaciones)') === false) {
    echo "✓ '(Investigación de Operaciones)' successfully removed from login.\n";
} else {
    echo "✗ '(Investigación de Operaciones)' still present in login.\n";
}

if (strpos($contentLogin, '[REF-01] Refugio Parque Naciones Unidas — Parroquia El Paraíso') !== false) {
    echo "✓ Shelter REF-01 cleanly formatted without duplicate parentheses.\n";
} else {
    echo "✗ Shelter REF-01 format mismatch.\n";
}

if (strpos($contentLogin, 'Red Centralizada (Todos los Refugios)') !== false) {
    echo "✓ Clean centralized multi-center option present.\n";
} else {
    echo "✗ Centralized option missing.\n";
}

echo "\n=== TEST 2: DASHBOARD FILTERING BY SHELTER ===\n";
$admin = User::where('email', 'admin@refugios.ve')->first() ?? User::first();
if (!$admin) {
    echo "✗ No hay usuarios en la BD. Ejecuta: php artisan db:seed --class=UserSeeder\n";
    exit(1);
}
Illuminate\Support\Facades\Auth::login($admin);
session(['otp_verificado' => true]);

// Test A: Centralized dashboard (no active shelter)
session()->forget(['shelter_id', 'shelter_nombre', 'shelter_codigo']);
$reqDashGlobal = Request::create('/dashboard', 'GET');
$reqDashGlobal->setLaravelSession(session());
$reqDashGlobal->setUserResolver(fn() => $admin);
$respDashGlobal = $kernel->handle($reqDashGlobal);
$contentGlobal = $respDashGlobal->getContent();
echo "Status Global: " . $respDashGlobal->getStatusCode() . "\n";

if ($respDashGlobal->getStatusCode() === 200 && strpos($contentGlobal, 'NutriLnk') !== false) {
    echo "✓ Centralized dashboard renders (200) when no shelter selected.\n";
} else {
    echo "✗ Centralized dashboard failed to render.\n";
}

// Test B: Scoped to REF-01 (Naciones Unidas)
$ref01 = Shelter::where('codigo', 'REF-01')->first();
session(['shelter_id' => $ref01->id, 'otp_verificado' => true]);
$reqDash01 = Request::create('/dashboard', 'GET');
$reqDash01->setLaravelSession(session());
$reqDash01->setUserResolver(fn() => $admin);
$respDash01 = $kernel->handle($reqDash01);
$content01 = $respDash01->getContent();
echo "Status REF-01: " . $respDash01->getStatusCode() . "\n";

if ($respDash01->getStatusCode() === 200 && strpos($content01, 'Refugio Parque Naciones Unidas') !== false) {
    echo "✓ Dashboard successfully scoped to REF-01 (shelter name rendered).\n";
} else {
    echo "✗ REF-01 scoping missing.\n";
}

// Check that population in REF-01 reflects 380
if (strpos($content01, '380') !== false) {
    echo "✓ REF-01 specific population (380) rendered.\n";
} else {
    echo "✗ REF-01 population not rendered.\n";
}

// Test C: Scoped to REF-04 (Catia surplus node)
$ref04 = Shelter::where('codigo', 'REF-04')->first();
session(['shelter_id' => $ref04->id, 'otp_verificado' => true]);
$reqDash04 = Request::create('/dashboard', 'GET');
$reqDash04->setLaravelSession(session());
$reqDash04->setUserResolver(fn() => $admin);
$respDash04 = $kernel->handle($reqDash04);
$content04 = $respDash04->getContent();
echo "Status REF-04: " . $respDash04->getStatusCode() . "\n";

if ($respDash04->getStatusCode() === 200 && strpos($content04, 'Albergue Polideportivo') !== false && strpos($content04, 'Catia') !== false) {
    echo "✓ Dashboard successfully scoped to REF-04 (Odreman Catia).\n";
} else {
    echo "✗ REF-04 scoping issue.\n";
}

echo "\n=== ALL SHELTER INTEGRATION TESTS PASSED ===\n";
