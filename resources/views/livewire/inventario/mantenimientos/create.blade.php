<?php
// resources/views/livewire/inventario/mantenimientos/create.blade.php

use App\Models\Mantenimiento;
use App\Models\Herramienta;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public $idHerMan = '';
    public string $fecMan = '';
    public string $tipMan = '';
    public string $estMan = 'pendiente';
    public ?string $desMan = null;
    public ?string $resMan = null;
    public ?string $obsMan = null;

    public $herramientas = [];

    public function mount()
    {
        $this->fecMan = now()->format('Y-m-d');
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        $this->herramientas = Herramienta::whereNull('deleted_at')
            ->orderBy('nomHer')
            ->get(['idHer', 'nomHer', 'canHer']);
    }

    public function rules(): array
    {
        return [
            'idHerMan' => 'required|exists:herramientas,idHer',
            'fecMan' => 'required|date',
            'tipMan' => 'required|in:preventivo,correctivo,predictivo',
            'estMan' => 'required|in:pendiente,en proceso,completado',
            'desMan' => 'nullable|string',
            'resMan' => 'nullable|string|max:100|required_if:estMan,completado',
            'obsMan' => 'nullable|string'
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

    public function save(): void
    {
        $validated = $this->validate($this->rules());

        try {
            // Obtener la herramienta seleccionada
            $herramienta = Herramienta::find($validated['idHerMan']);

            // Validar que la herramienta existe y tiene un nombre válido
            if (!$herramienta || empty($herramienta->nomHer)) {
                throw new \Exception('La herramienta seleccionada no existe o no tiene un nombre válido.');
            }

            $data = [
                'idHerMan' => $validated['idHerMan'],
                'nomHerMan' => $herramienta->nomHer, // Asignar automáticamente el nombre de la herramienta
                'fecMan' => $validated['fecMan'],
                'tipMan' => $validated['tipMan'],
                'estMan' => $validated['estMan'],
                'desMan' => $validated['desMan'],
                'resMan' => $validated['resMan'],
                'obsMan' => $validated['obsMan']
            ];
            
            Mantenimiento::create($data);

            // Limpiar el formulario después del registro exitoso
            $this->limpiarFormulario();
            
            // Disparar evento para mostrar mensaje de éxito
            $this->dispatch('registro-exitoso');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = [];
            foreach ($e->errors() as $field => $messages) {
                $errors[] = $field . ': ' . implode(', ', $messages);
            }
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error de validación: ' . implode(' | ', $errors)
            ]);
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No se pudo registrar el mantenimiento: ' . $e->getMessage()
            ]);
        }
    }

    // Método para limpiar todos los campos
    public function limpiarFormulario(): void
    {
        $this->idHerMan = '';
        $this->fecMan = now()->format('Y-m-d');
        $this->tipMan = '';
        $this->estMan = 'pendiente';
        $this->desMan = null;
        $this->resMan = null;
        $this->obsMan = null;
        
        $this->resetErrorBag();
    }

    // Método para cancelar registro (limpia el formulario)
    public function cancelarRegistro(): void
    {
        $this->limpiarFormulario();
        
        // Disparar evento para mostrar mensaje
        $this->dispatch('registro-cancelado');
    }

    public function getStockActual($id)
    {
        if ($id) {
            $herramienta = $this->herramientas->firstWhere('idHer', $id);
            return $herramienta ? $herramienta->canHer : 0;
        }
        
        return 0;
    }
}; ?>

@section('title', 'Nuevo Mantenimiento')

<!-- SOLUCIÓN: Un solo div contenedor que envuelve todo -->
<div>
    <div class="flex items-center justify-center min-h-screen py-3">
        <div class="w-full max-w-7xl mx-auto bg-white/80 backdrop-blur-xl shadow rounded-3xl p-3 relative border border-white/20">
            <!-- Botón Volver -->
            <div class="absolute top-2 right-2">
                <a href="{{ route('inventario.mantenimientos.index') }}" wire:navigate
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
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <h1 class="text-base font-black bg-gradient-to-r from-gray-900 via-gray-800 to-green-800 bg-clip-text text-transparent mb-1">
                    Programar Nuevo Mantenimiento
                </h1>
                
                <template x-if="showSuccess">
                    <div class="rounded bg-green-100 px-2 py-1 text-green-800 border border-green-400 text-xs mb-1 font-semibold">
                        ¡Mantenimiento programado exitosamente!
                    </div>
                </template>

                <template x-if="showCancel">
                    <div class="rounded bg-yellow-100 px-2 py-1 text-yellow-800 border border-yellow-400 text-xs mb-1 font-semibold">
                        Formulario limpiado. Puede programar un nuevo mantenimiento.
                    </div>
                </template>

                <template x-if="!showSuccess && !showCancel">
                    <p class="text-gray-600 text-xs">Complete los datos del mantenimiento programado</p>
                </template>
            </div>

            <!-- Formulario -->
            <form wire:submit="save" class="space-y-2">
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
                                    <p class="text-gray-600 text-[10px]">Datos principales del mantenimiento</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <!-- Herramienta -->
                                <div class="md:col-span-2">
                                    <label for="idHerMan" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                        Herramienta <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative group">
                                        <select id="idHerMan"
                                                wire:model.live="idHerMan"
                                                wire:blur="validateField('idHerMan')"
                                                class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('idHerMan') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                            <option value="">Seleccionar herramienta</option>
                                            @foreach($herramientas as $herramienta)
                                                <option value="{{ $herramienta->idHer }}">
                                                    {{ $herramienta->nomHer }} (Stock: {{ $herramienta->canHer }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    @error('idHerMan')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                    @enderror
                                    
                                    @if($idHerMan)
                                        <div class="mt-1 text-[10px] text-blue-600 font-medium">
                                            Herramienta seleccionada: {{ $herramientas->firstWhere('idHer', $idHerMan)->nomHer ?? 'No disponible' }} (Stock: {{ $this->getStockActual($idHerMan) }} unidades)
                                        </div>
                                    @endif
                                </div>

                                <!-- Tipo de Mantenimiento -->
                                <div>
                                    <label for="tipMan" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                        Tipo <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative group">
                                        <select id="tipMan"
                                                wire:model="tipMan"
                                                wire:blur="validateField('tipMan')"
                                                class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('tipMan') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                            <option value="">Seleccionar tipo</option>
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
                                        Estado <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative group">
                                        <select id="estMan"
                                                wire:model="estMan"
                                                wire:blur="validateField('estMan')"
                                                class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('estMan') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
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
                                        <input type="date"
                                               id="fecMan"
                                               wire:model="fecMan"
                                               wire:blur="validateField('fecMan')"
                                               class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('fecMan') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                               required>
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
                                @if($estMan === 'completado')
                                <div class="md:col-span-2">
                                    <label for="resMan" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                        Resultado <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative group">
                                        <input type="text"
                                               id="resMan"
                                               wire:model="resMan"
                                               wire:blur="validateField('resMan')"
                                               class="w-full px-1.5 py-1 bg-green-50/50 border-2 border-green-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('resMan') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                               placeholder="Describa el resultado del mantenimiento..."
                                               maxlength="100">
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
                                @endif
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
                                        Descripción
                                    </label>
                                    <div class="relative group">
                                        <textarea id="desMan"
                                                  wire:model="desMan"
                                                  wire:blur="validateField('desMan')"
                                                  rows="4"
                                                  class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('desMan') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                                  placeholder="Describa detalladamente el mantenimiento a realizar, procedimientos, materiales necesarios..."></textarea>
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
                                        Observaciones
                                    </label>
                                    <div class="relative group">
                                        <textarea id="obsMan"
                                                  wire:model="obsMan"
                                                  wire:blur="validateField('obsMan')"
                                                  rows="3"
                                                  class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('obsMan') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                                  placeholder="Observaciones adicionales, recomendaciones, notas importantes..."></textarea>
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
                        <span class="relative z-10 text-xs">Programar Mantenimiento</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>