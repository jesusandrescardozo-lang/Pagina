<?php
$lock = json_decode(file_get_contents(__DIR__ . '/../composer.lock'), true);
$exts = [];
foreach ($lock['packages'] as $p) {
    foreach ($p['require'] ?? [] as $k => $v) {
        if (strpos($k, 'ext-') === 0) {
            $exts[$k] = array_merge($exts[$k] ?? [], [$p['name']]);
        }
    }
}
ksort($exts);
echo "Extensiones obligatorias (paquete que la pide):\n";
foreach ($exts as $ext => $pkgs) {
    echo "  - {$ext}  <- " . implode(', ', $pkgs) . "\n";
}
