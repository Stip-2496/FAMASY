<?php
// resources/views/livewire/inventario/mantenimientos/edit.blade.php

use App\Models\Mantenimiento;
use App\Models\Herramienta;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public Mantenimiento $mantenimiento;
    
    // Propiedades públicas para el formulario
    public string $nombreHerramienta;
    public string $fecMan;
    public string $tipMan;
    public string $estMan;
    public ?string $desMan = null;
    public ?string $resMan = null;
    public ?string $obsMan = null;

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

    // Método para reglas de validación
    public function rules(): array
    {
        $rules = [
            'nombreHerramienta' => 'required|string|max:255',
            'fecMan' => 'required|date',
            'tipMan' => 'required|in:preventivo,correctivo,predictivo',
            'estMan' => 'required|in:pendiente,en proceso,completado',
            'desMan' => 'nullable|string',
            'obsMan' => 'nullable|string'
        ];

        // Si el estado es completado, el resultado es requerido
        if ($this->estMan === 'completado') {
            $rules['resMan'] = 'required|string|max:100';
        } else {
            $rules['resMan'] = 'nullable|string|max:100';
        }

        return $rules;
    }

    // Inicializar componente
    public function mount(Mantenimiento $mantenimiento): void
    {
        $this->mantenimiento = $mantenimiento;
        $this->nombreHerramienta = $mantenimiento->getNombreHerramientaCompleto();
        $this->fecMan = $mantenimiento->fecMan->format('Y-m-d');
        $this->tipMan = $mantenimiento->tipMan;
        $this->estMan = $mantenimiento->estMan;
        $this->desMan = $mantenimiento->desMan;
        $this->resMan = $mantenimiento->resMan;
        $this->obsMan = $mantenimiento->obsMan;
    }

    // Actualizar mantenimiento
    public function update(): void
    {
        $validated = $this->validate();
        
        try {
            $this->mantenimiento->update([
                'nomHerMan' => $validated['nombreHerramienta'],
                'fecMan' => $validated['fecMan'],
                'tipMan' => $validated['tipMan'],
                'estMan' => $validated['estMan'],
                'desMan' => $validated['desMan'],
                'resMan' => $validated['resMan'],
                'obsMan' => $validated['obsMan']
            ]);
            
            $this->dispatch('actualizacion-exitosa');
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar el mantenimiento: ' . $e->getMessage()
            ]);
        }
    }

    // Método para resetear el formulario a los valores originales
    public function resetForm(): void
    {
        $this->nombreHerramienta = $this->mantenimiento->getNombreHerramientaCompleto();
        $this->fecMan = $this->mantenimiento->fecMan->format('Y-m-d');
        $this->tipMan = $this->mantenimiento->tipMan;
        $this->estMan = $this->mantenimiento->estMan;
        $this->desMan = $this->mantenimiento->desMan;
        $this->resMan = $this->mantenimiento->resMan;
        $this->obsMan = $this->mantenimiento->obsMan;
        
        $this->resetErrorBag();
        $this->dispatch('actualizacion-cancelada');
    }
}; ?>

@section('title', 'Editar Mantenimiento')

<div class="flex items-center justify-center min-h-screen py-3">
    <div class="w-full max-w-7xl mx-auto bg-white/80 backdrop-blur-xl shadow rounded-3xl p-3 relative border border-white/20">
        <!-- Botón Volver -->
        <div class="absolute top-2 right-2">
            <a href="{{ route('inventario.mantenimientos.index', $mantenimiento) }}" wire:navigate
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
             x-on:actualizacion-exitosa.window="showSuccess = true; showCancel = false; setTimeout(() => showSuccess = false, 3000)"
             x-on:actualizacion-cancelada.window="showCancel = true; showSuccess = false; setTimeout(() => showCancel = false, 3000)">
            <div class="flex justify-center mb-1">
                <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </div>
            </div>
            <h1 class="text-base font-black bg-gradient-to-r from-gray-900 via-gray-800 to-green-800 bg-clip-text text-transparent mb-1">
                Editar Mantenimiento
            </h1>
            
            <template x-if="showSuccess">
                <div class="rounded bg-green-100 px-2 py-1 text-green-800 border border-green-400 text-xs mb-1 font-semibold">
                    ¡Mantenimiento actualizado exitosamente!
                </div>
            </template>

            <template x-if="showCancel">
                <div class="rounded bg-yellow-100 px-2 py-1 text-yellow-800 border border-yellow-400 text-xs mb-1 font-semibold">
                    Cambios descartados. Los datos se han restablecido.
                </div>
            </template>

            <template x-if="!showSuccess && !showCancel">
                <p class="text-gray-600 text-xs">Modifica y actualiza los datos del mantenimiento</p>
            </template>
        </div>

        <!-- Formulario -->
        <form wire:submit="update" class="space-y-2">
            <!-- Fila 1: Información principal -->
            <div class="flex flex-col md:flex-row gap-2">
                <!-- Información del Mantenimiento -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#39A900]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Información del Mantenimiento</h2>
                                <p class="text-gray-600 text-[10px]">Edita los datos principales del mantenimiento</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <!-- Herramienta -->
                            <div class="md:col-span-2">
                                <label for="nombreHerramienta" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    🔧 Nombre de la Herramienta <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="text" wire:model="nombreHerramienta" id="nombreHerramienta" required maxlength="255"
                                           wire:blur="validateField('nombreHerramienta')"
                                           placeholder="Ingrese el nombre completo de la herramienta..."
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('nombreHerramienta') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                    <div class="absolute inset-0 bg-gradient-to-r from-green-500/5 to-emerald-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('nombreHerramienta')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                                
                                @if($mantenimiento->herramienta)
                                <div class="mt-1 bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-2xl p-2 text-[10px]">
                                    <div class="flex items-start space-x-2">
                                        <div class="p-1 bg-blue-500 rounded-lg">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-blue-900 mb-0.5">Herramienta Registrada</h4>
                                            <p class="text-blue-800 leading-tight">
                                                Esta herramienta está en el sistema. Puedes cambiar el nombre si es necesario.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="mt-1 bg-gradient-to-br from-amber-50 to-yellow-50 border-2 border-amber-200 rounded-2xl p-2 text-[10px]">
                                    <div class="flex items-start space-x-2">
                                        <div class="p-1 bg-amber-500 rounded-lg">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.314 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-amber-900 mb-0.5">Herramienta Personalizada</h4>
                                            <p class="text-amber-800 leading-tight">
                                                Esta herramienta no está registrada en el sistema inventario.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Tipo de Mantenimiento -->
                            <div>
                                <label for="tipMan" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Tipo de Mantenimiento <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <select wire:model="tipMan" id="tipMan" required
                                            wire:blur="validateField('tipMan')"
                                            class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('tipMan') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                        <option value="">Seleccione un tipo</option>
                                        <option value="preventivo">Preventivo</option>
                                        <option value="correctivo">Correctivo</option>
                                        <option value="predictivo">Predictivo</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('tipMan')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Estado -->
                            <div>
                                <label for="estMan" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Estado Actual <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <select wire:model.live="estMan" id="estMan" required
                                            wire:blur="validateField('estMan')"
                                            class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('estMan') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                        <option value="">Seleccione un estado</option>
                                        <option value="pendiente">Pendiente</option>
                                        <option value="en proceso">En Proceso</option>
                                        <option value="completado">Completado</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('estMan')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Fecha del Mantenimiento -->
                            <div>
                                <label for="fecMan" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Fecha Programada <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="date" wire:model="fecMan" id="fecMan" required
                                           wire:blur="validateField('fecMan')"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('fecMan') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('fecMan')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Resultado (solo si está completado) -->
                            <div id="campoResultado" class="md:col-span-1" style="{{ $estMan === 'completado' ? 'display: block;' : 'display: none;' }}">
                                <label for="resMan" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Resultado del Mantenimiento
                                    <span id="resultadoRequerido" class="text-red-500" style="{{ $estMan === 'completado' ? 'display: inline;' : 'display: none;' }}">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="text" wire:model="resMan" id="resMan" maxlength="100"
                                           wire:blur="validateField('resMan')"
                                           placeholder="Describa detalladamente el resultado..."
                                           {{ $estMan === 'completado' ? 'required' : '' }}
                                           class="w-full px-1.5 py-1 bg-green-50/50 border-2 border-green-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('resMan') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                    <div class="absolute inset-0 bg-gradient-to-r from-green-500/5 to-emerald-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('resMan')
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

                <!-- Detalles Adicionales -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#39A900] to-[#000000]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Detalles Adicionales</h2>
                                <p class="text-gray-600 text-[10px]">Información complementaria del mantenimiento</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-2">
                            <!-- Descripción -->
                            <div>
                                <label for="desMan" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Descripción del Mantenimiento
                                </label>
                                <div class="relative group">
                                    <textarea wire:model="desMan" id="desMan" rows="4"
                                              wire:blur="validateField('desMan')"
                                              placeholder="Describa detalladamente el mantenimiento..."
                                              class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl resize-none text-xs @error('desMan') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"></textarea>
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-purple-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('desMan')
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
                                <label for="obsMan" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Observaciones Adicionales
                                </label>
                                <div class="relative group">
                                    <textarea wire:model="obsMan" id="obsMan" rows="3"
                                              wire:blur="validateField('obsMan')"
                                              placeholder="Observaciones adicionales..."
                                              class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl resize-none text-xs @error('obsMan') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"></textarea>
                                    <div class="absolute inset-0 bg-gradient-to-r from-purple-500/5 to-pink-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('obsMan')
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
                <button type="button" wire:click="resetForm"
                   class="cursor-pointer group relative inline-flex items-center justify-center px-2.5 py-1 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                    <svg class="w-3 h-3 mr-1.5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span class="relative z-10 text-xs">Cancelar Cambios</span>
                </button>
                <button type="submit"
                        class="cursor-pointer group relative inline-flex items-center justify-center px-2.5 py-1 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                    <svg class="w-3 h-3 mr-1.5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="relative z-10 text-xs">Guardar Cambios</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('livewire:initialized', () => {
    // Función para actualizar la visibilidad del campo resultado
    function updateResultadoVisibility() {
        const estManSelect = document.getElementById('estMan');
        const campoResultado = document.getElementById('campoResultado');
        const resultadoInput = document.getElementById('resMan');
        const resultadoRequerido = document.getElementById('resultadoRequerido');
        
        if (estManSelect && campoResultado && resultadoInput && resultadoRequerido) {
            if (estManSelect.value === 'completado') {
                campoResultado.style.display = 'block';
                resultadoInput.required = true;
                resultadoRequerido.style.display = 'inline';
            } else {
                campoResultado.style.display = 'none';
                resultadoInput.required = false;
                resultadoRequerido.style.display = 'none';
            }
        }
    }

    // Escuchar cambios en el select de estado
    const estManSelect = document.getElementById('estMan');
    if (estManSelect) {
        estManSelect.addEventListener('change', updateResultadoVisibility);
    }

    // Ejecutar una vez al cargar para establecer el estado inicial
    updateResultadoVisibility();

    // También escuchar eventos de Livewire
    Livewire.on('estMan', (value) => {
        updateResultadoVisibility();
    });
});
</script>