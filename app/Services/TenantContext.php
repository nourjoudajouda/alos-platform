<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;

/**
 * ALOS-S1-01 — Tenant context resolved from the authenticated user (temporary).
 * Use: app(TenantContext::class)->current() or TenantContext::current().
 */
class TenantContext
{
    /**
     * Get the current tenant from the logged-in user.
     */
    public function current(): ?Tenant
    {
        $user = Auth::user();

        if (! $user || ! $user->tenant_id) {
            return null;
        }

        return $user->tenant;
    }

    /**
     * Resolve current tenant (convenience static).
     */
    public static function currentTenant(): ?Tenant
    {
        return app(self::class)->current();
    }
}
