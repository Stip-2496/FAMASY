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
     * Cancel form changes and reset fields.
     */
    public function cancelarCambios(): void
    {
        $this->resetForm();
        $this->dispatch('password-cancelled');
        $this->dispatch('reset-password-fields');
    }

    /**
     * Reset form fields and state, preserving email.
     */
    public function resetForm(): void
    {
        $this->reset('password', 'password_confirmation', 'errors', 'submitAttempted', 'shouldShowErrors');
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

<div class="flex items-center justify-center p-3 min-h-screen">
    <div class="w-full max-w-md bg-white/80 backdrop-blur-xl shadow rounded-3xl p-5 border border-white/20">
        <!-- Encabezado -->
        <div class="text-center mb-4"
             x-data="{
                 showSuccess: false,
                 showCancel: false,
                 showErrors: false,
                 errors: []
             }" 
             x-on:validation-failed.window="
                 showErrors = true;
                 errors = $event.detail.errors;
                 showSuccess = false;
                 showCancel = false;"
             x-on:password-cancelled.window="
                 showCancel = true;
                 showSuccess = false;
                 showErrors = false;
                 setTimeout(() => showCancel = false, 3000)">
            <div class="flex justify-center mb-2">
                <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </div>
            </div>
            <h2 class="text-xl font-black bg-gradient-to-r from-gray-900 via-gray-800 to-green-800 bg-clip-text text-transparent mb-1">
                Nueva Contraseña
            </h2>
            
            <!-- Mensaje de cancelación -->
            <template x-if="showCancel">
                <div class="rounded bg-yellow-100 px-3 py-1.5 text-yellow-800 border border-yellow-400 text-xs mb-3">
                    Cambios descartados.
                </div>
            </template>

            <!-- Mensajes de error -->
            <template x-if="showErrors && errors.length > 0">
                <div class="rounded bg-red-100 px-3 py-1.5 text-red-800 border border-red-400 text-left mb-3">
                    <ul class="list-disc list-inside">
                        <template x-for="error in errors" :key="error">
                            <li x-text="error" class="text-xs"></li>
                        </template>
                    </ul>
                </div>
            </template>

            <template x-if="!showSuccess && !showCancel && (!showErrors || errors.length === 0)">
                <p class="text-xs text-gray-600">Ingresa tu nueva contraseña</p>
            </template>
        </div>

        <!-- Estado de sesión -->
        <x-auth-session-status class="mb-3 text-center text-xs text-green-600" :status="session('status')" />

        <form wire:submit.prevent="resetPassword" class="space-y-4">
            <!-- Campo de correo (readonly) -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Correo Electrónico</label>
                <input type="email"
                       wire:model="email"
                       readonly
                       class="w-full px-3 py-2 bg-gray-100 border-2 border-gray-200 rounded-2xl shadow-lg text-gray-600 cursor-not-allowed text-sm">
            </div>

            <!-- Campo de nueva contraseña -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">
                    Nueva Contraseña <span class="text-red-500">*</span>
                </label>
                <div class="relative group" 
                     x-data="{ show: false, hasValue: false }"
                     x-on:reset-password-fields.window="show = false; hasValue = false;">
                    <input wire:model.live="password"
                           :type="show ? 'text' : 'password'"
                           class="w-full px-3 py-2 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-gray-900 pr-10 text-sm"
                           autocomplete="new-password"
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
                            <path d="M2 2 20 20"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Campo de confirmación de contraseña -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">
                    Confirmar Contraseña <span class="text-red-500">*</span>
                </label>
                <div class="relative group" 
                     x-data="{ show: false, hasValue: false }"
                     x-on:reset-password-fields.window="show = false; hasValue = false;">
                    <input wire:model.live="password_confirmation"
                           :type="show ? 'text' : 'password'"
                           class="w-full px-3 py-2 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-gray-900 pr-10 text-sm"
                           autocomplete="new-password"
                           @input="const val = $event.target.value; hasValue = val.length > 0; if (val.length === 0) show = false;">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>

                    <!-- Botón para mostrar/ocultar confirmación -->
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
                            <path d="M2 2 20 20"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex justify-center space-x-2 pt-3">
                <!-- Botón de cancelar -->
                <button type="button" wire:click="cancelarCambios"
                        class="cursor-pointer group relative inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                    <svg class="w-3.5 h-3.5 mr-1.5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span class="relative z-10 text-sm">Cancelar</span>
                </button>
                
                <!-- Botón de restablecer -->
                <button type="submit"
                        class="cursor-pointer group relative inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden"
                        :disabled="$wire.shouldShowErrors && $wire.errors.length > 0"
                        :class="{'opacity-50 cursor-not-allowed': $wire.shouldShowErrors && $wire.errors.length > 0}">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                    <svg class="w-3.5 h-3.5 mr-1.5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="relative z-10 text-sm">Restablecer</span>
                </button>
            </div>
        </form>

        <!-- Volver al login -->
        <div class="mt-4 text-center text-xs text-gray-700">
            <span>¿Recordaste tu contraseña?</span>
            <a href="{{ route('login') }}" wire:navigate class="text-green-600 hover:text-green-700 font-semibold hover:underline transition-colors duration-200 ml-1">
                Inicia sesión
            </a>
        </div>
    </div>
</div>