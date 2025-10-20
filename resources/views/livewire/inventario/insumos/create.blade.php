<?php
// resources/views/livewire/inventario/insumos/create.php

use App\Models\Insumo;
use App\Models\Proveedor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    // Propiedades del formulario
    public string $nomIns = '';
    public string $tipIns = '';
    public ?float $canIns = null;
    public string $marIns = '';
    public ?float $stockMinIns = null;
    public ?float $stockMaxIns = null;
    public ?int $idProveIns = null;
    public string $uniIns = '';
    public ?string $fecVenIns = null;
    public string $estIns = 'disponible';
    public string $obsIns = '';

    // Propiedades para proveedores
    public string $searchProveedor = '';

    // Tipos predefinidos
    public array $tiposDisponibles = [
        'medicamento veterinario',
        'concentrado',
        'vacuna',
        'vitamina',
        'suplemento',
        'desinfectante',
        'insecticida',
        'fertilizante',
        'semilla',
        'otro'
    ];

    // Unidades comunes
    public array $unidadesDisponibles = [
        'kg', 'g', 'lb', 'ton',
        'l', 'ml', 'gal',
        'unidades', 'dosis', 'sobres',
        'cajas', 'bolsas', 'bultos',
        'm', 'm2', 'm3'
    ];

    public function with(): array
    {
        $query = Proveedor::query();
        
        if ($this->searchProveedor) {
            $query->where('nomProve', 'like', "%{$this->searchProveedor}%")
                  ->orWhere('nitProve', 'like', "%{$this->searchProveedor}%");
        }
        
        $proveedores = $query->orderBy('nomProve')->get();
        
        return [
            'proveedores' => $proveedores
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

    public function rules(): array
    {
        return [
            'nomIns' => 'required|string|max:100',
            'tipIns' => 'required|string|max:100',
            'marIns' => 'nullable|string|max:100',
            'canIns' => 'nullable|numeric|min:0',
            'stockMinIns' => 'nullable|numeric|min:0',
            'stockMaxIns' => 'nullable|numeric|min:0|gte:stockMinIns',
            'idProveIns' => 'nullable|exists:proveedores,idProve',
            'uniIns' => 'required|string|max:50',
            'fecVenIns' => 'nullable|date|after:today',
            'estIns' => 'required|in:disponible,agotado,vencido',
            'obsIns' => 'nullable|string|max:500'
        ];
    }

    public function messages(): array
    {
        return [
            'nomIns.required' => 'El nombre del insumo es obligatorio.',
            'tipIns.required' => 'El tipo de insumo es obligatorio.',
            'uniIns.required' => 'La unidad de medida es obligatoria.',
            'stockMaxIns.gte' => 'El stock máximo debe ser mayor o igual al stock mínimo.',
            'fecVenIns.after' => 'La fecha de vencimiento debe ser posterior a hoy.',
            'idProveIns.exists' => 'El proveedor seleccionado no es válido.',
            'estIns.in' => 'El estado seleccionado no es válido.'
        ];
    }

    // Método para limpiar todos los campos
    public function limpiarFormulario(): void
    {
        $this->nomIns = '';
        $this->tipIns = '';
        $this->marIns = '';
        $this->canIns = null;
        $this->stockMinIns = null;
        $this->stockMaxIns = null;
        $this->idProveIns = null;
        $this->uniIns = '';
        $this->fecVenIns = null;
        $this->estIns = 'disponible';
        $this->obsIns = '';
        $this->searchProveedor = '';
        
        $this->resetErrorBag();
    }

    // Método para cancelar registro (limpia el formulario)
    public function cancelarRegistro(): void
    {
        $this->limpiarFormulario();
        
        // Disparar evento para mostrar mensaje
        $this->dispatch('registro-cancelado');
    }

    public function crearInsumo(): void
    {
        $this->validate();

        try {
            $insumo = Insumo::create([
                'nomIns' => $this->nomIns,
                'tipIns' => $this->tipIns,
                'marIns' => $this->marIns ?: null,
                'canIns' => $this->canIns,
                'stockMinIns' => $this->stockMinIns,
                'stockMaxIns' => $this->stockMaxIns,
                'idProveIns' => $this->idProveIns,
                'uniIns' => $this->uniIns,
                'fecVenIns' => $this->fecVenIns ? \Carbon\Carbon::parse($this->fecVenIns) : null,
                'estIns' => $this->estIns,
                'obsIns' => $this->obsIns ?: null,
            ]);

            // Limpiar el formulario después del registro exitoso
            $this->limpiarFormulario();
            
            // Disparar evento para mostrar mensaje de éxito
            $this->dispatch('registro-exitoso');
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al crear el insumo: ' . $e->getMessage()
            ]);
        }
    }

    public function selectProveedor($proveedorId): void
    {
        $this->idProveIns = $proveedorId;
        $this->searchProveedor = '';
    }

    public function clearProveedor(): void
    {
        $this->idProveIns = null;
    }
};
?>

@section('title', 'Crear Insumo')

<div class="flex items-center justify-center min-h-screen py-3">
    <div class="w-full max-w-7xl mx-auto bg-white/80 backdrop-blur-xl shadow rounded-3xl p-3 relative border border-white/20">
        <!-- Botón Volver -->
        <div class="absolute top-2 right-2">
            <a href="{{ route('inventario.insumos.index') }}" wire:navigate
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
                        <i class="fas fa-boxes text-sm"></i>
                    </div>
                </div>
            </div>
            <h1 class="text-base font-black bg-gradient-to-r from-gray-900 via-gray-800 to-green-800 bg-clip-text text-transparent mb-1">
                Registrar Nuevo Insumo
            </h1>
            
            <template x-if="showSuccess">
                <div class="rounded bg-green-100 px-2 py-1 text-green-800 border border-green-400 text-xs mb-1 font-semibold">
                    ¡Insumo registrado exitosamente!
                </div>
            </template>

            <template x-if="showCancel">
                <div class="rounded bg-yellow-100 px-2 py-1 text-yellow-800 border border-yellow-400 text-xs mb-1 font-semibold">
                    Formulario limpiado. Puede registrar un nuevo insumo.
                </div>
            </template>

            <template x-if="!showSuccess && !showCancel">
                <p class="text-gray-600 text-xs">Complete los datos del nuevo insumo</p>
            </template>
        </div>

        <!-- Formulario -->
        <form wire:submit="crearInsumo" class="space-y-2">
            <!-- Fila 1: Información básica -->
            <div class="flex flex-col md:flex-row gap-2">
                <!-- Información Básica -->
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
                                <h2 class="text-xs font-bold text-gray-900">Información Básica</h2>
                                <p class="text-gray-600 text-[10px]">Datos principales de identificación</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <!-- Nombre del Insumo -->
                            <div>
                                <label for="nomIns" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Nombre <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="text"
                                           id="nomIns"
                                           wire:model="nomIns"
                                           wire:blur="validateField('nomIns')"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('nomIns') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                           placeholder="Ej: Vacuna contra Aftosa"
                                           maxlength="100"
                                           required>
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('nomIns')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Tipo -->
                            <div>
                                <label for="tipIns" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Tipo <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <select id="tipIns"
                                            wire:model="tipIns"
                                            wire:blur="validateField('tipIns')"
                                            class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('tipIns') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                        <option value="">Seleccionar tipo</option>
                                        @foreach($tiposDisponibles as $tipo)
                                            <option value="{{ $tipo }}">{{ ucfirst($tipo) }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('tipIns')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Marca -->
                            <div>
                                <label for="marIns" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Marca
                                </label>
                                <div class="relative group">
                                    <input type="text"
                                           id="marIns"
                                           wire:model="marIns"
                                           wire:blur="validateField('marIns')"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('marIns') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                           placeholder="Ej: Zoetis, Bayer"
                                           maxlength="100">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('marIns')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Unidad de Medida -->
                            <div>
                                <label for="uniIns" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Unidad <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <select id="uniIns"
                                            wire:model="uniIns"
                                            wire:blur="validateField('uniIns')"
                                            class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('uniIns') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                        <option value="">Seleccionar unidad</option>
                                        @foreach($unidadesDisponibles as $unidad)
                                            <option value="{{ $unidad }}">{{ $unidad }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('uniIns')
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

                <!-- Información del Proveedor -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#39A900] to-[#000000]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Información del Proveedor</h2>
                                <p class="text-gray-600 text-[10px]">Asigna un proveedor para mejor trazabilidad</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-2">
                            <!-- Proveedor Seleccionado -->
                            @if($idProveIns)
                                @php
                                    $proveedorSeleccionado = $proveedores->find($idProveIns);
                                @endphp
                                @if($proveedorSeleccionado)
                                    <div class="mb-2 p-2 bg-green-50 border border-green-200 rounded-2xl">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-6 w-6">
                                                    <div class="h-6 w-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="ml-2">
                                                    <p class="text-xs font-medium text-green-800">{{ $proveedorSeleccionado->nomProve }}</p>
                                                    <p class="text-[10px] text-green-600">{{ $proveedorSeleccionado->nitProve }}</p>
                                                </div>
                                            </div>
                                            <button type="button" 
                                                    wire:click="clearProveedor"
                                                    class="cursor-pointer text-green-600 hover:text-green-800">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <!-- Búsqueda de Proveedor -->
                                <div class="space-y-2">
                                    <div class="flex space-x-1">
                                        <div class="flex-1">
                                            <input type="text" 
                                                   wire:model.live.debounce.300ms="searchProveedor"
                                                   class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 text-xs"
                                                   placeholder="Buscar proveedor por nombre o NIT...">
                                        </div>
                                    </div>

                                    <!-- Lista de Proveedores -->
                                    @if($searchProveedor && $proveedores->count() > 0)
                                        <div class="border border-gray-300 rounded-2xl max-h-32 overflow-y-auto">
                                            @foreach($proveedores as $proveedor)
                                                <button type="button" 
                                                        wire:click="selectProveedor({{ $proveedor->idProve }})"
                                                        class="cursor-pointer w-full px-2 py-1.5 text-left hover:bg-gray-50 border-b border-gray-100 last:border-b-0 flex items-center text-xs">
                                                    <div class="flex-shrink-0 h-5 w-5">
                                                        <div class="h-5 w-5 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center">
                                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <div class="ml-2">
                                                        <p class="text-xs font-medium text-gray-900">{{ $proveedor->nomProve }}</p>
                                                        <p class="text-[10px] text-gray-500">{{ $proveedor->nitProve }}</p>
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    @elseif($searchProveedor && $proveedores->count() === 0)
                                        <div class="text-center py-2 text-gray-500 text-[10px]">
                                            No se encontraron proveedores con ese criterio
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @error('idProveIns')
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

            <!-- Fila 2: Control de Stock y Vencimiento -->
<div class="flex flex-col md:flex-row gap-2">
    <!-- Control de Stock -->
    <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
        <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#39A900]"></div>
        <div class="p-2">
            <div class="flex items-center space-x-2 mb-2">
                <div class="p-1.5 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl shadow-lg">
                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xs font-bold text-gray-900">Control de Stock</h2>
                    <p class="text-gray-600 text-[10px]">Define cantidades y límites para alertas</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                <!-- Cantidad Actual -->
                <div>
                    <label for="canIns" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                        Cantidad Actual
                    </label>
                    <div class="relative group">
                        <input type="number"
                               id="canIns"
                               wire:model="canIns"
                               wire:blur="validateField('canIns')"
                               step="0.01"
                               min="0"
                               class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('canIns') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                               placeholder="0.00"
                               value="0">
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/5 to-green-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                    </div>
                    @error('canIns')
                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- Stock Mínimo -->
                <div>
                    <label for="stockMinIns" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                        Stock Mínimo
                    </label>
                    <div class="relative group">
                        <input type="number"
                               id="stockMinIns"
                               wire:model="stockMinIns"
                               wire:blur="validateField('stockMinIns')"
                               step="0.01"
                               min="0"
                               class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('stockMinIns') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                               placeholder="Cantidad mínima para alertas">
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/5 to-green-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                    </div>
                    @error('stockMinIns')
                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- Stock Máximo -->
                <div>
                    <label for="stockMaxIns" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                        Stock Máximo
                    </label>
                    <div class="relative group">
                        <input type="number"
                               id="stockMaxIns"
                               wire:model="stockMaxIns"
                               wire:blur="validateField('stockMaxIns')"
                               step="0.01"
                               min="0"
                               class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('stockMaxIns') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                               placeholder="Cantidad máxima operativa">
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/5 to-green-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                    </div>
                    @error('stockMaxIns')
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

                <!-- Vencimiento y Estado -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#39A900] to-[#000000]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Vencimiento y Estado</h2>
                                <p class="text-gray-600 text-[10px]">Información de caducidad y disponibilidad</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <!-- Fecha de Vencimiento -->
                            <div>
                                <label for="fecVenIns" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Fecha de Vencimiento
                                </label>
                                <div class="relative group">
                                    <input type="date"
                                           id="fecVenIns"
                                           wire:model="fecVenIns"
                                           wire:blur="validateField('fecVenIns')"
                                           min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('fecVenIns') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                    <div class="absolute inset-0 bg-gradient-to-r from-purple-500/5 to-pink-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('fecVenIns')
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
                                <label for="estIns" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Estado <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <select id="estIns"
                                            wire:model="estIns"
                                            wire:blur="validateField('estIns')"
                                            class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('estIns') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                        <option value="disponible">Disponible</option>
                                        <option value="agotado">Agotado</option>
                                        <option value="vencido">Vencido</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('estIns')
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

            <!-- Fila 3: Observaciones -->
            <div class="flex flex-col md:flex-row gap-2">
                <!-- Observaciones -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#39A900]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-yellow-500 to-amber-600 rounded-xl shadow-lg">
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
                                <label for="obsIns" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Observaciones y Notas
                                </label>
                                <div class="relative group">
                                    <textarea id="obsIns"
                                              wire:model="obsIns"
                                              wire:blur="validateField('obsIns')"
                                              rows="3"
                                              class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('obsIns') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                              placeholder="Información adicional sobre el insumo..."></textarea>
                                    <div class="absolute inset-0 bg-gradient-to-r from-yellow-500/5 to-amber-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('obsIns')
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
                    <span class="relative z-10 text-xs">Guardar Insumo</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('livewire:initialized', () => {
    Livewire.on('notify', (event) => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
        
        Toast.fire({
            icon: event.type,
            title: event.message
        });
    });
});
</script>