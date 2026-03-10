<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $query = Role::query()->withCount('permissions')->orderBy('name');

        if ($request->filled('search')) {
            $term = $request->get('search');
            $query->where('name', 'like', "%{$term}%");
        }

        $roles = $query->paginate($perPage)->withQueryString();

        $totalRoles = Role::count();
        $rolesWithPermissions = Role::whereHas('permissions')->count();
        $rolesWithoutPermissions = $totalRoles - $rolesWithPermissions;
        $recentRoles = Role::where('created_at', '>=', now()->subDays(30))->count();

        return view('core::content.roles.index', [
            'roles' => $roles,
            'perPage' => $perPage,
            'totalRoles' => $totalRoles,
            'rolesWithPermissions' => $rolesWithPermissions,
            'rolesWithoutPermissions' => $rolesWithoutPermissions,
            'recentRoles' => $recentRoles,
        ]);
    }

    public function create(): View
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(fn ($p) => explode(' ', $p->name)[0]);

        return view('core::content.roles.create', ['permissions' => $permissions]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);
        $role->syncPermissions($request->input('permissions', []));

        return redirect()
            ->route('admin.core.roles.index')
            ->with('success', __('Role created successfully.'));
    }

    public function show(Role $role): View
    {
        $role->load('permissions');

        return view('core::content.roles.show', ['role' => $role]);
    }

    public function edit(Role $role): View
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(fn ($p) => explode(' ', $p->name)[0]);
        $role->load('permissions');

        return view('core::content.roles.edit', [
            'role' => $role,
            'permissions' => $permissions,
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
        ]);

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($request->input('permissions', []));

        return redirect()
            ->route('admin.core.roles.index')
            ->with('success', __('Role updated successfully.'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        $systemRoles = config('roles.system_role_names', ['admin']);
        if (in_array($role->name, $systemRoles, true)) {
            return redirect()
                ->route('admin.core.roles.index')
                ->with('error', __('Cannot delete system role.'));
        }

        $role->delete();

        return redirect()
            ->route('admin.core.roles.index')
            ->with('success', __('Role deleted successfully.'));
    }
}
