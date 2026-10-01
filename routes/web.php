<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ShelterController;
use App\Http\Controllers\SupplyController;
use App\Http\Controllers\InventoryMovementController;
use App\Http\Controllers\SupplyTransferController;
use App\Http\Controllers\CpmController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CarnetController;
use App\Http\Controllers\GuestController;

/*
|--------------------------------------------------------------------------
| Web Routes — Sistema de Gestión de Inventarios de Refugios (Investigación de Operaciones)
|--------------------------------------------------------------------------
*/

// Redirigir raíz al dashboard o login
Route::get('/', function () {
    if (auth()->check()) return redirect()->route('dashboard');
    if (session('guest_mode')) return redirect()->route('dashboard');
    return redirect()->route('login');
});

// ---------------------------------------------------------------
// Modo Invitado (Guest Mode) — Sin necesidad de autenticación
// ---------------------------------------------------------------
Route::get('/guest', [GuestController::class, 'enter'])->name('guest.enter');
Route::get('/guest/exit', [GuestController::class, 'exit'])->name('guest.exit');

// Rutas de autenticación básica
require __DIR__.'/auth.php';

// Rutas protegidas por autenticación
Route::middleware(['auth'])->group(function () {

    // ---------------------------------------------------------------
    // 2. Refugios y Población Albergada (Admin, Coordinador, Auditor)
    // ---------------------------------------------------------------
    Route::middleware(['can:manage-shelters'])->group(function () {
        Route::resource('shelters', ShelterController::class)->except(['index', 'show'])->names([
            'create'  => 'shelters.create',
            'store'   => 'shelters.store',
            'edit'    => 'shelters.edit',
            'update'  => 'shelters.update',
            'destroy' => 'shelters.destroy',
        ]);
    });

    // ---------------------------------------------------------------
    // 3. Catálogo de Insumos Médicos & Alimentos (escritura: Admin, Almacenista)
    // ---------------------------------------------------------------

    Route::middleware(['can:manage-supplies'])->group(function () {
        Route::resource('supplies', SupplyController::class)->except(['index', 'show'])->names([
            'create'  => 'supplies.create',
            'store'   => 'supplies.store',
            'edit'    => 'supplies.edit',
            'update'  => 'supplies.update',
            'destroy' => 'supplies.destroy',
        ]);
    });

    // ---------------------------------------------------------------
    // 4. Kardex y Movimientos de Inventario — escritura
    // ---------------------------------------------------------------
    Route::middleware(['can:manage-kardex'])->group(function () {
        Route::get('/movements/create', [InventoryMovementController::class, 'create'])->name('movements.create');
        Route::post('/movements', [InventoryMovementController::class, 'store'])->name('movements.store');
    });

    // ---------------------------------------------------------------
    // 5. Transferencias — escritura (cada acción valida su transición de estado)
    // ---------------------------------------------------------------
    Route::middleware(['can:manage-transfers'])->group(function () {
        Route::get('/transfers/create', [SupplyTransferController::class, 'create'])->name('transfers.create');
        Route::post('/transfers', [SupplyTransferController::class, 'store'])->name('transfers.store');
        Route::post('/transfers/{transfer}/approve', [SupplyTransferController::class, 'approve'])->name('transfers.approve');
        Route::post('/transfers/{transfer}/dispatch', [SupplyTransferController::class, 'despachar'])->name('transfers.dispatch');
        Route::post('/transfers/{transfer}/receive', [SupplyTransferController::class, 'receive'])->name('transfers.receive');
        Route::patch('/transfers/{transfer}/status', [SupplyTransferController::class, 'updateStatus'])->name('transfers.status');
    });

    // ---------------------------------------------------------------
    // 6. Método CPM — escritura (Solo Admin)
    // ---------------------------------------------------------------
    Route::middleware(['can:manage-cpm'])->group(function () {
        Route::resource('cpm', CpmController::class)->except(['index', 'show'])->names([
            'create'  => 'cpm.create',
            'store'   => 'cpm.store',
            'edit'    => 'cpm.edit',
            'update'  => 'cpm.update',
            'destroy' => 'cpm.destroy',
        ]);
        Route::post('/cpm/{cpm}/calculate', [CpmController::class, 'calculate'])->name('cpm.calculate');
        Route::post('/cpm/{project}/activities', [CpmController::class, 'storeActivity'])->name('cpm.activities.store');
        Route::put('/cpm/activities/{activity}', [CpmController::class, 'updateActivity'])->name('cpm.activities.update');
        Route::delete('/cpm/activities/{activity}', [CpmController::class, 'destroyActivity'])->name('cpm.activities.destroy');
    });

    // ---------------------------------------------------------------
    // 7. Auditoría y Trazabilidad — Solo usuarios autenticados (NUNCA invitados)
    // ---------------------------------------------------------------
    Route::middleware(['can:view-audit'])->group(function () {
        Route::get('/audit', [AuditLogController::class, 'index'])->name('audit.index');
        Route::get('/audit/export', [AuditLogController::class, 'export'])->name('audit.export');
    });

    // ---------------------------------------------------------------
    // 9. Personal y Credenciales — Solo usuarios autenticados (NUNCA invitados)
    // ---------------------------------------------------------------
    Route::get('/carnets/{user}', [CarnetController::class, 'show'])->name('carnets.show');

    Route::middleware(['can:manage-carnets'])->group(function () {
        Route::get('/carnets', [CarnetController::class, 'index'])->name('carnets.index');
        Route::post('/carnets/{user}/regenerate', [CarnetController::class, 'regenerate'])->name('carnets.regenerate');
        Route::patch('/carnets/{user}/toggle', [CarnetController::class, 'toggle'])->name('carnets.toggle');
    });

});

// ---------------------------------------------------------------
// Rutas accesibles en Modo Invitado (solo lectura) Y para usuarios autenticados
// ---------------------------------------------------------------
Route::middleware(['guest.allowed'])->group(function () {

    // Contexto de Refugio (Cambio de vista operativa en sesión)
    Route::match(['GET', 'POST'], '/shelter/switch', [DashboardController::class, 'switchShelter'])->name('shelter.switch');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Calculadora interactiva y simulador I.O. (Modelos EOQ y Newsvendor)
    Route::get('/supplies/calculator/io', [SupplyController::class, 'calculator'])->name('supplies.calculator');

    // Refugios — solo lectura
    Route::get('/shelters', [ShelterController::class, 'index'])->name('shelters.index');
    Route::get('/shelters/{shelter}', [ShelterController::class, 'show'])->name('shelters.show');

    // Insumos — solo lectura
    Route::get('/supplies', [SupplyController::class, 'index'])->name('supplies.index');
    Route::get('/supplies/{supply}', [SupplyController::class, 'show'])->name('supplies.show');

    // Movimientos — solo lectura
    Route::get('/movements', [InventoryMovementController::class, 'index'])->name('movements.index');

    // Transferencias — solo lectura
    Route::get('/transfers', [SupplyTransferController::class, 'index'])->name('transfers.index');
    Route::get('/transfers/{transfer}', [SupplyTransferController::class, 'show'])->name('transfers.show');

    // CPM — solo lectura
    Route::get('/cpm', [CpmController::class, 'index'])->name('cpm.index');
    Route::get('/cpm/{cpm}', [CpmController::class, 'show'])->name('cpm.show');

    // Reportes — solo lectura
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/inventario-refugios', [ReportController::class, 'inventarioRefugios'])->name('reports.inventario-refugios');
    Route::get('/reports/kardex-movimientos', [ReportController::class, 'kardexMovimientos'])->name('reports.kardex-movimientos');
    Route::get('/reports/analisis-operaciones', [ReportController::class, 'analisisOperaciones'])->name('reports.analisis-operaciones');

});