<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\CaseModel;
use App\Models\CaseSession;
use App\Models\InAppNotification;
use App\Models\User;
use App\Services\SystemSettingsService;
use App\Services\TenantContext;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
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
        // ALOS-S1-29 / ALOS-S1-29B — Usage warnings + feature flags for office (company) layout
        View::composer('core::layouts.officeLayout', function ($view) {
            $user = \Illuminate\Support\Facades\Auth::user();
            $warnings = [];
            $hasFinanceModule = false;
            if ($user && $user->tenant_id && ! $user->client_id) {
                $tenant = $user->tenant;
                if ($tenant) {
                    $limitService = app(\App\Services\PlanLimitService::class);
                    $warnings = $limitService->getUsageWarnings($tenant);
                    $hasFinanceModule = $limitService->hasFeature($tenant, \App\Services\PlanLimitService::FEATURE_FINANCE_MODULE);
                }
            }
            $view->with('usageWarnings', $warnings);
            $view->with('hasFinanceModule', $hasFinanceModule);
        });

        // ALOS-S1-30 — Global identity (system name, logo, favicon) for public/login/admin brand
        View::composer([
            'core::content.public.landing',
            'core::content.authentications.auth-login-basic',
            'core::layouts.sections.menu.verticalMenu',
        ], function ($view) {
            $systemName = config('app.name');
            $systemLogoUrl = null;
            $systemFaviconUrl = null;
            if (Schema::hasTable('system_settings')) {
                try {
                    $settings = app(SystemSettingsService::class);
                    $systemName = $settings->get('system_name', config('app.name'));
                    $systemLogoUrl = $settings->get('system_logo');
                    $systemFaviconUrl = $settings->get('favicon');
                } catch (\Throwable $e) {
                    // ignore
                }
            }
            $view->with('systemName', $systemName);
            $view->with('systemLogoUrl', $systemLogoUrl);
            $view->with('systemFaviconUrl', $systemFaviconUrl);
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
