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

@section('title', 'Inicio de sesión') <!-- Título de la página -->

<div class="flex items-center justify-center p-4 min-h-screen">
    <div class="w-full max-w-md bg-white shadow rounded-lg p-8 border border-gray-300">
        <!-- Encabezado -->
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Inicio de Sesión</h2>
            <p class="text-sm text-gray-500">Ingresa tus credenciales para acceder</p>
        </div>

        <!-- Estado de sesión - Muestra mensajes de estado como "Sesión iniciada" -->
        <x-auth-session-status class="mb-4 text-center text-sm text-green-600" :status="session('status')" />

        <!-- Mensajes de error - Maneja los errores de validación -->
        <div class="text-center mb-4" 
             x-data="{
                 showErrors: false,
                 errors: []
             }" 
             x-on:validation-failed.window="
                 showErrors = true;
                 errors = $event.detail.errors;">
            
            <!-- Muestra los errores si existen -->
            <template x-if="showErrors && errors.length > 0">
                <div class="rounded bg-red-100 px-4 py-2 text-red-800 border border-red-400 text-left mb-4">
                    <ul class="list-disc list-inside">
                        <template x-for="error in errors" :key="error">
                            <li x-text="error" class="text-sm"></li>
                        </template>
                    </ul>
                </div>
            </template>
        </div>

        <!-- Formulario de inicio de sesión -->
        <form wire:submit.prevent="login" class="space-y-6">
            <!-- Campo de correo electrónico -->
            <div>
                <label class="block text-sm font-medium text-gray-800 mb-1">Correo Electrónico</label>
                <input type="email"
                       wire:model.live="email"
                       placeholder="ejemplo@correo.com"
                       class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
            </div>

            <!-- Campo de contraseña con toggle para mostrar/ocultar -->
            <div>
                <label class="block text-sm font-medium text-gray-800 mb-1">Contraseña</label>
                <div class="relative" x-data="{ show: false, hasValue: false }">
                    <input wire:model.live="password"
                           :type="show ? 'text' : 'password'"
                           class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white pr-10"
                           autocomplete="current-password"
                           @input="const val = $event.target.value; hasValue = val.length > 0; if (val.length === 0) show = false;">

                    <!-- Botón para mostrar/ocultar contraseña -->
                    <button type="button"
                            x-show="hasValue"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center justify-center text-black w-10 h-full"
                            @click="show = !show"
                            x-cloak>
                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="cursor-pointer h-5 w-5" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7s-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="cursor-pointer h-5 w-5" fill="none"
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
            <div class="text-center mb-4 space-x-4">
                <!-- Botón para volver a la página de inicio -->
                <a href="{{ route('welcome') }}" wire:navigate class="cursor-pointer px-6 py-2 bg-gray-500 text-white rounded-md font-semibold hover:bg-gray-600 transition duration-150">
                    Volver
                </a>
                <!-- Botón de inicio de sesión -->
                <button type="submit" 
                        class="cursor-pointer px-6 py-2 bg-[#007832] text-white rounded-md font-semibold hover:bg-green-700 transition duration-150"
                        :disabled="$wire.shouldShowErrors && $wire.errors.length > 0"
                        :class="{'opacity-50 cursor-not-allowed': $wire.shouldShowErrors && $wire.errors.length > 0}">
                    Ingresar
                </button>
            </div>
        </form>

        <!-- Enlace para recuperación de contraseña -->
        <div class="mt-4 text-center text-sm text-gray-800">
            ¿Olvidaste tu contraseña?
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" wire:navigate class="text-green-600 hover:underline">Recupérala aquí</a>
            @endif
        </div>
    </div>
</div>