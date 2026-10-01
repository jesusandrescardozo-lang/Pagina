<?php
// Prueba los FormRequests de refugios/insumos (creación/edición/validación)
// y el borrado lógico (soft delete): destruir ya NO borra en cascada
// inventarios ni kardex.
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Shelter;
use App\Models\ShelterInventory;
use App\Models\Supply;
use App\Models\User;
use Illuminate\Http\Request;

$pasados = 0; $total = 0;
function check(string $label, bool $ok): void
{
    global $pasados, $total;
    $total++;
    if ($ok) { $pasados++; echo "  [PASS] $label\n"; }
    else { echo "  [FAIL] $label\n"; }
}

echo "===============================================\n";
echo " TEST: FORMREQUESTS CRUD + SOFT DELETES\n";
echo "===============================================\n";

/** Petición HTTP como usuario autenticado; devuelve [status, errors]. */
function httpForm($kernel, User $user, string $uri, string $method = 'GET', array $data = []): array
{
    $req = Request::create($uri, $method, $data);
    \Illuminate\Support\Facades\Auth::login($user);
    $session = session();
    $session->put('otp_verificado', true);
    $session->put('_token', csrf_token());
    $session->forget('errors');
    $req->setLaravelSession($session);
    $req->setUserResolver(fn () => $user);
    $req->headers->set('X-CSRF-TOKEN', csrf_token());

    $status = 0;
    $errors = [];
    try {
        $resp = $kernel->handle($req);
        $status = $resp->getStatusCode();
        $errorsBag = session()->get('errors');
        if ($errorsBag) {
            $errors = $errorsBag->messages();
        }
    } catch (\Throwable $e) {
        echo "       (excepción: " . get_class($e) . ": " . substr($e->getMessage(), 0, 120) . ")\n";
    } finally {
        $kernel->terminate($req, null);
        \Illuminate\Support\Facades\Auth::logout();
        session()->forget('otp_verificado');
    }

    return [$status, $errors];
}

/** Limpia registros de prueba (por si una corrida anterior quedó a medias). */
function limpiarPruebas(): void
{
    foreach ([['shelters', 'REF-TST'], ['supplies', 'TST-001']] as [$tabla, $codigo]) {
        $modelo = $tabla === 'shelters' ? Shelter::class : Supply::class;
        $modelo::withTrashed()->where('codigo', $codigo)->get()->each(function ($m) use ($tabla) {
            ShelterInventory::where($tabla === 'shelters' ? 'shelter_id' : 'supply_id', $m->id)->delete();
            $m->forceDelete();
        });
    }
}

limpiarPruebas();

$admin = User::where('rol', 'admin')->firstOrFail();
$sheltersAntes = Shelter::count();

echo "\n-- Formularios (vistas) --\n";
[$st] = httpForm($kernel, $admin, '/shelters/create');
check('GET /shelters/create -> 200', $st === 200);
[$st] = httpForm($kernel, $admin, '/supplies/create');
check('GET /supplies/create -> 200', $st === 200);

echo "\n-- StoreShelterRequest --\n";
[$st, $errs] = httpForm($kernel, $admin, '/shelters', 'POST', [
    'codigo' => 'REF-TST',
    // 'nombre' ausente a propósito
    'capacidad_maxima' => 100,
]);
check('POST /shelters inválido -> 302 con error de validación', $st === 302 && isset($errs['nombre']));
check('Refugio no creado si la validación falla', Shelter::withTrashed()->where('codigo', 'REF-TST')->count() === 0);

[$st, $errs] = httpForm($kernel, $admin, '/shelters', 'POST', [
    'codigo' => 'REF-01', // ya existente
    'nombre' => 'Duplicado',
    'departamento' => 'Distrito Capital',
    'municipio' => 'Libertador',
    'direccion' => 'Calle Falsa 123',
    'capacidad_maxima' => 100,
    'responsable_nombre' => 'Test',
    'responsable_telefono' => '0414-0000000',
    'estado' => 'activo',
]);
check('POST /shelters con código duplicado -> error unique', $st === 302 && isset($errs['codigo']));

[$st, $errs] = httpForm($kernel, $admin, '/shelters', 'POST', [
    'codigo' => 'REF-TST',
    'nombre' => 'Refugio FormRequest Test',
    'departamento' => 'Distrito Capital',
    'municipio' => 'Libertador',
    'direccion' => 'Av. Test 1',
    'capacidad_maxima' => 100,
    'poblacion_adultos' => 10,
    'poblacion_ninos' => 5,
    'responsable_nombre' => 'Test Responsable',
    'responsable_telefono' => '0414-0000000',
    'estado' => 'activo',
]);
$shelter = Shelter::where('codigo', 'REF-TST')->first();
check('POST /shelters válido -> 302 y creado', $st === 302 && $shelter !== null);
check('Total de refugios incrementó', $shelter && Shelter::count() === $sheltersAntes + 1);
check('Inventario inicializado para todos los insumos', $shelter
    && ShelterInventory::where('shelter_id', $shelter->id)->count() === Supply::count());
check('poblacion_total calculada (10+5)', $shelter && $shelter->poblacion_total === 15);

echo "\n-- UpdateShelterRequest (reutiliza reglas de store) --\n";
if ($shelter) {
    [$st, $errs] = httpForm($kernel, $admin, "/shelters/{$shelter->id}", 'POST', [
        '_method' => 'PUT',
        'codigo' => 'REF-01', // código ajeno -> unique debe ignorar solo el propio
        'nombre' => 'X',
        'departamento' => 'D',
        'municipio' => 'M',
        'direccion' => 'Dir',
        'capacidad_maxima' => 100,
        'poblacion_adultos' => 1,
        'poblacion_ninos' => 0,
        'poblacion_adultos_mayores' => 0,
        'poblacion_atencion_medica' => 0,
        'responsable_nombre' => 'R',
        'responsable_telefono' => 'T',
        'estado' => 'activo',
    ]);
    check('PUT con código ajeno -> error unique', $st === 302 && isset($errs['codigo']));

    [$st, $errs] = httpForm($kernel, $admin, "/shelters/{$shelter->id}", 'POST', [
        '_method' => 'PUT',
        'codigo' => 'REF-TST', // el propio, debe ser válido
        'nombre' => 'Refugio FormRequest Test v2',
        'departamento' => 'Distrito Capital',
        'municipio' => 'Libertador',
        'direccion' => 'Av. Test 1',
        'capacidad_maxima' => 200,
        'poblacion_adultos' => 10,
        'poblacion_ninos' => 5,
        'poblacion_adultos_mayores' => 0,
        'poblacion_atencion_medica' => 0,
        'responsable_nombre' => 'Test Responsable',
        'responsable_telefono' => '0414-0000000',
        'estado' => 'activo',
    ]);
    $shelter->refresh();
    check('PUT con código propio -> sin errores de validación', ($st === 200 || $st === 302) && !$errs);
    check('Edición aplicada (capacidad 200)', $shelter->capacidad_maxima === 200);
} else {
    check('PUT skipped (no hay refugio)', false);
}

echo "\n-- Soft delete de refugio (sin cascada) --\n";
if ($shelter) {
    $inventariosAntes = ShelterInventory::where('shelter_id', $shelter->id)->count();
    [$st] = httpForm($kernel, $admin, "/shelters/{$shelter->id}", 'POST', ['_method' => 'DELETE']);
    $shelterTras = Shelter::withTrashed()->find($shelter->id);
    check('DELETE /shelters/{id} -> 302', $st === 302);
    check('Refugio con borrado lógico (trashed)', $shelterTras && $shelterTras->trashed());
    check('Refugio oculto de las consultas normales', Shelter::where('codigo', 'REF-TST')->count() === 0);
    check('Inventarios NO borrados en cascada',
        ShelterInventory::where('shelter_id', $shelter->id)->count() === $inventariosAntes && $inventariosAntes > 0);
} else {
    check('Soft delete skip', false);
}

echo "\n-- StoreSupplyRequest + soft delete de insumo --\n";
[$st, $errs] = httpForm($kernel, $admin, '/supplies', 'POST', [
    'codigo' => 'TST-001',
    // 'nombre' ausente
    'categoria' => 'alimento',
    'costo_unitario' => 1,
]);
check('POST /supplies inválido -> 302 con error', $st === 302 && isset($errs['nombre']));

[$st, $errs] = httpForm($kernel, $admin, '/supplies', 'POST', [
    'codigo' => 'TST-001',
    'nombre' => 'Insumo FormRequest Test',
    'categoria' => 'alimento',
    'subcategoria' => 'test',
    'unidad_medida' => 'unidad',
    'clasificacion_abc' => 'C',
    'es_perecedero' => '1',
    'costo_unitario' => 2.5,
    'costo_pedido' => 10,
    'costo_mantenimiento_anual' => 100,
    'tiempo_entrega_dias' => 3,
    'consumo_diario_referencia' => 0.5,
    'stock_seguridad_sugerido' => 10,
]);
$supply = Supply::where('codigo', 'TST-001')->first();
check('POST /supplies válido -> creado', $st === 302 && $supply !== null);
check('Inventario del insumo creado en todos los refugios', $supply
    && ShelterInventory::where('supply_id', $supply->id)->count() === Shelter::count());

if ($supply) {
    $inventariosAntes = ShelterInventory::where('supply_id', $supply->id)->count();
    [$st] = httpForm($kernel, $admin, "/supplies/{$supply->id}", 'POST', ['_method' => 'DELETE']);
    $supplyTras = Supply::withTrashed()->find($supply->id);
    check('DELETE /supplies/{id} -> 302', $st === 302);
    check('Insumo con borrado lógico (trashed)', $supplyTras && $supplyTras->trashed());
    check('Inventarios del insumo NO borrados en cascada',
        ShelterInventory::where('supply_id', $supply->id)->count() === $inventariosAntes);
}

echo "\n-- Código único reservado aunque esté borrado --\n";
[$st, $errs] = httpForm($kernel, $admin, '/shelters', 'POST', [
    'codigo' => 'REF-TST', // sigue reservado por el registro dado de baja
    'nombre' => 'Intento Reuso',
    'departamento' => 'D',
    'municipio' => 'M',
    'direccion' => 'Dir',
    'capacidad_maxima' => 10,
    'responsable_nombre' => 'R',
    'responsable_telefono' => 'T',
    'estado' => 'activo',
]);
check('Reusar código de refugio dado de baja -> error unique', $st === 302 && isset($errs['codigo']));

// ---------- Limpieza final ----------
limpiarPruebas();
check('Limpieza: sin restos REF-TST/TST-001',
    Shelter::withTrashed()->where('codigo', 'REF-TST')->count() === 0
    && Supply::withTrashed()->where('codigo', 'TST-001')->count() === 0);

echo "\n RESULTADO: $pasados/$total PRUEBAS EXITOSAS\n";
exit($pasados === $total ? 0 : 1);
