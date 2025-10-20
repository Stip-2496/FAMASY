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

@section('title', 'Reestablecer contraseña')

<div class="flex items-center justify-center p-3 min-h-screen">
    <div class="w-full max-w-md bg-white/80 backdrop-blur-xl shadow rounded-3xl p-5 border border-white/20">
        <!-- Encabezado -->
        <div class="text-center mb-4">
            <div class="flex justify-center mb-2">
                <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
            </div>
            <h2 class="text-xl font-black bg-gradient-to-r from-gray-900 via-gray-800 to-green-800 bg-clip-text text-transparent mb-1">
                ¿Olvidaste tu contraseña?
            </h2>
            <p class="text-xs text-gray-600">Ingresa tu correo para recibir el enlace de restablecimiento</p>
        </div>

        <!-- Estado de sesión -->
        <x-auth-session-status class="mb-3 text-center text-xs text-green-600" :status="session('status')" />

        <form wire:submit.prevent="sendPasswordResetLink" class="space-y-4">
            <!-- Campo de correo -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">
                    Correo Electrónico <span class="text-red-500">*</span>
                </label>
                <div class="relative group">
                    <input type="email"
                           wire:model="email"
                           placeholder="ejemplo@correo.com"
                           required
                           class="w-full px-3 py-2 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-gray-900 text-sm">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
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
                
                <!-- Botón de envío -->
                <button type="submit" 
                        class="cursor-pointer group relative inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                    <svg class="w-3.5 h-3.5 mr-1.5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span class="relative z-10 text-sm">Enviar</span>
                </button>
            </div>
        </form>

        <!-- Volver al login -->
        <div class="mt-4 text-center text-xs text-gray-700">
            <span>¿Ya lo recordaste?</span>
            <a href="{{ route('login') }}" wire:navigate class="text-green-600 hover:text-green-700 font-semibold hover:underline transition-colors duration-200 ml-1">
                Inicia sesión
            </a>
        </div>
    </div>
</div>