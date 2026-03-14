<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

/**
 * لوحة التيننت — يضمن أن مستخدم المكتب لديه دور واحد على الأقل.
 * بدون دور، لا يمرّ فحص Spatie permission ويحدث 403.
 * يتم تعيين دور "assistant" كافتراضي للمستخدمين بدون أدوار.
 * مع Spatie teams: tenant_id يُمرّر كـ team.
 */
class EnsureTenantStaffHasRole
{
    private const FALLBACK_ROLE = 'assistant';

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (! $user instanceof User || ! $user->isTenantStaff()) {
            return $next($request);
        }

        if ($user->roles()->count() > 0) {
            return $next($request);
        }

        $role = Role::where('name', self::FALLBACK_ROLE)->where('guard_name', 'web')->first();
        if ($role && $user->tenant_id) {
            app(PermissionRegistrar::class)->setPermissionsTeamId($user->tenant_id);
            $user->assignRole($role);
        }

        return $next($request);
    }
}
