<?php
// resources/views/livewire/inventario/prestamos/create.blade.php

use App\Models\Herramienta;
use App\Models\PrestamoHerramienta;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public PrestamoHerramienta $prestamo;
    
    public int $idHerPre = 0;
    public int $idUsuPre = 0;
    public int $idUsuSol = 0;
    public string $fecPre = '';
    public ?string $fecDev = null;
    public string $estPre = 'prestado';
    public ?string $obsPre = null;
    
    public function mount(): void
    {
        $this->prestamo = new PrestamoHerramienta();
        $this->fecPre = now()->format('Y-m-d H:i:s');
        $this->idUsuPre = auth()->id();
    }
    
    public function rules(): array
    {
        return [
            'idHerPre' => 'required|exists:herramientas,idHer',
            'idUsuPre' => 'required|exists:users,id',
            'idUsuSol' => 'required|exists:users,id',
            'fecPre' => 'required|date',
            'fecDev' => 'required|date|after_or_equal:fecPre',
            'estPre' => 'required|in:prestado,devuelto,vencido',
            'obsPre' => 'nullable|string'
        ];
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
        $this->idHerPre = 0;
        $this->idUsuSol = 0;
        $this->fecPre = now()->format('Y-m-d H:i:s');
        $this->fecDev = null;
        $this->obsPre = null;
        
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
        $validated = $this->validate();
        
        try {
            PrestamoHerramienta::create($validated);
            
            // Limpiar el formulario después del registro exitoso
            $this->limpiarFormulario();
            
            // Disparar evento para mostrar mensaje de éxito
            $this->dispatch('registro-exitoso');
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al registrar el préstamo: ' . $e->getMessage()
            ]);
        }
    }
    
    public function with(): array
    {
        return [
            'herramientas' => Herramienta::all(),
            'usuarioActual' => auth()->user(),
            'aprendices' => User::where('idRolUsu', 3)->get(), // Solo aprendices pueden solicitar herramientas
        ];
    }
}; ?>

@section('title', 'Registrar préstamo')

<div class="flex items-center justify-center min-h-screen py-3">
    <div class="w-full max-w-7xl mx-auto bg-white/80 backdrop-blur-xl shadow rounded-3xl p-3 relative border border-white/20">
        <!-- Botón Volver -->
        <div class="absolute top-2 right-2">
            <a href="{{ route('inventario.prestamos.index') }}" wire:navigate
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
                <div class="p-2 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <div class="w-4 h-4 text-white flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <h1 class="text-base font-black bg-gradient-to-r from-gray-900 via-gray-800 to-blue-800 bg-clip-text text-transparent mb-1">
                Registrar Nuevo Préstamo
            </h1>
            
            <template x-if="showSuccess">
                <div class="rounded bg-green-100 px-2 py-1 text-green-800 border border-green-400 text-xs mb-1 font-semibold">
                    ¡Préstamo registrado exitosamente!
                </div>
            </template>

            <template x-if="showCancel">
                <div class="rounded bg-yellow-100 px-2 py-1 text-yellow-800 border border-yellow-400 text-xs mb-1 font-semibold">
                    Formulario limpiado. Puede registrar un nuevo préstamo.
                </div>
            </template>

            <template x-if="!showSuccess && !showCancel">
                <p class="text-gray-600 text-xs">Complete la información para registrar un préstamo de herramienta</p>
            </template>
        </div>

        <!-- Formulario -->
        <form wire:submit="save" class="space-y-2">
            <!-- Fila 1: Información del Préstamo -->
            <div class="flex flex-col md:flex-row gap-2">
                <!-- Información de Herramienta y Usuarios -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#2563eb]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Información del Préstamo</h2>
                                <p class="text-gray-600 text-[10px]">Datos principales del préstamo</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <!-- Herramienta -->
                            <div>
                                <label for="idHerPre" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Herramienta <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <select id="idHerPre"
                                            wire:model="idHerPre"
                                            wire:blur="validateField('idHerPre')"
                                            class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('idHerPre') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                            required>
                                        <option value="">Seleccionar herramienta</option>
                                        @foreach($herramientas as $herramienta)
                                            <option value="{{ $herramienta->idHer }}" 
                                                    data-disponible="{{ $herramienta->canHer }}"
                                                    data-codigo="{{ $herramienta->codHer }}">
                                                {{ $herramienta->codHer }} - {{ $herramienta->nomHer }}
                                                (Disponibles: {{ $herramienta->canHer }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('idHerPre')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                                <p class="mt-0.5 text-[10px] text-gray-500 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    Solo se muestran herramientas con stock disponible
                                </p>
                            </div>

                            <!-- Estado -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Estado
                                </label>
                                <div class="w-full px-1.5 py-1 bg-gray-100/50 border-2 border-gray-200 rounded-2xl shadow-lg text-xs">
                                    Prestado
                                </div>
                                <input type="hidden" wire:model="estPre">
                            </div>

                            <!-- Encargado -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Encargado
                                </label>
                                <div class="w-full px-1.5 py-1 bg-gray-100/50 border-2 border-gray-200 rounded-2xl shadow-lg text-xs">
                                    {{ $usuarioActual->nomUsu }} {{ $usuarioActual->apeUsu }}
                                </div>
                                <input type="hidden" wire:model="idUsuPre">
                            </div>

                            <!-- Solicitante -->
                            <div>
                                <label for="idUsuSol" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Solicitante (Aprendiz) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <select id="idUsuSol"
                                            wire:model="idUsuSol"
                                            wire:blur="validateField('idUsuSol')"
                                            class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('idUsuSol') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                            required>
                                        <option value="">Seleccionar aprendiz</option>
                                        @foreach($aprendices as $aprendiz)
                                            <option value="{{ $aprendiz->id }}">
                                                {{ $aprendiz->nomUsu }} {{ $aprendiz->apeUsu }} ({{ $aprendiz->numDocUsu }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('idUsuSol')
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

                <!-- Fechas del Préstamo -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#2563eb] to-[#000000]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Fechas del Préstamo</h2>
                                <p class="text-gray-600 text-[10px]">Control de fechas de préstamo y devolución</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-2">
                            <!-- Fecha de Préstamo -->
                            <div>
                                <label for="fecPre" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Fecha y Hora de Préstamo
                                </label>
                                <div class="relative group">
                                    <input type="datetime-local"
                                           id="fecPre"
                                           wire:model="fecPre"
                                           readonly
                                           class="w-full px-1.5 py-1 bg-gray-100/50 border-2 border-gray-200 rounded-2xl shadow-lg cursor-not-allowed text-xs">
                                </div>
                            </div>

                            <!-- Fecha de Devolución -->
                            <div>
                                <label for="fecDev" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Fecha y Hora de Devolución <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="datetime-local"
                                           id="fecDev"
                                           wire:model="fecDev"
                                           wire:blur="validateField('fecDev')"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('fecDev') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                           required>
                                    <div class="absolute inset-0 bg-gradient-to-r from-green-500/5 to-emerald-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('fecDev')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                                <p class="mt-0.5 text-[10px] text-gray-500 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    Fecha y hora estimada de devolución (obligatoria)
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fila 2: Observaciones -->
            <div class="border border-gray-300 rounded-3xl overflow-hidden">
                <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#2563eb]"></div>
                <div class="p-2">
                    <div class="flex items-center space-x-2 mb-2">
                        <div class="p-1.5 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl shadow-lg">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xs font-bold text-gray-900">Observaciones</h2>
                            <p class="text-gray-600 text-[10px]">Detalles extras para referencia futura</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        <!-- Observaciones -->
                        <div>
                            <label for="obsPre" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                Observaciones y Notas
                            </label>
                            <div class="relative group">
                                <textarea id="obsPre"
                                          wire:model="obsPre"
                                          wire:blur="validateField('obsPre')"
                                          rows="3"
                                          class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('obsPre') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                          placeholder="Escriba cualquier observación adicional sobre el préstamo..."></textarea>
                                <div class="absolute inset-0 bg-gradient-to-r from-purple-500/5 to-pink-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                            </div>
                            @error('obsPre')
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
                        class="cursor-pointer group relative inline-flex items-center justify-center px-2.5 py-1 bg-gradient-to-r from-blue-600 to-blue-600 hover:from-blue-700 hover:to-blue-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                    <svg class="w-3 h-3 mr-1.5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <span class="relative z-10 text-xs">Registrar Préstamo</span>
                </button>
            </div>
        </form>
    </div>
</div>

@script
<script>
document.addEventListener('livewire:initialized', () => {
    // Configuración inicial de fecha y hora
    const now = new Date();
    const timezoneOffset = now.getTimezoneOffset() * 60000;
    const localISOTime = (new Date(now - timezoneOffset)).toISOString().slice(0, 16);
    
    // Establecer fecha y hora actual si está vacío
    const fecPre = document.getElementById('fecPre');
    if (!fecPre.value) {
        fecPre.value = localISOTime;
        @this.set('fecPre', localISOTime.replace('T', ' '));
    }

    // Validación de fechas
    const fecDev = document.getElementById('fecDev');
    
    // Establecer la fecha mínima de devolución como la fecha actual
    fecDev.min = fecPre.value;
    
    fecPre.addEventListener('change', function() {
        fecDev.min = this.value;
        if (fecDev.value && fecDev.value < this.value) {
            fecDev.value = '';
            @this.set('fecDev', null);
        }
    });

    // Asegurar que fecDev tenga al menos la fecha mínima
    fecDev.addEventListener('focus', function() {
        if (!this.value) {
            this.min = fecPre.value;
        }
    });
    
    // Información de herramientas
    const selectHerramienta = document.getElementById('idHerPre');
    selectHerramienta.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const disponibles = selectedOption.dataset.disponible;
        
        if (disponibles && parseInt(disponibles) <= 0) {
            alert('Esta herramienta no tiene stock disponible');
            this.value = '';
            @this.set('idHerPre', 0);
        }
    });
    
    // Convertir valores existentes al formato datetime-local
    function convertToDatetimeLocalFormat(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return new Date(date.getTime() - (date.getTimezoneOffset() * 60000))
            .toISOString()
            .slice(0, 16);
    }

    // Si hay valores precargados, convertirlos al formato correcto
    if (@this.get('fecPre')) {
        fecPre.value = convertToDatetimeLocalFormat(@this.get('fecPre'));
    }
    if (@this.get('fecDev')) {
        fecDev.value = convertToDatetimeLocalFormat(@this.get('fecDev'));
    }
});
</script>
@endscript