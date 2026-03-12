<?php

namespace App\Services;

use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use Illuminate\Support\Facades\Schema;

/**
 * ALOS-S1-31B — Platform Dashboard Summary Service.
 *
 * Provides platform-level metrics for Super Admin only (law firms, subscriptions, contracts).
 * No tenant operational data (clients, cases, consultations, messages).
 */
class PlatformDashboardService
{
    /** Number of days ahead to consider a contract as "expiring soon". */
    public const EXPIRING_DAYS = 30;

    public function getMetrics(): array
    {
        $totalLawFirms = Tenant::count();
        $activeLawFirms = Tenant::where('is_active', true)->count();
        $suspendedLawFirms = $totalLawFirms - $activeLawFirms;

        $activeSubscriptions = Tenant::where('is_active', true)
            ->whereNotNull('subscription_plan_id')
            ->count();

        $expiringContracts = 0;
        if (Schema::hasColumn('tenants', 'contract_end_date')) {
            $expiringContracts = Tenant::where('is_active', true)
                ->whereNotNull('contract_end_date')
                ->where('contract_end_date', '>=', now()->startOfDay())
                ->where('contract_end_date', '<=', now()->addDays(self::EXPIRING_DAYS)->endOfDay())
                ->count();
        }

        $totalSubscriptionPlans = SubscriptionPlan::count();

        return [
            'total_law_firms' => $totalLawFirms,
            'active_law_firms' => $activeLawFirms,
            'suspended_law_firms' => $suspendedLawFirms,
            'active_subscriptions' => $activeSubscriptions,
            'expiring_contracts' => $expiringContracts,
            'total_subscription_plans' => $totalSubscriptionPlans,
        ];
    }
}
