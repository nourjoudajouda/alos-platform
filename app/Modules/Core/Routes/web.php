<?php

use App\Modules\Core\Http\Controllers\DashboardController;
use App\Modules\Core\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Core Module Web Routes
|--------------------------------------------------------------------------
|
| ALOS-S0-03 — Modular Monolith: موديول Core.
|
*/

Route::get('/module-core', function () {
    return response()->json([
        'module' => 'Core',
        'message' => 'Modular monolith structure is working.',
    ]);
})->name('module.core.check');

Route::get('/core/dashboard', DashboardController::class)
    ->name('core.dashboard')
    ->middleware('auth');

// Tenants CRUD — بنفس آلية Advocate SaaS Companies
Route::middleware('auth')->prefix('core/tenants')->name('core.tenants.')->group(function () {
    Route::get('/', [TenantController::class, 'index'])->name('index');
    Route::get('/create', [TenantController::class, 'create'])->name('create');
    Route::post('/', [TenantController::class, 'store'])->name('store');
    Route::get('/{tenant}', [TenantController::class, 'show'])->name('show');
    Route::get('/{tenant}/edit', [TenantController::class, 'edit'])->name('edit');
    Route::put('/{tenant}', [TenantController::class, 'update'])->name('update');
    Route::delete('/{tenant}', [TenantController::class, 'destroy'])->name('destroy');
});
