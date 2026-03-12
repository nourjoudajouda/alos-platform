<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\AdminIpWhitelist;
use App\Models\ComplianceLog;
use Illuminate\Support\Facades\Log;

/**
 * ALOS-S1-37 — Admin security utilities (IP restriction validation capability).
 * Structure and validation logic prepared; strict blocking not enforced by default.
 */
class AdminSecurityService
{
    /**
     * Check if an IP is allowed for admin access.
     * Returns true if no restrictions (allow), false if restricted and IP not in whitelist.
     * When admin has IP restrictions: verify IP is in their whitelist or global whitelist.
     *
     * @param  string  $ip  Client IP address
     * @param  Admin|null  $admin  Admin user (null for login attempts before auth)
     * @return bool true = allowed, false = denied
     */
    public function isIpAllowed(string $ip, ?Admin $admin = null): bool
    {
        // Global whitelist (admin_user_id null, status active)
        $globalAllowed = AdminIpWhitelist::whereNull('admin_user_id')
            ->where('status', AdminIpWhitelist::STATUS_ACTIVE)
            ->where('ip_address', $ip)
            ->exists();

        if ($globalAllowed) {
            return true;
        }

        // If no per-admin restriction, allow (do not block by default)
        if (! $admin) {
            return true;
        }

        // Check if this admin has any IP whitelist entries
        $adminHasRestriction = AdminIpWhitelist::where('admin_user_id', $admin->id)
            ->where('status', AdminIpWhitelist::STATUS_ACTIVE)
            ->exists();

        if (! $adminHasRestriction) {
            return true; // No restriction = allow
        }

        // Admin has restriction: IP must be in their whitelist
        return AdminIpWhitelist::where('admin_user_id', $admin->id)
            ->where('status', AdminIpWhitelist::STATUS_ACTIVE)
            ->where('ip_address', $ip)
            ->exists();
    }

    /**
     * Record IP restriction violation (for audit/compliance).
     * Call this when IP check fails, if you choose to enforce blocking.
     */
    public function recordIpRestrictionViolation(?int $adminId, string $ip, string $userAgent = ''): void
    {
        try {
            ComplianceLog::create([
                'tenant_id' => null,
                'user_id' => null,
                'admin_user_id' => $adminId,
                'user_type' => 'admin',
                'attempted_action' => 'ip_restriction_violation',
                'target_entity' => 'admin',
                'target_id' => $adminId,
                'description' => __('Admin IP restriction violation: access attempt from disallowed IP.'),
                'ip_address' => $ip,
                'user_agent' => $userAgent,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to record IP restriction violation', ['error' => $e->getMessage()]);
        }
    }
}
