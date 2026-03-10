<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\CaseModel;
use App\Models\CaseSession;
use App\Services\TenantContext;
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
        // Platform admins bypass all permission checks
        Gate::before(function ($user, $ability) {
            if ($user instanceof Admin) {
                return true;
            }
        });

        View::addNamespace('portal', resource_path('views/portal'));

        // Route model binding
        Route::bind('case', fn (string $value) => CaseModel::findOrFail($value));
        Route::bind('session', fn (string $value) => CaseSession::findOrFail($value));
    }
}
