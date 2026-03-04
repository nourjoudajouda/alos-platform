<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * CRUD Tenants — بنفس آلية Advocate SaaS Companies.
 */
class TenantController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $query = Tenant::query()->withCount('users')->orderByDesc('created_at');

        if ($request->filled('search')) {
            $term = $request->get('search');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', "%{$term}%");
            });
        }

        if ($request->filled('plan') && in_array($request->get('plan'), Tenant::PLANS, true)) {
            $query->where('plan', $request->get('plan'));
        }

        $statusFilter = $request->get('status');
        if ($statusFilter === 'active') {
            $query->has('users');
        } elseif ($statusFilter === 'pending') {
            $query->doesntHave('users');
        }

        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $tenants = $query->paginate($perPage)->withQueryString();

        $totalTenants = Tenant::count();
        $tenantsWithUsers = Tenant::has('users')->count();
        $recentTenants = Tenant::where('created_at', '>=', now()->subDays(30))->count();

        return view('core::content.tenants.index', [
            'tenants' => $tenants,
            'perPage' => $perPage,
            'totalTenants' => $totalTenants,
            'tenantsWithUsers' => $tenantsWithUsers,
            'recentTenants' => $recentTenants,
            'filterPlan' => $request->get('plan', ''),
            'filterStatus' => $request->get('status', ''),
            'filterDateFrom' => $dateFrom ?? '',
            'filterDateTo' => $dateTo ?? '',
        ]);
    }

    public function create(): View
    {
        return view('core::content.tenants.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:tenants,slug', 'regex:/^[a-z0-9\-]+$/'],
            'plan' => ['nullable', 'string', 'in:'.implode(',', Tenant::PLANS)],
        ]);

        Tenant::create($validated);

        return redirect()
            ->route('core.tenants.index')
            ->with('success', __('Tenant created successfully.'));
    }

    public function show(Tenant $tenant): View
    {
        return view('core::content.tenants.show', ['tenant' => $tenant]);
    }

    public function edit(Tenant $tenant): View
    {
        return view('core::content.tenants.edit', ['tenant' => $tenant]);
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:tenants,slug,' . $tenant->id, 'regex:/^[a-z0-9\-]+$/'],
            'plan' => ['nullable', 'string', 'in:'.implode(',', Tenant::PLANS)],
        ]);

        $tenant->update($validated);

        return redirect()
            ->route('core.tenants.index')
            ->with('success', __('Tenant updated successfully.'));
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        if ($tenant->users()->exists()) {
            return redirect()
                ->route('core.tenants.index')
                ->with('error', __('Cannot delete tenant with existing users.'));
        }

        $tenant->delete();

        return redirect()
            ->route('core.tenants.index')
            ->with('success', __('Tenant deleted successfully.'));
    }
}
