<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\Portal\PortalAuthController;
use App\Http\Controllers\Portal\PortalDashboardController;
use App\Http\Controllers\Portal\PortalMessageController;

// Authentication (guest)
Route::get('/login', [LoginBasic::class, 'index'])->name('login');
Route::post('/login', [LoginBasic::class, 'store'])->name('login.store');
Route::post('/logout', [LoginBasic::class, 'destroy'])->name('logout')->middleware('auth');
Route::get('/register', [RegisterBasic::class, 'index'])->name('register');

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
});

// Home: dashboard if authenticated, else login (ALOS-S1-08: client portal users go to portal)
Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }
    return auth()->user()->isClientPortalUser()
        ? redirect()->route('portal.dashboard')
        : redirect()->route('core.dashboard');
})->name('home');

// Locale (public)
Route::get('/lang/{locale}', [LanguageController::class, 'swap']);
