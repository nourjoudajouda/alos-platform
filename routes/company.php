<?php

use App\Modules\Core\Http\Controllers\CaseController;
use App\Modules\Core\Http\Controllers\CaseSessionController;
use App\Modules\Core\Http\Controllers\ClientController;
use App\Modules\Core\Http\Controllers\ClientReportController;
use App\Modules\Core\Http\Controllers\ConsultationController;
use App\Modules\Core\Http\Controllers\DocumentController;
use App\Modules\Core\Http\Controllers\MessageThreadController;
use Illuminate\Support\Facades\Route;

/*
| ALOS-S1-31B — Tenant (company) scope: clients, cases, consultations.
| Only tenant staff; no tenant/company picker (context from auth).
*/

Route::prefix('clients')->name('clients.')->group(function () {
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

    Route::get('/{client}/threads', [MessageThreadController::class, 'index'])->name('threads.index');
    Route::post('/{client}/threads', [MessageThreadController::class, 'store'])->name('threads.store');
    Route::get('/{client}/threads/{thread}', [MessageThreadController::class, 'show'])->name('threads.show');
    Route::post('/{client}/threads/{thread}/messages', [MessageThreadController::class, 'storeMessage'])->name('threads.messages.store');
    Route::post('/{client}/threads/{thread}/archive', [MessageThreadController::class, 'archive'])->name('threads.archive');
    Route::get('/{client}/threads/{thread}/attachments/{attachment}', [MessageThreadController::class, 'downloadAttachment'])->name('threads.attachments.download');

    Route::get('/{client}/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/{client}/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::put('/{client}/documents/{document}/visibility', [DocumentController::class, 'updateVisibility'])->name('documents.visibility');
    Route::get('/{client}/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');

    Route::get('/{client}/reports', [ClientReportController::class, 'index'])->name('reports.index')->middleware('permission:reports.view');
    Route::put('/{client}/reports/settings', [ClientReportController::class, 'updateSettings'])->name('reports.settings.update')->middleware('permission:reports.manage');
    Route::get('/{client}/reports/{report}', [ClientReportController::class, 'show'])->name('reports.show')->middleware('permission:reports.view');
});

Route::prefix('consultations')->name('consultations.')->group(function () {
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

Route::prefix('cases')->name('cases.')->group(function () {
    Route::get('/', [CaseController::class, 'index'])->name('index')->middleware('permission:cases.view');
    Route::get('/create', [CaseController::class, 'create'])->name('create')->middleware('permission:cases.manage');
    Route::post('/', [CaseController::class, 'store'])->name('store')->middleware('permission:cases.manage');
    Route::get('/{case}', [CaseController::class, 'show'])->name('show')->middleware('permission:cases.view');
    Route::get('/{case}/edit', [CaseController::class, 'edit'])->name('edit')->middleware('permission:cases.manage');
    Route::put('/{case}', [CaseController::class, 'update'])->name('update')->middleware('permission:cases.manage');
    Route::delete('/{case}', [CaseController::class, 'destroy'])->name('destroy')->middleware('permission:cases.manage');

    Route::get('/{case}/sessions', [CaseSessionController::class, 'index'])->name('sessions.index')->middleware('permission:cases.view');
    Route::get('/{case}/sessions/calendar', [CaseSessionController::class, 'calendar'])->name('sessions.calendar')->middleware('permission:cases.view');
    Route::get('/{case}/sessions/events', [CaseSessionController::class, 'events'])->name('sessions.events')->middleware('permission:cases.view');
    Route::get('/{case}/sessions/create', [CaseSessionController::class, 'create'])->name('sessions.create')->middleware('permission:cases.manage');
    Route::post('/{case}/sessions', [CaseSessionController::class, 'store'])->name('sessions.store')->middleware('permission:cases.manage');
    Route::get('/{case}/sessions/{session}/edit', [CaseSessionController::class, 'edit'])->name('sessions.edit')->middleware('permission:cases.manage');
    Route::put('/{case}/sessions/{session}', [CaseSessionController::class, 'update'])->name('sessions.update')->middleware('permission:cases.manage');
    Route::delete('/{case}/sessions/{session}', [CaseSessionController::class, 'destroy'])->name('sessions.destroy')->middleware('permission:cases.manage');
});
