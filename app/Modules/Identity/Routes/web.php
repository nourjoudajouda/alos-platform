<?php

use App\Modules\Identity\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Identity Module — Internal Users Management (ALOS-S1-05)
|--------------------------------------------------------------------------
| فقط admin أو managing_partner يستطيعون إدارة المستخدمين.
| المستخدمون مُحددون حسب tenant المستخدم الحالي.
*/

Route::middleware(['auth', 'role:admin|managing_partner'])
    ->prefix('identity/users')
    ->name('identity.users.')
    ->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
    });
