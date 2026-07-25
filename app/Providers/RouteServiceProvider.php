<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Nwidart\Modules\Facades\Module;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        foreach (Module::allEnabled() as $module) {
            $routeFile = $module->getPath() . '/Routes/api.php';
            if (file_exists($routeFile)) {
                Route::prefix('api')
                    ->middleware(['api', 'auth:sanctum'])
                    ->group($routeFile);
            }
        }
    }
}
