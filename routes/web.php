<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;

// Authentication (guest)
Route::get('/login', [LoginBasic::class, 'index'])->name('login');
Route::post('/login', [LoginBasic::class, 'store'])->name('login.store');
Route::post('/logout', [LoginBasic::class, 'destroy'])->name('logout')->middleware('auth');
Route::get('/register', [RegisterBasic::class, 'index'])->name('register');

// Home: dashboard if authenticated, else login
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('core.dashboard')
        : redirect()->route('login');
})->name('home');

// Locale (public)
Route::get('/lang/{locale}', [LanguageController::class, 'swap']);
