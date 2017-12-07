<?php

namespace Scrutiny;

use Scrutiny\Support\AvailabilityMonitorValidator;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ProbeManager::class);
    }

    public function boot()
    {
        $this->loadRoutes();
        $this->configureViews();
        $this->configureCache();
    }

    protected function loadRoutes()
    {
        if (method_exists($this, 'loadRoutesFrom')) {
            return $this->loadRoutesFrom(__DIR__.'/Http/routes.php');
        }

        require __DIR__.'/Http/routes.php';
    }

    protected function configureViews()
    {
        $this->loadViewsFrom(
            realpath(__DIR__.'/resources/views'),
            'scrutiny'
        );
    }

    protected function configureCache()
    {
        config([
            'cache.stores.scrutiny-file' => [
                'driver' => 'file',
                'path'   => storage_path('app/scrutiny'),
            ]
        ]);
    }
}
