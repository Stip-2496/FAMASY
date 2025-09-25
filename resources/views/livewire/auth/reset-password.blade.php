<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public array $errors = [];
    public bool $submitAttempted = false;
    public bool $shouldShowErrors = false;

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->string('email');
    }

    /**
     * Reset the user's password.
     */
    public function resetPassword(): void
    {
        $this->submitAttempted = true;
        $this->shouldShowErrors = true;
        $this->validateForm();
        
        // Si hay errores de validación, detener el proceso
        if (count($this->errors) > 0) {
            $this->dispatch('validation-failed', errors: $this->errors);
            return;
        }

        // Intentar resetear la contraseña
        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            session()->flash('status', 'Tu contraseña ha sido restablecida exitosamente.');
            $this->redirect(route('login'), navigate: true);
        } else {
            $this->errors[] = __($status);
            $this->dispatch('validation-failed', errors: $this->errors);
        }
    }

    /**
     * Valida todos los campos del formulario
     */
    public function validateForm(): void
    {
        $this->errors = [];
        
        // Validar email
        if (empty($this->email)) {
            $this->errors[] = 'El correo electrónico es requerido';
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'El correo electrónico debe ser válido';
        }
        
        // Validar contraseña
        if (empty($this->password)) {
            $this->errors[] = 'La contraseña es requerida';
        } elseif (strlen($this->password) < 8) {
            $this->errors[] = 'La contraseña debe tener al menos 8 caracteres';
        }
        
        // Validar confirmación de contraseña
        if (empty($this->password_confirmation)) {
            $this->errors[] = 'La confirmación de contraseña es requerida';
        } elseif ($this->password !== $this->password_confirmation) {
            $this->errors[] = 'Las contraseñas no coinciden';
        }
        
        // Validar token
        if (empty($this->token)) {
            $this->errors[] = 'Token de restablecimiento inválido';
        }
    }
    
    /**
     * Limpia los errores cuando se editan los campos
     */
    public function updated($property)
    {
        if (in_array($property, ['email', 'password', 'password_confirmation'])) {
            $this->shouldShowErrors = false;
            $this->submitAttempted = false;
            $this->errors = [];
        }
    }
};
?>

@section('title', 'Establecer nueva contraseña')

<div class="flex items-center justify-center p-4 min-h-screen">
    <div class="w-full max-w-md bg-white shadow rounded-lg p-8 border border-gray-300">
        <!-- Encabezado -->
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Nueva Contraseña</h2>
            <p class="text-sm text-gray-500">Ingresa tu nueva contraseña</p>
        </div>

        <!-- Estado de sesión -->
        <x-auth-session-status class="mb-4 text-center text-sm text-green-600" :status="session('status')" />

        <!-- Mensajes de error -->
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

        <form wire:submit.prevent="resetPassword" class="space-y-6">
            <!-- Campo de correo (readonly) -->
            <div>
                <label class="block text-sm font-medium text-gray-800 mb-1">Correo Electrónico</label>
                <input type="email"
                       wire:model="email"
                       readonly
                       class="w-full px-3 py-2 border border-gray-400 rounded-md bg-gray-100 text-gray-600 cursor-not-allowed">
            </div>

            <!-- Campo de nueva contraseña -->
            <div>
                <label class="block text-sm font-medium text-gray-800 mb-1">Nueva Contraseña</label>
                <div class="relative" x-data="{ show: false, hasValue: false }">
                    <input wire:model.live="password"
                           :type="show ? 'text' : 'password'"
                           class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white pr-10"
                           autocomplete="new-password"
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

            <!-- Campo de confirmación de contraseña -->
            <div>
                <label class="block text-sm font-medium text-gray-800 mb-1">Confirmar Contraseña</label>
                <div class="relative" x-data="{ show: false, hasValue: false }">
                    <input wire:model.live="password_confirmation"
                           :type="show ? 'text' : 'password'"
                           class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white pr-10"
                           autocomplete="new-password"
                           @input="const val = $event.target.value; hasValue = val.length > 0; if (val.length === 0) show = false;">

                    <!-- Botón para mostrar/ocultar confirmación -->
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
                <a href="{{ route('login') }}" wire:navigate class="cursor-pointer px-6 py-2 bg-gray-500 text-white rounded-md font-semibold hover:bg-gray-600 transition duration-150">
                    Cancelar
                </a>
                <button type="submit"
                        class="cursor-pointer px-6 py-2 bg-[#007832] text-white rounded-md font-semibold hover:bg-green-700 transition duration-150"
                        :disabled="$wire.shouldShowErrors && $wire.errors.length > 0"
                        :class="{'opacity-50 cursor-not-allowed': $wire.shouldShowErrors && $wire.errors.length > 0}">
                    Restablecer Contraseña
                </button>
            </div>
        </form>

        <!-- Volver al login -->
        <div class="mt-4 text-center text-sm text-gray-800">
            <span>¿Recordaste tu contraseña?</span>
            <a href="{{ route('login') }}" wire:navigate class="text-green-600 hover:underline">Inicia sesión</a>
        </div>
    </div>
</div>