<?php

namespace App\Modules\Core\Providers;

use App\Modules\Core\Module;
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
     * Bootstrap the Core module: view namespace only.
     * Core routes are loaded in routes/web.php under prefix('admin') so all admin panel URLs have /admin.
     */
    public function boot(): void
    {
        $this->registerViews();
    }

    private function registerViews(): void
    {
        $path = Module::viewsPath();
        if (is_dir($path)) {
            View::addNamespace(Module::viewsNamespace(), $path);
        }
    }
}
