<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * ALOS-S1-31B — Platform Contracts overview.
 * Lists law firms (tenants) with their contract dates. Read-only; edit contract dates via Law Firms.
 */
class ContractController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50, 100], true) ? $perPage : 15;

        $hasContractEndDate = Schema::hasColumn('tenants', 'contract_end_date');

        $query = Tenant::query()->with('subscriptionPlan');

        if ($hasContractEndDate) {
            $query->orderByRaw('contract_end_date IS NULL, contract_end_date ASC');
        } else {
            $query->orderBy('name');
        }

        if ($request->filled('search')) {
            $term = $request->get('search');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', "%{$term}%");
            });
        }

        if ($hasContractEndDate && $request->filled('expired') && $request->get('expired') === '1') {
            $query->whereNotNull('contract_end_date')
                ->whereDate('contract_end_date', '<', now()->startOfDay());
        } elseif ($hasContractEndDate && $request->filled('expiring') && $request->get('expiring') === '1') {
            $query->whereNotNull('contract_end_date')
                ->where('contract_end_date', '>=', now()->startOfDay())
                ->where('contract_end_date', '<=', now()->addDays(30)->endOfDay());
        }

        $tenants = $query->paginate($perPage)->withQueryString();

        return view('core::content.contracts.index', [
            'tenants' => $tenants,
            'perPage' => $perPage,
            'hasContractEndDate' => $hasContractEndDate,
        ]);
    }
}
