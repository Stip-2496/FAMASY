<?php
// resources/views/livewire/inventario/mantenimientos/create.blade.php

use App\Models\Mantenimiento;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public Mantenimiento $mantenimiento;
    
    public string $nomHerMan = '';
    public string $fecMan = '';
    public string $tipMan = '';
    public string $estMan = 'pendiente';
    public ?string $desMan = null;
    public ?string $resMan = null;
    public ?string $obsMan = null;
    
    // Para mostrar mensajes de debug
    public string $debugMessage = '';
    public string $debugType = '';

    public function mount(): void
    {
        $this->fecMan = now()->format('Y-m-d');
        $this->mantenimiento = new Mantenimiento();
        $this->debugMessage = 'Componente inicializado correctamente';
        $this->debugType = 'info';
    }

    public function rules(): array
    {
        return [
            'nomHerMan' => 'required|string|max:255',
            'fecMan' => 'required|date',
            'tipMan' => 'required|in:preventivo,correctivo,predictivo',
            'estMan' => 'required|in:pendiente,en proceso,completado',
            'desMan' => 'nullable|string',
            'resMan' => 'nullable|string|max:100|required_if:estMan,completado',
            'obsMan' => 'nullable|string'
        ];
    }

    // TEST: Método súper simple para probar que wire:click funciona
    public function testButton(): void
    {
        $this->debugMessage = '¡EL BOTÓN FUNCIONA! wire:click está trabajando correctamente';
        $this->debugType = 'success';
    }

    public function save(): void
    {
        try {
            // DEBUG: Verificar que el método se ejecuta
            $this->debugMessage = 'PASO 1: Método save() ejecutado correctamente';
            $this->debugType = 'info';
            
            // Verificar que tenemos datos
            if (empty($this->nomHerMan) || empty($this->tipMan)) {
                $this->debugMessage = 'ERROR: Faltan datos obligatorios - Nombre: "' . $this->nomHerMan . '" - Tipo: "' . $this->tipMan . '"';
                $this->debugType = 'error';
                return;
            }
            
            // DEBUG: Mostrar datos antes de validar
            $this->debugMessage = 'PASO 2: Validando datos... Nombre: ' . $this->nomHerMan . ' - Tipo: ' . $this->tipMan;
            $this->debugType = 'info';
            
            $validated = $this->validate();
            
            // DEBUG: Validación exitosa
            $this->debugMessage = 'PASO 3: Validación exitosa, creando registro...';
            $this->debugType = 'info';
            
            // Verificar que la tabla existe
            if (!\Schema::hasTable('mantenimientos')) {
                $this->debugMessage = 'ERROR: La tabla "mantenimientos" no existe en la base de datos';
                $this->debugType = 'error';
                return;
            }
            
            // ✅ SOLUCIÓN FINAL: No incluir idHerMan cuando usamos nombre libre
            $data = [
                'nomHerMan' => $validated['nomHerMan'],
                'fecMan' => $validated['fecMan'],
                'tipMan' => $validated['tipMan'],
                'estMan' => $validated['estMan'],
                'desMan' => $validated['desMan'],
                'resMan' => $validated['resMan'],
                'obsMan' => $validated['obsMan']
            ];
            
            // NO incluir idHerMan para evitar el error de clave foránea
            // Solo se incluiría si el usuario selecciona una herramienta del catálogo
            
            $mantenimiento = Mantenimiento::create($data);
            
            if ($mantenimiento) {
                $this->debugMessage = 'PASO 4: ¡ÉXITO! Mantenimiento creado con ID: ' . $mantenimiento->idMan;
                $this->debugType = 'success';
                
                // Limpiar el formulario después del éxito
                $this->clear();
                
                // Opcional: Redireccionar después de un delay
                // $this->redirect(route('inventario.mantenimientos.index'), navigate: true);
            } else {
                $this->debugMessage = 'ERROR: No se pudo crear el mantenimiento';
                $this->debugType = 'error';
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = [];
            foreach ($e->errors() as $field => $messages) {
                $errors[] = $field . ': ' . implode(', ', $messages);
            }
            $this->debugMessage = 'ERROR VALIDACIÓN: ' . implode(' | ', $errors);
            $this->debugType = 'error';
            
        } catch (\Exception $e) {
            $this->debugMessage = 'ERROR GENERAL: ' . $e->getMessage() . ' (Línea: ' . $e->getLine() . ')';
            $this->debugType = 'error';
        }
    }

    public function clear(): void
    {
        $this->nomHerMan = '';
        $this->fecMan = now()->format('Y-m-d');
        $this->tipMan = '';
        $this->estMan = 'pendiente';
        $this->desMan = null;
        $this->resMan = null;
        $this->obsMan = null;
        
        $this->resetValidation();
        $this->debugMessage = 'Formulario limpiado correctamente';
        $this->debugType = 'info';
    }

};?>

@section('title', 'Programar Nuevo Mantenimiento')

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- DEBUG MESSAGES - TEMPORAL -->
        @if($debugMessage)
            <div class="mb-4 p-4 rounded-lg @if($debugType === 'success') bg-green-100 border border-green-400 text-green-800 @elseif($debugType === 'error') bg-red-100 border border-red-400 text-red-800 @else bg-blue-100 border border-blue-400 text-blue-800 @endif">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        @if($debugType === 'success')
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        @elseif($debugType === 'error')
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        @else
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        @endif
                    </svg>
                    <strong>DEBUG:</strong> {{ $debugMessage }}
                </div>
            </div>
        @endif

        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Programar Nuevo Mantenimiento
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">Registra un nuevo mantenimiento para una herramienta</p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <a href="{{ route('inventario.mantenimientos.index') }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Cancelar
                    </a>
                </div>
            </div>
        </div>

        <!-- Formulario -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Información Principal -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Datos del Mantenimiento -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-green-600 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Información del Mantenimiento
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Herramienta -->
                            <div class="md:col-span-2">
                                <label for="nomHerMan" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nombre de la Herramienta <span class="text-red-500">*</span>
                                </label>
                                <input type="text" wire:model.live="nomHerMan" id="nomHerMan" required maxlength="255"
                                       placeholder="Ingrese el nombre de la herramienta..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('nomHerMan') border-red-500 @enderror">
                                @error('nomHerMan')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">
                                    Escriba el nombre completo de la herramienta que necesita mantenimiento
                                </p>
                            </div>

                            <!-- Tipo de Mantenimiento -->
                            <div>
                                <label for="tipMan" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipo de Mantenimiento <span class="text-red-500">*</span>
                                </label>
                                <select wire:model.live="tipMan" id="tipMan" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('tipMan') border-red-500 @enderror">
                                    <option value="">Seleccione un tipo</option>
                                    <option value="preventivo">Preventivo</option>
                                    <option value="correctivo">Correctivo</option>
                                    <option value="predictivo">Predictivo</option>
                                </select>
                                @error('tipMan')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">
                                    <strong>Preventivo:</strong> Mantenimiento programado para prevenir fallas<br>
                                    <strong>Correctivo:</strong> Reparación de fallas existentes<br>
                                    <strong>Predictivo:</strong> Basado en condiciones y análisis
                                </p>
                            </div>

                            <!-- Estado -->
                            <div>
                                <label for="estMan" class="block text-sm font-medium text-gray-700 mb-2">
                                    Estado <span class="text-red-500">*</span>
                                </label>
                                <select wire:model.live="estMan" id="estMan" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('estMan') border-red-500 @enderror">
                                    <option value="pendiente">Pendiente</option>
                                    <option value="en proceso">En Proceso</option>
                                    <option value="completado">Completado</option>
                                </select>
                                @error('estMan')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Fecha del Mantenimiento -->
                            <div>
                                <label for="fecMan" class="block text-sm font-medium text-gray-700 mb-2">
                                    Fecha Programada <span class="text-red-500">*</span>
                                </label>
                                <input type="date" wire:model.live="fecMan" id="fecMan" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('fecMan') border-red-500 @enderror">
                                @error('fecMan')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Resultado (solo si está completado) -->
                            @if($estMan === 'completado')
                            <div class="md:col-span-2">
                                <label for="resMan" class="block text-sm font-medium text-gray-700 mb-2">
                                    Resultado del Mantenimiento <span class="text-red-500">*</span>
                                </label>
                                <input type="text" wire:model.live="resMan" id="resMan" maxlength="100"
                                       placeholder="Describa el resultado del mantenimiento..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('resMan') border-red-500 @enderror">
                                @error('resMan')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            @endif
                        </div>

                        <!-- Descripción -->
                        <div class="mt-6">
                            <label for="desMan" class="block text-sm font-medium text-gray-700 mb-2">
                                Descripción del Mantenimiento
                            </label>
                            <textarea wire:model.live="desMan" id="desMan" rows="4"
                                      placeholder="Describa detalladamente el mantenimiento a realizar..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('desMan') border-red-500 @enderror"></textarea>
                            @error('desMan')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Observaciones -->
                        <div class="mt-6">
                            <label for="obsMan" class="block text-sm font-medium text-gray-700 mb-2">
                                Observaciones
                            </label>
                            <textarea wire:model.live="obsMan" id="obsMan" rows="3"
                                      placeholder="Observaciones adicionales sobre el mantenimiento..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('obsMan') border-red-500 @enderror"></textarea>
                            @error('obsMan')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Lateral -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Acciones -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-green-600 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Acciones
                        </h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <!-- Botón de Test -->
                        <button type="button" wire:click="testButton" 
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                            🧪 Test Botón
                        </button>

                        <button type="button" wire:click="save" 
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Programar Mantenimiento
                        </button>

                        <button type="button" wire:click="clear"
                           class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Limpiar Formulario
                        </button>

                        <a href="{{ route('inventario.mantenimientos.index') }}" wire:navigate
                           class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Cancelar
                        </a>
                    </div>
                </div>

                <!-- Información de la Herramienta -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-blue-600 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z"></path>
                            </svg>
                            Información Importante
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4 text-sm">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <h4 class="font-medium text-blue-900 mb-2">Antes de programar:</h4>
                                        <ul class="space-y-1 text-blue-800 text-xs">
                                            <li>• Verifique que la herramienta existe</li>
                                            <li>• Confirme el tipo de mantenimiento necesario</li>
                                            <li>• Establezca una fecha realista</li>
                                            <li>• Describa claramente el trabajo a realizar</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.314 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    <div>
                                        <h4 class="font-medium text-yellow-900 mb-2">Recomendación:</h4>
                                        <p class="text-yellow-800 text-xs">
                                            Escriba el nombre completo y específico de la herramienta para facilitar su identificación posterior.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>