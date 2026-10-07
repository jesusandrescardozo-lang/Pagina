<?php
// Simple HTTP test - just check rendered HTML pages
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

echo "=== TEST 1: LOGIN PAGE ===\n";
$req = Request::create('/login', 'GET');
$resp = $kernel->handle($req);
$html = $resp->getContent();
echo "Status: " . $resp->getStatusCode() . "\n";

$checks = [
    'NutriLnk' => 'NutriLnk branding',
    'Optimización de Suministros' => 'Subtitle correct',
    'REF-01' => 'REF-01 shelter code present',
    'REF-05' => 'REF-05 shelter code present',
    'Red Centralizada' => 'Central option present',
    '🌐' => 'Globe emoji in central option',
    'Parroquia El Paraíso' => 'Clean parroquia name',
    'Parroquia Coche' => 'Parroquia Coche shown',
    'Explorar como Invitado' => 'Guest mode entry button present',
];
$failures = 0;
foreach ($checks as $needle => $label) {
    if (str_contains($html, $needle)) {
        echo "  ✓ $label\n";
    } else {
        echo "  ✗ $label (missing: $needle)\n";
        $failures++;
    }
}

// Check for bad patterns
$bad = [
    '(La Candelaria) (Libertador' => 'Duplicate parens',
    'Investigación de Operaciones)' => 'Old subtitle in login',
];
echo "  --- No bad patterns ---\n";
foreach ($bad as $needle => $label) {
    if (!str_contains($html, $needle)) {
        echo "  ✓ No: $label\n";
    } else {
        echo "  ✗ Found bad pattern: $label ($needle)\n";
        $failures++;
    }
}

echo "\n=== TEST 2: DASHBOARD (authenticated) ===\n";
// Bootstrap the app session
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$user = \App\Models\User::find(1); // admin

// Create a fake session and log in
$sessionManager = $app->make('session');
$session = $sessionManager->driver();
$session->setId('test-session-id-12345');
$session->start();

// Guard
$authGuard = $app->make('auth')->guard('web');
$authGuard->setUser($user);

$session->put('_token', csrf_token());
$session->put('otp_verificado', true);
$session->put('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d', $user->getAuthIdentifier());

$req2 = Request::create('/dashboard', 'GET');
$req2->setLaravelSession($session);
$req2->cookies->set('XSRF-TOKEN', 'fake');

$resp2 = $kernel->handle($req2);
$html2 = $resp2->getContent();
$status2 = $resp2->getStatusCode();
echo "Status: $status2\n";

if ($status2 !== 200) {
    $loc = $resp2->headers->get('Location');
    echo "  ✗ Se esperaba 200, se obtuvo {$status2}" . ($loc ? " -> {$loc}" : '') . "\n";
    $failures++;
} else {
    $checks2 = [
        'NutriLnk' => 'NutriLnk branding',
        'topbarShelterForm' => 'Topbar shelter switcher form present',
        'REF-01' => 'Shelter REF-01 listed on dashboard',
        'REF-02' => 'Multiple shelters listed on dashboard',
    ];
    foreach ($checks2 as $needle => $label) {
        if (str_contains($html2, $needle)) {
            echo "  ✓ $label\n";
        } else {
            echo "  ✗ $label (missing: $needle)\n";
            $failures++;
        }
    }
}

echo "\n=== TEST 3: CREDENCIALES DEMO DEL FORMULARIO DE LOGIN ===\n";
// Regresión: los botones "Rápido" del login deben rellenar las contraseñas
// REALES del seeder. Si no coinciden, el usuario pulsa el botón y el
// sistema rechaza el acceso.
$demoClaves = [
    'admin@refugios.ve'      => 'Admin1234!',
    'coordinador@refugios.ve' => 'Coordinador1234!',
    'almacen@refugios.ve'    => 'Almacen1234!',
    'auditor@refugios.ve'    => 'Auditor1234!',
];
foreach ($demoClaves as $demoEmail => $demoPassword) {
    $fragmento = "fillCredentials('{$demoEmail}', '{$demoPassword}')";
    if (str_contains($html, $fragmento)) {
        echo "  ✓ {$demoEmail} usa su contraseña real\n";
    } else {
        echo "  ✗ {$demoEmail} NO rellena su contraseña real\n";
        $failures++;
    }
    if (Hash::check($demoPassword, \App\Models\User::where('email', $demoEmail)->first()->password ?? '')) {
        echo "  ✓ {$demoPassword} es válida en el seeder\n";
    } else {
        echo "  ✗ {$demoPassword} no valida contra el seeder\n";
        $failures++;
    }
}

// El campo contraseña no debe exponer un valor por defecto en el HTML.
if (str_contains($html, 'name="password"') && preg_match('/name="password"[^>]*\svalue="/', $html)) {
    echo "  ✗ El campo contraseña expone un valor precargado en el HTML\n";
    $failures++;
} else {
    echo "  ✓ El campo contraseña no expone valor precargado\n";
}

echo "\n=== TESTS COMPLETE: " . ($failures === 0 ? 'OK' : "{$failures} FALLO(S)") . " ===\n";
exit($failures === 0 ? 0 : 1);
