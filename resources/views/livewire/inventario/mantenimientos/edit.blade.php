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
            
            session()->flash('success', 'Mantenimiento actualizado exitosamente.');
            $this->redirect(route('inventario.mantenimientos.show', $this->mantenimiento), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al actualizar el mantenimiento: ' . $e->getMessage());
        }
    }

    // Eliminar mantenimiento
    public function delete(): void
    {
        try {
            $this->mantenimiento->delete();
            session()->flash('success', 'Mantenimiento eliminado exitosamente.');
            $this->redirect(route('inventario.mantenimientos.index'), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el mantenimiento: ' . $e->getMessage());
        }
    }
}; ?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header Ultra Moderno -->
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 p-8 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-green-500/5 to-emerald-500/5"></div>
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-green-400/10 to-emerald-600/10 rounded-full -mr-16 -mt-16"></div>
                
                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center space-x-4 mb-6 lg:mb-0">
                        <div class="p-4 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-4xl font-black bg-gradient-to-r from-gray-900 via-gray-800 to-green-800 bg-clip-text text-transparent leading-tight">
                                Editar Mantenimiento #{{ $mantenimiento->idMan }}
                            </h1>
                            <p class="text-gray-600 mt-1 text-lg">Modifica y actualiza los datos del mantenimiento</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('inventario.mantenimientos.show', $mantenimiento) }}" wire:navigate
                           class="group relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                            <svg class="w-5 h-5 mr-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            <span class="relative z-10">Cancelar Edición</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulario Principal -->
        <form wire:submit="update" id="editMantenimientoForm">
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <!-- Información Principal -->
                <div class="xl:col-span-2 space-y-8">
                    <!-- Datos del Mantenimiento -->
                    <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 overflow-hidden">
                        <div class="h-2 bg-gradient-to-r from-green-500 to-emerald-500"></div>
                        <div class="p-8">
                            <div class="flex items-center space-x-3 mb-8">
                                <div class="p-3 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-900">Información del Mantenimiento</h2>
                                    <p class="text-gray-600">Edita los datos principales del mantenimiento</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Herramienta -->
                                <div class="lg:col-span-2">
                                    <label for="nombreHerramienta" class="block text-sm font-bold text-gray-700 mb-3">
                                        🔧 Nombre de la Herramienta <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative group">
                                        <input type="text" wire:model="nombreHerramienta" id="nombreHerramienta" required maxlength="255"
                                               placeholder="Ingrese el nombre completo de la herramienta..."
                                               class="w-full px-5 py-4 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 group-hover:shadow-xl @error('nombreHerramienta') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                        <div class="absolute inset-0 bg-gradient-to-r from-green-500/5 to-emerald-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                    </div>
                                    @error('nombreHerramienta')
                                        <p class="mt-2 text-sm text-red-600 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                    
                                    @if($mantenimiento->herramienta)
                                    <div class="mt-3 bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-2xl p-4">
                                        <div class="flex items-start space-x-3">
                                            <div class="p-2 bg-blue-500 rounded-lg">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-blue-900 text-sm mb-1">✅ Herramienta Registrada</h4>
                                                <p class="text-xs text-blue-800 leading-relaxed">
                                                    Esta herramienta está en el sistema. Puedes cambiar el nombre si es necesario.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    @else
                                    <div class="mt-3 bg-gradient-to-br from-amber-50 to-yellow-50 border-2 border-amber-200 rounded-2xl p-4">
                                        <div class="flex items-start space-x-3">
                                            <div class="p-2 bg-amber-500 rounded-lg">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.314 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-amber-900 text-sm mb-1">⚠️ Herramienta Personalizada</h4>
                                                <p class="text-xs text-amber-800 leading-relaxed">
                                                    Esta herramienta no está registrada en el sistema inventario.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <!-- Tipo de Mantenimiento -->
                                <div>
                                    <label for="tipMan" class="block text-sm font-bold text-gray-700 mb-3">
                                        ⚙️ Tipo de Mantenimiento <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative group">
                                        <select wire:model="tipMan" id="tipMan" required
                                                class="w-full px-5 py-4 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 group-hover:shadow-xl appearance-none @error('tipMan') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                            <option value="">Seleccione un tipo</option>
                                            <option value="preventivo">🛡️ Preventivo</option>
                                            <option value="correctivo">🔧 Correctivo</option>
                                            <option value="predictivo">📊 Predictivo</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    @error('tipMan')
                                        <p class="mt-2 text-sm text-red-600 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Estado -->
                                <div>
                                    <label for="estMan" class="block text-sm font-bold text-gray-700 mb-3">
                                        📊 Estado Actual <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative group">
                                        <select wire:model.live="estMan" id="estMan" required
                                                class="w-full px-5 py-4 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 group-hover:shadow-xl appearance-none @error('estMan') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                            <option value="">Seleccione un estado</option>
                                            <option value="pendiente">🔄 Pendiente</option>
                                            <option value="en proceso">⚙️ En Proceso</option>
                                            <option value="completado">✅ Completado</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    @error('estMan')
                                        <p class="mt-2 text-sm text-red-600 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Fecha del Mantenimiento -->
                                <div>
                                    <label for="fecMan" class="block text-sm font-bold text-gray-700 mb-3">
                                        📅 Fecha Programada <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative group">
                                        <input type="date" wire:model="fecMan" id="fecMan" required
                                               class="w-full px-5 py-4 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 group-hover:shadow-xl @error('fecMan') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                    </div>
                                    @error('fecMan')
                                        <p class="mt-2 text-sm text-red-600 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Creado -->
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-3">📝 Fecha de Creación</label>
                                    <div class="bg-gradient-to-br from-gray-50 to-blue-50 border-2 border-gray-200 rounded-2xl p-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="p-2 bg-gray-500 rounded-lg">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                            <p class="text-gray-700 font-medium">
                                                {{ $mantenimiento->created_at ? $mantenimiento->created_at->format('d/m/Y H:i') : 'No disponible' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Resultado (solo si está completado) -->
                                <div id="campoResultado" class="lg:col-span-2" style="{{ $estMan === 'completado' ? 'display: block;' : 'display: none;' }}">
                                    <label for="resMan" class="block text-sm font-bold text-gray-700 mb-3">
                                        ✅ Resultado del Mantenimiento
                                        <span id="resultadoRequerido" class="text-red-500" style="{{ $estMan === 'completado' ? 'display: inline;' : 'display: none;' }}">*</span>
                                    </label>
                                    <div class="relative group">
                                        <input type="text" wire:model="resMan" id="resMan" maxlength="100"
                                               placeholder="Describa detalladamente el resultado del mantenimiento realizado..."
                                               {{ $estMan === 'completado' ? 'required' : '' }}
                                               class="w-full px-5 py-4 bg-green-50/50 border-2 border-green-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 group-hover:shadow-xl @error('resMan') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                        <div class="absolute inset-0 bg-gradient-to-r from-green-500/5 to-emerald-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                    </div>
                                    @error('resMan')
                                        <p class="mt-2 text-sm text-red-600 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Descripción -->
                            <div class="mt-8">
                                <label for="desMan" class="block text-sm font-bold text-gray-700 mb-3">
                                    📝 Descripción del Mantenimiento
                                </label>
                                <div class="relative group">
                                    <textarea wire:model="desMan" id="desMan" rows="5"
                                              placeholder="Describa detalladamente el mantenimiento a realizar, incluyendo procedimientos específicos, materiales necesarios, etc."
                                              class="w-full px-5 py-4 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 group-hover:shadow-xl resize-none @error('desMan') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"></textarea>
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-purple-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('desMan')
                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Observaciones -->
                            <div class="mt-8">
                                <label for="obsMan" class="block text-sm font-bold text-gray-700 mb-3">
                                    💭 Observaciones Adicionales
                                </label>
                                <div class="relative group">
                                    <textarea wire:model="obsMan" id="obsMan" rows="4"
                                              placeholder="Observaciones adicionales, recomendaciones, notas importantes..."
                                              class="w-full px-5 py-4 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 group-hover:shadow-xl resize-none @error('obsMan') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"></textarea>
                                    <div class="absolute inset-0 bg-gradient-to-r from-purple-500/5 to-pink-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('obsMan')
                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel Lateral -->
                <div class="xl:col-span-1 space-y-8">
                    <!-- Estado Actual -->
                    <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 overflow-hidden">
                        <div class="h-2 bg-gradient-to-r from-blue-500 to-indigo-500"></div>
                        <div class="p-6">
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="p-3 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">Estado Actual</h3>
                                    <p class="text-sm text-gray-600">Información previa</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <!-- Estado -->
                                <div class="bg-gradient-to-br from-gray-50 to-blue-50 border-2 border-gray-200 rounded-2xl p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-bold text-gray-700">📊 Estado:</span>
                                        @php
                                            $estadoConfig = [
                                                'pendiente' => ['bg' => 'from-orange-500 to-red-500', 'icon' => '🔄'],
                                                'en proceso' => ['bg' => 'from-yellow-500 to-amber-500', 'icon' => '⚙️'],
                                                'completado' => ['bg' => 'from-green-500 to-emerald-500', 'icon' => '✅']
                                            ];
                                            $config = $estadoConfig[$mantenimiento->estMan] ?? ['bg' => 'from-gray-500 to-gray-600', 'icon' => '❓'];
                                        @endphp
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r {{ $config['bg'] }} text-white shadow-lg">
                                            {{ $config['icon'] }} {{ ucfirst($mantenimiento->estMan) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Tipo -->
                                <div class="bg-gradient-to-br from-gray-50 to-blue-50 border-2 border-gray-200 rounded-2xl p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-bold text-gray-700">🔧 Tipo:</span>
                                        @php
                                            $tipoConfig = [
                                                'preventivo' => ['bg' => 'from-green-500 to-emerald-500', 'icon' => '🛡️'],
                                                'correctivo' => ['bg' => 'from-red-500 to-pink-500', 'icon' => '🔧'],
                                                'predictivo' => ['bg' => 'from-blue-500 to-indigo-500', 'icon' => '📊']
                                            ];
                                            $config = $tipoConfig[$mantenimiento->tipMan] ?? ['bg' => 'from-gray-500 to-gray-600', 'icon' => '❓'];
                                        @endphp
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r {{ $config['bg'] }} text-white shadow-lg">
                                            {{ $config['icon'] }} {{ ucfirst($mantenimiento->tipMan) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Fecha programada original -->
                                <div class="bg-gradient-to-br from-purple-50 to-pink-50 border-2 border-purple-200 rounded-2xl p-4">
                                    <span class="text-sm font-bold text-purple-700 block mb-2">📅 Fecha Original:</span>
                                    <p class="text-purple-900 font-medium">{{ $mantenimiento->fecMan->format('d/m/Y') }}</p>
                                    <p class="text-xs text-purple-700 mt-1">{{ $mantenimiento->fecMan->diffForHumans() }}</p>
                                </div>

                                @if($mantenimiento->resMan)
                                <div class="bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-200 rounded-2xl p-4">
                                    <span class="text-sm font-bold text-green-700 block mb-2">✅ Resultado Actual:</span>
                                    <p class="text-green-900 text-sm leading-relaxed">{{ $mantenimiento->resMan }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Información de la Herramienta -->
                    @if($mantenimiento->herramienta)
                    <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 overflow-hidden">
                        <div class="h-2 bg-gradient-to-r from-purple-500 to-pink-500"></div>
                        <div class="p-6">
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="p-3 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl shadow-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">Herramienta</h3>
                                    <p class="text-sm text-gray-600">Datos del inventario</p>
                                </div>
                            </div>

                            <div class="bg-gradient-to-br from-purple-50 to-pink-50 border-2 border-purple-200 rounded-2xl p-6 mb-6">
                                <div class="space-y-4">
                                    <div>
                                        <span class="text-xs font-bold text-purple-700 block mb-1">🏷️ Nombre:</span>
                                        <p class="text-purple-900 font-medium">{{ $mantenimiento->herramienta->nomHer }}</p>
                                    </div>
                                    
                                    @if($mantenimiento->herramienta->catHer)
                                    <div>
                                        <span class="text-xs font-bold text-purple-700 block mb-1">📂 Categoría:</span>
                                        <p class="text-purple-900 font-medium">{{ $mantenimiento->herramienta->catHer }}</p>
                                    </div>
                                    @endif

                                    @if($mantenimiento->herramienta->marHer)
                                    <div>
                                        <span class="text-xs font-bold text-purple-700 block mb-1">🏭 Marca:</span>
                                        <p class="text-purple-900 font-medium">{{ $mantenimiento->herramienta->marHer }}</p>
                                    </div>
                                    @endif

                                    @if($mantenimiento->herramienta->estHer)
                                    <div>
                                        <span class="text-xs font-bold text-purple-700 block mb-1">⚡ Estado:</span>
                                        <p class="text-purple-900 font-medium">{{ ucfirst($mantenimiento->herramienta->estHer) }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <a href="{{ route('inventario.herramientas.show', $mantenimiento->herramienta) }}" wire:navigate
                               class="w-full group relative inline-flex items-center justify-center px-6 py-4 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                                <svg class="w-5 h-5 mr-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 616 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <span class="relative z-10">🔍 Ver Herramienta Completa</span>
                            </a>
                        </div>
                    </div>
                    @endif

                    <!-- Panel de Acciones -->
                    <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 overflow-hidden">
                        <div class="h-2 bg-gradient-to-r from-emerald-500 to-green-500"></div>
                        <div class="p-6">
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="p-3 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl shadow-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">Acciones</h3>
                                    <p class="text-sm text-gray-600">Controles disponibles</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <button type="submit" 
                                        class="w-full group relative inline-flex items-center justify-center px-6 py-4 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                                    <svg class="w-5 h-5 mr-3 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="relative z-10">💾 Guardar Cambios</span>
                                </button>

                                <a href="{{ route('inventario.mantenimientos.show', $mantenimiento) }}" wire:navigate
                                   class="w-full group relative inline-flex items-center justify-center px-6 py-4 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                                    <svg class="w-5 h-5 mr-3 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    <span class="relative z-10">❌ Cancelar Edición</span>
                                </a>

                                @if($mantenimiento->estMan === 'pendiente')
                                <button type="button" 
                                        wire:click="delete"
                                        wire:confirm="¿Está seguro de eliminar este mantenimiento? Esta acción no se puede deshacer."
                                        class="w-full group relative inline-flex items-center justify-center px-6 py-4 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                                    <svg class="w-5 h-5 mr-3 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    <span class="relative z-10">🗑️ Eliminar Mantenimiento</span>
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
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