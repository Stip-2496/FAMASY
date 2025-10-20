<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.auth')] class extends Component {
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';
    public array $errors = [];
    public bool $submitAttempted = false;
    public bool $shouldShowErrors = false; // Controla cuando mostrar errores

    public function updatePassword(): void
    {
        $this->submitAttempted = true;
        $this->shouldShowErrors = true;
        $this->validateForm();
        
        if (count($this->errors) > 0) {
            $this->dispatch('validation-failed', errors: $this->errors);
            return;
        }

        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->resetForm();
        $this->dispatch('password-updated');
        $this->dispatch('password-updated');
        $this->dispatch('form-reset');
    }
    
    public function cancelarCambios(): void
    {
        $this->resetForm();
        $this->dispatch('password-cancelled');
    }
    
    public function resetForm(): void
    {
        $this->reset('current_password', 'password', 'password_confirmation', 'errors', 'submitAttempted', 'shouldShowErrors');
    }
    
    public function updated($property)
    {
        if (in_array($property, ['current_password', 'password', 'password_confirmation'])) {
            $this->shouldShowErrors = false; // Oculta errores al editar
            $this->submitAttempted = false; // Reactiva el botón
            $this->errors = []; // Limpia errores anteriores
        }
    }
    
    public function validateForm(): void
    {
        $this->errors = [];
        
        // Validación de campos vacíos
        if (empty($this->current_password) && empty($this->password) && empty($this->password_confirmation)) {
            $this->errors[] = 'Todos los campos están vacíos';
            return;
        }
        
        if (empty($this->current_password)) {
            $this->errors[] = 'La contraseña actual es requerida';
        }
        if (empty($this->password)) {
            $this->errors[] = 'La nueva contraseña es requerida';
        }
        if (empty($this->password_confirmation)) {
            $this->errors[] = 'Debes confirmar la nueva contraseña';
        }
        
        if (count($this->errors) > 0) return;
        
        if ($this->password !== $this->password_confirmation) {
            $this->errors[] = 'Las nuevas contraseñas no coinciden';
            return;
        }
        
        if (!Hash::check($this->current_password, Auth::user()->password)) {
            $this->errors[] = 'La contraseña actual no es correcta';
        }
    }
};   
?>

@section('title', 'Configuración: contraseña')

<div class="flex items-center justify-center p-4 min-h-screen">

    <div class="w-full max-w-md bg-white/80 backdrop-blur-xl shadow rounded-3xl p-8 border border-white/20">
        <!-- Encabezado -->
        <div class="text-center mb-8"
             x-data="{
                 showSuccess: false, 
                 showCancel: false,
                 showErrors: false,
                 errors: []
             }" 
             x-on:password-updated.window="
                 showSuccess = true; 
                 showCancel = false; 
                 showErrors = false;
                 setTimeout(() => showSuccess = false, 3000)" 
             x-on:password-cancelled.window="
                 showCancel = true; 
                 showSuccess = false; 
                 showErrors = false;
                 setTimeout(() => showCancel = false, 3000)"
             x-on:validation-failed.window="
                 showErrors = true;
                 errors = $event.detail.errors;
                 showSuccess = false;
                 showCancel = false;"> 
            <div class="flex justify-center mb-2">
                <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd" d="M15.75 1.5a6.75 6.75 0 0 0-6.651 7.906c.067.39-.032.717-.221.906l-6.5 6.499a3 3 0 0 0-.878 2.121v2.818c0 .414.336.75.75.75H6a.75.75 0 0 0 .75-.75v-1.5h1.5A.75.75 0 0 0 9 19.5V18h1.5a.75.75 0 0 0 .53-.22l2.658-2.658c.19-.189.517-.288.906-.22A6.75 6.75 0 1 0 15.75 1.5Zm0 3a.75.75 0 0 0 0 1.5A2.25 2.25 0 0 1 18 8.25a.75.75 0 0 0 1.5 0 3.75 3.75 0 0 0-3.75-3.75Z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
            <h1 class="text-2xl font-black bg-gradient-to-r from-gray-900 via-gray-800 to-green-800 bg-clip-text text-transparent mb-2">
                Actualizar Contraseña
            </h1>

            <!-- Mensaje de éxito -->
            <template x-if="showSuccess">
                <div class="rounded bg-green-100 px-4 text-green-800 border border-green-400">
                    ¡Contraseña actualizada correctamente!
                </div>
            </template>

            <!-- Mensaje de cancelación -->
            <template x-if="showCancel">
                <div class="rounded bg-yellow-100 px-4 text-yellow-800 border border-yellow-400">
                    Cambios descartados.
                </div>
            </template>

            <!-- Mensajes de error -->
            <template x-if="showErrors && errors.length > 0">
                <div class="rounded bg-red-100 px-4 py-2 text-red-800 border border-red-400 text-left">
                    <p class="font-semibold">No se puede guardar porque:</p>
                    <ul class="list-disc list-inside">
                        <template x-for="error in errors" :key="error">
                            <li x-text="error"></li>
                        </template>
                    </ul>
                </div>
            </template>

            <template x-if="!showSuccess && !showCancel && (!showErrors || errors.length === 0)">
                <p class="text-gray-600">Para mantener tu cuenta segura</p>
            </template>
        </div>

        <form wire:submit.prevent="updatePassword" class="space-y-6">
            <!-- Contraseña actual -->
            <div>
                <label for="current_password" class="block text-sm font-bold text-gray-700 mb-1">
                    Contraseña actual <span class="text-red-500">*</span>
                </label>
                <div class="relative group" x-data="{ show: false, hasValue: false }">
                    <input wire:model.live="current_password"
                           :type="show ? 'text' : 'password'"
                           id="current_password"
                           class="w-full px-2 py-2 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl pr-10"
                           autocomplete="current-password"
                           @input="const val = $event.target.value;hasValue = val.length > 0; if (val.length === 0) show = false;">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                    <button type="button"
                            x-show="hasValue"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center justify-center text-gray-400 w-10 h-full"
                            @click="show = !show"
                            x-cloak>
                        <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7s-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.733 5.076a10.744 10.744 0 0111.205 6.575 1 1 0 010 .696 10.747 10.747 0 01-1.444 2.49" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.084 14.158a3 3 0 01-4.242-4.242" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.479 17.499a10.75 10.75 0 01-15.417-5.151 1 1 0 010-.696 10.75 10.75 0 014.446-5.143" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 2l20 20" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Nueva contraseña -->
            <div>
                <label for="password" class="block text-sm font-bold text-gray-700 mb-1">
                    Nueva contraseña <span class="text-red-500">*</span>
                </label>
                <div class="relative group" x-data="{ show: false, hasValue: false }">
                    <input wire:model.live="password"
                           :type="show ? 'text' : 'password'"
                           id="password"
                           class="w-full px-2 py-2 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl pr-10"
                           autocomplete="new-password"
                           @input="const val = $event.target.value;hasValue = val.length > 0; if (val.length === 0) show = false;">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                    <button type="button"
                            x-show="hasValue"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center justify-center text-gray-400 w-10 h-full"
                            @click="show = !show"
                            x-cloak>
                        <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7s-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.733 5.076a10.744 10.744 0 0111.205 6.575 1 1 0 010 .696 10.747 10.747 0 01-1.444 2.49" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.084 14.158a3 3 0 01-4.242-4.242" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.479 17.499a10.75 10.75 0 01-15.417-5.151 1 1 0 010-.696 10.75 10.75 0 014.446-5.143" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 2l20 20" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Confirmar nueva contraseña -->
            <div>
                <label for="password_confirmation" class="block text-sm font-bold text-gray-700 mb-1">
                    Confirmar nueva contraseña <span class="text-red-500">*</span>
                </label>
                <div class="relative group" x-data="{ show: false, hasValue: false }">
                    <input wire:model.live="password_confirmation"
                           :type="show ? 'text' : 'password'"
                           id="password_confirmation"
                           class="w-full px-2 py-2 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl pr-10"
                           autocomplete="new-password"
                           @input="const val = $event.target.value;hasValue = val.length > 0; if (val.length === 0) show = false;">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                    <button type="button"
                            x-show="hasValue"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center justify-center text-gray-400 w-10 h-full"
                            @click="show = !show"
                            x-cloak>
                        <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7s-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.733 5.076a10.744 10.744 0 0111.205 6.575 1 1 0 010 .696 10.747 10.747 0 01-1.444 2.49" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.084 14.158a3 3 0 01-4.242-4.242" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.479 17.499a10.75 10.75 0 01-15.417-5.151 1 1 0 010-.696 10.75 10.75 0 014.446-5.143" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 2l20 20" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Botones -->
            <div class="text-center mb-4 space-x-4">
                <button type="button" wire:click="cancelarCambios"
                        class="cursor-pointer group relative inline-flex items-center justify-center px-6 py-2 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                    <svg class="w-5 h-5 mr-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span class="relative z-10">Cancelar</span>
                </button>
                <button type="submit"
                        class="cursor-pointer group relative inline-flex items-center justify-center px-6 py-2 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden"
                        :disabled="$wire.shouldShowErrors && $wire.errors.length > 0"
                        :class="{'opacity-50 cursor-not-allowed': $wire.shouldShowErrors && $wire.errors.length > 0}">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                    <svg class="w-5 h-5 mr-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="relative z-10">Guardar</span>
                </button>
            </div>
        </form>
    </div>
</div>