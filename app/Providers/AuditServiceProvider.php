<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Event;
use App\Models\Auditoria;
use Illuminate\Support\Facades\Auth;
use App\Models\User; 

class AuditServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Registrar eventos de autenticación
        Event::listen(Login::class, function ($event) {
            $this->logAuthEvent('LOGIN', $event->user, 'Inicio de sesión exitoso');
        });

        Event::listen(Logout::class, function ($event) {
            $this->logAuthEvent('LOGOUT', $event->user, 'Cierre de sesión');
        });

        Event::listen(Failed::class, function ($event) {
            $this->logFailedLogin($event);
        });
    }

    protected function logAuthEvent($operation, $user, $description)
    {
        Auditoria::create([
            'idUsuAud' => $user->id,
            'usuAud' => $user->nomUsu . ' ' . $user->apeUsu,
            'rolAud' => $user->rol->nomRol,
            'opeAud' => $operation,
            'tablaAud' => 'users',
            'regAud' => $user->id,
            'desAud' => $description,
            'ipAud' => request()->ip()
        ]);
    }

    protected function logFailedLogin($event)
    {
        $user = User::where('email', $event->credentials['email'])->first();

        Auditoria::create([
            'usuAud' => $event->credentials['email'],
            'rolAud' => 'N/A',
            'opeAud' => 'LOGIN_FAILED',
            'tablaAud' => 'users',
            'desAud' => 'Intento fallido de inicio de sesión',
            'ipAud' => request()->ip()
        ]);
    }
}