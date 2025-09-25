<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::component('layouts.app', 'app-layout');
        Blade::component('layouts.auth', 'auth-layout');
        Blade::component('partials.auth-session-status', 'auth-session-status');
        // Registrar observer para modelos específicos
        \App\Models\User::observe(\App\Observers\ModelAuditObserver::class);
        \App\Models\Animal::observe(\App\Observers\ModelAuditObserver::class);
        \App\Models\Cliente::observe(\App\Observers\ModelAuditObserver::class);
        \App\Models\CompraGasto::observe(\App\Observers\ModelAuditObserver::class);
        \App\Models\Contacto::observe(\App\Observers\ModelAuditObserver::class);
        \App\Models\CuentaPendiente::observe(\App\Observers\ModelAuditObserver::class);
        \App\Models\DatabaseBackup::observe(\App\Observers\ModelAuditObserver::class);
        \App\Models\Direccion::observe(\App\Observers\ModelAuditObserver::class);
        \App\Models\Factura::observe(\App\Observers\ModelAuditObserver::class);
        \App\Models\Herramienta::observe(\App\Observers\ModelAuditObserver::class);
        \App\Models\HistorialMedico::observe(\App\Observers\ModelAuditObserver::class);
        \App\Models\Insumo::observe(\App\Observers\ModelAuditObserver::class);
        \App\Models\Inventario::observe(\App\Observers\ModelAuditObserver::class);
        \App\Models\Mantenimiento::observe(\App\Observers\ModelAuditObserver::class);
        \App\Models\MovimientoContable::observe(\App\Observers\ModelAuditObserver::class);
        \App\Models\Pago::observe(\App\Observers\ModelAuditObserver::class);
        \App\Models\PrestamoHerramienta::observe(\App\Observers\ModelAuditObserver::class);
        \App\Models\ProduccionAnimal::observe(\App\Observers\ModelAuditObserver::class);
        \App\Models\Proveedor::observe(\App\Observers\ModelAuditObserver::class);
        // Agrega otros modelos que necesites auditar
        // Ejemplo: Herramienta::observe(ModelAuditObserver::class);
        // Forzar HTTPS en desarrollo y producción
        if (config('app.env') !== 'local' || config('app.force_https')) {
            URL::forceScheme('https');
        }
    }
}
