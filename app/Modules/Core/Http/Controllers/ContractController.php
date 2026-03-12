<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
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
        $perPage = in_array($perPage, [10, 15, 25, 50], true) ? $perPage : 15;

        $query = Tenant::query()
            ->with('subscriptionPlan')
            ->orderByRaw('contract_end_date IS NULL, contract_end_date ASC');

        if ($request->filled('search')) {
            $term = $request->get('search');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', "%{$term}%");
            });
        }

        if ($request->filled('expiring')) {
            if ($request->get('expiring') === '1') {
                $query->whereNotNull('contract_end_date')
                    ->where('contract_end_date', '>=', now()->startOfDay())
                    ->where('contract_end_date', '<=', now()->addDays(30)->endOfDay());
            }
        }

        $tenants = $query->paginate($perPage)->withQueryString();

        return view('core::content.contracts.index', [
            'tenants' => $tenants,
            'perPage' => $perPage,
        ]);
    }
}
