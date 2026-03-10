<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.login');
            }
            if ($request->is('company') || $request->is('company/*')) {
                return route('login');
            }
            return route('login');
        });
        // عند دخول مستخدم مسجّل على /login أو /register يوجّه للوحة التيننت؛ على /admin يوجّه للوحة الأدمن
        $middleware->redirectUsersTo(function (Request $request) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.core.dashboard');
            }
            if ($request->is('company') || $request->is('company/*')) {
                return route('company.dashboard');
            }
            return route('company.dashboard');
        });
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'not_client_portal' => \App\Http\Middleware\EnsureNotClientPortalUser::class,
            'portal_client' => \App\Http\Middleware\EnsurePortalClient::class,
            'admin_or_tenant' => \App\Http\Middleware\EnsureAdminOrTenantUser::class,
            'tenant_user' => \App\Http\Middleware\EnsureTenantUser::class,
            'tenant_staff' => \App\Http\Middleware\EnsureTenantStaff::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
