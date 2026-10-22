<?php
// Diagnóstico de cuentas: ¿hay hashes rotos, vacíos o en claro?
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$filas = DB::table('users')->select('id', 'email', 'rol', 'qr_activo', 'created_at', 'password')->orderBy('id')->get();

echo "================================================\n";
echo " DIAGNÓSTICO DE CUENTAS (tabla users)\n";
echo "================================================\n";
foreach ($filas as $x) {
    $h = $x->password;
    $tipo = 'bcrypt OK';
    if ($h === null || $h === '') {
        $tipo = '!! VACIO (login siempre falla)';
    } elseif (preg_match('/^(admin|password|123|test)/i', $h)) {
        $tipo = '!! EN CLARO (login siempre falla)';
    } elseif (! str_starts_with($h, '$2y$') && ! str_starts_with($h, '$argon')) {
        $tipo = '!! NO ES HASH: ' . substr($h, 0, 40);
    } elseif (substr_count($h, '$2y$') > 1 || preg_match('/^\$2y\$\d+\$[A-Za-z0-9\/\.]{53}\$?\$2y\$/', $h)) {
        $tipo = '!! DOBLE HASH (login siempre falla)';
    }
    printf("#%-3d %-36s %-16s qr=%d  %-19s  %s\n",
        $x->id, $x->email, $x->rol, $x->qr_activo, (string) $x->created_at, $tipo);
}

echo "\n-- Usuarios creados por auto-registro (rol operador, dominio refugios.ve) --\n";
$ops = DB::table('users')->where('rol', 'operador')->get();
if ($ops->isEmpty()) {
    echo "  (ninguno)\n";
}
foreach ($ops as $o) {
    $esValido = Hash::check('Secre123!', $o->password) ? 'solo si password=Secre123!' : '';
    printf("  #%d %s  hash=%s...\n", $o->id, $o->email, substr($o->password, 0, 12));
}

echo "\n-- Comprobación: ¿las cuentas sembradas loguean con sus contraseñas? --\n";
$demo = [
    'admin@refugios.ve' => 'Admin1234!',
    'coordinador@refugios.ve' => 'Coordinador1234!',
    'almacen@refugios.ve' => 'Almacen1234!',
    'auditor@refugios.ve' => 'Auditor1234!',
];
foreach ($demo as $mail => $pass) {
    $ok = Auth::attempt(['email' => $mail, 'password' => $pass]);
    echo ($ok ? '  [OK]   ' : '  [FAIL] ') . $mail . "\n";
    if ($ok) { Auth::logout(); }
}

echo "\n-- ¿Existe alguna cuenta cuyo email NO case-sensitive duplique? --\n"
    . "  (correos duplicados ignorando mayúsculas)\n";
$dups = DB::table('users')->selectRaw('lower(email) as correo, count(*) as total')->groupBy('correo')->havingRaw('count(*) > 1')->get();
echo $dups->isEmpty() ? "  (ninguno)\n" : $dups->pluck('correo')->implode(', ') . "\n";
