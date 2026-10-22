<?php
// Prueba rápida: renderiza la vista de paginación personalizada.
require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Pagination\LengthAwarePaginator;

function renderPagina(int $pagina): string
{
    $p = new LengthAwarePaginator(
        array_slice(range(1, 200), ($pagina - 1) * 20, 20),
        36,
        20,
        $pagina,
        ['path' => 'http://localhost/audit']
    );

    return $p->render();
}

echo "=== Página 1 de 2 ===\n";
echo strip_tags(html_entity_decode(renderPagina(1))) . "\n\n";
echo "=== Página 2 de 2 ===\n";
echo strip_tags(html_entity_decode(renderPagina(2))) . "\n\n";

$html = renderPagina(1);
$ok = str_contains($html, 'pagination-glass')
    && str_contains($html, 'Anterior')
    && str_contains($html, 'Siguiente')
    && !str_contains($html, 'pagination.previous');

echo $ok ? "OK: vista de paginación renderizada correctamente\n" : "FALLO: revisar vista\n";
