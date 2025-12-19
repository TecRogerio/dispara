<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // ✅ Gate para Admin (usado no middleware can:admin)
        Gate::define('admin', function ($user) {
            // Prioridade 1: coluna is_admin (int/bool)
            if (isset($user->is_admin) && (int) $user->is_admin === 1) {
                return true;
            }

            // Prioridade 2: coluna role = 'admin'
            if (isset($user->role) && (string) $user->role === 'admin') {
                return true;
            }

            return false;
        });
    }
}
