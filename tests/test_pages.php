<?php
// Simple HTTP test - just check rendered HTML pages
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Http\Request;

echo "=== TEST 1: LOGIN PAGE ===\n";
$req = Request::create('/login', 'GET');
$resp = $kernel->handle($req);
$html = $resp->getContent();
echo "Status: " . $resp->getStatusCode() . "\n";

$checks = [
    'NutriLnk' => 'NutriLnk branding',
    'Gestión y Optimización de Insumos' => 'Subtitle correct',
    'REF-01' => 'REF-01 shelter code present',
    'REF-05' => 'REF-05 shelter code present',
    'Red Centralizada' => 'Central option present',
    '🌐' => 'Globe emoji in central option',
    'Parroquia El Paraíso' => 'Clean parroquia name',
    'Parroquia Coche' => 'Parroquia Coche shown',
    'circle-info' => 'Helper text with icon present',
];
foreach ($checks as $needle => $label) {
    if (str_contains($html, $needle)) {
        echo "  ✓ $label\n";
    } else {
        echo "  ✗ $label (missing: $needle)\n";
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

if ($status2 === 302) {
    $loc = $resp2->headers->get('Location');
    echo "  → Redirected to: $loc\n";
} elseif ($status2 === 200) {
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
        }
    }
}

echo "\n=== TESTS COMPLETE ===\n";
