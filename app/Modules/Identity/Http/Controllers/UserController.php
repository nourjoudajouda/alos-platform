<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Identity\Module;
use Spatie\Permission\PermissionRegistrar;
use App\Services\PlanLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/**
 * ALOS-S1-05 — إدارة مستخدمي المكتب (Internal Users).
 * فقط مستخدمون من نفس الـ tenant؛ فقط admin أو managing_partner.
 */
class UserController extends Controller
{
    private function tenantId(): int
    {
        $id = (int) auth()->user()->tenant_id;
        if ($id === 0) {
            abort(403, __('You must belong to a tenant to manage internal users.'));
        }
        return $id;
    }

    private function ensureSameTenant(User $user): void
    {
        if ((int) $user->tenant_id !== $this->tenantId()) {
            abort(404);
        }
    }

    public function index(Request $request): View
    {
        $tenantId = $this->tenantId();
        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $query = User::query()
            ->where('tenant_id', $tenantId)
            ->whereNull('client_id')
            ->with('tenant')
            ->orderBy('name');

        if ($request->filled('search')) {
            $term = $request->get('search');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        $filterRole = $request->get('role');
        if ($filterRole && in_array($filterRole, Module::internalRoleNames(), true)) {
            $query->role($filterRole);
        }

        $users = $query->paginate($perPage)->withQueryString();

        $totalUsers = User::where('tenant_id', $tenantId)->whereNull('client_id')->count();

        $internalRoles = Role::whereIn('name', Module::internalRoleNames())
            ->orderBy('name')
            ->get();

        return view('identity::content.users.index', [
            'users' => $users,
            'perPage' => $perPage,
            'totalUsers' => $totalUsers,
            'internalRoles' => $internalRoles,
            'filterRole' => $filterRole ?? '',
        ]);
    }

    public function create(): View
    {
        $internalRoles = Role::whereIn('name', Module::internalRoleNames())
            ->orderBy('name')
            ->get();

        return view('identity::content.users.create', ['internalRoles' => $internalRoles]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $this->tenantId();
        $tenant = User::find(auth()->id())->tenant;
        if (! $tenant) {
            abort(403, __('You must belong to a tenant to manage internal users.'));
        }

        $limitService = app(PlanLimitService::class);
        $role = $request->input('role');
        try {
            $limitService->checkUserLimit($tenant);
            if ($role === 'lawyer') {
                $limitService->checkLawyerLimit($tenant);
            }
            if ($role === 'admin') {
                $limitService->checkAdminLimit($tenant);
            }
            if ($role === 'assistant') {
                $limitService->checkSecretaryLimit($tenant);
            }
            if ($role === 'finance') {
                $limitService->checkAccountantLimit($tenant);
            }
            if ($role === 'trainee') {
                $limitService->checkTraineeLimit($tenant);
            }
        } catch (\Throwable $e) {
            return redirect()
                ->route('identity.users.create')
                ->withInput()
                ->with('error', $e->getMessage());
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->where('tenant_id', $tenantId),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:' . implode(',', Module::internalRoleNames())],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'tenant_id' => $tenantId,
            'user_type' => User::USER_TYPE_TENANT_STAFF,
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($tenantId);
        $user->syncRoles([$validated['role']]);
        $limitService->invalidateUsageCache($tenant);

        return redirect()
            ->route('identity.users.index')
            ->with('success', __('User created successfully.'));
    }

    public function edit(User $user): View|RedirectResponse
    {
        $this->ensureSameTenant($user);
        if ($user->isClientPortalUser()) {
            abort(404);
        }

        $internalRoles = Role::whereIn('name', Module::internalRoleNames())
            ->orderBy('name')
            ->get();

        return view('identity::content.users.edit', [
            'user' => $user,
            'internalRoles' => $internalRoles,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureSameTenant($user);
        if ($user->isClientPortalUser()) {
            abort(404);
        }

        $tenantId = $this->tenantId();
        $tenant = $user->tenant;
        if (! $tenant) {
            abort(403);
        }

        $newRole = $request->input('role');
        $limitService = app(PlanLimitService::class);
        try {
            if ($newRole === 'lawyer') {
                $limitService->checkLawyerLimit($tenant, $user->id);
            }
            if ($newRole === 'admin') {
                $limitService->checkAdminLimit($tenant, $user->id);
            }
            if ($newRole === 'assistant') {
                $limitService->checkSecretaryLimit($tenant, $user->id);
            }
            if ($newRole === 'finance') {
                $limitService->checkAccountantLimit($tenant, $user->id);
            }
            if ($newRole === 'trainee') {
                $limitService->checkTraineeLimit($tenant, $user->id);
            }
        } catch (\Throwable $e) {
            return redirect()
                ->route('identity.users.edit', $user)
                ->withInput()
                ->with('error', $e->getMessage());
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->where('tenant_id', $tenantId)->ignore($user->id),
            ],
            'role' => ['required', 'string', 'in:' . implode(',', Module::internalRoleNames())],
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['string', 'min:8', 'confirmed'];
        }

        $validated = $request->validate($rules);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        app(PermissionRegistrar::class)->setPermissionsTeamId($this->tenantId());
        $user->syncRoles([$validated['role']]);
        app(PlanLimitService::class)->invalidateUsageCache($tenant);

        return redirect()
            ->route('identity.users.index')
            ->with('success', __('User updated successfully.'));
    }
}
