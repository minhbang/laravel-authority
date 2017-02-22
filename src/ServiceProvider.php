<?php

namespace Minhbang\Authority;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Foundation\AliasLoader;
use MenuManager;
/**
 * Class ServiceProvider
 *
 * @package Minhbang\Authority
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'authority');
        $this->loadViewsFrom(__DIR__ . '/../views', 'authority');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        $this->publishes([
            __DIR__ . '/../views'                => base_path('resources/views/vendor/authority'),
            __DIR__ . '/../lang'                 => base_path('resources/lang/vendor/authority'),
            __DIR__ . '/../config/authority.php' => config_path('authority.php'),
        ]);
        // Add user menus
        MenuManager::addItems(config('authority.menus'));
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/authority.php', 'authority');

        $this->app->singleton('authority', function () {
            return new Manager();
        });
        $this->app->booting(function () {
            AliasLoader::getInstance()->alias('Authority', Facade::class);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['authority'];
    }
}
