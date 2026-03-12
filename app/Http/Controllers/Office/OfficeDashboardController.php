<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Services\DashboardSummaryService;
use Illuminate\View\View;

/**
 * ALOS-S1-27 — Internal Office Dashboard.
 * لوحة التيننت — مكتب المحاماة. يعرض المؤشرات والملخصات حسب صلاحيات المستخدم و tenant.
 */
class OfficeDashboardController extends Controller
{
    public function __invoke(): View
    {
        $pageConfigs = ['myLayout' => 'office', 'customizerHide' => true];
        $dashboardSummary = new DashboardSummaryService(auth()->user());
        $summary = $dashboardSummary->getSummary();
        $user = auth()->user();
        $hasFinanceModule = false;
        if ($user && $user->tenant_id && ! $user->client_id && $user->tenant) {
            $hasFinanceModule = app(\App\Services\PlanLimitService::class)->hasFeature($user->tenant, \App\Services\PlanLimitService::FEATURE_FINANCE_MODULE);
        }
        return view('office.dashboard', [
            'pageConfigs' => $pageConfigs,
            'summary' => $summary,
            'hasFinanceModule' => $hasFinanceModule,
        ]);
    }
}
