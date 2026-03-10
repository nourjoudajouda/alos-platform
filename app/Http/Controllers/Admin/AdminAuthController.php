<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * تسجيل دخول لوحة الإدارة — تحت /admin/login (مميز عن تسجيل التيننت).
 */
class AdminAuthController extends Controller
{
    public function index()
    {
        $pageConfigs = ['myLayout' => 'blank', 'customizerHide' => true];
        return view('core::content.authentications.auth-login-basic', [
            'pageConfigs' => $pageConfigs,
            'isAdminLogin' => true,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('admin')->attempt($validated, (bool) $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $request->session()->regenerate();
        return redirect()->intended(route('admin.core.dashboard'));
    }

    public function destroy(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
