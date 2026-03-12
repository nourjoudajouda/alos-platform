<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\App;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * ALOS-S1-06 — Client Module Skeleton (CRUD + Team Access Placeholder).
 * ALOS-S1-07 — Client Team Access (Lead Lawyer + Assigned Users).
 * ALOS-S1-31B — When used under company.* routes: tenant-scoped, no company picker.
 */
class ClientController extends Controller
{
    protected function isCompanyContext(): bool
    {
        return str_starts_with(request()->route()->getName() ?? '', 'company.');
    }

    protected function clientRoutePrefix(): string
    {
        return $this->isCompanyContext() ? 'company.clients' : 'admin.core.clients';
    }

    protected function companyPageConfigs(): array
    {
        return $this->isCompanyContext() ? ['myLayout' => 'office', 'customizerHide' => true] : [];
    }

    public function index(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $query = Client::query()->with('tenant')->orderByDesc('created_at');

        if (! auth()->user() instanceof Admin) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }

        if ($request->filled('search')) {
            $term = $request->get('search');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            });
        }

        if (! $this->isCompanyContext() && $request->filled('tenant_id')) {
            $query->where('tenant_id', $request->get('tenant_id'));
        }

        $clients = $query->paginate($perPage)->withQueryString();

        $baseQuery = Client::query();
        if (! auth()->user() instanceof Admin) {
            $baseQuery->where('tenant_id', auth()->user()->tenant_id);
        }
        $totalClients = $baseQuery->count();
        $recentClients = (clone $baseQuery)->where('created_at', '>=', now()->subDays(30))->count();

        $tenants = $this->isCompanyContext() ? collect() : Tenant::orderBy('name')->get();

        return view('core::content.clients.index', [
            'clients' => $clients,
            'perPage' => $perPage,
            'totalClients' => $totalClients,
            'recentClients' => $recentClients,
            'filterTenantId' => $request->get('tenant_id', ''),
            'tenants' => $tenants,
            'clientRoutePrefix' => $this->clientRoutePrefix(),
            'pageConfigs' => $this->companyPageConfigs(),
        ]);
    }

    public function create(): View
    {
        $tenants = $this->isCompanyContext() ? collect() : Tenant::orderBy('name')->get();
        return view('core::content.clients.create', [
            'tenants' => $tenants,
            'clientRoutePrefix' => $this->clientRoutePrefix(),
            'pageConfigs' => $this->companyPageConfigs(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ];
        if (! $this->isCompanyContext()) {
            $rules['tenant_id'] = ['nullable', 'integer', 'exists:tenants,id'];
        }
        $validated = $request->validate($rules);

        if ($this->isCompanyContext()) {
            $validated['tenant_id'] = auth()->user()->tenant_id;
        } else {
            $validated['tenant_id'] = $validated['tenant_id'] ?? null;
        }

        $tenantId = $validated['tenant_id'] ?? null;
        if ($tenantId) {
            $tenant = Tenant::find($tenantId);
            if ($tenant) {
                try {
                    app(\App\Services\PlanLimitService::class)->checkClientLimit($tenant);
                } catch (\RuntimeException $e) {
                    return redirect()->route($this->clientRoutePrefix() . '.create')->withInput()->with('error', $e->getMessage());
                }
            }
        }

        $client = Client::create($validated);
        if ($client->tenant_id) {
            $t = Tenant::find($client->tenant_id);
            if ($t) {
                app(\App\Services\PlanLimitService::class)->invalidateUsageCache($t);
            }
        }
        App::make(AuditLogService::class)->recordAudit(AuditLog::ACTION_CREATE_CLIENT, AuditLog::ENTITY_CLIENT, $client->id, [], [], $client->tenant_id);

        return redirect()
            ->route($this->clientRoutePrefix() . '.index')
            ->with('success', __('Client created successfully.'));
    }

    public function show(Client $client): View
    {
        $user = auth()->user();
        if ($this->isCompanyContext() && $client->tenant_id !== $user->tenant_id) {
            abort(404, __('Not found.'));
        }
        $isAdmin = $user instanceof Admin;
        $userHasClientAccess = $isAdmin
            || (! $user->isClientPortalUser() && $client->teamAccess()->where('user_id', $user->id)->exists());
        if (! $userHasClientAccess) {
            App::make(AuditLogService::class)->recordCompliance('access_client', __('Attempted access to client outside team.'), 'client', $client->id, $client->tenant_id);
            abort(404, __('Not found.'));
        }

        $client->load(['tenant', 'teamAccess', 'portalUser']);
        $assignableUsers = $client->tenant_id
            ? User::where('tenant_id', $client->tenant_id)->whereNull('client_id')->orderBy('name')->get()
            : User::whereNull('client_id')->orderBy('name')->get();
        $leadLawyer = $client->leadLawyer();
        $assignedUserIds = $client->teamAccess->pluck('id')->all();

        $clientCases = $userHasClientAccess
            ? $client->cases()->with('responsibleLawyer')->orderByDesc('updated_at')->get()
            : collect();
        $clientConsultations = ($userHasClientAccess && auth()->user()->can('consultations.view'))
            ? $client->consultations()->with('responsibleUser')->orderByDesc('consultation_date')->orderByDesc('updated_at')->get()
            : collect();

        $caseRoutePrefix = $this->isCompanyContext() ? 'company.cases' : 'admin.core.cases';
        $consultationRoutePrefix = $this->isCompanyContext() ? 'company.consultations' : 'admin.core.consultations';

        return view('core::content.clients.show', [
            'client' => $client,
            'assignableUsers' => $assignableUsers,
            'leadLawyer' => $leadLawyer,
            'assignedUserIds' => $assignedUserIds,
            'userHasClientAccess' => $userHasClientAccess,
            'clientCases' => $clientCases,
            'clientConsultations' => $clientConsultations,
            'clientRoutePrefix' => $this->clientRoutePrefix(),
            'caseRoutePrefix' => $caseRoutePrefix,
            'consultationRoutePrefix' => $consultationRoutePrefix,
            'pageConfigs' => $this->companyPageConfigs(),
        ]);
    }

    public function edit(Client $client): View
    {
        if ($this->isCompanyContext() && $client->tenant_id !== auth()->user()->tenant_id) {
            abort(404, __('Not found.'));
        }
        $tenants = $this->isCompanyContext() ? collect() : Tenant::orderBy('name')->get();
        return view('core::content.clients.edit', [
            'client' => $client,
            'tenants' => $tenants,
            'clientRoutePrefix' => $this->clientRoutePrefix(),
            'pageConfigs' => $this->companyPageConfigs(),
        ]);
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        if ($this->isCompanyContext() && $client->tenant_id !== auth()->user()->tenant_id) {
            abort(404, __('Not found.'));
        }
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ];
        if (! $this->isCompanyContext()) {
            $rules['tenant_id'] = ['nullable', 'integer', 'exists:tenants,id'];
        }
        $validated = $request->validate($rules);
        if ($this->isCompanyContext()) {
            $validated['tenant_id'] = $client->tenant_id;
        } else {
            $validated['tenant_id'] = $validated['tenant_id'] ?? null;
        }

        $client->update($validated);
        App::make(AuditLogService::class)->recordAudit(AuditLog::ACTION_UPDATE_CLIENT, AuditLog::ENTITY_CLIENT, $client->id, [], [], $client->tenant_id);

        return redirect()
            ->route($this->clientRoutePrefix() . '.index')
            ->with('success', __('Client updated successfully.'));
    }

    public function destroy(Client $client): RedirectResponse
    {
        if ($this->isCompanyContext() && $client->tenant_id !== auth()->user()->tenant_id) {
            abort(404, __('Not found.'));
        }
        $tenantId = $client->tenant_id;
        $oldValues = $client->only(['name', 'email', 'tenant_id']);
        App::make(AuditLogService::class)->recordAudit(AuditLog::ACTION_DELETE, AuditLog::ENTITY_CLIENT, $client->id, $oldValues, [], $tenantId);
        $client->delete();
        if ($tenantId) {
            $t = Tenant::find($tenantId);
            if ($t) {
                app(\App\Services\PlanLimitService::class)->invalidateUsageCache($t);
            }
        }

        return redirect()
            ->route($this->clientRoutePrefix() . '.index')
            ->with('success', __('Client deleted successfully.'));
    }

    /**
     * ALOS-S1-07 — Update client team access (lead lawyer + assigned users).
     */
    public function updateTeamAccess(Request $request, Client $client): RedirectResponse
    {
        // Normalize Select2: empty string or empty array => null so validation passes
        $leadInput = $request->input('lead_lawyer_id');
        if ($leadInput === '' || $leadInput === []) {
            $request->merge(['lead_lawyer_id' => null]);
        } elseif (is_array($leadInput)) {
            $request->merge(['lead_lawyer_id' => ! empty($leadInput) ? (int) reset($leadInput) : null]);
        }

        $validated = $request->validate([
            'lead_lawyer_id' => ['nullable', 'integer', 'exists:users,id'],
            'assigned_user_ids' => ['nullable', 'array'],
            'assigned_user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $leadId = isset($validated['lead_lawyer_id']) && $validated['lead_lawyer_id'] !== '' ? (int) $validated['lead_lawyer_id'] : null;
        $assignedIds = $validated['assigned_user_ids'] ?? [];
        $assignedIds = array_unique(array_map('intval', $assignedIds));

        $sync = [];
        if ($leadId) {
            $sync[$leadId] = ['role' => Client::TEAM_ROLE_LEAD_LAWYER];
        }
        foreach ($assignedIds as $userId) {
            if ($userId === $leadId) {
                continue;
            }
            $sync[$userId] = ['role' => Client::TEAM_ROLE_LAWYER];
        }

        $client->teamAccess()->sync($sync);

        return redirect()
            ->route($this->clientRoutePrefix() . '.show', [$client, 'tab' => 'team-access'])
            ->with('success', __('Team access updated successfully.'));
    }

    /**
     * ALOS-S1-08 — Create portal user for client (from Client Profile).
     */
    public function storePortalUser(Request $request, Client $client): RedirectResponse
    {
        $tenant = $client->tenant;
        if ($tenant) {
            try {
                app(\App\Services\PlanLimitService::class)->ensureFeature($tenant, \App\Services\PlanLimitService::FEATURE_CLIENT_PORTAL);
            } catch (\RuntimeException $e) {
                return redirect()->route($this->clientRoutePrefix() . '.show', [$client, 'tab' => 'portal'])->with('error', $e->getMessage());
            }
        }
        if ($client->portalUser) {
            return redirect()
                ->route($this->clientRoutePrefix() . '.show', [$client, 'tab' => 'portal'])
                ->with('error', __('This client already has a portal account.'));
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'portal_permission' => ['required', Rule::in([
                User::PORTAL_PERMISSION_VIEW_ONLY,
                User::PORTAL_PERMISSION_MESSAGING,
                User::PORTAL_PERMISSION_MESSAGING_UPLOAD,
            ])],
            'portal_active' => ['nullable', 'boolean'],
        ]);

        $user = new User([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'tenant_id' => $client->tenant_id ?? 1,
            'client_id' => $client->id,
            'user_type' => User::USER_TYPE_CLIENT,
            'portal_permission' => $validated['portal_permission'],
            'portal_active' => $request->boolean('portal_active', true),
        ]);
        $user->save();

        return redirect()
            ->route($this->clientRoutePrefix() . '.show', [$client, 'tab' => 'portal'])
            ->with('success', __('Portal account created successfully.'));
    }

    /**
     * ALOS-S1-08 — Update portal user (permission, status, optional password).
     */
    public function updatePortalUser(Request $request, Client $client): RedirectResponse
    {
        $tenant = $client->tenant;
        if ($tenant) {
            try {
                app(\App\Services\PlanLimitService::class)->ensureFeature($tenant, \App\Services\PlanLimitService::FEATURE_CLIENT_PORTAL);
            } catch (\RuntimeException $e) {
                return redirect()->route($this->clientRoutePrefix() . '.show', [$client, 'tab' => 'portal'])->with('error', $e->getMessage());
            }
        }
        $portalUser = $client->portalUser;
        if (! $portalUser) {
            return redirect()
                ->route($this->clientRoutePrefix() . '.show', [$client, 'tab' => 'portal'])
                ->with('error', __('No portal account for this client.'));
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($portalUser->id)],
            'portal_permission' => ['required', Rule::in([
                User::PORTAL_PERMISSION_VIEW_ONLY,
                User::PORTAL_PERMISSION_MESSAGING,
                User::PORTAL_PERMISSION_MESSAGING_UPLOAD,
            ])],
            'portal_active' => ['nullable', 'boolean'],
        ];
        $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
        $validated = $request->validate($rules);

        $portalUser->name = $validated['name'];
        $portalUser->email = $validated['email'];
        $portalUser->portal_permission = $validated['portal_permission'];
        $portalUser->portal_active = $request->boolean('portal_active');
        if (! empty($validated['password'])) {
            $portalUser->password = Hash::make($validated['password']);
        }
        $portalUser->save();

        return redirect()
            ->route($this->clientRoutePrefix() . '.show', [$client, 'tab' => 'portal'])
            ->with('success', __('Portal account updated successfully.'));
    }

    /**
     * ALOS-S1-08 — Toggle portal account active/inactive.
     */
    public function togglePortalStatus(Client $client): RedirectResponse
    {
        $tenant = $client->tenant;
        if ($tenant) {
            try {
                app(\App\Services\PlanLimitService::class)->ensureFeature($tenant, \App\Services\PlanLimitService::FEATURE_CLIENT_PORTAL);
            } catch (\RuntimeException $e) {
                return redirect()->route($this->clientRoutePrefix() . '.show', [$client, 'tab' => 'portal'])->with('error', $e->getMessage());
            }
        }
        $portalUser = $client->portalUser;
        if (! $portalUser) {
            return redirect()
                ->route($this->clientRoutePrefix() . '.show', [$client, 'tab' => 'portal'])
                ->with('error', __('No portal account for this client.'));
        }

        $portalUser->portal_active = ! $portalUser->portal_active;
        $portalUser->save();

        $status = $portalUser->portal_active ? __('activated') : __('deactivated');
        return redirect()
            ->route($this->clientRoutePrefix() . '.show', [$client, 'tab' => 'portal'])
            ->with('success', __('Portal account :status.', ['status' => $status]));
    }
}
