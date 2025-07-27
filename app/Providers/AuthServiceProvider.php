<?php

namespace App\Providers;
use Illuminate\Support\Facades\Gate; // Importamos Gate
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::define('manage-users', function ($user) {
            return $user->idRolUsu == 2; // Solo superusuario
        });
    }
}
