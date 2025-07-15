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

        $this->redirectIntended(default: route('home', absolute: false), navigate: true);
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


<div class="bg-gray-100 flex items-center justify-center p-4 min-h-screen">
    <div class="w-full max-w-md bg-white shadow rounded-lg p-8 border border-gray-300">
        <!-- Encabezado -->
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Inicio de Sesión</h2>
            <p class="text-sm text-gray-500">Ingresa tus credenciales para acceder</p>
        </div>

        <!-- Estado de sesión -->
        <x-auth-session-status class="mb-4 text-center text-sm text-green-600" :status="session('status')" />

        <form wire:submit.prevent="login" class="space-y-6">
            <!-- Correo -->
            <div>
                <label class="block text-sm font-medium text-gray-800 mb-1">Correo Electrónico</label>
                <input type="email"
                       wire:model="email"
                       placeholder="ejemplo@correo.com"
                       required
                       class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
            </div>

            <!-- Contraseña -->
            <div>
                <label class="block text-sm font-medium text-gray-800 mb-1">Contraseña</label>
                <input type="password"
                       wire:model="password"
                       required
                       class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
            </div>

            <!-- Recordarme -->
            <div class="flex items-center gap-2">
                <input type="checkbox"
                       wire:model="remember"
                       class="h-4 w-4 text-green-600 border-gray-400 rounded">
                <label class="text-sm text-gray-800">Recordarme</label>
            </div>

            <!-- Botón de login -->
            <div>
                <button type="submit"
                        class="w-full py-2 bg-[#007832] text-white rounded-md font-semibold hover:bg-green-700 transition duration-150 cursor-pointer">
                    Ingresar
                </button>
            </div>
        </form>

        <!-- Enlaces adicionales -->
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

