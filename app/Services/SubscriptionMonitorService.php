<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Collection;

/**
 * ALOS-S1-36 — Renewal monitoring & expiry detection.
 *
 * Detects tenants with contracts expiring soon or already expired.
 * Used by the daily scheduler job and platform dashboard.
 */
class SubscriptionMonitorService
{
    /** Default window (days) before contract_end_date to consider "expiring soon". */
    public const DEFAULT_EXPIRING_SOON_DAYS = 7;

    /**
     * Tenants whose contract_end_date is within the next $days (inclusive).
     * Excludes already expired (subscription_status = expired or contract_end_date in the past).
     */
    public function getExpiringSoonTenants(int $days = self::DEFAULT_EXPIRING_SOON_DAYS): Collection
    {
        $start = now()->startOfDay();
        $end = now()->addDays($days)->endOfDay();

        return Tenant::query()
            ->with('subscriptionPlan')
            ->whereNotNull('contract_end_date')
            ->where('contract_end_date', '>=', $start)
            ->where('contract_end_date', '<=', $end)
            ->whereNotIn('subscription_status', [Tenant::SUBSCRIPTION_STATUS_EXPIRED])
            ->orderBy('contract_end_date')
            ->get();
    }

    /**
     * Tenants whose contract_end_date is in the past (or today).
     * Optionally only those not yet marked as expired (so the job can update them).
     */
    public function getExpiredTenants(bool $onlyNotMarkedExpired = false): Collection
    {
        $query = Tenant::query()
            ->with('subscriptionPlan')
            ->whereNotNull('contract_end_date')
            ->whereDate('contract_end_date', '<', now()->startOfDay());

        if ($onlyNotMarkedExpired) {
            $query->where('subscription_status', '!=', Tenant::SUBSCRIPTION_STATUS_EXPIRED);
        }

        return $query->orderBy('contract_end_date')->get();
    }

    /**
     * Mark tenants as expired when contract_end_date has passed.
     * Returns the number of tenants updated.
     */
    public function markExpiredContracts(): int
    {
        $tenants = $this->getExpiredTenants(true);
        $count = 0;
        foreach ($tenants as $tenant) {
            $tenant->update(['subscription_status' => Tenant::SUBSCRIPTION_STATUS_EXPIRED]);
            app(\App\Services\PlanLimitService::class)->invalidateUsageCache($tenant);
            $count++;
        }
        return $count;
    }
}
