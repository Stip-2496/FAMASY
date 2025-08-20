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
        $hayC = $this->hayC();
        
        return [
            'proveedores' => $proveedores,
            'hayC' => $hayC
        ];
    }

    public function rules(): array
    {
        return [
            'nomIns' => 'required|string|max:100',
            'tipIns' => 'required|string|max:100',
            'marIns' => 'nullable|string|max:100',
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

    private function hayC(): bool
    {
        $datosActuales = [
            'nomIns' => $this->nomIns,
            'tipIns' => $this->tipIns,
            'marIns' => $this->marIns,
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
                'stockMinIns' => $this->stockMinIns,
                'stockMaxIns' => $this->stockMaxIns,
                'idProveIns' => $this->idProveIns,
                'uniIns' => $this->uniIns,
                'fecVenIns' => $this->fecVenIns ? \Carbon\Carbon::parse($this->fecVenIns) : null,
                'estIns' => $this->estIns,
                'obsIns' => $this->obsIns ?: null,
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Insumo actualizado exitosamente.'
            ]);

            $this->redirect(route('inventario.insumos.index'), navigate: true);

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
        $this->stockMinIns = $this->datosOriginales['stockMinIns'];
        $this->stockMaxIns = $this->datosOriginales['stockMaxIns'];
        $this->idProveIns = $this->datosOriginales['idProveIns'];
        $this->uniIns = $this->datosOriginales['uniIns'];
        $this->fecVenIns = $this->datosOriginales['fecVenIns'];
        $this->estIns = $this->datosOriginales['estIns'];
        $this->obsIns = $this->datosOriginales['obsIns'];

        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Cambios revertidos a los valores originales.'
        ]);
    }

    public function cancelar(): void
    {
        $this->redirect(route('inventario.insumos.index'), navigate: true);
    }
};
?>

@section('title', 'Editar Insumo')

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar Insumo
                    </h1>
                    <p class="mt-2 text-gray-600">Modifica la información del insumo</p>
                    <div class="mt-2 flex items-center text-sm text-gray-500">
                        <span class="font-medium">ID:</span>
                        <span class="ml-1">{{ $insumo->idIns }}</span>
                        <span class="mx-2">•</span>
                        <span class="font-medium">Creado:</span>
                        <span class="ml-1">{{ $insumo->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
                <div class="flex space-x-3">
                    @if($hayC)
                        <button wire:click="resetearCambios"
                                class="cursor-pointer inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Revertir
                        </button>
                    @endif
                    <a href="{{ route('inventario.insumos.index') }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Indicador de Cambios -->
        @if($hayC)
            <div class="mb-6 bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <strong>Tienes cambios sin guardar.</strong> No olvides hacer clic en "Actualizar Insumo" para guardar los cambios.
            </div>
        @endif

        <!-- Información del Proveedor Actual -->
        @if($insumo->proveedor)
            <div class="mb-6 bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-indigo-900">Proveedor Actual</p>
                        <p class="text-sm text-indigo-700">{{ $insumo->proveedor->nomProve }} - {{ $insumo->proveedor->nitProve }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Formulario -->
        <div class="bg-white shadow rounded-lg">
            <form wire:submit="actualizarInsumo">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Información del Insumo</h3>
                    <p class="mt-1 text-sm text-gray-600">Modifica los datos necesarios.</p>
                </div>

                <div class="px-6 py-6 space-y-6">
                    <!-- Fila 1: Nombre y Tipo -->
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Nombre del Insumo -->
                        <div>
                            <label for="nomIns" class="block text-sm font-medium text-gray-700 mb-2">
                                Nombre del Insumo *
                            </label>
                            <input type="text" 
                                   wire:model="nomIns"
                                   id="nomIns"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('nomIns') border-red-500 @enderror"
                                   placeholder="Ej: Vacuna contra Aftosa">
                            @error('nomIns')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tipo -->
                        <div>
                            <label for="tipIns" class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo de Insumo *
                            </label>
                            <select wire:model="tipIns" 
                                    id="tipIns"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('tipIns') border-red-500 @enderror">
                                <option value="">Selecciona un tipo</option>
                                @foreach($tiposDisponibles as $tipo)
                                    <option value="{{ $tipo }}">{{ ucfirst($tipo) }}</option>
                                @endforeach
                            </select>
                            @error('tipIns')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Fila 2: Marca y Unidad -->
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Marca -->
                        <div>
                            <label for="marIns" class="block text-sm font-medium text-gray-700 mb-2">
                                Marca
                            </label>
                            <input type="text" 
                                   wire:model="marIns"
                                   id="marIns"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Ej: Zoetis, Bayer">
                        </div>

                        <!-- Unidad de Medida -->
                        <div>
                            <label for="uniIns" class="block text-sm font-medium text-gray-700 mb-2">
                                Unidad de Medida *
                            </label>
                            <select wire:model="uniIns" 
                                    id="uniIns"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('uniIns') border-red-500 @enderror">
                                <option value="">Selecciona una unidad</option>
                                @foreach($unidadesDisponibles as $unidad)
                                    <option value="{{ $unidad }}">{{ $unidad }}</option>
                                @endforeach
                            </select>
                            @error('uniIns')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Sección Proveedor -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Proveedor
                        </label>
                        
                        <!-- Proveedor Seleccionado -->
                        @if($idProveIns)
                            @php
                                $proveedorSeleccionado = $proveedores->find($idProveIns);
                            @endphp
                            @if($proveedorSeleccionado)
                                <div class="mb-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-blue-800">{{ $proveedorSeleccionado->nomProve }}</p>
                                                <p class="text-xs text-blue-600">{{ $proveedorSeleccionado->nitProve }}</p>
                                            </div>
                                        </div>
                                        <button type="button" 
                                                wire:click="clearProveedor"
                                                class="cursor-pointer text-blue-600 hover:text-blue-800">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @else
                            <!-- Búsqueda de Proveedor -->
                            <div class="space-y-3">
                                <div class="flex space-x-2">
                                    <div class="flex-1">
                                        <input type="text" 
                                               wire:model.live.debounce.300ms="searchProveedor"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="Buscar proveedor por nombre o NIT...">
                                    </div>
                                    <button type="button" 
                                            wire:click="toggleCreateProveedor"
                                            class="cursor-pointer px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Lista de Proveedores -->
                                @if($searchProveedor && $proveedores->count() > 0)
                                    <div class="border border-gray-300 rounded-lg max-h-40 overflow-y-auto">
                                        @foreach($proveedores as $proveedor)
                                            <button type="button" 
                                                    wire:click="selectProveedor({{ $proveedor->idProve }})"
                                                    class="cursor-pointer w-full px-4 py-3 text-left hover:bg-gray-50 border-b border-gray-100 last:border-b-0 flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8">
                                                    <div class="h-8 w-8 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm font-medium text-gray-900">{{ $proveedor->nomProve }}</p>
                                                    <p class="text-xs text-gray-500">{{ $proveedor->nitProve }}</p>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                @elseif($searchProveedor && $proveedores->count() === 0)
                                    <div class="text-center py-4 text-gray-500 text-sm">
                                        No se encontraron proveedores con ese criterio
                                    </div>
                                @endif
                            </div>
                        @endif

                        @error('idProveIns')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Formulario Crear Proveedor -->
                    @if($showCreateProveedor)
                        <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                            <h4 class="text-lg font-medium text-green-900 mb-4">Crear Nuevo Proveedor</h4>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="nuevoProveedorNombre" class="block text-sm font-medium text-green-700 mb-1">
                                        Nombre *
                                    </label>
                                    <input type="text" 
                                           wire:model="nuevoProveedorNombre"
                                           id="nuevoProveedorNombre"
                                           class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('nuevoProveedorNombre') border-red-500 @enderror">
                                    @error('nuevoProveedorNombre')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="nuevoProveedorNit" class="block text-sm font-medium text-green-700 mb-1">
                                        NIT *
                                    </label>
                                    <input type="text" 
                                           wire:model="nuevoProveedorNit"
                                           id="nuevoProveedorNit"
                                           class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('nuevoProveedorNit') border-red-500 @enderror">
                                    @error('nuevoProveedorNit')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="nuevoProveedorTelefono" class="block text-sm font-medium text-green-700 mb-1">
                                        Teléfono
                                    </label>
                                    <input type="text" 
                                           wire:model="nuevoProveedorTelefono"
                                           id="nuevoProveedorTelefono"
                                           class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                                </div>
                                <div>
                                    <label for="nuevoProveedorEmail" class="block text-sm font-medium text-green-700 mb-1">
                                        Email
                                    </label>
                                    <input type="email" 
                                           wire:model="nuevoProveedorEmail"
                                           id="nuevoProveedorEmail"
                                           class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('nuevoProveedorEmail') border-red-500 @enderror">
                                    @error('nuevoProveedorEmail')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="flex justify-end space-x-3 mt-4">
                                <button type="button" 
                                        wire:click="toggleCreateProveedor"
                                        class="cursor-pointer px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg">
                                    Cancelar
                                </button>
                                <button type="button" 
                                        wire:click="crearProveedor"
                                        class="cursor-pointer px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                                    Crear Proveedor
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Fila 3: Stocks -->
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Stock Mínimo -->
                        <div>
                            <label for="stockMinIns" class="block text-sm font-medium text-gray-700 mb-2">
                                Stock Mínimo
                            </label>
                            <input type="number" 
                                   wire:model="stockMinIns"
                                   id="stockMinIns"
                                   step="0.01"
                                   min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('stockMinIns') border-red-500 @enderror"
                                   placeholder="Cantidad mínima para alertas">
                            @error('stockMinIns')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Stock Máximo -->
                        <div>
                            <label for="stockMaxIns" class="block text-sm font-medium text-gray-700 mb-2">
                                Stock Máximo
                            </label>
                            <input type="number" 
                                   wire:model="stockMaxIns"
                                   id="stockMaxIns"
                                   step="0.01"
                                   min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('stockMaxIns') border-red-500 @enderror"
                                   placeholder="Cantidad máxima operativa">
                            @error('stockMaxIns')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Fila 4: Vencimiento y Estado -->
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Fecha de Vencimiento -->
                        <div>
                            <label for="fecVenIns" class="block text-sm font-medium text-gray-700 mb-2">
                                Fecha de Vencimiento
                            </label>
                            <input type="date" 
                                   wire:model="fecVenIns"
                                   id="fecVenIns"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('fecVenIns') border-red-500 @enderror">
                            @error('fecVenIns')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @if($fecVenIns)
                                @php
                                    $diasParaVencer = \Carbon\Carbon::parse($fecVenIns)->diffInDays(now(), false);
                                @endphp
                                @if($diasParaVencer < 0)
                                    <p class="mt-1 text-sm text-red-600">⚠️ Esta fecha ya venció hace {{ abs($diasParaVencer) }} días</p>
                                @elseif($diasParaVencer <= 30)
                                    <p class="mt-1 text-sm text-yellow-600">⚠️ Vence en {{ $diasParaVencer }} días</p>
                                @else
                                    <p class="mt-1 text-sm text-green-600">✓ {{ $diasParaVencer }} días para el vencimiento</p>
                                @endif
                            @endif
                        </div>

                        <!-- Estado -->
                        <div>
                            <label for="estIns" class="block text-sm font-medium text-gray-700 mb-2">
                                Estado *
                            </label>
                            <select wire:model="estIns" 
                                    id="estIns"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('estIns') border-red-500 @enderror">
                                <option value="disponible">Disponible</option>
                                <option value="agotado">Agotado</option>
                                <option value="vencido">Vencido</option>
                            </select>
                            @error('estIns')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div>
                        <label for="obsIns" class="block text-sm font-medium text-gray-700 mb-2">
                            Observaciones
                        </label>
                        <textarea wire:model="obsIns"
                                  id="obsIns"
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Información adicional sobre el insumo..."></textarea>
                        @error('obsIns')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Botones -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
                    <div class="flex space-x-3">
                        <button type="button" 
                                wire:click="cancelar"
                                class="cursor-pointer px-6 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out">
                            Cancelar
                        </button>
                        @if($hayC)
                            <button type="button" 
                                    wire:click="resetearCambios"
                                    class="cursor-pointer px-6 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-lg transition duration-150 ease-in-out flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Revertir Cambios
                            </button>
                        @endif
                    </div>
                    <button type="submit" 
                            class="cursor-pointer px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition duration-150 ease-in-out flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Actualizar Insumo
                    </button>
                </div>
            </form>
        </div>

        <!-- Información Adicional -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Información importante</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Los campos marcados con (*) son obligatorios</li>
                            <li>El stock actual se maneja a través de movimientos de inventario</li>
                            <li>Puedes cambiar el proveedor o crear uno nuevo si es necesario</li>
                            <li>El botón "Revertir" restaura todos los valores originales</li>
                            <li>Solo puedes guardar cuando hay cambios pendientes</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historial de Cambios (Si tuviéramos audit log) -->
        <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-800 mb-2">Datos del Registro</h3>
            <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                <div>
                    <span class="font-medium">Creado:</span> {{ $insumo->created_at->format('d/m/Y H:i:s') }}
                </div>
                <div>
                    <span class="font-medium">Última modificación:</span> {{ $insumo->updated_at->format('d/m/Y H:i:s') }}
                </div>
                <div>
                    <span class="font-medium">ID Insumo:</span> {{ $insumo->idIns }}
                </div>
                <div>
                    <span class="font-medium">Estado en BD:</span> 
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $insumo->deleted_at ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                        {{ $insumo->deleted_at ? 'Eliminado' : 'Activo' }}
                    </span>
                </div>
            </div>
        </div>
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
        if (@this.hayC) {
            e.preventDefault();
            e.returnValue = 'Tienes cambios sin guardar. ¿Estás seguro de que quieres salir?';
            return e.returnValue;
        }
    });
});
</script>