<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Services\PlanLimitService;
use Illuminate\View\View;

/**
 * ALOS-S1-29 — Subscription info in Settings: plan name, usage (users, lawyers, storage), end date, warnings.
 */
class SubscriptionSettingsController extends Controller
{
    public function show(): View
    {
        $user = auth()->user();
        $tenant = $user->tenant;
        abort_unless($tenant, 403);

        $tenant->load('subscriptionPlan');
        $limitService = app(PlanLimitService::class);
        $limitService->refreshSubscriptionStatusIfExpired($tenant);

        $plan = $tenant->subscriptionPlan;
        $planName = $plan ? $plan->plan_name : __('Default');
        $userLimit = $limitService->getUserLimit($tenant);
        $userCount = $limitService->getCurrentUserCount($tenant);
        $lawyerLimit = $limitService->getLawyerLimit($tenant);
        $lawyerCount = $limitService->getCurrentLawyerCount($tenant, null);
        $storageLimitMb = $limitService->getStorageLimitMb($tenant);
        $storageUsedMb = $limitService->getCurrentStorageUsedMb($tenant);
        $storageLimitGb = $storageLimitMb > 0 ? round($storageLimitMb / 1024, 2) : 0;
        $storageUsedGb = round($storageUsedMb / 1024, 2);
        $adminLimit = $limitService->getMaxAdmins($tenant);
        $adminCount = $limitService->getCurrentAdminCount($tenant, null);
        $secretaryLimit = $limitService->getMaxSecretaries($tenant);
        $secretaryCount = $limitService->getCurrentSecretaryCount($tenant, null);
        $accountantLimit = $limitService->getMaxAccountants($tenant);
        $accountantCount = $limitService->getCurrentAccountantCount($tenant, null);
        $traineeLimit = $limitService->getMaxTrainees($tenant);
        $traineeCount = $limitService->getCurrentTraineeCount($tenant, null);
        $clientLimit = $limitService->getMaxClients($tenant);
        $clientCount = $limitService->getCurrentClientCount($tenant);
        $caseLimit = $limitService->getMaxCases($tenant);
        $caseCount = $limitService->getCurrentCaseCount($tenant);
        $documentLimit = $limitService->getMaxDocuments($tenant);
        $documentCount = $limitService->getCurrentDocumentCount($tenant);
        $contractStartDate = $tenant->contract_start_date ?? $tenant->start_date ?? null;
        $contractEndDate = $tenant->contract_end_date ?? $tenant->end_date ?? null;
        $subscriptionStatus = $tenant->subscription_status ?? $tenant->status ?? 'active';
        $billingCycle = $tenant->billing_cycle ?? null;
        $planPrice = $tenant->plan_price ?? ($plan ? $plan->price : null);
        $warnings = $limitService->getUsageWarnings($tenant);
        $canWrite = $limitService->canPerformWriteOperations($tenant);

        $pageConfigs = ['myLayout' => 'office', 'customizerHide' => true];

        return view('office.settings.subscription', [
            'pageConfigs' => $pageConfigs,
            'tenant' => $tenant,
            'planName' => $planName,
            'userCount' => $userCount,
            'userLimit' => $userLimit,
            'lawyerCount' => $lawyerCount,
            'lawyerLimit' => $lawyerLimit,
            'adminCount' => $adminCount,
            'adminLimit' => $adminLimit,
            'secretaryCount' => $secretaryCount,
            'secretaryLimit' => $secretaryLimit,
            'accountantCount' => $accountantCount,
            'accountantLimit' => $accountantLimit,
            'traineeCount' => $traineeCount,
            'traineeLimit' => $traineeLimit,
            'storageUsedMb' => $storageUsedMb,
            'storageLimitMb' => $storageLimitMb,
            'storageUsedGb' => $storageUsedGb,
            'storageLimitGb' => $storageLimitGb,
            'clientCount' => $clientCount,
            'clientLimit' => $clientLimit,
            'caseCount' => $caseCount,
            'caseLimit' => $caseLimit,
            'documentCount' => $documentCount,
            'documentLimit' => $documentLimit,
            'contractStartDate' => $contractStartDate,
            'contractEndDate' => $contractEndDate,
            'billingCycle' => $billingCycle,
            'planPrice' => $planPrice,
            'subscriptionStatus' => $subscriptionStatus,
            'warnings' => $warnings,
            'canWrite' => $canWrite,
        ]);
    }
}
