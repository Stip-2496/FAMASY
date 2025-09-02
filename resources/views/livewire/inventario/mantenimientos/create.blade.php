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

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- DEBUG MESSAGES - Mejorado con diseño moderno -->
        @if($debugMessage)
            <div class="mb-6 p-4 rounded-2xl shadow-xl border backdrop-blur-sm transform transition-all duration-300 hover:scale-[1.02] @if($debugType === 'success') bg-emerald-50/90 border-emerald-200 @elseif($debugType === 'error') bg-red-50/90 border-red-200 @else bg-blue-50/90 border-blue-200 @endif">
                <div class="flex items-center">
                    <div class="p-2 rounded-xl mr-3 @if($debugType === 'success') bg-emerald-500 @elseif($debugType === 'error') bg-red-500 @else bg-blue-500 @endif">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            @if($debugType === 'success')
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            @elseif($debugType === 'error')
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            @else
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            @endif
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold @if($debugType === 'success') text-emerald-900 @elseif($debugType === 'error') text-red-900 @else text-blue-900 @endif">DEBUG:</p>
                        <p class="text-sm @if($debugType === 'success') text-emerald-800 @elseif($debugType === 'error') text-red-800 @else text-blue-800 @endif">{{ $debugMessage }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Header Ultra Moderno -->
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 p-8 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-green-500/5 to-emerald-500/5"></div>
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-green-400/10 to-emerald-600/10 rounded-full -mr-16 -mt-16"></div>
                
                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center space-x-4 mb-6 lg:mb-0">
                        <div class="p-4 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-4xl font-black bg-gradient-to-r from-gray-900 via-gray-800 to-green-800 bg-clip-text text-transparent leading-tight">
                                Programar Nuevo Mantenimiento
                            </h1>
                            <p class="text-gray-600 mt-1 text-lg">Registra y programa un mantenimiento para cualquier herramienta</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('inventario.mantenimientos.index') }}" wire:navigate
                           class="group relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                            <svg class="w-5 h-5 mr-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            <span class="relative z-10">Volver al Listado</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulario Principal -->
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
                                <p class="text-gray-600">Complete los datos principales del mantenimiento</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Herramienta -->
                            <div class="lg:col-span-2">
                                <label for="nomHerMan" class="block text-sm font-bold text-gray-700 mb-3">
                                    🔧 Nombre de la Herramienta <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="text" wire:model.live="nomHerMan" id="nomHerMan" required maxlength="255"
                                           placeholder="Ingrese el nombre completo de la herramienta..."
                                           class="w-full px-5 py-4 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 group-hover:shadow-xl @error('nomHerMan') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                    <div class="absolute inset-0 bg-gradient-to-r from-green-500/5 to-emerald-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('nomHerMan')
                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                                <p class="mt-2 text-xs text-gray-500 flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    Escriba el nombre completo y específico de la herramienta
                                </p>
                            </div>

                            <!-- Tipo de Mantenimiento -->
                            <div>
                                <label for="tipMan" class="block text-sm font-bold text-gray-700 mb-3">
                                    ⚙️ Tipo de Mantenimiento <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <select wire:model.live="tipMan" id="tipMan" required
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
                                <div class="mt-2 text-xs text-gray-500 space-y-1">
                                    <p><strong>🛡️ Preventivo:</strong> Mantenimiento programado</p>
                                    <p><strong>🔧 Correctivo:</strong> Reparación de fallas</p>
                                    <p><strong>📊 Predictivo:</strong> Basado en análisis</p>
                                </div>
                            </div>

                            <!-- Estado -->
                            <div>
                                <label for="estMan" class="block text-sm font-bold text-gray-700 mb-3">
                                    📊 Estado Actual <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <select wire:model.live="estMan" id="estMan" required
                                            class="w-full px-5 py-4 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 group-hover:shadow-xl appearance-none @error('estMan') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
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
                                    <input type="date" wire:model.live="fecMan" id="fecMan" required
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

                            <!-- Resultado (solo si está completado) -->
                            @if($estMan === 'completado')
                            <div class="lg:col-span-2">
                                <label for="resMan" class="block text-sm font-bold text-gray-700 mb-3">
                                    ✅ Resultado del Mantenimiento <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="text" wire:model.live="resMan" id="resMan" maxlength="100"
                                           placeholder="Describa el resultado del mantenimiento realizado..."
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
                            @endif
                        </div>

                        <!-- Descripción -->
                        <div class="mt-8">
                            <label for="desMan" class="block text-sm font-bold text-gray-700 mb-3">
                                📝 Descripción del Mantenimiento
                            </label>
                            <div class="relative group">
                                <textarea wire:model.live="desMan" id="desMan" rows="5"
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
                                <textarea wire:model.live="obsMan" id="obsMan" rows="4"
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
                                <p class="text-sm text-gray-600">Controles del formulario</p>
                            </div>
                        </div>

                        <div class="space-y-4">

                            <!-- Botón Guardar -->
                            <button type="button" wire:click="save" 
                                    class="w-full group relative inline-flex items-center justify-center px-6 py-4 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                                <svg class="w-5 h-5 mr-3 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="relative z-10">✅ Programar Mantenimiento</span>
                            </button>

                            <!-- Botón Limpiar -->
                            <button type="button" wire:click="clear"
                                   class="w-full group relative inline-flex items-center justify-center px-6 py-4 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                                <svg class="w-5 h-5 mr-3 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                <span class="relative z-10">🧹 Limpiar Formulario</span>
                            </button>

                            <!-- Botón Cancelar -->
                            <a href="{{ route('inventario.mantenimientos.index') }}" wire:navigate
                               class="w-full group relative inline-flex items-center justify-center px-6 py-4 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                                <svg class="w-5 h-5 mr-3 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span class="relative z-10">❌ Cancelar</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Panel de Información -->
                <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 overflow-hidden">
                    <div class="h-2 bg-gradient-to-r from-blue-500 to-indigo-500"></div>
                    <div class="p-6">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="p-3 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Guía Rápida</h3>
                                <p class="text-sm text-gray-600">Información importante</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <!-- Tips de Uso -->
                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-2xl p-4">
                                <div class="flex items-start space-x-3">
                                    <div class="p-2 bg-blue-500 rounded-lg">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-blue-900 text-sm mb-2">💡 Antes de programar:</h4>
                                        <ul class="space-y-1 text-blue-800 text-xs">
                                            <li class="flex items-center"><span class="w-1 h-1 bg-blue-600 rounded-full mr-2"></span>Verifique que la herramienta existe</li>
                                            <li class="flex items-center"><span class="w-1 h-1 bg-blue-600 rounded-full mr-2"></span>Confirme el tipo de mantenimiento</li>
                                            <li class="flex items-center"><span class="w-1 h-1 bg-blue-600 rounded-full mr-2"></span>Establezca una fecha realista</li>
                                            <li class="flex items-center"><span class="w-1 h-1 bg-blue-600 rounded-full mr-2"></span>Describa claramente el trabajo</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Recomendación -->
                            <div class="bg-gradient-to-br from-amber-50 to-yellow-50 border-2 border-amber-200 rounded-2xl p-4">
                                <div class="flex items-start space-x-3">
                                    <div class="p-2 bg-amber-500 rounded-lg">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.314 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-amber-900 text-sm mb-2">⚡ Recomendación:</h4>
                                        <p class="text-amber-800 text-xs leading-relaxed">
                                            Use nombres específicos y detallados para facilitar la identificación posterior de las herramientas.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Estado Actual -->
                            <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-2xl p-4">
                                <div class="flex items-start space-x-3">
                                    <div class="p-2 bg-emerald-500 rounded-lg">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-emerald-900 text-sm mb-2">📊 Sistema listo:</h4>
                                        <p class="text-emerald-800 text-xs leading-relaxed">
                                            Todos los sistemas están funcionando correctamente. Puede proceder a crear el mantenimiento.
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