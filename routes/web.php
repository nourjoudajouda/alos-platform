<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\Public\LandingController;
use App\Http\Controllers\Portal\PortalAuthController;
use App\Http\Controllers\Portal\PortalDashboardController;
use App\Http\Controllers\Portal\PortalConsultationController;
use App\Http\Controllers\Portal\PortalDocumentController;
use App\Http\Controllers\Portal\PortalMessageController;
use App\Http\Controllers\Admin\AdminAuthController;

// ALOS-S1-16 — Public Website: Landing page (guests) | redirect (authenticated)
Route::get('/', [LandingController::class, 'index'])->name('home');

// تسجيل الدخول والتسجيل للتيننت (الموقع العام) — روابط مختلفة عن الأدمن
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginBasic::class, 'index'])->name('login');
    Route::post('/login', [LoginBasic::class, 'store'])->name('login.store');
    Route::get('/register', [RegisterBasic::class, 'index'])->name('register');
    Route::post('/register', [RegisterBasic::class, 'store'])->name('register.store');
});
Route::post('/logout', [LoginBasic::class, 'destroy'])->name('logout')->middleware('auth');

// لوحة التيننت (مكتب/شركة) — منفصلة عن الأدمن؛ الرابط /company
Route::prefix('company')->name('company.')->middleware(['auth', 'tenant_staff'])->group(function () {
    Route::get('/', \App\Http\Controllers\Office\OfficeDashboardController::class)->name('dashboard');
    // ALOS-S1-21 — Branding Settings
    Route::get('/settings/branding', [\App\Http\Controllers\Office\BrandingSettingsController::class, 'edit'])->name('settings.branding.edit');
    Route::put('/settings/branding', [\App\Http\Controllers\Office\BrandingSettingsController::class, 'update'])->name('settings.branding.update');
});

// ALOS-S1-08 — Client Portal (separate login + dashboard)
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/login', [PortalAuthController::class, 'index'])->name('login');
    Route::post('/login', [PortalAuthController::class, 'store'])->name('login.store');
    Route::post('/logout', [PortalAuthController::class, 'destroy'])->name('logout')->middleware('auth');
    Route::get('/', [PortalDashboardController::class, 'index'])->name('dashboard')->middleware(['auth', 'portal_client']);

    // ALOS-S1-09 — Secure Messaging (client portal)
    Route::get('/messages', [PortalMessageController::class, 'index'])->name('messages.index')->middleware(['auth', 'portal_client']);
    Route::post('/messages', [PortalMessageController::class, 'store'])->name('messages.store')->middleware(['auth', 'portal_client']);
    Route::get('/messages/{thread}', [PortalMessageController::class, 'show'])->name('messages.show')->middleware(['auth', 'portal_client']);
    Route::post('/messages/{thread}/reply', [PortalMessageController::class, 'storeMessage'])->name('messages.reply')->middleware(['auth', 'portal_client']);
    Route::get('/messages/{thread}/attachments/{attachment}', [PortalMessageController::class, 'downloadAttachment'])->name('messages.attachments.download')->middleware(['auth', 'portal_client']);

    // ALOS-S1-14 — Consultations (client portal: shared only)
    Route::get('/consultations', [PortalConsultationController::class, 'index'])->name('consultations.index')->middleware(['auth', 'portal_client']);
    Route::get('/consultations/{consultation}', [PortalConsultationController::class, 'show'])->name('consultations.show')->middleware(['auth', 'portal_client']);
    Route::get('/consultations/{consultation}/documents/{documentId}/download', [PortalConsultationController::class, 'downloadDocument'])->name('consultations.download-document')->middleware(['auth', 'portal_client']);

    // ALOS-S1-10 — Document Center (client portal: shared only + upload)
    Route::get('/documents', [PortalDocumentController::class, 'index'])->name('documents.index')->middleware(['auth', 'portal_client']);
    Route::post('/documents', [PortalDocumentController::class, 'store'])->name('documents.store')->middleware(['auth', 'portal_client']);
    Route::get('/documents/{document}/download', [PortalDocumentController::class, 'download'])->name('documents.download')->middleware(['auth', 'portal_client']);

    // ALOS-S1-15.7 — Reports (client portal: client sees only their reports)
    Route::get('/reports', [\App\Http\Controllers\Portal\PortalReportController::class, 'index'])->name('reports.index')->middleware(['auth', 'portal_client']);
    Route::get('/reports/{report}', [\App\Http\Controllers\Portal\PortalReportController::class, 'show'])->name('reports.show')->middleware(['auth', 'portal_client']);

    // ALOS-S1-26 — In-app notifications (portal)
    Route::get('/notifications', [\App\Http\Controllers\Portal\PortalNotificationController::class, 'index'])->name('notifications.index')->middleware(['auth', 'portal_client']);
    Route::post('/notifications/read-all', [\App\Http\Controllers\Portal\PortalNotificationController::class, 'markAllAsRead'])->name('notifications.read-all')->middleware(['auth', 'portal_client']);
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\Portal\PortalNotificationController::class, 'markAsRead'])->name('notifications.read')->middleware(['auth', 'portal_client']); // {notification} = id
});

// ALOS-S1-15.8 — Signed URL to view generated report (from email link)
Route::get('/reports/{report}/view', [\App\Http\Controllers\ReportViewController::class, 'show'])
    ->name('report.show.signed')
    ->middleware('signed');

// Locale (public)
Route::get('/lang/{locale}', [LanguageController::class, 'swap']);

// لوحة الإدارة — كل الروابط تحت البادئة admin (تسجيل دخول الأدمن منفصل)
// guest:admin = لا تعتبر المستخدم مسجلاً إلا بغارد الأدمن، فلا إعادة توجيه لمستخدم web إلى الداشبورد → يمنع ERR_TOO_MANY_REDIRECTS
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'index'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'store'])->name('login.store');
    });
    Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout')->middleware('auth:admin');
    require base_path('app/Modules/Core/Routes/web.php');
});

// ALOS-S1-19 / ALOS-S1-20 — Tenant public site: /{tenant_slug}
Route::get('/{slug}', [\App\Http\Controllers\Public\TenantPublicSiteController::class, 'show'])->name('public.tenant');
