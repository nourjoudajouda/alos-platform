<?php

namespace App\Modules\Core\Providers;

use App\Modules\Core\Module;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap the Core module: routes + view namespace.
     */
    public function boot(): void
    {
        $this->loadRoutes();
        $this->registerViews();
    }

    private function loadRoutes(): void
    {
        $path = Module::routesPath();
        if (is_file($path)) {
            Route::middleware('web')->group($path);
        }
    }

    private function registerViews(): void
    {
        $path = Module::viewsPath();
        if (is_dir($path)) {
            View::addNamespace(Module::viewsNamespace(), $path);
        }
    }
}
