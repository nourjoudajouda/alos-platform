<?php

use App\Modules\Core\Http\Controllers\ClientController;
use App\Modules\Core\Http\Controllers\DashboardController;
use App\Modules\Core\Http\Controllers\MessageThreadController;
use App\Modules\Core\Http\Controllers\PermissionController;
use App\Modules\Core\Http\Controllers\RoleController;
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
    ->middleware(['auth', 'not_client_portal']);

// Clients CRUD — ALOS-S1-06; ALOS-S1-07 Team Access (lead lawyer + assigned users); ALOS-S1-08 Portal
Route::middleware(['auth', 'not_client_portal'])->prefix('core/clients')->name('core.clients.')->group(function () {
    Route::get('/', [ClientController::class, 'index'])->name('index');
    Route::get('/create', [ClientController::class, 'create'])->name('create');
    Route::post('/', [ClientController::class, 'store'])->name('store');
    Route::get('/{client}', [ClientController::class, 'show'])->name('show');
    Route::get('/{client}/edit', [ClientController::class, 'edit'])->name('edit');
    Route::put('/{client}', [ClientController::class, 'update'])->name('update');
    Route::put('/{client}/team-access', [ClientController::class, 'updateTeamAccess'])->name('team-access.update');
    Route::post('/{client}/portal-user', [ClientController::class, 'storePortalUser'])->name('portal-user.store');
    Route::put('/{client}/portal-user', [ClientController::class, 'updatePortalUser'])->name('portal-user.update');
    Route::post('/{client}/portal-user/toggle', [ClientController::class, 'togglePortalStatus'])->name('portal-user.toggle');
    Route::delete('/{client}', [ClientController::class, 'destroy'])->name('destroy');

    // ALOS-S1-09 — Secure Messaging (office side)
    Route::get('/{client}/threads', [MessageThreadController::class, 'index'])->name('threads.index');
    Route::post('/{client}/threads', [MessageThreadController::class, 'store'])->name('threads.store');
    Route::get('/{client}/threads/{thread}', [MessageThreadController::class, 'show'])->name('threads.show');
    Route::post('/{client}/threads/{thread}/messages', [MessageThreadController::class, 'storeMessage'])->name('threads.messages.store');
    Route::post('/{client}/threads/{thread}/archive', [MessageThreadController::class, 'archive'])->name('threads.archive');
    Route::get('/{client}/threads/{thread}/attachments/{attachment}', [MessageThreadController::class, 'downloadAttachment'])->name('threads.attachments.download');
});

// Tenants CRUD — بنفس آلية Advocate SaaS Companies
Route::middleware(['auth', 'not_client_portal'])->prefix('core/tenants')->name('core.tenants.')->group(function () {
    Route::get('/', [TenantController::class, 'index'])->name('index');
    Route::get('/create', [TenantController::class, 'create'])->name('create');
    Route::post('/', [TenantController::class, 'store'])->name('store');
    Route::get('/{tenant}', [TenantController::class, 'show'])->name('show');
    Route::get('/{tenant}/edit', [TenantController::class, 'edit'])->name('edit');
    Route::put('/{tenant}', [TenantController::class, 'update'])->name('update');
    Route::delete('/{tenant}', [TenantController::class, 'destroy'])->name('destroy');
});

// Roles & Permissions (Spatie)
Route::middleware(['auth', 'not_client_portal'])->prefix('core/roles')->name('core.roles.')->group(function () {
    Route::get('/', [RoleController::class, 'index'])->name('index');
    Route::get('/create', [RoleController::class, 'create'])->name('create');
    Route::post('/', [RoleController::class, 'store'])->name('store');
    Route::get('/{role}', [RoleController::class, 'show'])->name('show');
    Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
    Route::put('/{role}', [RoleController::class, 'update'])->name('update');
    Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
});

Route::middleware(['auth', 'not_client_portal'])->prefix('core/permissions')->name('core.permissions.')->group(function () {
    Route::get('/', [PermissionController::class, 'index'])->name('index');
    Route::get('/create', [PermissionController::class, 'create'])->name('create');
    Route::post('/', [PermissionController::class, 'store'])->name('store');
    Route::get('/{permission}', [PermissionController::class, 'show'])->name('show');
    Route::get('/{permission}/edit', [PermissionController::class, 'edit'])->name('edit');
    Route::put('/{permission}', [PermissionController::class, 'update'])->name('update');
    Route::delete('/{permission}', [PermissionController::class, 'destroy'])->name('destroy');
});
