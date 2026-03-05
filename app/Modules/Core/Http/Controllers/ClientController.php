<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * ALOS-S1-06 — Client Module Skeleton (CRUD + Team Access Placeholder).
 * ALOS-S1-07 — Client Team Access (Lead Lawyer + Assigned Users).
 */
class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $query = Client::query()->with('tenant')->orderByDesc('created_at');

        if ($request->filled('search')) {
            $term = $request->get('search');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            });
        }

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->get('tenant_id'));
        }

        $clients = $query->paginate($perPage)->withQueryString();

        $totalClients = Client::count();
        $recentClients = Client::where('created_at', '>=', now()->subDays(30))->count();

        return view('core::content.clients.index', [
            'clients' => $clients,
            'perPage' => $perPage,
            'totalClients' => $totalClients,
            'recentClients' => $recentClients,
            'filterTenantId' => $request->get('tenant_id', ''),
            'tenants' => Tenant::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $tenants = Tenant::orderBy('name')->get();
        return view('core::content.clients.create', ['tenants' => $tenants]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['nullable', 'integer', 'exists:tenants,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        Client::create($validated);

        return redirect()
            ->route('core.clients.index')
            ->with('success', __('Client created successfully.'));
    }

    public function show(Client $client): View
    {
        $client->load(['tenant', 'teamAccess', 'portalUser']);
        $assignableUsers = $client->tenant_id
            ? User::where('tenant_id', $client->tenant_id)->whereNull('client_id')->orderBy('name')->get()
            : User::whereNull('client_id')->orderBy('name')->get();
        $leadLawyer = $client->leadLawyer();
        $assignedUserIds = $client->teamAccess->pluck('id')->all();

        return view('core::content.clients.show', [
            'client' => $client,
            'assignableUsers' => $assignableUsers,
            'leadLawyer' => $leadLawyer,
            'assignedUserIds' => $assignedUserIds,
        ]);
    }

    public function edit(Client $client): View
    {
        $tenants = Tenant::orderBy('name')->get();
        return view('core::content.clients.edit', ['client' => $client, 'tenants' => $tenants]);
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['nullable', 'integer', 'exists:tenants,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $client->update($validated);

        return redirect()
            ->route('core.clients.index')
            ->with('success', __('Client updated successfully.'));
    }

    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();

        return redirect()
            ->route('core.clients.index')
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
            ->route('core.clients.show', [$client, 'tab' => 'team-access'])
            ->with('success', __('Team access updated successfully.'));
    }

    /**
     * ALOS-S1-08 — Create portal user for client (from Client Profile).
     */
    public function storePortalUser(Request $request, Client $client): RedirectResponse
    {
        if ($client->portalUser) {
            return redirect()
                ->route('core.clients.show', [$client, 'tab' => 'portal'])
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
            'portal_permission' => $validated['portal_permission'],
            'portal_active' => $request->boolean('portal_active', true),
        ]);
        $user->save();

        return redirect()
            ->route('core.clients.show', [$client, 'tab' => 'portal'])
            ->with('success', __('Portal account created successfully.'));
    }

    /**
     * ALOS-S1-08 — Update portal user (permission, status, optional password).
     */
    public function updatePortalUser(Request $request, Client $client): RedirectResponse
    {
        $portalUser = $client->portalUser;
        if (! $portalUser) {
            return redirect()
                ->route('core.clients.show', [$client, 'tab' => 'portal'])
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
            ->route('core.clients.show', [$client, 'tab' => 'portal'])
            ->with('success', __('Portal account updated successfully.'));
    }

    /**
     * ALOS-S1-08 — Toggle portal account active/inactive.
     */
    public function togglePortalStatus(Client $client): RedirectResponse
    {
        $portalUser = $client->portalUser;
        if (! $portalUser) {
            return redirect()
                ->route('core.clients.show', [$client, 'tab' => 'portal'])
                ->with('error', __('No portal account for this client.'));
        }

        $portalUser->portal_active = ! $portalUser->portal_active;
        $portalUser->save();

        $status = $portalUser->portal_active ? __('activated') : __('deactivated');
        return redirect()
            ->route('core.clients.show', [$client, 'tab' => 'portal'])
            ->with('success', __('Portal account :status.', ['status' => $status]));
    }
}
