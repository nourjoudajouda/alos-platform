<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AuditLog;
use App\Services\AdminLoginLogService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * ALOS-S1-37 — Platform Admin login.
 * Login monitoring, audit and compliance logging integrated.
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
            app(AdminLoginLogService::class)->logFailed($validated['email'], $request);
            app(AuditLogService::class)->recordCompliance(
                'admin_login_failed',
                __('Admin login failed for email.'),
                null,
                null,
                null,
                null,
                'admin'
            );
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $request->session()->regenerate();
        /** @var Admin $admin */
        $admin = Auth::guard('admin')->user();
        app(AdminLoginLogService::class)->logSuccess($admin, $request);
        app(AuditLogService::class)->recordPlatformAudit(
            AuditLog::ACTION_ADMIN_LOGIN,
            AuditLog::ENTITY_ADMIN,
            $admin->id,
            [],
            ['email' => $validated['email']],
            null
        );
        return redirect()->intended(route('admin.core.dashboard'));
    }

    public function destroy(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if ($admin instanceof Admin) {
            app(AdminLoginLogService::class)->logLogout($admin, $request);
            app(AuditLogService::class)->recordPlatformAudit(
                AuditLog::ACTION_ADMIN_LOGOUT,
                AuditLog::ENTITY_ADMIN,
                $admin->id,
                [],
                [],
                null
            );
        }
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
