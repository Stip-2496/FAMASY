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

@section('title', 'Configuración: contraseña') <!--- título de la página  -->

<div class="flex items-center justify-center p-4 min-h-screen">

@include('partials.sidebar', [
    'active' => 'password', // o cualquier id del ítem activo
    'items' => [
        ['id' => 'home', 'label' => 'Inicio', 'route' => 'dashboard'],
        ['id' => 'password', 'label' => 'Contraseña', 'route' => 'settings.password'],
        ['id' => 'database', 'label' => 'Base de datos', 'route' => 'settings.database'],
    ]
])

    <div class="w-full max-w-md bg-white shadow rounded-lg p-8 border border-gray-300">
        <!-- Encabezado -->
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-2">Actualizar Contraseña</h1>
        
        <!-- Mensajes dinámicos -->
        <div class="text-center text-gray-500 mb-8" 
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
    <p>Para mantener tu cuenta segura</p>
</template>
        </div>

        <form wire:submit.prevent="updatePassword">
<!-- Contraseña actual -->
<div class="mb-6">
    <label class="block text-sm font-medium text-gray-800 mb-1">Contraseña actual</label>
    <div class="relative" x-data="{ show: false, hasValue: false }">
        <input wire:model.live="current_password"
               :type="show ? 'text' : 'password'"
               class="border p-2 rounded w-full text-black pr-10"
               autocomplete="current-password"
               @input="const val = $event.target.value;hasValue = val.length > 0; if (val.length === 0) show = false;">

        <button type="button"
                x-show="hasValue"
                class="absolute inset-y-0 right-0 pr-3 flex items-center justify-center text-black w-10 h-full"
                @click="show = !show"
                x-cloak>
            <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7s-8.268-2.943-9.542-7z" />
            </svg>
            <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/>
                <path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/>
                <path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/>
                <path d="m2 2 20 20"/>
            </svg>
        </button>
    </div>
</div>

<!-- Nueva contraseña -->
<div class="mb-6">
    <label class="block text-sm font-medium text-gray-800 mb-1">Nueva contraseña</label>
    <div class="relative" x-data="{ show: false, hasValue: false }">
        <input wire:model.live="password"
               :type="show ? 'text' : 'password'"
               class="border p-2 rounded w-full text-black pr-10"
               autocomplete="new-password"
               @input="const val = $event.target.value;hasValue = val.length > 0; if (val.length === 0) show = false;">

        <button type="button"
                x-show="hasValue"
                class="absolute inset-y-0 right-0 pr-3 flex items-center justify-center text-black w-10 h-full"
                @click="show = !show"
                x-cloak>
            <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7s-8.268-2.943-9.542-7z" />
            </svg>
            <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/>
                <path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/>
                <path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/>
                <path d="m2 2 20 20"/>
            </svg>
        </button>
    </div>
</div>

<!-- Confirmar nueva contraseña -->
<div class="mb-6">
    <label class="block text-sm font-medium text-gray-800 mb-1">Confirmar nueva contraseña</label>
    <div class="relative" x-data="{ show: false, hasValue: false }">
        <input wire:model.live="password_confirmation"
               :type="show ? 'text' : 'password'"
               class="border p-2 rounded w-full text-black pr-10"
               autocomplete="new-password"
               @input="const val = $event.target.value;hasValue = val.length > 0; if (val.length === 0) show = false;">

        <button type="button"
                x-show="hasValue"
                class="absolute inset-y-0 right-0 pr-3 flex items-center justify-center text-black w-10 h-full"
                @click="show = !show"
                x-cloak>
            <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7s-8.268-2.943-9.542-7z" />
            </svg>
            <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/>
                <path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/>
                <path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/>
                <path d="m2 2 20 20"/>
            </svg>
        </button>
    </div>
</div>


<!-- Botones -->
<div class="text-center mb-4 space-x-4">
    <button type="submit" 
        class="cursor-pointer px-6 py-2 bg-[#007832] text-white rounded-md font-semibold hover:bg-green-700 transition duration-150"
        :disabled="$wire.shouldShowErrors && $wire.errors.length > 0"
        :class="{'opacity-50 cursor-not-allowed': $wire.shouldShowErrors && $wire.errors.length > 0}">
    Guardar
</button>
                <button type="button" wire:click="cancelarCambios" class="cursor-pointer px-6 py-2 bg-red-500 text-white rounded-md font-semibold hover:bg-red-600 transition duration-150">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

