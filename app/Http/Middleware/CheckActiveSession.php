<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckActiveSession
{
    public function handle($request, Closure $next)
    {
        // Verificar si el usuario está autenticado
        if (Auth::check()) {
            // Si la sesión no está activa o está corrupta
            if (!session()->isStarted() || empty(session()->get('auth'))) {
                Auth::logout(); // Cerrar sesión
                session()->invalidate(); // Invalidar la sesión
                return redirect()->route('login')->withErrors([
                    'message' => 'La sesión ha expirado. Por favor, inicia sesión nuevamente.'
                ]);
            }
        }

        return $next($request);
    }
}