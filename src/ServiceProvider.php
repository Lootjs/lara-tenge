<?php

namespace Loot\Tenge;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/tenge.php' => config_path('tenge.php'),
        ]);
        $this->mergeConfigFrom(__DIR__.'/../config/tenge.php', 'tenge');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->publishes([
            __DIR__.'/../assets/' => storage_path('tenge'),
        ], 'secrets');
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->app->singleton('tenge_logger', function () {
            return (new Logger)->getManager();
        });
    }
}
