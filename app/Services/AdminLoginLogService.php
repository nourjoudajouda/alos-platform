<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\AdminLoginLog;
use Illuminate\Http\Request;

/**
 * ALOS-S1-37 — Platform Login Monitoring service.
 */
class AdminLoginLogService
{
    public function logSuccess(Admin $admin, Request $request): AdminLoginLog
    {
        return AdminLoginLog::create([
            'admin_user_id' => $admin->id,
            'login_time' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_status' => AdminLoginLog::STATUS_SUCCESS,
            'email' => $admin->email,
        ]);
    }

    public function logFailed(?string $email, Request $request): AdminLoginLog
    {
        return AdminLoginLog::create([
            'admin_user_id' => null,
            'login_time' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_status' => AdminLoginLog::STATUS_FAILED,
            'email' => $email,
        ]);
    }

    public function logLogout(Admin $admin, Request $request): AdminLoginLog
    {
        return AdminLoginLog::create([
            'admin_user_id' => $admin->id,
            'login_time' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_status' => AdminLoginLog::STATUS_LOGOUT,
            'email' => $admin->email,
        ]);
    }
}
