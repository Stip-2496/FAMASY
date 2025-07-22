<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public string $email = '';

    /**
     * Enviar enlace de restablecimiento de contraseña.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        Password::sendResetLink($this->only('email'));

        session()->flash('status', __('Un enlace de restablecimiento será enviado si el correo está registrado.'));
    }
};
?>

@section('title', 'Reestablecer contraseña') <!--- título de la página  -->

<div class="flex items-center justify-center p-4 min-h-screen">
    <div class="w-full max-w-md bg-white shadow rounded-lg p-8 border border-gray-300">
        <!-- Encabezado -->
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">¿Olvidaste tu contraseña?</h2>
            <p class="text-sm text-gray-500">Ingresa tu correo para recibir el enlace de restablecimiento</p>
        </div>

        <!-- Estado de sesión -->
        <x-auth-session-status class="mb-4 text-center text-sm text-green-600" :status="session('status')" />

        <form wire:submit.prevent="sendPasswordResetLink" class="space-y-6">
            <!-- Campo de correo -->
            <div>
                <label class="block text-sm font-medium text-gray-800 mb-1">Correo Electrónico</label>
                <input type="email"
                       wire:model="email"
                       placeholder="ejemplo@correo.com"
                       required
                       class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
            </div>

            <!-- Botón de volver y envío -->
            <div class="text-center mb-4 space-x-4">
                <a href="{{ route('welcome') }}" wire:navigation class="cursor-pointer px-6 py-2 bg-gray-500 text-white rounded-md font-semibold hover:bg-gray-600 transition duration-150">
                    Volver
                </a>
                <button type="submit"class="cursor-pointer px-6 py-2 bg-[#007832] text-white rounded-md font-semibold hover:bg-green-700 transition duration-150">
                    Enviar
                </button>
            </div>
        </form>

        <!-- Volver al login -->
        <div class="mt-4 text-center text-sm text-gray-800">
            <span>¿Ya lo recordaste?</span>
            <a href="{{ route('login') }}" class="text-green-600 hover:underline">Inicia sesión</a>
        </div>
    </div>
</div>
