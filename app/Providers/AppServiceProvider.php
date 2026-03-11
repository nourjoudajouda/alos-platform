<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\CaseModel;
use App\Models\CaseSession;
use App\Models\InAppNotification;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        class_alias(\App\Helpers\Helpers::class, 'Helper');

        $this->app->singleton(TenantContext::class, fn () => new TenantContext);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Platform admins bypass all permission checks
        Gate::before(function ($user, $ability) {
            if ($user instanceof Admin) {
                return true;
            }
        });

        View::addNamespace('portal', resource_path('views/portal'));

        // ALOS-S1-26 — Notifications for navbar (only for User, not Admin)
        View::composer('core::layouts.sections.navbar.navbar-partial', function ($view) {
            $current = \Illuminate\Support\Facades\Auth::guard('admin')->user() ?? \Illuminate\Support\Facades\Auth::user();
            $unread = 0;
            $recent = collect([]);
            if ($current instanceof User) {
                $unread = InAppNotification::forUser($current->id)->forTenant($current->tenant_id)->unread()->count();
                $recent = InAppNotification::forUser($current->id)->forTenant($current->tenant_id)->orderByDesc('created_at')->limit(8)->get();
            }
            $view->with('notificationUnreadCount', $unread);
            $view->with('notificationsRecent', $recent);
        });
        View::composer('portal::layouts.sections.portalNavbar', function ($view) {
            $user = \Illuminate\Support\Facades\Auth::user();
            $unread = 0;
            $recent = collect([]);
            if ($user instanceof User) {
                $unread = InAppNotification::forUser($user->id)->forTenant($user->tenant_id)->unread()->count();
                $recent = InAppNotification::forUser($user->id)->forTenant($user->tenant_id)->orderByDesc('created_at')->limit(6)->get();
            }
            $view->with('notificationUnreadCount', $unread);
            $view->with('notificationsRecent', $recent);
        });

        // Route model binding
        Route::bind('case', fn (string $value) => CaseModel::findOrFail($value));
        Route::bind('session', fn (string $value) => CaseSession::findOrFail($value));
    }
}
