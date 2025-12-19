<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        // Aqui você registra bindings/containers, configs, singletons, etc.
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // Aqui você faz bootstraps globais (macros, observers, timezone, etc.)
    }
}
