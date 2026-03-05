<?php

namespace App\Modules\Identity\Providers;

use App\Modules\Identity\Module;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

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
