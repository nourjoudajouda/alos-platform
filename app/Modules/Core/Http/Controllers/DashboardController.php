<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\PlatformDashboardSummaryService;
use Illuminate\Contracts\View\View;

/**
 * ALOS-S1-31B — Platform (Super Admin) Dashboard.
 * Shows only platform-level metrics: law firms, subscriptions, contracts. No tenant operational data.
 */
class DashboardController extends Controller
{
    public function __invoke(PlatformDashboardSummaryService $platformSummary): View
    {
        $summary = $platformSummary->getSummary();
        return view('core::dashboard', compact('summary'));
    }
}
