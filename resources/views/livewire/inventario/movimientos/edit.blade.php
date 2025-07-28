<?php
// resources/views/livewire/inventario/movimientos/edit.blade.php

use App\Models\Inventario;
use App\Models\Insumo;
use App\Models\Herramienta;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public Inventario $inventario;
    
    // Propiedades del formulario
    public string $tipMovInv;
    public string $fecMovInv;
    public float $cantMovInv;
    public string $uniMovInv;
    public ?float $costoUnitInv = null;
    public ?float $costoTotInv = null;
    public ?string $loteInv = null;
    public ?string $fecVenceInv = null;
    public ?int $idProve = null;
    public ?string $obsInv = null;

    // Datos para selects
    public array $tiposMovimiento = [
        'apertura' => 'Apertura',
        'entrada' => 'Entrada',
        'salida' => 'Salida',
        'consumo' => 'Consumo',
        'prestamo_salida' => 'Préstamo Salida',
        'prestamo_retorno' => 'Préstamo Retorno',
        'perdida' => 'Pérdida',
        'ajuste_pos' => 'Ajuste Positivo',
        'ajuste_neg' => 'Ajuste Negativo',
        'mantenimiento' => 'Mantenimiento',
        'venta' => 'Venta'
    ];
    
    /** @var Collection<Proveedor> */
    public Collection $proveedores;

    public function mount(Inventario $movimiento): void
    {
        $this->inventario = $movimiento;
        $this->proveedores = Proveedor::all();
        
        $this->fill([
            'tipMovInv' => $this->inventario->tipMovInv,
            'fecMovInv' => $this->inventario->fecMovInv->format('Y-m-d\TH:i'),
            'cantMovInv' => $this->inventario->cantMovInv,
            'uniMovInv' => $this->inventario->uniMovInv,
            'costoUnitInv' => $this->inventario->costoUnitInv,
            'costoTotInv' => $this->inventario->costoTotInv,
            'loteInv' => $this->inventario->loteInv,
            'fecVenceInv' => $this->inventario->fecVenceInv?->format('Y-m-d'),
            'idProve' => $this->inventario->idProve,
            'obsInv' => $this->inventario->obsInv,
        ]);
    }
}; ?>

@section('title', 'Editar Movimiento #' . $inventario->idInv)

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar Movimiento #{{ $inventario->idInv }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">Modifica los datos del movimiento de inventario</p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <a href="{{ route('inventario.movimientos.show', $inventario->idInv) }}" 
                       wire:navigate
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
        <form wire:submit="update">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Información Principal -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Datos del Movimiento -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 bg-blue-600 rounded-t-lg">
                            <h3 class="text-lg font-medium text-white flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                Información del Movimiento
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Tipo de Movimiento -->
                                <div>
                                    <label for="tipMovInv" class="block text-sm font-medium text-gray-700 mb-2">
                                        Tipo de Movimiento <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="tipMovInv" id="tipMovInv" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('tipMovInv') border-red-500 @enderror">
                                        <option value="">Seleccione un tipo</option>
                                        @foreach($tiposMovimiento as $valor => $etiqueta)
                                            <option value="{{ $valor }}" @selected($valor == $tipMovInv)>
                                                {{ $etiqueta }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tipMovInv')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Fecha del Movimiento -->
                                <div>
                                    <label for="fecMovInv" class="block text-sm font-medium text-gray-700 mb-2">
                                        Fecha del Movimiento <span class="text-red-500">*</span>
                                    </label>
                                    <input type="datetime-local" wire:model="fecMovInv" id="fecMovInv" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('fecMovInv') border-red-500 @enderror">
                                    @error('fecMovInv')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Cantidad -->
                                <div>
                                    <label for="cantMovInv" class="block text-sm font-medium text-gray-700 mb-2">
                                        Cantidad <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" wire:model="cantMovInv" id="cantMovInv" step="0.01" min="0.01" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('cantMovInv') border-red-500 @enderror">
                                    @error('cantMovInv')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Unidad -->
                                <div>
                                    <label for="uniMovInv" class="block text-sm font-medium text-gray-700 mb-2">
                                        Unidad <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" wire:model="uniMovInv" id="uniMovInv" required maxlength="50"
                                           placeholder="ej: kg, unidades, litros"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('uniMovInv') border-red-500 @enderror">
                                    @error('uniMovInv')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Costo Unitario -->
                                <div>
                                    <label for="costoUnitInv" class="block text-sm font-medium text-gray-700 mb-2">
                                        Costo Unitario
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-gray-500">$</span>
                                        <input type="number" wire:model="costoUnitInv" id="costoUnitInv" step="0.01" min="0"
                                               class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('costoUnitInv') border-red-500 @enderror">
                                    </div>
                                    @error('costoUnitInv')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Costo Total (Solo lectura, se calcula automáticamente) -->
                                <div>
                                    <label for="costoTotInv" class="block text-sm font-medium text-gray-700 mb-2">
                                        Costo Total (calculado)
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-gray-500">$</span>
                                        <input type="number" wire:model="costoTotInv" id="costoTotInv" readonly
                                               class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg shadow-sm bg-gray-100 cursor-not-allowed">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información Adicional -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 bg-gray-600 rounded-t-lg">
                            <h3 class="text-lg font-medium text-white flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Información Adicional
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Lote/Serie -->
                                <div>
                                    <label for="loteInv" class="block text-sm font-medium text-gray-700 mb-2">
                                        Lote/Serie
                                    </label>
                                    <input type="text" wire:model="loteInv" id="loteInv" maxlength="100"
                                           placeholder="Número de lote o serie"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('loteInv') border-red-500 @enderror">
                                    @error('loteInv')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Fecha de Vencimiento -->
                                <div>
                                    <label for="fecVenceInv" class="block text-sm font-medium text-gray-700 mb-2">
                                        Fecha de Vencimiento
                                    </label>
                                    <input type="date" wire:model="fecVenceInv" id="fecVenceInv"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('fecVenceInv') border-red-500 @enderror">
                                    @error('fecVenceInv')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Observaciones -->
                            <div class="mt-6">
                                <label for="obsInv" class="block text-sm font-medium text-gray-700 mb-2">
                                    Observaciones
                                </label>
                                <textarea wire:model="obsInv" id="obsInv" rows="4"
                                          placeholder="Observaciones adicionales sobre el movimiento..."
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('obsInv') border-red-500 @enderror"></textarea>
                                @error('obsInv')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel Lateral -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Item Asociado (Solo lectura) -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 bg-green-600 rounded-t-lg">
                            <h3 class="text-lg font-medium text-white flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                Item Asociado
                            </h3>
                        </div>
                        <div class="p-6">
                            @if($inventario->insumo)
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 mr-3">
                                        Insumo
                                    </span>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $inventario->insumo->nomIns }}</p>
                                        <p class="text-sm text-gray-500">{{ $inventario->insumo->tipIns ?? '' }} - {{ $inventario->insumo->marIns ?? '' }}</p>
                                    </div>
                                </div>
                            @elseif($inventario->herramienta)
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800 mr-3">
                                        Herramienta
                                    </span>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $inventario->herramienta->nomHer }}</p>
                                        <p class="text-sm text-gray-500">{{ $inventario->herramienta->catHer ?? '' }}</p>
                                    </div>
                                </div>
                            @else
                                <p class="text-gray-500">Item no especificado</p>
                            @endif
                            <div class="mt-4 text-sm text-gray-600">
                                <p><strong>Nota:</strong> El item asociado no se puede cambiar una vez creado el movimiento.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Proveedor -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 bg-purple-600 rounded-t-lg">
                            <h3 class="text-lg font-medium text-white flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                Proveedor
                            </h3>
                        </div>
                        <div class="p-6">
                            <label for="idProve" class="block text-sm font-medium text-gray-700 mb-2">
                                Seleccionar Proveedor
                            </label>
                            <select wire:model="idProve" id="idProve"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('idProve') border-red-500 @enderror">
                                <option value="">Sin proveedor</option>
                                @foreach($proveedores as $proveedor)
                                    <option value="{{ $proveedor->idProve }}" @selected($proveedor->idProve == $idProve)>
                                        {{ $proveedor->nomProve }}
                                    </option>
                                @endforeach
                            </select>
                            @error('idProve')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 bg-indigo-600 rounded-t-lg">
                            <h3 class="text-lg font-medium text-white flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                Acciones
                            </h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <button type="submit" 
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Guardar Cambios
                            </button>

                            <a href="{{ route('inventario.movimientos.show', $inventario->idInv) }}" 
                               wire:navigate
                               class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Cancelar
                            </a>

                            <a href="{{ route('inventario.movimientos.index') }}" 
                               wire:navigate
                               class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                </svg>
                                Ver Todos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>