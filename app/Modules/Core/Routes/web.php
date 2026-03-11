<?php

use App\Modules\Core\Http\Controllers\AuditLogController;
use App\Modules\Core\Http\Controllers\CaseController;
use App\Modules\Core\Http\Controllers\NotificationController;
use App\Modules\Core\Http\Controllers\ComplianceLogController;
use App\Modules\Core\Http\Controllers\CaseSessionController;
use App\Modules\Core\Http\Controllers\ClientReportController;
use App\Modules\Core\Http\Controllers\ClientController;
use App\Modules\Core\Http\Controllers\ConsultationController;
use App\Modules\Core\Http\Controllers\DashboardController;
use App\Modules\Core\Http\Controllers\DocumentController;
use App\Modules\Core\Http\Controllers\MessageThreadController;
use App\Modules\Core\Http\Controllers\PermissionController;
use App\Modules\Core\Http\Controllers\ReminderRuleController;
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

// لوحة الإدارة العليا — للأدمن فقط (جدول admins)
Route::get('/dashboard', DashboardController::class)
    ->name('core.dashboard')
    ->middleware(['auth:admin']);

// Clients CRUD — ALOS-S1-06; ALOS-S1-07 Team Access (lead lawyer + assigned users); ALOS-S1-08 Portal
Route::middleware(['auth:admin'])->prefix('clients')->name('core.clients.')->group(function () {
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

    // ALOS-S1-10 — Client Document Center (office)
    Route::get('/{client}/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/{client}/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::put('/{client}/documents/{document}/visibility', [DocumentController::class, 'updateVisibility'])->name('documents.visibility');
    Route::get('/{client}/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');

    // ALOS-S1-15.7 — Client reports (settings + generated reports list)
    Route::get('/{client}/reports', [ClientReportController::class, 'index'])->name('reports.index')->middleware('permission:reports.view');
    Route::put('/{client}/reports/settings', [ClientReportController::class, 'updateSettings'])->name('reports.settings.update')->middleware('permission:reports.manage');
    Route::get('/{client}/reports/{report}', [ClientReportController::class, 'show'])->name('reports.show')->middleware('permission:reports.view');
});

// ALOS-S1-14 — Consultations CRUD; scoped by client team access; permissions: consultations.view, consultations.manage
Route::middleware(['auth:admin'])->prefix('consultations')->name('core.consultations.')->group(function () {
    Route::get('/', [ConsultationController::class, 'index'])->name('index')->middleware('permission:consultations.view');
    Route::get('/create', [ConsultationController::class, 'create'])->name('create')->middleware('permission:consultations.manage');
    Route::post('/', [ConsultationController::class, 'store'])->name('store')->middleware('permission:consultations.manage');
    Route::get('/{consultation}', [ConsultationController::class, 'show'])->name('show')->middleware('permission:consultations.view');
    Route::get('/{consultation}/edit', [ConsultationController::class, 'edit'])->name('edit')->middleware('permission:consultations.manage');
    Route::put('/{consultation}', [ConsultationController::class, 'update'])->name('update')->middleware('permission:consultations.manage');
    Route::delete('/{consultation}', [ConsultationController::class, 'destroy'])->name('destroy')->middleware('permission:consultations.manage');
    Route::post('/{consultation}/link-thread', [ConsultationController::class, 'linkThread'])->name('link-thread')->middleware('permission:consultations.manage');
    Route::post('/{consultation}/unlink-thread/{thread}', [ConsultationController::class, 'unlinkThread'])->name('unlink-thread')->middleware('permission:consultations.manage');
    Route::post('/{consultation}/create-thread', [ConsultationController::class, 'createThread'])->name('create-thread')->middleware('permission:consultations.manage');
});

// Cases CRUD — scoped by client team access; permissions: cases.view, cases.manage
Route::middleware(['auth:admin'])->prefix('cases')->name('core.cases.')->group(function () {
    Route::get('/', [CaseController::class, 'index'])->name('index')->middleware('permission:cases.view');
    Route::get('/create', [CaseController::class, 'create'])->name('create')->middleware('permission:cases.manage');
    Route::post('/', [CaseController::class, 'store'])->name('store')->middleware('permission:cases.manage');
    Route::get('/{case}', [CaseController::class, 'show'])->name('show')->middleware('permission:cases.view');
    Route::get('/{case}/edit', [CaseController::class, 'edit'])->name('edit')->middleware('permission:cases.manage');
    Route::put('/{case}', [CaseController::class, 'update'])->name('update')->middleware('permission:cases.manage');
    Route::delete('/{case}', [CaseController::class, 'destroy'])->name('destroy')->middleware('permission:cases.manage');

    // ALOS-S1-12 — Case Sessions & Calendar (Court Hearings)
    Route::get('/{case}/sessions', [CaseSessionController::class, 'index'])->name('sessions.index')->middleware('permission:cases.view');
    Route::get('/{case}/sessions/calendar', [CaseSessionController::class, 'calendar'])->name('sessions.calendar')->middleware('permission:cases.view');
    Route::get('/{case}/sessions/events', [CaseSessionController::class, 'events'])->name('sessions.events')->middleware('permission:cases.view');
    Route::get('/{case}/sessions/create', [CaseSessionController::class, 'create'])->name('sessions.create')->middleware('permission:cases.manage');
    Route::post('/{case}/sessions', [CaseSessionController::class, 'store'])->name('sessions.store')->middleware('permission:cases.manage');
    Route::get('/{case}/sessions/{session}/edit', [CaseSessionController::class, 'edit'])->name('sessions.edit')->middleware('permission:cases.manage');
    Route::put('/{case}/sessions/{session}', [CaseSessionController::class, 'update'])->name('sessions.update')->middleware('permission:cases.manage');
    Route::delete('/{case}/sessions/{session}', [CaseSessionController::class, 'destroy'])->name('sessions.destroy')->middleware('permission:cases.manage');
});

// ALOS-S1-13 — Session Reminder Rules (Admin)
Route::middleware(['auth:admin'])->prefix('reminder-rules')->name('core.reminder-rules.')->group(function () {
    Route::get('/', [ReminderRuleController::class, 'index'])->name('index');
    Route::get('/create', [ReminderRuleController::class, 'create'])->name('create');
    Route::post('/', [ReminderRuleController::class, 'store'])->name('store');
    Route::get('/{reminderRule}/edit', [ReminderRuleController::class, 'edit'])->name('edit');
    Route::put('/{reminderRule}', [ReminderRuleController::class, 'update'])->name('update');
    Route::delete('/{reminderRule}', [ReminderRuleController::class, 'destroy'])->name('destroy');
});

// Tenants CRUD — بنفس آلية Advocate SaaS Companies
Route::middleware(['auth:admin'])->prefix('tenants')->name('core.tenants.')->group(function () {
    Route::get('/', [TenantController::class, 'index'])->name('index');
    Route::get('/create', [TenantController::class, 'create'])->name('create');
    Route::post('/', [TenantController::class, 'store'])->name('store');
    Route::get('/{tenant}', [TenantController::class, 'show'])->name('show');
    Route::get('/{tenant}/edit', [TenantController::class, 'edit'])->name('edit');
    Route::put('/{tenant}', [TenantController::class, 'update'])->name('update');
    Route::delete('/{tenant}', [TenantController::class, 'destroy'])->name('destroy');
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

// ALOS-S1-25 — Audit Log & Compliance Log
Route::middleware(['auth:admin'])->prefix('audit-logs')->name('core.audit-logs.')->group(function () {
    Route::get('/', [AuditLogController::class, 'index'])->name('index');
    Route::get('/{audit_log}', [AuditLogController::class, 'show'])->name('show');
});
Route::middleware(['auth:admin'])->prefix('compliance-logs')->name('core.compliance-logs.')->group(function () {
    Route::get('/', [ComplianceLogController::class, 'index'])->name('index');
    Route::get('/{compliance_log}', [ComplianceLogController::class, 'show'])->name('show');
});
