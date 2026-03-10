<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
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

    public function show(Permission $permission): View
    {
        $permission->load('roles');

        return view('core::content.permissions.show', ['permission' => $permission]);
    }

}
