<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $this->validate();
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
};
?>


<div class="w-full min-h-screen bg-white flex items-center justify-center px-4">
    <div class="bg-gray-300/70 backdrop-blur-lg p-6 rounded-xl shadow-2xl w-full max-w-md border border-gray-400">
        
  
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Inicio de Sesión</h2>
        </div>

        <x-auth-session-status class="mb-2 text-center text-sm text-green-600" :status="session('status')" />

        <form wire:submit="login" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-800 mb-1">Correo Electrónico</label>
                <input wire:model="email" type="email" required
                    class="w-full px-3 py-2 bg-white border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900"
                    placeholder="ejemplo@correo.com">
            </div>

  
            <div>
                <label class="block text-sm font-medium text-gray-800 mb-1">Contraseña</label>
                <input wire:model="password" type="password" required
                    class="w-full px-3 py-2 bg-white border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900">
            </div>


            <div class="flex items-center gap-2">
                <input wire:model="remember" type="checkbox"
                    class="h-4 w-4 text-green-600 border-gray-400 rounded">
                <label class="text-sm text-gray-800">Recordarme</label>
            </div>


            <div>
                <button type="submit"
                    class="w-full py-2 bg-green-600 text-white rounded-md font-semibold hover:bg-green-700 transition duration-150">
                    Ingresar
                </button>
            </div>
        </form>


        <div class="mt-4 text-center text-sm text-gray-800">
            ¿Olvidaste tu contraseña?
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-green-600 hover:underline">Recupérala aquí</a>
            @endif
        </div>

        <div class="text-center text-sm text-gray-800 mt-1">
            ¿No tienes cuenta?
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="text-green-600 hover:underline">¡Regístrate!</a>
            @endif
        </div>
    </div>
</div>
