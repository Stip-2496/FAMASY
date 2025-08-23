<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Auth\Events\Logout as LogoutEvent;

class Logout
{
    public function __invoke()
    {
        $user = Auth::user();
        
        Auth::guard('web')->logout();

     

        Session::invalidate();
        Session::regenerateToken();

        return redirect('login');
    }
}