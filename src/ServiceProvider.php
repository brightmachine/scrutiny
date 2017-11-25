<?php

namespace Scrutiny;

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
}
