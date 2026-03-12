<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Document;
use App\Models\Tenant;
use App\Models\User;
use App\Models\CaseModel;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

/**
 * ALOS-S1-29 — Plan limits enforcement: user limit, lawyer limit, storage limit, subscription status.
 * All checks are scoped by tenant_id; no tenant can affect another tenant's plan.
 */
class PlanLimitService
{
    public const SUBSCRIPTION_STATUS_ACTIVE = 'active';
    public const SUBSCRIPTION_STATUS_SUSPENDED = 'suspended';
    public const SUBSCRIPTION_STATUS_EXPIRED = 'expired';
    public const SUBSCRIPTION_STATUS_TRIAL = 'trial';

    public const LIMIT_EXCEEDED_MESSAGE = 'You have reached your plan limit. Please upgrade your subscription.';
    public const SUBSCRIPTION_EXPIRED_MESSAGE = 'Your subscription has expired. Please renew to continue.';
    public const SUBSCRIPTION_SUSPENDED_MESSAGE = 'Your subscription is suspended. Please contact support.';
    public const FEATURE_DISABLED_MESSAGE = 'This feature is not available in your subscription plan.';

    /** Known plan feature keys (stored in features_json). */
    public const FEATURE_CASE_MANAGEMENT = 'case_management';
    public const FEATURE_CLIENT_PORTAL = 'client_portal';
    public const FEATURE_INTERNAL_CHAT = 'internal_chat';
    public const FEATURE_CALENDAR = 'calendar';
    public const FEATURE_REPORTS = 'reports';
    public const FEATURE_FINANCE_MODULE = 'finance_module';
    public const FEATURE_HR_MODULE = 'hr_module';
    public const FEATURE_MARKETPLACE = 'marketplace';
    public const FEATURE_ADVANCED_SECURITY = 'advanced_security';
    public const FEATURE_API_ACCESS = 'api_access';
    public const FEATURE_CUSTOM_INTEGRATIONS = 'custom_integrations';

    /** Keys for extra limits stored in features_json['limits'] (0 = unlimited). */
    public const LIMIT_MAX_ADMINS = 'max_admins';
    public const LIMIT_MAX_SECRETARIES = 'max_secretaries';
    public const LIMIT_MAX_ACCOUNTANTS = 'max_accountants';
    public const LIMIT_MAX_TRAINEES = 'max_trainees';
    public const LIMIT_MAX_CLIENTS = 'max_clients';
    public const LIMIT_MAX_CASES = 'max_cases';
    public const LIMIT_MAX_DOCUMENTS = 'max_documents';

    /** Cache TTL for usage stats (seconds). */
    private const USAGE_CACHE_TTL = 300;

    /**
     * Get a limit from plan's features_json['limits'][$key]. Returns 0 for unlimited.
     */
    public function getLimitFromPlan(Tenant $tenant, string $key): int
    {
        $plan = $tenant->subscriptionPlan;
        if (! $plan || ! is_array($plan->features_json)) {
            return 0;
        }
        $limits = $plan->features_json['limits'] ?? null;
        if (! is_array($limits) || ! array_key_exists($key, $limits)) {
            return 0;
        }
        return (int) $limits[$key];
    }

    /**
     * Whether the tenant's plan has the given feature enabled (from features_json).
     * If no plan or feature not set/truthy, returns false.
     */
    public function hasFeature(Tenant $tenant, string $feature): bool
    {
        $plan = $tenant->subscriptionPlan;
        if (! $plan || ! is_array($plan->features_json)) {
            return false;
        }
        return ! empty($plan->features_json[$feature]);
    }

    /**
     * Ensure the tenant's plan has the feature; throws otherwise.
     */
    public function ensureFeature(Tenant $tenant, string $feature): void
    {
        $this->ensureWriteAllowed($tenant);
        if (! $this->hasFeature($tenant, $feature)) {
            throw new \RuntimeException(self::FEATURE_DISABLED_MESSAGE);
        }
    }

    /**
     * Whether the tenant can perform write operations (add data, upload, send messages).
     */
    public function canPerformWriteOperations(Tenant $tenant): bool
    {
        $this->refreshSubscriptionStatusIfExpired($tenant);
        return in_array($tenant->subscription_status ?? $tenant->status ?? 'active', [
            self::SUBSCRIPTION_STATUS_ACTIVE,
            self::SUBSCRIPTION_STATUS_TRIAL,
        ], true);
    }

    /**
     * ALOS-S1-29B — Can the tenant create a new lawyer? (write allowed + under lawyer limit)
     */
    public function canCreateLawyer(Tenant $tenant, ?int $excludeUserId = null): bool
    {
        if (! $this->canPerformWriteOperations($tenant)) {
            return false;
        }
        $limit = $this->getLawyerLimit($tenant);
        if ($limit <= 0) {
            return true;
        }
        return $this->getCurrentLawyerCount($tenant, $excludeUserId) < $limit;
    }

    /**
     * ALOS-S1-29B — Can the tenant create a new admin?
     */
    public function canCreateAdmin(Tenant $tenant, ?int $excludeUserId = null): bool
    {
        if (! $this->canPerformWriteOperations($tenant)) {
            return false;
        }
        $limit = $this->getLimitFromPlan($tenant, self::LIMIT_MAX_ADMINS);
        if ($limit <= 0) {
            return true;
        }
        return $this->getCurrentAdminCount($tenant, $excludeUserId) < $limit;
    }

    /**
     * ALOS-S1-29B — Can the tenant create a new client?
     */
    public function canCreateClient(Tenant $tenant): bool
    {
        if (! $this->canPerformWriteOperations($tenant)) {
            return false;
        }
        $limit = $this->getLimitFromPlan($tenant, self::LIMIT_MAX_CLIENTS);
        if ($limit <= 0) {
            return true;
        }
        return $this->getCurrentClientCount($tenant) < $limit;
    }

    /**
     * ALOS-S1-29B — Can the tenant create a new case?
     */
    public function canCreateCase(Tenant $tenant): bool
    {
        if (! $this->canPerformWriteOperations($tenant)) {
            return false;
        }
        $limit = $this->getLimitFromPlan($tenant, self::LIMIT_MAX_CASES);
        if ($limit <= 0) {
            return true;
        }
        return $this->getCurrentCaseCount($tenant) < $limit;
    }

    /**
     * ALOS-S1-29B — Can the tenant upload a new document? (document count + storage limit)
     */
    public function canUploadDocument(Tenant $tenant, int $additionalBytes = 0): bool
    {
        if (! $this->canPerformWriteOperations($tenant)) {
            return false;
        }
        $docLimit = $this->getLimitFromPlan($tenant, self::LIMIT_MAX_DOCUMENTS);
        if ($docLimit > 0 && $this->getCurrentDocumentCount($tenant) >= $docLimit) {
            return false;
        }
        $limitBytes = $this->getStorageLimitBytes($tenant);
        $currentBytes = $this->getCurrentStorageUsedBytes($tenant);
        return $currentBytes + $additionalBytes <= $limitBytes;
    }

    /**
     * ALOS-S1-29B — Can the tenant create a new document? (same as canUploadDocument for count)
     */
    public function canCreateDocument(Tenant $tenant): bool
    {
        return $this->canUploadDocument($tenant, 0);
    }

    /**
     * Refresh subscription_status to expired when end_date < today.
     */
    public function refreshSubscriptionStatusIfExpired(Tenant $tenant): void
    {
        $status = $tenant->subscription_status ?? $tenant->status ?? 'active';
        if ($status === self::SUBSCRIPTION_STATUS_EXPIRED) {
            return;
        }
        $endDate = $tenant->contract_end_date ?? $tenant->end_date ?? $tenant->subscription_end_date ?? null;
        if ($endDate && $endDate->isPast()) {
            $tenant->update(['subscription_status' => self::SUBSCRIPTION_STATUS_EXPIRED]);
        }
    }

    /**
     * Check user limit before adding an internal user. Throws on failure. 0 = unlimited.
     */
    public function checkUserLimit(Tenant $tenant): void
    {
        $this->ensureWriteAllowed($tenant);
        $limit = $this->getUserLimit($tenant);
        if ($limit <= 0) {
            return;
        }
        $current = $this->getCurrentUserCount($tenant);
        if ($current >= $limit) {
            throw new \RuntimeException(self::LIMIT_EXCEEDED_MESSAGE);
        }
    }

    /**
     * Check lawyer limit before assigning lawyer role. Throws on failure. 0 = unlimited.
     */
    public function checkLawyerLimit(Tenant $tenant, ?int $excludeUserId = null): void
    {
        $this->ensureWriteAllowed($tenant);
        $limit = $this->getLawyerLimit($tenant);
        if ($limit <= 0) {
            return;
        }
        $current = $this->getCurrentLawyerCount($tenant, $excludeUserId);
        if ($current >= $limit) {
            throw new \RuntimeException(self::LIMIT_EXCEEDED_MESSAGE);
        }
    }

    /**
     * Check admin limit (role admin). 0 = unlimited.
     */
    public function checkAdminLimit(Tenant $tenant, ?int $excludeUserId = null): void
    {
        $this->ensureWriteAllowed($tenant);
        $limit = $this->getLimitFromPlan($tenant, self::LIMIT_MAX_ADMINS);
        if ($limit <= 0) {
            return;
        }
        $current = $this->getCurrentAdminCount($tenant, $excludeUserId);
        if ($current >= $limit) {
            throw new \RuntimeException(self::LIMIT_EXCEEDED_MESSAGE);
        }
    }

    /**
     * Check secretary (assistant) limit. 0 = unlimited.
     */
    public function checkSecretaryLimit(Tenant $tenant, ?int $excludeUserId = null): void
    {
        $this->ensureWriteAllowed($tenant);
        $limit = $this->getLimitFromPlan($tenant, self::LIMIT_MAX_SECRETARIES);
        if ($limit <= 0) {
            return;
        }
        $current = $this->getCurrentSecretaryCount($tenant, $excludeUserId);
        if ($current >= $limit) {
            throw new \RuntimeException(self::LIMIT_EXCEEDED_MESSAGE);
        }
    }

    /**
     * Check client limit. 0 = unlimited.
     */
    public function checkClientLimit(Tenant $tenant): void
    {
        $this->ensureWriteAllowed($tenant);
        $limit = $this->getLimitFromPlan($tenant, self::LIMIT_MAX_CLIENTS);
        if ($limit <= 0) {
            return;
        }
        $current = $this->getCurrentClientCount($tenant);
        if ($current >= $limit) {
            throw new \RuntimeException(self::LIMIT_EXCEEDED_MESSAGE);
        }
    }

    /**
     * Check case limit. 0 = unlimited.
     */
    public function checkCaseLimit(Tenant $tenant): void
    {
        $this->ensureWriteAllowed($tenant);
        $limit = $this->getLimitFromPlan($tenant, self::LIMIT_MAX_CASES);
        if ($limit <= 0) {
            return;
        }
        $current = $this->getCurrentCaseCount($tenant);
        if ($current >= $limit) {
            throw new \RuntimeException(self::LIMIT_EXCEEDED_MESSAGE);
        }
    }

    /**
     * Check accountant (finance role) limit. 0 = unlimited.
     */
    public function checkAccountantLimit(Tenant $tenant, ?int $excludeUserId = null): void
    {
        $this->ensureWriteAllowed($tenant);
        $limit = $this->getLimitFromPlan($tenant, self::LIMIT_MAX_ACCOUNTANTS);
        if ($limit <= 0) {
            return;
        }
        $current = $this->getCurrentAccountantCount($tenant, $excludeUserId);
        if ($current >= $limit) {
            throw new \RuntimeException(self::LIMIT_EXCEEDED_MESSAGE);
        }
    }

    /**
     * Check trainee limit. 0 = unlimited.
     */
    public function checkTraineeLimit(Tenant $tenant, ?int $excludeUserId = null): void
    {
        $this->ensureWriteAllowed($tenant);
        $limit = $this->getLimitFromPlan($tenant, self::LIMIT_MAX_TRAINEES);
        if ($limit <= 0) {
            return;
        }
        $current = $this->getCurrentTraineeCount($tenant, $excludeUserId);
        if ($current >= $limit) {
            throw new \RuntimeException(self::LIMIT_EXCEEDED_MESSAGE);
        }
    }

    /**
     * Check document count limit before uploading. 0 = unlimited.
     */
    public function checkDocumentLimit(Tenant $tenant): void
    {
        $this->ensureWriteAllowed($tenant);
        $limit = $this->getLimitFromPlan($tenant, self::LIMIT_MAX_DOCUMENTS);
        if ($limit <= 0) {
            return;
        }
        $current = $this->getCurrentDocumentCount($tenant);
        if ($current >= $limit) {
            throw new \RuntimeException(self::LIMIT_EXCEEDED_MESSAGE);
        }
    }

    /**
     * Check storage limit before uploading. $additionalBytes = size of file to add. Throws on failure.
     */
    public function checkStorageLimit(Tenant $tenant, int $additionalBytes = 0): void
    {
        $this->ensureWriteAllowed($tenant);
        $limitBytes = $this->getStorageLimitBytes($tenant);
        $currentBytes = $this->getCurrentStorageUsedBytes($tenant);
        if ($currentBytes + $additionalBytes > $limitBytes) {
            throw new \RuntimeException(self::LIMIT_EXCEEDED_MESSAGE);
        }
    }

    public function ensureWriteAllowed(Tenant $tenant): void
    {
        $this->refreshSubscriptionStatusIfExpired($tenant);
        if (! $this->canPerformWriteOperations($tenant)) {
            $status = $tenant->subscription_status ?? $tenant->status ?? 'active';
            if ($status === self::SUBSCRIPTION_STATUS_EXPIRED) {
                throw new \RuntimeException(self::SUBSCRIPTION_EXPIRED_MESSAGE);
            }
            if ($status === self::SUBSCRIPTION_STATUS_SUSPENDED) {
                throw new \RuntimeException(self::SUBSCRIPTION_SUSPENDED_MESSAGE);
            }
            throw new \RuntimeException(self::SUBSCRIPTION_EXPIRED_MESSAGE);
        }
    }

    public function getUserLimit(Tenant $tenant): int
    {
        $plan = $tenant->subscriptionPlan;
        if ($plan) {
            return (int) $plan->user_limit;
        }
        return (int) ($tenant->user_limit ?? 10);
    }

    /** 0 = unlimited. */
    public function getLawyerLimit(Tenant $tenant): int
    {
        $plan = $tenant->subscriptionPlan;
        if ($plan) {
            return (int) $plan->lawyer_limit;
        }
        return (int) ($tenant->lawyer_limit ?? 5);
    }

    /** 0 = unlimited. */
    public function getMaxAdmins(Tenant $tenant): int
    {
        return $this->getLimitFromPlan($tenant, self::LIMIT_MAX_ADMINS);
    }

    /** 0 = unlimited. */
    public function getMaxSecretaries(Tenant $tenant): int
    {
        return $this->getLimitFromPlan($tenant, self::LIMIT_MAX_SECRETARIES);
    }

    /** 0 = unlimited. */
    public function getMaxClients(Tenant $tenant): int
    {
        return $this->getLimitFromPlan($tenant, self::LIMIT_MAX_CLIENTS);
    }

    /** 0 = unlimited. */
    public function getMaxCases(Tenant $tenant): int
    {
        return $this->getLimitFromPlan($tenant, self::LIMIT_MAX_CASES);
    }

    /** 0 = unlimited. */
    public function getMaxAccountants(Tenant $tenant): int
    {
        return $this->getLimitFromPlan($tenant, self::LIMIT_MAX_ACCOUNTANTS);
    }

    /** 0 = unlimited. */
    public function getMaxTrainees(Tenant $tenant): int
    {
        return $this->getLimitFromPlan($tenant, self::LIMIT_MAX_TRAINEES);
    }

    /** 0 = unlimited. */
    public function getMaxDocuments(Tenant $tenant): int
    {
        return $this->getLimitFromPlan($tenant, self::LIMIT_MAX_DOCUMENTS);
    }

    /** Storage limit in MB. */
    public function getStorageLimitMb(Tenant $tenant): int
    {
        $plan = $tenant->subscriptionPlan;
        if ($plan) {
            return (int) $plan->storage_limit;
        }
        return (int) ($tenant->storage_limit ?? 1024);
    }

    /** Storage limit in GB (for display / storage_limit_gb). */
    public function getStorageLimitGb(Tenant $tenant): float
    {
        return round($this->getStorageLimitMb($tenant) / 1024, 2);
    }

    public function getStorageLimitBytes(Tenant $tenant): int
    {
        $mb = $this->getStorageLimitMb($tenant);
        return $mb <= 0 ? PHP_INT_MAX : $mb * 1024 * 1024;
    }

    public function getCurrentUserCount(Tenant $tenant): int
    {
        $cacheKey = 'plan_limit:users:' . $tenant->id;
        return (int) Cache::remember($cacheKey, self::USAGE_CACHE_TTL, function () use ($tenant) {
            return User::where('tenant_id', $tenant->id)->whereNull('client_id')->count();
        });
    }

    public function getCurrentAdminCount(Tenant $tenant, ?int $excludeUserId = null): int
    {
        return $this->getCurrentCountByRole($tenant, 'admin', 'plan_limit:admins:', $excludeUserId);
    }

    public function getCurrentSecretaryCount(Tenant $tenant, ?int $excludeUserId = null): int
    {
        return $this->getCurrentCountByRole($tenant, 'assistant', 'plan_limit:secretaries:', $excludeUserId);
    }

    public function getCurrentClientCount(Tenant $tenant): int
    {
        $cacheKey = 'plan_limit:clients:' . $tenant->id;
        return (int) Cache::remember($cacheKey, self::USAGE_CACHE_TTL, function () use ($tenant) {
            return Client::where('tenant_id', $tenant->id)->count();
        });
    }

    public function getCurrentCaseCount(Tenant $tenant): int
    {
        $cacheKey = 'plan_limit:cases:' . $tenant->id;
        return (int) Cache::remember($cacheKey, self::USAGE_CACHE_TTL, function () use ($tenant) {
            return CaseModel::where('tenant_id', $tenant->id)->count();
        });
    }

    public function getCurrentAccountantCount(Tenant $tenant, ?int $excludeUserId = null): int
    {
        return $this->getCurrentCountByRole($tenant, 'finance', 'plan_limit:accountants:', $excludeUserId);
    }

    public function getCurrentTraineeCount(Tenant $tenant, ?int $excludeUserId = null): int
    {
        return $this->getCurrentCountByRole($tenant, 'trainee', 'plan_limit:trainees:', $excludeUserId);
    }

    public function getCurrentDocumentCount(Tenant $tenant): int
    {
        $cacheKey = 'plan_limit:documents:' . $tenant->id;
        return (int) Cache::remember($cacheKey, self::USAGE_CACHE_TTL, function () use ($tenant) {
            return Document::where('tenant_id', $tenant->id)->count();
        });
    }

    public function getCurrentLawyerCount(Tenant $tenant, ?int $excludeUserId = null): int
    {
        return $this->getCurrentCountByRole($tenant, 'lawyer', 'plan_limit:lawyers:', $excludeUserId);
    }

    /**
     * Count internal users with the given role. Returns 0 if the role does not exist (e.g. not seeded).
     */
    private function getCurrentCountByRole(Tenant $tenant, string $roleName, string $cacheKeyPrefix, ?int $excludeUserId = null): int
    {
        $cacheKey = $cacheKeyPrefix . $tenant->id . ':' . ($excludeUserId ?? 0);
        return (int) Cache::remember($cacheKey, self::USAGE_CACHE_TTL, function () use ($tenant, $roleName, $excludeUserId) {
            try {
                $query = User::where('tenant_id', $tenant->id)->whereNull('client_id')->role($roleName);
                if ($excludeUserId !== null) {
                    $query->where('id', '!=', $excludeUserId);
                }
                return $query->count();
            } catch (RoleDoesNotExist $e) {
                return 0;
            }
        });
    }

    public function getCurrentStorageUsedBytes(Tenant $tenant): int
    {
        $cacheKey = 'plan_limit:storage:' . $tenant->id;
        return (int) Cache::remember($cacheKey, self::USAGE_CACHE_TTL, function () use ($tenant) {
            return (int) Document::where('tenant_id', $tenant->id)->sum('file_size');
        });
    }

    public function getCurrentStorageUsedMb(Tenant $tenant): float
    {
        return round($this->getCurrentStorageUsedBytes($tenant) / (1024 * 1024), 2);
    }

    /**
     * Invalidate usage cache for a tenant (call after adding/removing user, changing role, uploading/deleting document).
     */
    public function invalidateUsageCache(Tenant $tenant): void
    {
        Cache::forget('plan_limit:users:' . $tenant->id);
        Cache::forget('plan_limit:storage:' . $tenant->id);
        Cache::forget('plan_limit:clients:' . $tenant->id);
        Cache::forget('plan_limit:cases:' . $tenant->id);
        Cache::forget('plan_limit:documents:' . $tenant->id);
        foreach ([null, 0] as $exclude) {
            Cache::forget('plan_limit:lawyers:' . $tenant->id . ':' . $exclude);
            Cache::forget('plan_limit:admins:' . $tenant->id . ':' . $exclude);
            Cache::forget('plan_limit:secretaries:' . $tenant->id . ':' . $exclude);
            Cache::forget('plan_limit:accountants:' . $tenant->id . ':' . $exclude);
            Cache::forget('plan_limit:trainees:' . $tenant->id . ':' . $exclude);
        }
    }

    /**
     * Whether usage is at or above 90% for warning (users, lawyers, or storage).
     */
    public function getUsageWarnings(Tenant $tenant): array
    {
        $warnings = [];
        $userLimit = $this->getUserLimit($tenant);
        $userCount = $this->getCurrentUserCount($tenant);
        if ($userLimit > 0 && $userCount >= $userLimit * 0.9) {
            $warnings['users'] = [
                'current' => $userCount,
                'limit' => $userLimit,
                'percent' => round($userCount / $userLimit * 100, 1),
            ];
        }
        $lawyerLimit = $this->getLawyerLimit($tenant);
        $lawyerCount = $this->getCurrentLawyerCount($tenant, null);
        if ($lawyerLimit > 0 && $lawyerCount >= $lawyerLimit * 0.9) {
            $warnings['lawyers'] = [
                'current' => $lawyerCount,
                'limit' => $lawyerLimit,
                'percent' => round($lawyerCount / $lawyerLimit * 100, 1),
            ];
        }
        $storageLimitMb = $this->getStorageLimitMb($tenant);
        $storageUsedMb = $this->getCurrentStorageUsedMb($tenant);
        if ($storageLimitMb > 0 && $storageUsedMb >= $storageLimitMb * 0.9) {
            $warnings['storage'] = [
                'current_mb' => $storageUsedMb,
                'limit_mb' => $storageLimitMb,
                'percent' => round($storageUsedMb / $storageLimitMb * 100, 1),
            ];
        }
        $clientLimit = $this->getMaxClients($tenant);
        $clientCount = $this->getCurrentClientCount($tenant);
        if ($clientLimit > 0 && $clientCount >= $clientLimit * 0.9) {
            $warnings['clients'] = [
                'current' => $clientCount,
                'limit' => $clientLimit,
                'percent' => round($clientCount / $clientLimit * 100, 1),
            ];
        }
        $caseLimit = $this->getMaxCases($tenant);
        $caseCount = $this->getCurrentCaseCount($tenant);
        if ($caseLimit > 0 && $caseCount >= $caseLimit * 0.9) {
            $warnings['cases'] = [
                'current' => $caseCount,
                'limit' => $caseLimit,
                'percent' => round($caseCount / $caseLimit * 100, 1),
            ];
        }
        $accountantLimit = $this->getMaxAccountants($tenant);
        $accountantCount = $this->getCurrentAccountantCount($tenant, null);
        if ($accountantLimit > 0 && $accountantCount >= $accountantLimit * 0.9) {
            $warnings['accountants'] = [
                'current' => $accountantCount,
                'limit' => $accountantLimit,
                'percent' => round($accountantCount / $accountantLimit * 100, 1),
            ];
        }
        $traineeLimit = $this->getMaxTrainees($tenant);
        $traineeCount = $this->getCurrentTraineeCount($tenant, null);
        if ($traineeLimit > 0 && $traineeCount >= $traineeLimit * 0.9) {
            $warnings['trainees'] = [
                'current' => $traineeCount,
                'limit' => $traineeLimit,
                'percent' => round($traineeCount / $traineeLimit * 100, 1),
            ];
        }
        $documentLimit = $this->getMaxDocuments($tenant);
        $documentCount = $this->getCurrentDocumentCount($tenant);
        if ($documentLimit > 0 && $documentCount >= $documentLimit * 0.9) {
            $warnings['documents'] = [
                'current' => $documentCount,
                'limit' => $documentLimit,
                'percent' => round($documentCount / $documentLimit * 100, 1),
            ];
        }
        return $warnings;
    }
}
