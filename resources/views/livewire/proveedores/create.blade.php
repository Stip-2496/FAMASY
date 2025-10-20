<?php

use App\Models\Proveedor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public Proveedor $proveedor;
    
    public string $nomProve = '';
    public string $apeProve = '';
    public string $nitProve = '';
    public ?string $conProve = null;
    public ?string $telProve = null;
    public ?string $emailProve = null;
    public ?string $dirProve = null;
    public string $ciuProve = '';
    public string $depProve = 'Huila';
    public ?string $tipSumProve = null;
    public ?string $obsProve = null;

    public function mount(): void
    {
        $this->proveedor = new Proveedor();
    }

    public function rules(): array
    {
        return $this->proveedor->getRules();
    }

    // Validación en tiempo real para campos individuales
    public function validateField($fieldName)
    {
        $this->validateOnly($fieldName, $this->rules());
    }

    // Validación automática cuando los campos cambian
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules());
    }

    // Método para limpiar todos los campos
    public function limpiarFormulario(): void
    {
        $this->nomProve = '';
        $this->apeProve = '';
        $this->nitProve = '';
        $this->conProve = null;
        $this->telProve = null;
        $this->emailProve = null;
        $this->dirProve = null;
        $this->ciuProve = '';
        $this->depProve = 'Huila';
        $this->tipSumProve = null;
        $this->obsProve = null;
        
        $this->resetErrorBag();
    }

    // Método para cancelar registro (limpia el formulario)
    public function cancelarRegistro(): void
    {
        $this->limpiarFormulario();
        
        // Disparar evento para mostrar mensaje
        $this->dispatch('registro-cancelado');
    }

    public function save(): void
    {
        $validated = $this->validate($this->rules());
        
        try {
            Proveedor::create($validated);
            
            // Limpiar el formulario después del registro exitoso
            $this->limpiarFormulario();
            
            // Disparar evento para mostrar mensaje de éxito
            $this->dispatch('registro-exitoso');
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al registrar el proveedor: ' . $e->getMessage()
            ]);
        }
    }
}; ?>

@section('title', 'Crear proveedor')

<div class="flex items-center justify-center min-h-screen py-3">
    <div class="w-full max-w-7xl mx-auto bg-white/80 backdrop-blur-xl shadow rounded-3xl p-3 relative border border-white/20">
        <!-- Botón Volver -->
        <div class="absolute top-2 right-2">
            <a href="{{ route('proveedores.index') }}" wire:navigate
               class="group relative inline-flex items-center px-2 py-1 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                <svg class="w-3 h-3 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span class="relative z-10 text-xs">Volver</span>
            </a>
        </div>

        <!-- Encabezado -->
        <div class="text-center mb-3"
             x-data="{ showSuccess: false, showCancel: false }"
             x-on:registro-exitoso.window="showSuccess = true; showCancel = false; setTimeout(() => showSuccess = false, 3000)"
             x-on:registro-cancelado.window="showCancel = true; showSuccess = false; setTimeout(() => showCancel = false, 3000)">
            <div class="flex justify-center mb-1">
                <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <div class="w-4 h-4 text-white flex items-center justify-center">
                        <i class="fas fa-truck text-base"></i>
                    </div>
                </div>
            </div>
            <h1 class="text-base font-black bg-gradient-to-r from-gray-900 via-gray-800 to-green-800 bg-clip-text text-transparent mb-1">
                Registrar Nuevo Proveedor
            </h1>
            
            <template x-if="showSuccess">
                <div class="rounded bg-green-100 px-2 py-1 text-green-800 border border-green-400 text-xs mb-1 font-semibold">
                    ¡Proveedor registrado exitosamente!
                </div>
            </template>

            <template x-if="showCancel">
                <div class="rounded bg-yellow-100 px-2 py-1 text-yellow-800 border border-yellow-400 text-xs mb-1 font-semibold">
                    Formulario limpiado. Puede registrar un nuevo proveedor.
                </div>
            </template>

            <template x-if="!showSuccess && !showCancel">
                <p class="text-gray-600 text-xs">Complete los datos del nuevo proveedor</p>
            </template>
        </div>

        <!-- Formulario -->
        <form wire:submit="save" class="space-y-2">
            <!-- Fila 1: Información básica y Contacto -->
            <div class="flex flex-col md:flex-row gap-2">
                <!-- Información del Proveedor -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#39A900]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Información del Proveedor</h2>
                                <p class="text-gray-600 text-[10px]">Datos básicos del proveedor</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <!-- Nombre del Proveedor -->
                            <div>
                                <label for="nomProve" class="block text-[10px] font-bold text-gray-700 mb-0.5">Nombre<span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <input type="text"
                                           id="nomProve"
                                           wire:model="nomProve"
                                           wire:blur="validateField('nomProve')"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('nomProve') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                           placeholder="Ingrese el nombre del proveedor"
                                           required>
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('nomProve')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Apellido del Proveedor -->
                            <div>
                                <label for="apeProve" class="block text-[10px] font-bold text-gray-700 mb-0.5">Apellido<span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <input type="text"
                                           id="apeProve"
                                           wire:model="apeProve"
                                           wire:blur="validateField('apeProve')"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('apeProve') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                           placeholder="Ingrese el apellido del proveedor"
                                           required>
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('apeProve')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- NIT -->
                            <div class="md:col-span-2">
                                <label for="nitProve" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    NIT <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="text"
                                           id="nitProve"
                                           wire:model="nitProve"
                                           wire:blur="validateField('nitProve')"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('nitProve') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                           placeholder="Ej: 900123456-7"
                                           required>
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('nitProve')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contacto -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#39A900] to-[#000000]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Contacto</h2>
                                <p class="text-gray-600 text-[10px]">Información de comunicación</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <!-- Celular -->
                            <div>
                                <label for="conProve" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Celular<span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="text"
                                           id="conProve"
                                           wire:model="conProve"
                                           wire:blur="validateField('conProve')"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('conProve') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                           placeholder="Ej: 3001234567"
                                           maxlength="10"
                                           pattern="[0-9]{10}"
                                           title="Debe contener exactamente 10 dígitos">
                                    <div class="absolute inset-0 bg-gradient-to-r from-purple-500/5 to-pink-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('conProve')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Teléfono -->
                            <div>
                                <label for="telProve" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Teléfono<span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="text"
                                           id="telProve"
                                           wire:model="telProve"
                                           wire:blur="validateField('telProve')"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('telProve') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                           placeholder="Ej: 000-00-00">
                                    <div class="absolute inset-0 bg-gradient-to-r from-purple-500/5 to-pink-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('telProve')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="md:col-span-2">
                                <label for="emailProve" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Email<span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="email"
                                           id="emailProve"
                                           wire:model="emailProve"
                                           wire:blur="validateField('emailProve')"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('emailProve') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                           placeholder="correo@ejemplo.com">
                                    <div class="absolute inset-0 bg-gradient-to-r from-purple-500/5 to-pink-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('emailProve')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fila 2: Ubicación y Bienes/Servicios -->
            <div class="flex flex-col md:flex-row gap-2">
                <!-- Ubicación -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#39A900]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Ubicación</h2>
                                <p class="text-gray-600 text-[10px]">Información de ubicación del proveedor</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-2">
                            <!-- Dirección -->
                            <div>
                                <label for="dirProve" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Dirección<span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="text"
                                           id="dirProve"
                                           wire:model="dirProve"
                                           wire:blur="validateField('dirProve')"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('dirProve') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                           placeholder="Dirección completa">
                                    <div class="absolute inset-0 bg-gradient-to-r from-orange-500/5 to-red-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('dirProve')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Ciudad -->
                            <div>
                                <label for="ciuProve" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Ciudad <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="text"
                                           id="ciuProve"
                                           wire:model="ciuProve"
                                           wire:blur="validateField('ciuProve')"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('ciuProve') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                           placeholder="Ej: Neiva"
                                           required>
                                    <div class="absolute inset-0 bg-gradient-to-r from-orange-500/5 to-red-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('ciuProve')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Departamento -->
                            <div>
                                <label for="depProve" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Departamento <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <select id="depProve"
                                            wire:model="depProve"
                                            wire:blur="validateField('depProve')"
                                            class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('depProve') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                        <option value="Amazonas">Amazonas</option>
                                        <option value="Antioquia">Antioquia</option>
                                        <option value="Arauca">Arauca</option>
                                        <option value="Atlántico">Atlántico</option>
                                        <option value="Bolívar">Bolívar</option>
                                        <option value="Boyacá">Boyacá</option>
                                        <option value="Caldas">Caldas</option>
                                        <option value="Caquetá">Caquetá</option>
                                        <option value="Casanare">Casanare</option>
                                        <option value="Cauca">Cauca</option>
                                        <option value="Cesar">Cesar</option>
                                        <option value="Chocó">Chocó</option>
                                        <option value="Córdoba">Córdoba</option>
                                        <option value="Cundinamarca">Cundinamarca</option>
                                        <option value="Guanía">Guanía</option>
                                        <option value="Guaviare">Guaviare</option>
                                        <option value="Huila">Huila</option>
                                        <option value="La Guajira">La Guajira</option>
                                        <option value="Magdalena">Magdalena</option>
                                        <option value="Meta">Meta</option>
                                        <option value="Nariño">Nariño</option>
                                        <option value="Norte de Santander">Norte de Santander</option>
                                        <option value="Putumayo">Putumayo</option>
                                        <option value="Quindío">Quindío</option>
                                        <option value="Risaralda">Risaralda</option>
                                        <option value="San Andrés y Providencia">San Andrés y Providencia</option>
                                        <option value="Santander">Santander</option>
                                        <option value="Sucre">Sucre</option>
                                        <option value="Tolima">Tolima</option>
                                        <option value="Valle del Cauca">Valle del Cauca</option>
                                        <option value="Vaupés">Vaupés</option>
                                        <option value="Vichada">Vichada</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('depProve')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de Bienes y Servicios -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#39A900] to-[#000000]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-green-500 to-teal-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Información de Bienes y Servicios</h2>
                                <p class="text-gray-600 text-[10px]">Detalles de suministros y observaciones</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-2">
                            <!-- Tipo de Suministro -->
                            <div>
                                <label for="tipSumProve" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Tipo de Suministro<span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <select id="tipSumProve"
                                            wire:model="tipSumProve"
                                            wire:blur="validateField('tipSumProve')"
                                            class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('tipSumProve') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                        <option value="">Seleccione un tipo</option>
                                        <option value="Alimentos concentrados">Alimentos concentrados</option>
                                        <option value="Medicamentos veterinarios">Medicamentos veterinarios</option>
                                        <option value="Herramientas agrícolas">Herramientas agrícolas</option>
                                        <option value="Insumos ganaderos">Insumos ganaderos</option>
                                        <option value="Servicios veterinarios">Servicios veterinarios</option>
                                        <option value="Otros">Otros</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('tipSumProve')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Observaciones -->
                            <div>
                                <label for="obsProve" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Observaciones<span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <textarea id="obsProve"
                                              wire:model="obsProve"
                                              wire:blur="validateField('obsProve')"
                                              rows="4"
                                              class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('obsProve') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                              placeholder="Observaciones adicionales sobre el proveedor"></textarea>
                                    <div class="absolute inset-0 bg-gradient-to-r from-green-500/5 to-teal-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('obsProve')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-center space-x-2 pt-2">
                <button type="button" wire:click="cancelarRegistro"
                   class="cursor-pointer group relative inline-flex items-center justify-center px-2.5 py-1 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                    <svg class="w-3 h-3 mr-1.5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span class="relative z-10 text-xs">Cancelar</span>
                </button>
                <button type="submit"
                        class="cursor-pointer group relative inline-flex items-center justify-center px-2.5 py-1 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                    <svg class="w-3 h-3 mr-1.5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="relative z-10 text-xs">Guardar Proveedor</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('livewire:initialized', () => {
    // Validación del NIT en tiempo real
    Livewire.on('nitProve', (value) => {
        document.getElementById('nitProve').value = value.replace(/[^0-9-]/g, '');
    });

    // Validación del teléfono en tiempo real
    Livewire.on('telProve', (value) => {
        document.getElementById('telProve').value = value.replace(/[^0-9\s\-\(\)]/g, '');
    });

    // Validación del celular - solo números, máximo 10 dígitos
    Livewire.on('conProve', (value) => {
        const input = document.getElementById('conProve');
        input.value = value.replace(/[^0-9]/g, '');
        
        if (input.value.length > 10) {
            input.value = input.value.slice(0, 10);
        }
    });
});
</script>