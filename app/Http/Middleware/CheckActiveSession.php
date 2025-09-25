<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CheckActiveSession
{
    public function handle($request, Closure $next)
    {
        // Verificar si el usuario está autenticado
        if (Auth::check()) {
            $user = Auth::user();
            
            // Actualizar last_login_at si la sesión es válida
            if (session()->isStarted() && !empty(session()->get('auth'))) {
                // Actualizar la fecha de último login si ha pasado más de 5 minutos
                if (!$user->last_login_at || $user->last_login_at->diffInMinutes(now()) > 5) {
                    $user->last_login_at = now();
                    $user->save();
                }
                return $next($request);
            }
            
            // Si la sesión no está activa o está corrupta
            Auth::logout(); // Cerrar sesión
            session()->invalidate(); // Invalidar la sesión
            return redirect()->route('login')->withErrors([
                'message' => 'La sesión ha expirado. Por favor, inicia sesión nuevamente.'
            ]);
        }

        return $next($request);
    }
}