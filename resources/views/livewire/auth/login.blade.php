<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;
    public array $errors = [];
    public bool $submitAttempted = false;
    public bool $shouldShowErrors = false;

    /**
     * Método principal para manejar el inicio de sesión
     */
    public function login(): void
    {
        // Marcar que se intentó enviar el formulario y mostrar errores si existen
        $this->submitAttempted = true;
        $this->shouldShowErrors = true;
        $this->validateForm();
        
        // Si hay errores de validación, detener el proceso
        if (count($this->errors) > 0) {
            $this->dispatch('validation-failed', errors: $this->errors);
            return;
        }

        // Verificar límite de intentos de inicio de sesión
        $this->ensureIsNotRateLimited();

        // Intentar autenticar al usuario
        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            // Incrementar el contador de intentos fallidos
            RateLimiter::hit($this->throttleKey());

            // El evento Failed se maneja automáticamente a través de AuditServiceProvider
            // No es necesario dispararlo manualmente para evitar duplicación

            $this->errors[] = 'Credenciales incorrectas';
            $this->dispatch('validation-failed', errors: $this->errors);
            return;
        }

        // Limpiar el contador de intentos fallidos
        RateLimiter::clear($this->throttleKey());
        
        // Regenerar la sesión por seguridad
        Session::regenerate();

        // Obtener el usuario autenticado y actualizar last_login_at
        $user = Auth::user();
        $user->last_login_at = now();
        $user->save();

        // Redirigir según el ID del rol del usuario
        $redirectRoute = match($user->idRolUsu) {
            1 => route('inventario.dashboard'), // Administrador (idRolUsu = 1)
            2 => route('settings.manage-users.dashboard'), // Superusuario (idRolUsu = 2)
            3 => route('inventario.prestamos.index'), // Aprendiz (idRolUsu = 3)
            default => route('dashboard') // Ruta por defecto
        };
        
        // Redirección con navegación optimizada (Livewire)
        $this->redirect($redirectRoute, navigate: true);
    }

    /**
     * Verifica que no se haya excedido el límite de intentos de inicio de sesión
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 3)) {
            return;
        }

        // El evento Lockout se maneja automáticamente a través de AuditServiceProvider
        // No es necesario dispararlo manualmente para evitar duplicación

        // Calcular tiempo de espera
        $seconds = RateLimiter::availableIn($this->throttleKey());

        $this->errors[] = __('auth.throttle', [
            'seconds' => $seconds,
            'minutes' => ceil($seconds / 60),
        ]);
        $this->dispatch('validation-failed', errors: $this->errors);
    }

    /**
     * Genera una clave única para el límite de intentos basada en email e IP
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
    
    /**
     * Valida los campos del formulario
     */
    public function validateForm(): void
    {
        $this->errors = [];
        
        // Validación de campos vacíos
        if (empty($this->email) && empty($this->password)) {
            $this->errors[] = 'Todos los campos son requeridos';
            return;
        }
        
        if (empty($this->email)) {
            $this->errors[] = 'El correo electrónico es requerido';
        }
        if (empty($this->password)) {
            $this->errors[] = 'La contraseña es requerida';
        }
    }
    
    /**
     * Limpia los errores cuando se editan los campos
     */
    public function updated($property)
    {
        if (in_array($property, ['email', 'password'])) {
            $this->shouldShowErrors = false;
            $this->submitAttempted = false;
            $this->errors = [];
        }
    }
};
?>

@section('title', 'Inicio de sesión')

<div class="flex items-center justify-center p-3 min-h-screen">
    <div class="w-full max-w-md bg-white/80 backdrop-blur-xl shadow rounded-3xl p-5 border border-white/20">
        <!-- Encabezado -->
        <div class="text-center mb-4">
            <div class="flex justify-center mb-2">
                <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </div>
            <h2 class="text-xl font-black bg-gradient-to-r from-gray-900 via-gray-800 to-green-800 bg-clip-text text-transparent mb-1">
                Inicio de Sesión
            </h2>
            <p class="text-xs text-gray-600">Ingresa tus credenciales para acceder</p>
        </div>

        <!-- Estado de sesión -->
        <x-auth-session-status class="mb-3 text-center text-xs text-green-600" :status="session('status')" />

        <!-- Mensajes de error -->
        <div class="text-center mb-3" 
             x-data="{
                 showErrors: false,
                 errors: []
             }" 
             x-on:validation-failed.window="
                 showErrors = true;
                 errors = $event.detail.errors;">
            
            <template x-if="showErrors && errors.length > 0">
                <div class="rounded bg-red-100 px-3 py-1.5 text-red-800 border border-red-400 text-left mb-3">
                    <ul class="list-disc list-inside">
                        <template x-for="error in errors" :key="error">
                            <li x-text="error" class="text-xs"></li>
                        </template>
                    </ul>
                </div>
            </template>
        </div>

        <!-- Formulario de inicio de sesión -->
        <form wire:submit.prevent="login" class="space-y-4">
            <!-- Campo de correo electrónico -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">
                    Correo Electrónico <span class="text-red-500">*</span>
                </label>
                <div class="relative group">
                    <input type="email"
                           wire:model.live="email"
                           placeholder="ejemplo@correo.com"
                           class="w-full px-3 py-2 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-gray-900 text-sm">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                </div>
            </div>

            <!-- Campo de contraseña -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">
                    Contraseña <span class="text-red-500">*</span>
                </label>
                <div class="relative group" x-data="{ show: false, hasValue: false }">
                    <input wire:model.live="password"
                           :type="show ? 'text' : 'password'"
                           class="w-full px-3 py-2 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-gray-900 pr-10 text-sm"
                           autocomplete="current-password"
                           @input="const val = $event.target.value; hasValue = val.length > 0; if (val.length === 0) show = false;">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>

                    <!-- Botón para mostrar/ocultar contraseña -->
                    <button type="button"
                            x-show="hasValue"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center justify-center text-gray-400 w-10 h-full"
                            @click="show = !show"
                            x-cloak>
                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="cursor-pointer h-4 w-4" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7s-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="cursor-pointer h-4 w-4" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/>
                            <path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/>
                            <path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/>
                            <path d="m2 2 20 20"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex justify-center space-x-2 pt-3">
                <!-- Botón para volver -->
                <a href="{{ route('welcome') }}" wire:navigate 
                   class="cursor-pointer group relative inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                    <svg class="w-3.5 h-3.5 mr-1.5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span class="relative z-10 text-sm">Volver</span>
                </a>
                
                <!-- Botón de inicio de sesión -->
                <button type="submit" 
                        class="cursor-pointer group relative inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden"
                        :disabled="$wire.shouldShowErrors && $wire.errors.length > 0"
                        :class="{'opacity-50 cursor-not-allowed': $wire.shouldShowErrors && $wire.errors.length > 0}">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                    <svg class="w-3.5 h-3.5 mr-1.5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                    </svg>
                    <span class="relative z-10 text-sm">Ingresar</span>
                </button>
            </div>
        </form>

        <!-- Enlace para recuperación de contraseña -->
        <div class="mt-4 text-center text-xs text-gray-700">
            ¿Olvidaste tu contraseña?
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" wire:navigate class="text-green-600 hover:text-green-700 font-semibold hover:underline transition-colors duration-200">
                    Recupérala aquí
                </a>
            @endif
        </div>
    </div>
</div>