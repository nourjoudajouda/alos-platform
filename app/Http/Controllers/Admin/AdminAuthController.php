<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\AuditLogService;
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
            app(AuditLogService::class)->recordCompliance('login_failed', __('Admin login failed for email.'), null, null, null, null);
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $request->session()->regenerate();
        app(AuditLogService::class)->recordAudit(AuditLog::ACTION_LOGIN, AuditLog::ENTITY_USER, 0, [], ['email' => $validated['email']], null);
        return redirect()->intended(route('admin.core.dashboard'));
    }

    public function destroy(Request $request)
    {
        app(AuditLogService::class)->recordAudit(AuditLog::ACTION_LOGOUT, AuditLog::ENTITY_USER, 0, [], [], null);
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
