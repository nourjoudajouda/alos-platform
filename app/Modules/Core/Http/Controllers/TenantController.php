<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
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
        $subscriptionPlans = SubscriptionPlan::orderBy('price')->get();
        return view('core::content.tenants.create', ['subscriptionPlans' => $subscriptionPlans]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:tenants,slug', 'regex:/^[a-z0-9\-]+$/'],
            'username' => ['nullable', 'string', 'min:3', 'max:64', 'regex:/^[a-zA-Z0-9_-]+$/', 'unique:tenants,username'],
            'domain' => ['nullable', 'string', 'max:255'],
            'plan' => ['nullable', 'string', 'in:'.implode(',', Tenant::PLANS)],
            'subscription_plan_id' => ['nullable', 'integer', 'exists:subscription_plans,id'],
            'subscription_status' => ['nullable', 'string', 'in:active,suspended,expired,trial'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
            'public_site_enabled' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:2000'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:64'],
            'city' => ['nullable', 'string', 'max:128'],
        ]);

        if (!empty($validated['username'])) {
            $validated['domain'] = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::slug(trim($validated['username']), '-'));
        } else {
            $validated['username'] = null;
        }
        if (!isset($validated['domain'])) {
            $validated['domain'] = null;
        }
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['public_site_enabled'] = $request->boolean('public_site_enabled', true);
        $validated['subscription_plan_id'] = $validated['subscription_plan_id'] ?? null;
        $validated['subscription_status'] = $validated['subscription_status'] ?? 'active';
        $validated['start_date'] = $validated['start_date'] ?? null;
        $validated['end_date'] = $validated['end_date'] ?? null;
        $validated['logo'] = $validated['logo'] ?? null;
        $validated['description'] = $validated['description'] ?? null;
        $validated['email'] = $validated['email'] ?? null;
        $validated['phone'] = $validated['phone'] ?? null;
        $validated['city'] = $validated['city'] ?? null;
        $tenant = Tenant::create($validated);
        App::make(AuditLogService::class)->recordAudit(AuditLog::ACTION_CREATE_TENANT, AuditLog::ENTITY_TENANT, $tenant->id, [], [], $tenant->id);

        return redirect()
            ->route('admin.core.tenants.index')
            ->with('success', __('Tenant created successfully.'));
    }

    public function show(Tenant $tenant): View
    {
        $tenant->loadCount(['users', 'clients']);
        $settings = $tenant->getSettingsOrCreate();
        return view('core::content.tenants.show', [
            'tenant' => $tenant,
            'settings' => $settings,
        ]);
    }

    public function edit(Tenant $tenant): View
    {
        $subscriptionPlans = SubscriptionPlan::orderBy('price')->get();
        return view('core::content.tenants.edit', [
            'tenant' => $tenant,
            'subscriptionPlans' => $subscriptionPlans,
        ]);
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:tenants,slug,' . $tenant->id, 'regex:/^[a-z0-9\-]+$/'],
            'username' => ['nullable', 'string', 'min:3', 'max:64', 'regex:/^[a-zA-Z0-9_-]+$/', 'unique:tenants,username,' . $tenant->id],
            'domain' => ['nullable', 'string', 'max:255'],
            'plan' => ['nullable', 'string', 'in:'.implode(',', Tenant::PLANS)],
            'subscription_plan_id' => ['nullable', 'integer', 'exists:subscription_plans,id'],
            'subscription_status' => ['nullable', 'string', 'in:active,suspended,expired,trial'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'contract_start_date' => ['nullable', 'date'],
            'contract_end_date' => ['nullable', 'date', 'after_or_equal:contract_start_date'],
            'billing_cycle' => ['nullable', 'string', 'in:monthly,yearly'],
            'plan_price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'public_site_enabled' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:2000'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:64'],
            'city' => ['nullable', 'string', 'max:128'],
        ]);

        if (!empty($validated['username'])) {
            $validated['domain'] = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::slug(trim($validated['username']), '-'));
        }
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['public_site_enabled'] = $request->boolean('public_site_enabled', true);
        $validated['subscription_plan_id'] = $validated['subscription_plan_id'] ?? null;
        $validated['subscription_status'] = $validated['subscription_status'] ?? 'active';
        $validated['start_date'] = $validated['start_date'] ?? null;
        $validated['end_date'] = $validated['end_date'] ?? null;
        $validated['contract_start_date'] = $validated['contract_start_date'] ?? null;
        $validated['contract_end_date'] = $validated['contract_end_date'] ?? null;
        $validated['billing_cycle'] = $validated['billing_cycle'] ?? null;
        $validated['plan_price'] = $validated['plan_price'] ?? null;
        $validated['logo'] = $validated['logo'] ?? null;
        $validated['description'] = $validated['description'] ?? null;
        $validated['email'] = $validated['email'] ?? null;
        $validated['phone'] = $validated['phone'] ?? null;
        $validated['city'] = $validated['city'] ?? null;
        $tenant->update($validated);

        return redirect()
            ->route('admin.core.tenants.index')
            ->with('success', __('Tenant updated successfully.'));
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        if ($tenant->users()->exists()) {
            return redirect()
                ->route('admin.core.tenants.index')
                ->with('error', __('Cannot delete tenant with existing users.'));
        }

        $oldValues = $tenant->only(['name', 'slug', 'domain']);
        App::make(AuditLogService::class)->recordAudit(AuditLog::ACTION_DELETE, AuditLog::ENTITY_TENANT, $tenant->id, $oldValues, [], $tenant->id);
        $tenant->delete();

        return redirect()
            ->route('admin.core.tenants.index')
            ->with('success', __('Tenant deleted successfully.'));
    }
}
