<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 25);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;

        $query = Permission::query()->orderBy('name');

        if ($request->filled('search')) {
            $term = $request->get('search');
            $query->where('name', 'like', "%{$term}%");
        }

        $permissions = $query->paginate($perPage)->withQueryString();

        $totalPermissions = Permission::count();
        $viewPermissions = Permission::where('name', 'like', 'view %')->count();
        $createPermissions = Permission::where('name', 'like', 'create %')->count();
        $editPermissions = Permission::where('name', 'like', 'edit %')->count();
        $deletePermissions = Permission::where('name', 'like', 'delete %')->count();
        $recentPermissions = Permission::where('created_at', '>=', now()->subDays(30))->count();

        return view('core::content.permissions.index', [
            'permissions' => $permissions,
            'perPage' => $perPage,
            'totalPermissions' => $totalPermissions,
            'viewPermissions' => $viewPermissions,
            'createPermissions' => $createPermissions,
            'editPermissions' => $editPermissions,
            'deletePermissions' => $deletePermissions,
            'recentPermissions' => $recentPermissions,
        ]);
    }

    public function create(): View
    {
        return view('core::content.permissions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        Permission::firstOrCreate([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        return redirect()
            ->route('core.permissions.index')
            ->with('success', __('Permission created successfully.'));
    }

    public function show(Permission $permission): View
    {
        $permission->load('roles');

        return view('core::content.permissions.show', ['permission' => $permission]);
    }

    public function edit(Permission $permission): View
    {
        return view('core::content.permissions.edit', ['permission' => $permission]);
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $permission->update(['name' => $validated['name']]);

        return redirect()
            ->route('core.permissions.index')
            ->with('success', __('Permission updated successfully.'));
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();

        return redirect()
            ->route('core.permissions.index')
            ->with('success', __('Permission deleted successfully.'));
    }
}
