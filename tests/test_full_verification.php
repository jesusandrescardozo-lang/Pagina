<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Http\Request;

// ===  TEST: Login page HTML content ===
echo "=== LOGIN PAGE SHELTER DROPDOWN ===\n";
$req = Request::create('/login', 'GET');
$resp = $kernel->handle($req);
$html = $resp->getContent();

// Extract the shelter select block
preg_match('/<select[^>]*shelter_id[^>]*>(.*?)<\/select>/s', $html, $matches);
if ($matches) {
    // Pretty-print option text content
    preg_match_all('/<option[^>]*>([^<]+)<\/option>/s', $matches[1], $opts);
    echo "Shelter dropdown options:\n";
    foreach ($opts[1] as $opt) {
        $opt = trim(html_entity_decode($opt));
        echo "  → \"$opt\"\n";
    }
} else {
    echo "✗ Could not find shelter select\n";
}

// === TEST: Dashboard with specific shelter ===
echo "\n=== DASHBOARD CONTENT TEST ===\n";

$user = \App\Models\User::find(1);
$sessionManager = $app->make('session');
$session = $sessionManager->driver();
$session->setId('testdash-' . time());
$session->start();
$session->put('otp_verificado', true);
$session->put('_token', 'test_token');
$session->put('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d', 1);

// Test 1: Global view
$session->forget(['shelter_id', 'shelter_nombre', 'shelter_codigo']);
$req1 = Request::create('/dashboard', 'GET');
$req1->setLaravelSession($session);
$resp1 = $kernel->handle($req1);
$html1 = $resp1->getContent();
echo "Global dashboard status: " . $resp1->getStatusCode() . "\n";

if (str_contains($html1, 'VISIÓN METROPOLITANA CENTRALIZADA')) {
    echo "  ✓ Shows Centralized Vision banner\n";
}
if (str_contains($html1, 'topbarShelterForm')) {
    echo "  ✓ Topbar shelter switcher present\n";
}
if (str_contains($html1, 'shelter.switch')) {
    echo "  ✓ shelter.switch route linked\n";
}
// Extract total population shown
preg_match('/Población Albergada.*?stat-value.*?>([^<]+)</s', $html1, $pm);
if ($pm) {
    echo "  Población mostrada: " . trim($pm[1]) . "\n";
}

// Test 2: REF-01 scoped
$session->put('shelter_id', 1);
$session->put('shelter_nombre', 'Refugio Parque Naciones Unidas');
$session->put('shelter_codigo', 'REF-01');
$req2 = Request::create('/dashboard', 'GET');
$req2->setLaravelSession($session);
$resp2 = $kernel->handle($req2);
$html2 = $resp2->getContent();
echo "\nREF-01 dashboard status: " . $resp2->getStatusCode() . "\n";

if (str_contains($html2, 'MODO OPERATIVO LOCAL')) {
    echo "  ✓ Shows LOCAL OPERATIONAL MODE banner\n";
}
if (str_contains($html2, 'Refugio Parque Naciones Unidas')) {
    echo "  ✓ REF-01 shelter name displayed\n";
}
preg_match('/Población Albergada.*?stat-value.*?>([^<]+)</s', $html2, $pm2);
if ($pm2) {
    echo "  Población REF-01: " . trim($pm2[1]) . "\n";
}

// Test 3: REF-04 (Catia - surplus)
$session->put('shelter_id', 4);
$session->put('shelter_nombre', 'Albergue Polideportivo "Gato" Odreman');
$session->put('shelter_codigo', 'REF-04');
$req3 = Request::create('/dashboard', 'GET');
$req3->setLaravelSession($session);
$resp3 = $kernel->handle($req3);
$html3 = $resp3->getContent();
echo "\nREF-04 dashboard status: " . $resp3->getStatusCode() . "\n";

if (str_contains($html3, 'MODO OPERATIVO LOCAL')) {
    echo "  ✓ Shows LOCAL OPERATIONAL MODE banner for REF-04\n";
}
if (str_contains($html3, 'Odreman') || str_contains($html3, 'Catia')) {
    echo "  ✓ REF-04 (Catia/Odreman) shelter name displayed\n";
}
preg_match('/Población Albergada.*?stat-value.*?>([^<]+)</s', $html3, $pm3);
if ($pm3) {
    echo "  Población REF-04: " . trim($pm3[1]) . "\n";
}

echo "\n=== ALL TESTS DONE ===\n";
