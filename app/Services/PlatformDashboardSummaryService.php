<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use Illuminate\Support\Facades\Schema;

/**
 * ALOS-S1-31B — Platform Dashboard Summary Service.
 *
 * Provides platform-level metrics and lists for Super Admin only.
 * No tenant operational data (clients, cases, consultations, messages, documents).
 * Does not depend on tenant_id; summarizes the whole platform.
 */
class PlatformDashboardSummaryService
{
    /** Number of days ahead to consider a contract as "expiring soon" for the count. */
    public const EXPIRING_DAYS = 30;

    /** Number of days for "expiring soon" list (e.g. 7 or 14). */
    public const EXPIRING_SOON_DAYS = 14;

    /** Limit for recently registered firms. */
    public const RECENT_FIRMS_LIMIT = 10;

    /** Limit for expiring contracts list. */
    public const EXPIRING_LIST_LIMIT = 10;

    /** Limit for recent platform activity. */
    public const RECENT_ACTIVITY_LIMIT = 15;

    public function getSummary(): array
    {
        return [
            'metrics' => $this->getMetrics(),
            'recently_registered_firms' => $this->getRecentlyRegisteredFirms(),
            'expiring_contracts_list' => $this->getExpiringContractsList(),
            'expired_contracts_list' => $this->getExpiredContractsList(),
            'recent_platform_activity' => $this->getRecentPlatformActivity(),
        ];
    }

    /**
     * Platform-level metrics only.
     */
    public function getMetrics(): array
    {
        $totalLawFirms = Tenant::count();
        $activeLawFirms = Tenant::where('is_active', true)->count();
        $suspendedLawFirms = $totalLawFirms - $activeLawFirms;

        $activeSubscriptions = Tenant::where('is_active', true)
            ->whereNotNull('subscription_plan_id')
            ->count();

        $expiringContracts = 0;
        $expiredContracts = 0;
        if (Schema::hasColumn('tenants', 'contract_end_date')) {
            $expiringContracts = Tenant::where('is_active', true)
                ->whereNotNull('contract_end_date')
                ->where('contract_end_date', '>=', now()->startOfDay())
                ->where('contract_end_date', '<=', now()->addDays(self::EXPIRING_DAYS)->endOfDay())
                ->count();
            $expiredContracts = Tenant::whereNotNull('contract_end_date')
                ->whereDate('contract_end_date', '<', now()->startOfDay())
                ->count();
        }

        $totalSubscriptionPlans = SubscriptionPlan::count();

        return [
            'total_law_firms' => $totalLawFirms,
            'active_law_firms' => $activeLawFirms,
            'suspended_law_firms' => $suspendedLawFirms,
            'active_subscriptions' => $activeSubscriptions,
            'expiring_contracts' => $expiringContracts,
            'expired_contracts' => $expiredContracts,
            'total_subscription_plans' => $totalSubscriptionPlans,
        ];
    }

    /**
     * Latest created tenants (company name, email, created date, plan, status).
     */
    public function getRecentlyRegisteredFirms(): array
    {
        return Tenant::query()
            ->with('subscriptionPlan')
            ->orderByDesc('created_at')
            ->limit(self::RECENT_FIRMS_LIMIT)
            ->get()
            ->map(function (Tenant $t) {
                return [
                    'id' => $t->id,
                    'name' => $t->name ?? '—',
                    'email' => $t->email ?? '—',
                    'created_at' => $t->created_at?->format('Y-m-d H:i'),
                    'created_at_human' => $t->created_at?->diffForHumans(),
                    'plan_name' => $t->subscriptionPlan?->plan_name ?? 'N/A',
                    'status' => $t->is_active ? __('Active') : __('Suspended'),
                    'is_active' => (bool) $t->is_active,
                ];
            })
            ->all();
    }

    /**
     * Tenants whose contract_end_date is within EXPIRING_SOON_DAYS.
     */
    public function getExpiringContractsList(): array
    {
        if (! Schema::hasColumn('tenants', 'contract_end_date')) {
            return [];
        }

        return Tenant::query()
            ->with('subscriptionPlan')
            ->whereNotNull('contract_end_date')
            ->where('contract_end_date', '>=', now()->startOfDay())
            ->where('contract_end_date', '<=', now()->addDays(self::EXPIRING_SOON_DAYS)->endOfDay())
            ->whereNotIn('subscription_status', [Tenant::SUBSCRIPTION_STATUS_EXPIRED])
            ->orderBy('contract_end_date')
            ->limit(self::EXPIRING_LIST_LIMIT)
            ->get()
            ->map(function (Tenant $t) {
                return [
                    'id' => $t->id,
                    'name' => $t->name ?? '—',
                    'contract_end_date' => $t->contract_end_date?->format('Y-m-d'),
                    'contract_end_date_human' => $t->contract_end_date?->diffForHumans(),
                    'plan_name' => $t->subscriptionPlan?->plan_name ?? 'N/A',
                    'status' => $t->subscription_status ?? $t->status ?? 'active',
                    'is_active' => (bool) $t->is_active,
                ];
            })
            ->all();
    }

    /**
     * Tenants whose contract_end_date has passed (expired).
     */
    public function getExpiredContractsList(): array
    {
        if (! Schema::hasColumn('tenants', 'contract_end_date')) {
            return [];
        }

        return Tenant::query()
            ->with('subscriptionPlan')
            ->whereNotNull('contract_end_date')
            ->whereDate('contract_end_date', '<', now()->startOfDay())
            ->orderByDesc('contract_end_date')
            ->limit(self::EXPIRING_LIST_LIMIT)
            ->get()
            ->map(function (Tenant $t) {
                return [
                    'id' => $t->id,
                    'name' => $t->name ?? '—',
                    'contract_end_date' => $t->contract_end_date?->format('Y-m-d'),
                    'plan_name' => $t->subscriptionPlan?->plan_name ?? 'N/A',
                    'status' => $t->subscription_status ?? $t->status ?? 'expired',
                ];
            })
            ->all();
    }

    /**
     * Recent platform-level activity: admin login/logout, tenant create/delete, etc.
     * Uses audit_logs where tenant_id is null (admin actions) or entity_type = tenant.
     */
    public function getRecentPlatformActivity(): array
    {
        $query = AuditLog::query()
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(self::RECENT_ACTIVITY_LIMIT * 2);

        // Platform-level: admin actions (tenant_id null) or tenant entity changes
        $query->where(function ($q) {
            $q->whereNull('tenant_id')
                ->orWhere('entity_type', AuditLog::ENTITY_TENANT);
        });

        $logs = $query->get()->take(self::RECENT_ACTIVITY_LIMIT);

        return $logs->map(function (AuditLog $log) {
            $userName = $log->user?->name ?? __('Platform Admin');
            $action = $log->action;
            $entity = $log->entity_type;

            return [
                'id' => $log->id,
                'user_name' => $userName,
                'action' => $action,
                'entity_type' => $entity,
                'entity_id' => $log->entity_id,
                'created_at' => $log->created_at?->toIso8601String(),
                'created_at_human' => $log->created_at?->diffForHumans(),
                'description' => $this->activityDescription($log),
            ];
        })->values()->all();
    }

    private function activityDescription(AuditLog $log): string
    {
        $action = $log->action;
        $entity = $log->entity_type;
        $key = "dashboard.activity.{$entity}.{$action}";
        $fallback = ucfirst(str_replace('_', ' ', $action)) . ' ' . str_replace('_', ' ', $entity);
        return __($key) !== $key ? __($key) : $fallback;
    }
}
