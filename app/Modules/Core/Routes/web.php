<?php

use App\Modules\Core\Http\Controllers\AuditLogController;
use App\Modules\Core\Http\Controllers\ContractController;
use App\Modules\Core\Http\Controllers\DashboardController;
use App\Modules\Core\Http\Controllers\NotificationController;
use App\Modules\Core\Http\Controllers\ComplianceLogController;
use App\Modules\Core\Http\Controllers\PermissionController;
use App\Modules\Core\Http\Controllers\RoleController;
use App\Modules\Core\Http\Controllers\SubscriptionPlanController;
use App\Modules\Core\Http\Controllers\SystemSettingsController;
use App\Modules\Core\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

/*
| ALOS-S1-31B — Platform Admin scope: no clients, cases, consultations, documents, messages.
| Those are tenant-scoped and live under /company (see routes/web.php).
*/

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

// لوحة الإدارة العليا — للأدمن فقط (جدول admins)
Route::get('/dashboard', DashboardController::class)
    ->name('core.dashboard')
    ->middleware(['auth:admin']);

// ALOS-S1-31B — Contracts overview (platform; edit via Law Firms)
Route::middleware(['auth:admin'])->prefix('contracts')->name('core.contracts.')->group(function () {
    Route::get('/', [ContractController::class, 'index'])->name('index');
});

// ALOS-S1-13 — Session Reminder Rules: tenant-scoped; live under /company (see routes/company.php).

// ALOS-S1-29 — Subscription Plans (admin CRUD)
Route::middleware(['auth:admin'])->prefix('subscription-plans')->name('core.subscription-plans.')->group(function () {
    Route::get('/', [SubscriptionPlanController::class, 'index'])->name('index');
    Route::get('/create', [SubscriptionPlanController::class, 'create'])->name('create');
    Route::post('/', [SubscriptionPlanController::class, 'store'])->name('store');
    Route::get('/{subscription_plan}/edit', [SubscriptionPlanController::class, 'edit'])->name('edit');
    Route::put('/{subscription_plan}', [SubscriptionPlanController::class, 'update'])->name('update');
    Route::delete('/{subscription_plan}', [SubscriptionPlanController::class, 'destroy'])->name('destroy');
});

// ALOS-S1-33 — Law Firms Management (Platform Admin)
Route::middleware(['auth:admin'])->prefix('tenants')->name('core.tenants.')->group(function () {
    Route::get('/', [TenantController::class, 'index'])->name('index');
    Route::get('/create', [TenantController::class, 'create'])->name('create');
    Route::post('/', [TenantController::class, 'store'])->name('store');
    Route::get('/{tenant}', [TenantController::class, 'show'])->name('show');
    Route::get('/{tenant}/edit', [TenantController::class, 'edit'])->name('edit');
    Route::put('/{tenant}', [TenantController::class, 'update'])->name('update');
    Route::delete('/{tenant}', [TenantController::class, 'destroy'])->name('destroy');
    Route::post('/{tenant}/suspend', [TenantController::class, 'suspend'])->name('suspend');
    Route::post('/{tenant}/activate', [TenantController::class, 'activate'])->name('activate');
});

// Roles & Permissions (Spatie)
Route::middleware(['auth:admin'])->prefix('roles')->name('core.roles.')->group(function () {
    Route::get('/', [RoleController::class, 'index'])->name('index');
    Route::get('/create', [RoleController::class, 'create'])->name('create');
    Route::post('/', [RoleController::class, 'store'])->name('store');
    Route::get('/{role}', [RoleController::class, 'show'])->name('show');
    Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
    Route::put('/{role}', [RoleController::class, 'update'])->name('update');
    Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
});

Route::middleware(['auth:admin'])->prefix('permissions')->name('core.permissions.')->group(function () {
    Route::get('/', [PermissionController::class, 'index'])->name('index');
    Route::get('/{permission}', [PermissionController::class, 'show'])->name('show');
});

// ALOS-S1-26 — In-app notifications (current user only)
Route::middleware(['auth:admin'])->prefix('notifications')->name('core.notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
    Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
});

// ALOS-S1-30 — System Settings (platform admin only; not visible to tenant admins)
Route::middleware(['auth:admin'])->prefix('system-settings')->name('core.system-settings.')->group(function () {
    Route::get('/', [SystemSettingsController::class, 'index'])->name('index');
    Route::put('/', [SystemSettingsController::class, 'update'])->name('update');
    Route::post('/test-mail', [SystemSettingsController::class, 'testMail'])->name('test-mail');
});

// ALOS-S1-25 — Audit Log & Compliance Log
Route::middleware(['auth:admin'])->prefix('audit-logs')->name('core.audit-logs.')->group(function () {
    Route::get('/', [AuditLogController::class, 'index'])->name('index');
    Route::get('/{audit_log}', [AuditLogController::class, 'show'])->name('show');
});
Route::middleware(['auth:admin'])->prefix('compliance-logs')->name('core.compliance-logs.')->group(function () {
    Route::get('/', [ComplianceLogController::class, 'index'])->name('index');
    Route::get('/{compliance_log}', [ComplianceLogController::class, 'show'])->name('show');
});
