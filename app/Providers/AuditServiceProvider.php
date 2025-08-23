<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Event;
use App\Models\Auditoria;

class AuditServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Eventos de autenticación
        Event::listen(Login::class, function ($event) {
            $this->logAuthEvent('LOGIN', $event->user, 'Inicio de sesión exitoso');
        });

        Event::listen(Logout::class, function ($event) {
            $this->logAuthEvent('LOGOUT', $event->user, 'Cierre de sesión');
        });

        Event::listen(Failed::class, function ($event) {
            $this->logFailedLogin($event);
        });

        Event::listen(Lockout::class, function ($event) {
            $this->logLockout($event);
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
        Auditoria::create([
            'usuAud' => $event->credentials['email'],
            'rolAud' => 'N/A',
            'opeAud' => 'LOGIN_FAILED',
            'tablaAud' => 'users',
            'desAud' => 'Intento fallido de inicio de sesión',
            'ipAud' => request()->ip()
        ]);
    }

    protected function logLockout($event)
    {
        Auditoria::create([
            'usuAud' => $event->request->email ?? 'Desconocido',
            'rolAud' => 'N/A',
            'opeAud' => 'LOGIN_FAILED',
            'tablaAud' => 'users',
            'desAud' => 'Bloqueo por demasiados intentos fallidos',
            'ipAud' => request()->ip()
        ]);
    }
}