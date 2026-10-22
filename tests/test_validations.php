<?php
// Prueba las validaciones de negocio agregadas:
// - Kardex: stock suficiente, lote/vencimiento en perecederos, motivo en
//   mermas/ajustes, documento en compras/transferencias, refugio cerrado.
// - Transferencias: origen != destino, stock en origen, refugios cerrados.
// - Login: limitación de intentos (throttle).
// - Carnets: no se puede suspender la propia credencial.
// - Reportes: rangos de fecha válidos.
// - Cambio de refugio: shelter_id debe existir.
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\InventoryMovement;
use App\Models\Shelter;
use App\Models\ShelterInventory;
use App\Models\Supply;
use App\Models\SupplyTransfer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

$pasados = 0; $total = 0;
function check(string $label, bool $ok): void
{
    global $pasados, $total;
    $total++;
    if ($ok) { $pasados++; echo "  [PASS] $label\n"; }
    else { echo "  [FAIL] $label\n"; }
}

echo "===============================================\n";
echo " TEST: VALIDACIONES DE NEGOCIO\n";
echo "===============================================\n";

/** Petición HTTP como usuario autenticado; devuelve [status, errors]. */
function httpForm($kernel, User $user, string $uri, string $method = 'GET', array $data = []): array
{
    $req = Request::create($uri, $method, $data);
    \Illuminate\Support\Facades\Auth::login($user);
    $session = session();
    $session->put('otp_verificado', true);
    // CSRF: el token debe existir en la sesión Y en la cabecera de la petición.
    if (! $session->has('_token')) {
        $session->put('_token', \Illuminate\Support\Str::random(40));
    }
    $token = $session->token();
    $session->forget('errors');
    $req->setLaravelSession($session);
    $req->setUserResolver(fn () => $user);
    $req->headers->set('X-CSRF-TOKEN', $token);

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

/** Limpia registros de prueba. */
function limpiarPruebas(): void
{
    \App\Models\AuditLog::where('descripcion', 'like', '%Validaciones Test%')
        ->orWhere('descripcion', 'like', '%REF-VAL%')
        ->orWhere('descripcion', 'like', '%VAL-001%')
        ->delete();

    SupplyTransfer::query()->delete();

    foreach ([['shelters', 'REF-VAL'], ['supplies', 'VAL-001']] as [$tabla, $codigo]) {
        $modelo = $tabla === 'shelters' ? Shelter::class : Supply::class;
        $modelo::withTrashed()->where('codigo', 'like', $codigo . '%')->get()->each(function ($m) use ($tabla) {
            ShelterInventory::where($tabla === 'shelters' ? 'shelter_id' : 'supply_id', $m->id)->delete();
            $m->forceDelete();
        });
    }
}

limpiarPruebas();

$admin = User::where('rol', 'admin')->firstOrFail();
DB::beginTransaction();

try {
    // ---------- Fixtures ----------
    $shelter = Shelter::create([
        'codigo' => 'REF-VAL', 'nombre' => 'Refugio Validaciones',
        'departamento' => 'DT', 'municipio' => 'MU', 'direccion' => 'Dir 1',
        'capacidad_maxima' => 500, 'poblacion_total' => 100,
        'responsable_nombre' => 'Test', 'responsable_telefono' => '0414-0000000',
        'estado' => 'activo',
    ]);
    $shelterCerrado = Shelter::create([
        'codigo' => 'REF-VAL-CERRADO', 'nombre' => 'Refugio Cerrado',
        'departamento' => 'DT', 'municipio' => 'MU', 'direccion' => 'Dir 2',
        'capacidad_maxima' => 100, 'poblacion_total' => 0,
        'responsable_nombre' => 'Test', 'responsable_telefono' => '0414-0000000',
        'estado' => 'cerrado',
    ]);
    $shelter2 = Shelter::create([
        'codigo' => 'REF-VAL-2', 'nombre' => 'Refugio Destino Validaciones',
        'departamento' => 'DT', 'municipio' => 'MU', 'direccion' => 'Dir 3',
        'capacidad_maxima' => 200, 'poblacion_total' => 50,
        'responsable_nombre' => 'Test', 'responsable_telefono' => '0414-0000000',
        'estado' => 'activo',
    ]);
    $perecedero = Supply::create([
        'codigo' => 'VAL-001', 'nombre' => 'Perecedero Test', 'categoria' => 'alimento',
        'subcategoria' => 'test', 'unidad_medida' => 'kg', 'clasificacion_abc' => 'A',
        'es_perecedero' => true, 'dias_vida_util_promedio' => 30,
        'costo_unitario' => 10, 'costo_pedido' => 5, 'costo_mantenimiento_anual' => 2,
        'tiempo_entrega_dias' => 3, 'consumo_diario_referencia' => 1,
        'stock_seguridad_sugerido' => 10,
    ]);
    $noPerecedero = Supply::create([
        'codigo' => 'VAL-002', 'nombre' => 'No Perecedero Test', 'categoria' => 'alimento',
        'subcategoria' => 'test', 'unidad_medida' => 'kg', 'clasificacion_abc' => 'B',
        'es_perecedero' => false,
        'costo_unitario' => 10, 'costo_pedido' => 5, 'costo_mantenimiento_anual' => 2,
        'tiempo_entrega_dias' => 3, 'consumo_diario_referencia' => 1,
        'stock_seguridad_sugerido' => 10,
    ]);

    ShelterInventory::create([
        'shelter_id' => $shelter->id, 'supply_id' => $perecedero->id,
        'stock_actual' => 50, 'stock_minimo' => 10, 'stock_maximo' => 250, 'punto_reorden' => 15,
    ]);
    ShelterInventory::create([
        'shelter_id' => $shelter->id, 'supply_id' => $noPerecedero->id,
        'stock_actual' => 80, 'stock_minimo' => 10, 'stock_maximo' => 400, 'punto_reorden' => 15,
    ]);

    echo "\n-- Kardex: salidas --\n";
    [$st, $errs] = httpForm($kernel, $admin, '/movements', 'POST', [
        'shelter_id' => $shelter->id,
        'supply_id' => $perecedero->id,
        'tipo' => 'salida_consumo',
        'cantidad' => 999,
    ]);
    check('Salida que supera el stock -> error StockSuficiente', $st === 302 && isset($errs['cantidad']));
    check('Salida rechazada no registrada en kardex', InventoryMovement::where('tipo', 'salida_consumo')->where('shelter_id', $shelter->id)->count() === 0);

    [$st, $errs] = httpForm($kernel, $admin, '/movements', 'POST', [
        'shelter_id' => $shelter->id,
        'supply_id' => $perecedero->id,
        'tipo' => 'salida_consumo',
        'cantidad' => 10,
    ]);
    check('Salida dentro del stock -> aceptada', ($st === 200 || $st === 302) && !$errs);

    echo "\n-- Kardex: entradas de perecederos --\n";
    [$st, $errs] = httpForm($kernel, $admin, '/movements', 'POST', [
        'shelter_id' => $shelter->id,
        'supply_id' => $perecedero->id,
        'tipo' => 'entrada_donacion',
        'cantidad' => 10,
        // numero_lote y fecha_vencimiento ausentes a propósito
    ]);
    check('Entrada de perecedero sin lote -> error', $st === 302 && isset($errs['numero_lote']));
    check('Entrada de perecedero sin vencimiento -> error', isset($errs['fecha_vencimiento']));

    [$st, $errs] = httpForm($kernel, $admin, '/movements', 'POST', [
        'shelter_id' => $shelter->id,
        'supply_id' => $perecedero->id,
        'tipo' => 'entrada_donacion',
        'cantidad' => 10,
        'numero_lote' => 'LOT-VAL-1',
        'fecha_vencimiento' => now()->subDays(5)->toDateString(), // vencido
    ]);
    check('Entrada con vencimiento pasado -> error after:today', $st === 302 && isset($errs['fecha_vencimiento']));

    [$st, $errs] = httpForm($kernel, $admin, '/movements', 'POST', [
        'shelter_id' => $shelter->id,
        'supply_id' => $noPerecedero->id,
        'tipo' => 'entrada_donacion',
        'cantidad' => 10,
    ]);
    check('Entrada de no perecedero sin lote -> aceptada', ($st === 200 || $st === 302) && !$errs);

    echo "\n-- Kardex: campos condicionales --\n";
    [$st, $errs] = httpForm($kernel, $admin, '/movements', 'POST', [
        'shelter_id' => $shelter->id,
        'supply_id' => $noPerecedero->id,
        'tipo' => 'salida_merma_caducidad',
        'cantidad' => 5,
        // motivo ausente a propósito
    ]);
    check('Merma sin motivo -> error required', $st === 302 && isset($errs['motivo']));

    [$st, $errs] = httpForm($kernel, $admin, '/movements', 'POST', [
        'shelter_id' => $shelter->id,
        'supply_id' => $noPerecedero->id,
        'tipo' => 'entrada_compra',
        'cantidad' => 5,
        // documento_referencia ausente a propósito
    ]);
    check('Compra sin documento de referencia -> error required', $st === 302 && isset($errs['documento_referencia']));

    [$st, $errs] = httpForm($kernel, $admin, '/movements', 'POST', [
        'shelter_id' => $shelterCerrado->id,
        'supply_id' => $noPerecedero->id,
        'tipo' => 'entrada_donacion',
        'cantidad' => 5,
    ]);
    check('Movimiento en refugio cerrado -> error', $st === 302 && isset($errs['shelter_id']));

    [$st, $errs] = httpForm($kernel, $admin, '/movements', 'POST', [
        'shelter_id' => 999999,
        'supply_id' => $noPerecedero->id,
        'tipo' => 'entrada_donacion',
        'cantidad' => 5,
    ]);
    check('Refugio inexistente -> error exists', $st === 302 && isset($errs['shelter_id']));

    echo "\n-- Transferencias --\n";
    [$st, $errs] = httpForm($kernel, $admin, '/transfers', 'POST', [
        'refugio_origen_id' => $shelter->id,
        'refugio_destino_id' => $shelter->id, // mismo refugio
        'supply_id' => $noPerecedero->id,
        'cantidad' => 10,
        'prioridad' => 'alta',
    ]);
    check('Transferencia con origen == destino -> error different', $st === 302 && isset($errs['refugio_origen_id']));

    [$st, $errs] = httpForm($kernel, $admin, '/transfers', 'POST', [
        'refugio_origen_id' => $shelter->id,
        'refugio_destino_id' => $shelterCerrado->id,
        'supply_id' => $noPerecedero->id,
        'cantidad' => 10,
        'prioridad' => 'alta',
    ]);
    check('Transferencia hacia refugio cerrado -> error', $st === 302 && isset($errs['refugio_destino_id']));

    [$st, $errs] = httpForm($kernel, $admin, '/transfers', 'POST', [
        'refugio_origen_id' => $shelter->id,
        'refugio_destino_id' => $shelterCerrado->id,
        'supply_id' => $noPerecedero->id,
        'cantidad' => 99999,
        'prioridad' => 'alta',
    ]);
    check('Transferencia que supera el stock origen -> error StockSuficiente', $st === 302 && isset($errs['cantidad']));

    [$st, $errs] = httpForm($kernel, $admin, '/transfers', 'POST', [
        'refugio_origen_id' => $shelter->id,
        'refugio_destino_id' => $shelter2->id,
        'supply_id' => $noPerecedero->id,
        'cantidad' => 10,
        'prioridad' => 'alta',
    ]);
    check('Transferencia válida -> creada', ($st === 200 || $st === 302) && SupplyTransfer::count() === 1);

    echo "\n-- Login: throttle de intentos --\n";
    // El helper httpForm autentica al usuario, y la ruta /login tiene el
    // middleware 'guest' que redirige a los ya autenticados. Para probar el
    // throttle se construye la petición SIN autenticar.
    // Se usa caché en memoria: la de base de datos vive dentro de la
    // transacción del test y en Postgres un fallo la aborta entera.
    config(['cache.default' => 'array']);
    cache()->store('array')->clear();
    RateLimiter::clear('login:127.0.0.1');
    $bloqueado = false;
    for ($i = 1; $i <= 6; $i++) {
        $req = Request::create('/login', 'POST', [
            'email' => 'admin@refugios.ve',
            'password' => 'wrong-password',
        ]);
        $session = session();
        if (! $session->has('_token')) {
            $session->put('_token', \Illuminate\Support\Str::random(40));
        }
        $token = $session->token();
        $req->setLaravelSession($session);
        $req->headers->set('X-CSRF-TOKEN', $token);

        $st = 0;
        $errs = [];
        try {
            $resp = $kernel->handle($req);
            $st = $resp->getStatusCode();
            $errorsBag = session()->get('errors');
            if ($errorsBag) {
                $errs = $errorsBag->messages();
            }
        } catch (\Throwable $e) {
            echo "       (excepción: " . get_class($e) . ": " . substr($e->getMessage(), 0, 120) . ")\n";
        } finally {
            $kernel->terminate($req, null);
        }
        if (isset($errs['email']) && str_contains($errs['email'][0] ?? '', 'Demasiados intentos')) {
            $bloqueado = true;
            break;
        }
    }
    check('Sexto intento de login fallido -> bloqueado por throttle', $bloqueado);
    RateLimiter::clear('login:127.0.0.1');
    config(['cache.default' => 'database']);

    echo "\n-- Carnets: no suspender la propia credencial --\n";
    $qrActivoAntes = (int) $admin->qr_activo;
    [$st, $errs] = httpForm($kernel, $admin, "/carnets/{$admin->id}/toggle", 'PATCH');
    check('Suspender el propio carnet -> error', $st === 302 && session()->get('error') !== null);
    check('El estado del carnet propio no cambió', (int) $admin->fresh()->qr_activo === $qrActivoAntes);

    echo "\n-- Reportes: parámetros --\n";
    [$st, $errs] = httpForm($kernel, $admin, '/reports/kardex-movimientos', 'GET', [
        'fecha_desde' => now()->toDateString(),
        'fecha_hasta' => now()->subDays(10)->toDateString(),
    ]);
    check('Kardex con fecha_hasta < fecha_desde -> error', $st === 302 && isset($errs['fecha_hasta']));

    [$st, $errs] = httpForm($kernel, $admin, '/reports/kardex-movimientos', 'GET', [
        'shelter_id' => 999999,
    ]);
    check('Kardex con refugio inexistente -> error exists', $st === 302 && isset($errs['shelter_id']));

    echo "\n-- Cambio de refugio en sesión --\n";
    [$st, $errs] = httpForm($kernel, $admin, '/shelter/switch', 'POST', [
        'shelter_id' => 999999,
    ]);
    check('Cambio de refugio con id inexistente -> error exists', $st === 302 && isset($errs['shelter_id']));

} finally {
    DB::rollBack();
}

limpiarPruebas();

echo "\n RESULTADO: $pasados/$total PRUEBAS EXITOSAS\n";
exit($pasados === $total ? 0 : 1);
