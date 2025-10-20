<?php
// resources/views/livewire/inventario/insumos/edit.php

use App\Models\Insumo;
use App\Models\Proveedor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    // ID del insumo a editar
    public Insumo $insumo;

    // Propiedades del formulario
    public string $nomIns = '';
    public string $tipIns = '';
    public string $marIns = '';
    public ?float $canIns = null;
    public ?float $stockMinIns = null;
    public ?float $stockMaxIns = null;
    public ?int $idProveIns = null;
    public string $uniIns = '';
    public ?string $fecVenIns = null;
    public string $estIns = 'disponible';
    public string $obsIns = '';

    // Propiedades para proveedores
    public string $searchProveedor = '';
    public bool $showCreateProveedor = false;
    public string $nuevoProveedorNombre = '';
    public string $nuevoProveedorNit = '';
    public string $nuevoProveedorTelefono = '';
    public string $nuevoProveedorEmail = '';

    // Datos originales para comparación
    public array $datosOriginales = [];

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

    public function mount(Insumo $insumo): void
    {
        $this->insumo = $insumo->load('proveedor');
        
        // Cargar datos del insumo
        $this->nomIns = $insumo->nomIns;
        $this->tipIns = $insumo->tipIns ?? '';
        $this->marIns = $insumo->marIns ?? '';
        $this->canIns = $insumo->canIns;
        $this->stockMinIns = $insumo->stockMinIns;
        $this->stockMaxIns = $insumo->stockMaxIns;
        $this->idProveIns = $insumo->idProveIns;
        $this->uniIns = $insumo->uniIns;
        $this->fecVenIns = $insumo->fecVenIns?->format('Y-m-d');
        $this->estIns = $insumo->estIns;
        $this->obsIns = $insumo->obsIns ?? '';

        // Guardar datos originales para comparar cambios
        $this->datosOriginales = [
            'nomIns' => $this->nomIns,
            'tipIns' => $this->tipIns,
            'marIns' => $this->marIns,
            'canIns' => $this->canIns,
            'stockMinIns' => $this->stockMinIns,
            'stockMaxIns' => $this->stockMaxIns,
            'idProveIns' => $this->idProveIns,
            'uniIns' => $this->uniIns,
            'fecVenIns' => $this->fecVenIns,
            'estIns' => $this->estIns,
            'obsIns' => $this->obsIns,
        ];
    }

    public function with(): array
    {
        $query = Proveedor::query();
        
        if ($this->searchProveedor) {
            $query->where('nomProve', 'like', "%{$this->searchProveedor}%")
                  ->orWhere('nitProve', 'like', "%{$this->searchProveedor}%");
        }
        
        $proveedores = $query->orderBy('nomProve')->get();
        
        // Verificar si hay cambios
        $hayCambios = $this->hayCambios();
        
        return [
            'proveedores' => $proveedores,
            'hayCambios' => $hayCambios
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
            'fecVenIns' => 'nullable|date|after:yesterday',
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
            'fecVenIns.after' => 'La fecha de vencimiento debe ser posterior a ayer.',
            'idProveIns.exists' => 'El proveedor seleccionado no es válido.',
            'estIns.in' => 'El estado seleccionado no es válido.'
        ];
    }

    private function hayCambios(): bool
    {
        $datosActuales = [
            'nomIns' => $this->nomIns,
            'tipIns' => $this->tipIns,
            'marIns' => $this->marIns,
            'canIns' => $this->canIns,
            'stockMinIns' => $this->stockMinIns,
            'stockMaxIns' => $this->stockMaxIns,
            'idProveIns' => $this->idProveIns,
            'uniIns' => $this->uniIns,
            'fecVenIns' => $this->fecVenIns,
            'estIns' => $this->estIns,
            'obsIns' => $this->obsIns,
        ];

        // Comparar cada campo individualmente para manejar valores nulos correctamente
        foreach ($datosActuales as $key => $valor) {
            $valorOriginal = $this->datosOriginales[$key] ?? null;
            
            // Convertir valores vacíos a null para comparación consistente
            $valorActual = $valor === '' ? null : $valor;
            $valorOriginal = $valorOriginal === '' ? null : $valorOriginal;
            
            if ($valorActual != $valorOriginal) {
                return true;
            }
        }
        
        return false;
    }

    public function actualizarInsumo(): void
    {
        $this->validate();

        try {
            $this->insumo->update([
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

            // Actualizar datos originales
            $this->datosOriginales = [
                'nomIns' => $this->nomIns,
                'tipIns' => $this->tipIns,
                'marIns' => $this->marIns,
                'canIns' => $this->canIns,
                'stockMinIns' => $this->stockMinIns,
                'stockMaxIns' => $this->stockMaxIns,
                'idProveIns' => $this->idProveIns,
                'uniIns' => $this->uniIns,
                'fecVenIns' => $this->fecVenIns,
                'estIns' => $this->estIns,
                'obsIns' => $this->obsIns,
            ];

            // Disparar evento para mostrar mensaje de éxito
            $this->dispatch('actualizacion-exitosa');
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar el insumo: ' . $e->getMessage()
            ]);
        }
    }

    public function toggleCreateProveedor(): void
    {
        $this->showCreateProveedor = !$this->showCreateProveedor;
        if (!$this->showCreateProveedor) {
            $this->reset(['nuevoProveedorNombre', 'nuevoProveedorNit', 'nuevoProveedorTelefono', 'nuevoProveedorEmail']);
        }
    }

    public function crearProveedor(): void
    {
        $this->validate([
            'nuevoProveedorNombre' => 'required|string|max:100',
            'nuevoProveedorNit' => 'required|string|max:20|unique:proveedores,nitProve',
            'nuevoProveedorTelefono' => 'nullable|string|max:20',
            'nuevoProveedorEmail' => 'nullable|email|max:100'
        ]);

        try {
            $proveedor = Proveedor::create([
                'nomProve' => $this->nuevoProveedorNombre,
                'nitProve' => $this->nuevoProveedorNit,
                'telProve' => $this->nuevoProveedorTelefono ?: null,
                'emailProve' => $this->nuevoProveedorEmail ?: null,
            ]);

            $this->idProveIns = $proveedor->idProve;
            $this->showCreateProveedor = false;
            $this->reset(['nuevoProveedorNombre', 'nuevoProveedorNit', 'nuevoProveedorTelefono', 'nuevoProveedorEmail']);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Proveedor creado y seleccionado exitosamente.'
            ]);

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al crear el proveedor: ' . $e->getMessage()
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

    public function resetearCambios(): void
    {
        $this->nomIns = $this->datosOriginales['nomIns'];
        $this->tipIns = $this->datosOriginales['tipIns'];
        $this->marIns = $this->datosOriginales['marIns'];
        $this->canIns = $this->datosOriginales['canIns'];
        $this->stockMinIns = $this->datosOriginales['stockMinIns'];
        $this->stockMaxIns = $this->datosOriginales['stockMaxIns'];
        $this->idProveIns = $this->datosOriginales['idProveIns'];
        $this->uniIns = $this->datosOriginales['uniIns'];
        $this->fecVenIns = $this->datosOriginales['fecVenIns'];
        $this->estIns = $this->datosOriginales['estIns'];
        $this->obsIns = $this->datosOriginales['obsIns'];

        $this->resetErrorBag();
        
        // Disparar evento para mostrar mensaje
        $this->dispatch('actualizacion-cancelada');
    }
};
?>

@section('title', 'Editar Insumo')

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
             x-data="{ showSuccess: false, showCancel: false, showChanges: @entangle('hayCambios') }"
             x-on:actualizacion-exitosa.window="showSuccess = true; showCancel = false; setTimeout(() => showSuccess = false, 3000)"
             x-on:actualizacion-cancelada.window="showCancel = true; showSuccess = false; setTimeout(() => showCancel = false, 3000)">
            <div class="flex justify-center mb-1">
                <div class="p-2 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <div class="w-4 h-4 text-white flex items-center justify-center">
                        <i class="fas fa-boxes text-sm"></i>
                    </div>
                </div>
            </div>
            <h1 class="text-base font-black bg-gradient-to-r from-gray-900 via-gray-800 to-blue-800 bg-clip-text text-transparent mb-1">
                Editar Insumo
            </h1>
            
            <template x-if="showSuccess">
                <div class="rounded bg-green-100 px-2 py-1 text-green-800 border border-green-400 text-xs mb-1 font-semibold">
                    ¡Insumo actualizado exitosamente!
                </div>
            </template>

            <template x-if="showCancel">
                <div class="rounded bg-yellow-100 px-2 py-1 text-yellow-800 border border-yellow-400 text-xs mb-1 font-semibold">
                    Cambios revertidos a los valores originales.
                </div>
            </template>

            <template x-if="showChanges && !showSuccess && !showCancel">
                <div class="rounded bg-orange-100 px-2 py-1 text-orange-800 border border-orange-400 text-xs mb-1 font-semibold">
                    Tienes cambios sin guardar. No olvides hacer clic en "Actualizar Insumo".
                </div>
            </template>

            <template x-if="!showChanges && !showSuccess && !showCancel">
                <div class="text-gray-600 text-xs space-y-1">
                    <p>Actualiza la información del insumo</p>
                </div>
            </template>
        </div>

        <!-- Formulario -->
        <form wire:submit="actualizarInsumo" class="space-y-2">
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
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('nomIns') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
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
                                            class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('tipIns') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
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
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('marIns') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
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
                                            class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('uniIns') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
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
                                    <div class="mb-2 p-2 bg-blue-50 border border-blue-200 rounded-2xl">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-6 w-6">
                                                    <div class="h-6 w-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="ml-2">
                                                    <p class="text-xs font-medium text-blue-800">{{ $proveedorSeleccionado->nomProve }}</p>
                                                    <p class="text-[10px] text-blue-600">{{ $proveedorSeleccionado->nitProve }}</p>
                                                </div>
                                            </div>
                                            <button type="button" 
                                                    wire:click="clearProveedor"
                                                    class="cursor-pointer text-blue-600 hover:text-blue-800">
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
                                                   class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 text-xs"
                                                   placeholder="Buscar proveedor por nombre o NIT...">
                                        </div>
                                        <button type="button" 
                                                wire:click="toggleCreateProveedor"
                                                class="cursor-pointer px-2 py-1 bg-green-600 hover:bg-green-700 text-white rounded-2xl font-medium transition text-xs">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        </button>
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
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('canIns') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                           placeholder="0.00">
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
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('stockMinIns') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
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
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('stockMaxIns') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
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
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('fecVenIns') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
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
                                @if($fecVenIns)
                                    @php
                                        $diasParaVencer = \Carbon\Carbon::parse($fecVenIns)->diffInDays(now(), false);
                                    @endphp
                                    @if($diasParaVencer < 0)
                                        <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                            <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            Esta fecha ya venció hace {{ abs($diasParaVencer) }} días
                                        </p>
                                    @elseif($diasParaVencer <= 30)
                                        <p class="mt-0.5 text-[10px] text-yellow-600 flex items-center">
                                            <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            Vence en {{ $diasParaVencer }} días
                                        </p>
                                    @else
                                        <p class="mt-0.5 text-[10px] text-green-600 flex items-center">
                                            <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            ✓ {{ $diasParaVencer }} días para el vencimiento
                                        </p>
                                    @endif
                                @endif
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
                                            class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('estIns') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
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

            <!-- Fila 3: Observaciones y Crear Proveedor -->
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
                                              class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('obsIns') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
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

                <!-- Formulario Crear Proveedor -->
                @if($showCreateProveedor)
                <div class="flex-1 border border-green-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-green-500 to-green-700"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-green-900">Crear Nuevo Proveedor</h2>
                                <p class="text-green-600 text-[10px]">Registra un proveedor que no esté en la lista</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <div>
                                <label for="nuevoProveedorNombre" class="block text-[10px] font-bold text-green-700 mb-0.5">
                                    Nombre *
                                </label>
                                <input type="text" 
                                       wire:model="nuevoProveedorNombre"
                                       id="nuevoProveedorNombre"
                                       class="w-full px-1.5 py-1 bg-white/50 border-2 border-green-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 text-xs @error('nuevoProveedorNombre') border-red-400 @enderror">
                                @error('nuevoProveedorNombre')
                                    <p class="mt-0.5 text-[10px] text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="nuevoProveedorNit" class="block text-[10px] font-bold text-green-700 mb-0.5">
                                    NIT *
                                </label>
                                <input type="text" 
                                       wire:model="nuevoProveedorNit"
                                       id="nuevoProveedorNit"
                                       class="w-full px-1.5 py-1 bg-white/50 border-2 border-green-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 text-xs @error('nuevoProveedorNit') border-red-400 @enderror">
                                @error('nuevoProveedorNit')
                                    <p class="mt-0.5 text-[10px] text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="nuevoProveedorTelefono" class="block text-[10px] font-bold text-green-700 mb-0.5">
                                    Teléfono
                                </label>
                                <input type="text" 
                                       wire:model="nuevoProveedorTelefono"
                                       id="nuevoProveedorTelefono"
                                       class="w-full px-1.5 py-1 bg-white/50 border-2 border-green-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 text-xs">
                            </div>
                            <div>
                                <label for="nuevoProveedorEmail" class="block text-[10px] font-bold text-green-700 mb-0.5">
                                    Email
                                </label>
                                <input type="email" 
                                       wire:model="nuevoProveedorEmail"
                                       id="nuevoProveedorEmail"
                                       class="w-full px-1.5 py-1 bg-white/50 border-2 border-green-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 text-xs @error('nuevoProveedorEmail') border-red-400 @enderror">
                                @error('nuevoProveedorEmail')
                                    <p class="mt-0.5 text-[10px] text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="flex justify-end space-x-2 mt-2">
                            <button type="button" 
                                    wire:click="toggleCreateProveedor"
                                    class="cursor-pointer px-2 py-1 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-2xl text-xs">
                                Cancelar
                            </button>
                            <button type="button" 
                                    wire:click="crearProveedor"
                                    class="cursor-pointer px-2 py-1 bg-green-600 hover:bg-green-700 text-white rounded-2xl text-xs">
                                Crear Proveedor
                            </button>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Botones -->
            <div class="flex justify-center space-x-2 pt-2">
                <button type="button" wire:click="resetearCambios"
                   class="cursor-pointer group relative inline-flex items-center justify-center px-2.5 py-1 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                    <svg class="w-3 h-3 mr-1.5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span class="relative z-10 text-xs">Cancelar</span>
                </button>
                <button type="submit"
                        class="cursor-pointer group relative inline-flex items-center justify-center px-2.5 py-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden"
                        x-bind:disabled="!showChanges"
                        x-bind:class="!showChanges ? 'opacity-50 cursor-not-allowed' : ''">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                    <svg class="w-3 h-3 mr-1.5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="relative z-10 text-xs">Actualizar Insumo</span>
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

    // Confirmar salida si hay cambios sin guardar
    window.addEventListener('beforeunload', function (e) {
        if (@this.hayCambios) {
            e.preventDefault();
            e.returnValue = 'Tienes cambios sin guardar. ¿Estás seguro de que quieres salir?';
            return e.returnValue;
        }
    });
});
</script>