<?php
// resources/views/livewire/inventario/movimientos/create.blade.php

use App\Models\Inventario;
use App\Models\Insumo;
use App\Models\Herramienta;
use App\Models\Proveedor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    // Propiedades del formulario
    public string $tipMovInv = '';
    public string $fecMovInv = '';
    public string $tipo_item = 'insumo';
    public ?int $idIns = null;
    public ?int $idHer = null;
    public float $cantMovInv = 0.0;
    public string $uniMovInv = '';
    public ?float $costoUnitInv = null;
    public ?float $costoTotInv = null;
    public ?string $loteInv = null;
    public ?string $fecVenceInv = null;
    public ?int $idProve = null;
    public ?string $idFac = null;
    public ?string $obsInv = null;

    // Listas de opciones
    public $insumos;
    public $herramientas;
    public $proveedores;
    public $tiposMovimiento;

    public function mount(): void
    {
        $this->insumos = Insumo::all();
        $this->herramientas = Herramienta::all();
        $this->proveedores = Proveedor::all();
        
        $this->tiposMovimiento = [
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

        $this->fecMovInv = now()->format('Y-m-d\TH:i');
    }

    public function rules(): array
    {
        $rules = [
            'tipo_item' => 'required|in:insumo,herramienta',
            'tipMovInv' => 'required|in:apertura,entrada,salida,consumo,prestamo_salida,prestamo_retorno,perdida,ajuste_pos,ajuste_neg,mantenimiento,venta',
            'cantMovInv' => 'required|numeric|min:0.01',
            'uniMovInv' => 'required|string|max:50',
            'costoUnitInv' => 'nullable|numeric|min:0',
            'fecMovInv' => 'required|date',
            'loteInv' => 'nullable|string|max:100',
            'fecVenceInv' => 'nullable|date|after:today',
            'idProve' => 'nullable|exists:proveedores,idProve',
            'obsInv' => 'nullable|string'
        ];

        // Validación condicional según el tipo de item
        if ($this->tipo_item === 'insumo') {
            $rules['idIns'] = 'required|exists:insumos,idIns';
        } else {
            $rules['idHer'] = 'required|exists:herramientas,idHer';
        }

        return $rules;
    }

    public function save(): void
    {
        $validated = $this->validate();

        try {
            $data = [
                'tipMovInv' => $this->tipMovInv,
                'cantMovInv' => $this->cantMovInv,
                'uniMovInv' => $this->uniMovInv,
                'costoUnitInv' => $this->costoUnitInv,
                'costoTotInv' => $this->costoTotInv,
                'fecMovInv' => $this->fecMovInv,
                'loteInv' => $this->loteInv,
                'fecVenceInv' => $this->fecVenceInv,
                'idProve' => $this->idProve,
                'idFac' => $this->idFac,
                'obsInv' => $this->obsInv,
                'idUsuReg' => auth()->id(),
            ];

            // Asignar el ID correcto según el tipo de item
            if ($this->tipo_item === 'insumo') {
                $data['idIns'] = $this->idIns;
                $data['idHer'] = null;
            } else {
                $data['idHer'] = $this->idHer;
                $data['idIns'] = null;
            }

            Inventario::create($data);

            session()->flash('success', 'Movimiento de inventario registrado exitosamente.');
            $this->redirect(route('inventario.movimientos.index'), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al registrar el movimiento: ' . $e->getMessage());
        }
    }

    // Actualizar consejos según tipo de movimiento
    public function updatedTipMovInv($value): void
    {
        $this->dispatch('actualizar-consejos', tipo: $value);
    }

    // Cambiar tipo de item (insumo/herramienta)
    public function updatedTipoItem($value): void
    {
        $this->reset(['idIns', 'idHer', 'uniMovInv']);
    }

    // Actualizar unidad de medida cuando se selecciona un item
    public function updatedIdIns($value): void
    {
        if ($value) {
            $insumo = Insumo::find($value);
            $this->uniMovInv = $insumo->uniIns;
        }
    }

    public function updatedIdHer($value): void
    {
        if ($value) {
            $herramienta = Herramienta::find($value);
            $this->uniMovInv = $herramienta->uniHer ?? 'Unidad';
        }
    }

    // Calcular costo total
    public function updatedCostoUnitInv($value): void
    {
        $this->costoTotInv = $this->cantMovInv * (float)$value;
    }

    public function updatedCantMovInv($value): void
    {
        if ($this->costoUnitInv) {
            $this->costoTotInv = (float)$value * $this->costoUnitInv;
        }
    }
}; ?>

@section('title', 'Nuevo Movimiento de Inventario')

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Nuevo Movimiento de Inventario
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">Registra entradas, salidas y consumos del inventario</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('inventario.movimientos.index') }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Formulario -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Formulario Principal -->
            <div class="lg:col-span-2">
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
                        <form wire:submit="save">
                            <!-- Alertas de Error -->
                            @if($errors->any())
                                <div class="mb-6 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg">
                                    <div class="flex items-center mb-2">
                                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                        <strong>¡Hay errores en el formulario!</strong>
                                    </div>
                                    <ul class="list-disc list-inside text-sm">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Tipo de Movimiento y Fecha -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label for="tipMovInv" class="block text-sm font-medium text-gray-700 mb-2">
                                        Tipo de Movimiento <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="tipMovInv" id="tipMovInv" required 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('tipMovInv') border-red-500 @enderror">
                                        <option value="">Seleccionar tipo...</option>
                                        @foreach($tiposMovimiento as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('tipMovInv')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">
                                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                        Selecciona según el tipo de operación
                                    </p>
                                </div>

                                <div>
                                    <label for="fecMovInv" class="block text-sm font-medium text-gray-700 mb-2">
                                        Fecha del Movimiento <span class="text-red-500">*</span>
                                    </label>
                                    <input type="datetime-local" wire:model="fecMovInv" id="fecMovInv" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('fecMovInv') border-red-500 @enderror">
                                    @error('fecMovInv')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">
                                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                        Fecha y hora del movimiento
                                    </p>
                                </div>
                            </div>

                            <!-- Selección de Item -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipo de Item <span class="text-red-500">*</span>
                                </label>
                                <div class="flex space-x-4 mb-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" wire:model="tipo_item" value="insumo" 
                                               class="form-radio text-blue-600">
                                        <span class="ml-2 text-sm text-gray-700">Insumo</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" wire:model="tipo_item" value="herramienta" 
                                               class="form-radio text-blue-600">
                                        <span class="ml-2 text-sm text-gray-700">Herramienta</span>
                                    </label>
                                </div>

                                <!-- Selección de Insumo -->
                                <div id="insumo_section" class="{{ $tipo_item == 'insumo' ? '' : 'hidden' }}">
                                    <label for="idIns" class="block text-sm font-medium text-gray-700 mb-2">
                                        Seleccionar Insumo <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="idIns" id="idIns" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('idIns') border-red-500 @enderror">
                                        <option value="">Seleccionar insumo...</option>
                                        @foreach($insumos as $insumo)
                                            <option value="{{ $insumo->idIns }}" 
                                                    data-unidad="{{ $insumo->uniIns }}"
                                                    data-nombre="{{ $insumo->nomIns }}">
                                                {{ $insumo->nomIns }} - {{ $insumo->marIns ?? 'Sin marca' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('idIns')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Selección de Herramienta -->
                                <div id="herramienta_section" class="{{ $tipo_item == 'herramienta' ? '' : 'hidden' }}">
                                    <label for="idHer" class="block text-sm font-medium text-gray-700 mb-2">
                                        Seleccionar Herramienta <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="idHer" id="idHer" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('idHer') border-red-500 @enderror">
                                        <option value="">Seleccionar herramienta...</option>
                                        @foreach($herramientas as $herramienta)
                                            <option value="{{ $herramienta->idHer }}" 
                                                    data-unidad="{{ $herramienta->uniHer ?? 'Unidad' }}"
                                                    data-nombre="{{ $herramienta->nomHer }}">
                                                {{ $herramienta->nomHer }} - {{ $herramienta->marHer ?? 'Sin marca' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('idHer')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Cantidad y Unidad -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label for="cantMovInv" class="block text-sm font-medium text-gray-700 mb-2">
                                        Cantidad <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" wire:model="cantMovInv" id="cantMovInv" required min="0.01" step="0.01"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('cantMovInv') border-red-500 @enderror">
                                    @error('cantMovInv')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">
                                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                        Cantidad del movimiento
                                    </p>
                                </div>

                                <div>
                                    <label for="uniMovInv" class="block text-sm font-medium text-gray-700 mb-2">
                                        Unidad de Medida <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" wire:model="uniMovInv" id="uniMovInv" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('uniMovInv') border-red-500 @enderror"
                                           placeholder="Ej: kg, litros, unidades">
                                    @error('uniMovInv')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">
                                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                        Se copia automáticamente del item
                                    </p>
                                </div>
                            </div>

                            <!-- Costos -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label for="costoUnitInv" class="block text-sm font-medium text-gray-700 mb-2">
                                        Costo Unitario
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-gray-500">$</span>
                                        <input type="number" wire:model="costoUnitInv" id="costoUnitInv" min="0" step="0.01"
                                               class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('costoUnitInv') border-red-500 @enderror">
                                    </div>
                                    @error('costoUnitInv')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">
                                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                        Opcional para control de costos
                                    </p>
                                </div>

                                <div>
                                    <label for="costoTotInv" class="block text-sm font-medium text-gray-700 mb-2">
                                        Costo Total
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-gray-500">$</span>
                                        <input type="number" wire:model="costoTotInv" id="costoTotInv" min="0" step="0.01"
                                               class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-gray-50 @error('costoTotInv') border-red-500 @enderror"
                                               readonly>
                                    </div>
                                    @error('costoTotInv')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">
                                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                        Se calcula automáticamente
                                    </p>
                                </div>
                            </div>

                            <!-- Información Adicional -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label for="loteInv" class="block text-sm font-medium text-gray-700 mb-2">
                                        Lote/Serie
                                    </label>
                                    <input type="text" wire:model="loteInv" id="loteInv"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('loteInv') border-red-500 @enderror"
                                           placeholder="Número de lote o serie">
                                    @error('loteInv')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">
                                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                        Para trazabilidad
                                    </p>
                                </div>

                                <div>
                                    <label for="fecVenceInv" class="block text-sm font-medium text-gray-700 mb-2">
                                        Fecha de Vencimiento
                                    </label>
                                    <input type="date" wire:model="fecVenceInv" id="fecVenceInv"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('fecVenceInv') border-red-500 @enderror">
                                    @error('fecVenceInv')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">
                                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                        Solo para productos con vencimiento
                                    </p>
                                </div>
                            </div>

                            <!-- Información de Proveedor (para entradas y ventas) -->
                            <div id="proveedor_info" class="{{ in_array($tipMovInv, ['entrada', 'venta']) ? 'grid' : 'hidden' }} grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label for="idProve" class="block text-sm font-medium text-gray-700 mb-2">
                                        Proveedor
                                    </label>
                                    <select wire:model="idProve" id="idProve" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('idProve') border-red-500 @enderror">
                                        <option value="">Seleccionar proveedor...</option>
                                        @foreach($proveedores as $proveedor)
                                            <option value="{{ $proveedor->idProve }}">
                                                {{ $proveedor->nomProve }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('idProve')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="idFac" class="block text-sm font-medium text-gray-700 mb-2">
                                        Número de Factura
                                    </label>
                                    <input type="text" wire:model="idFac" id="idFac"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('idFac') border-red-500 @enderror"
                                           placeholder="Número de factura">
                                    @error('idFac')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Observaciones -->
                            <div class="mb-6">
                                <label for="obsInv" class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                                <textarea wire:model="obsInv" id="obsInv" rows="4"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('obsInv') border-red-500 @enderror"
                                          placeholder="Descripción adicional del movimiento, motivo, responsable, etc..."></textarea>
                                @error('obsInv')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Botones -->
                            <div class="flex items-center justify-end space-x-4">
                                <a href="{{ route('inventario.movimientos.index') }}" wire:navigate
                                   class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Cancelar
                                </a>
                                <button type="submit" 
                                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                    </svg>
                                    Guardar Movimiento
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Panel de Ayuda -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Consejos por Tipo de Movimiento -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-yellow-500 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                            Consejos por Tipo
                        </h3>
                    </div>
                    <div class="p-6 space-y-4" id="consejosMovimientoContainer">
                        <div class="text-sm text-gray-600">
                            Selecciona un tipo de movimiento para ver consejos específicos.
                        </div>
                    </div>
                </div>

                <!-- Items Disponibles -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-blue-600 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                            Items del Inventario
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3 text-sm">
                            <div>
                                <h5 class="font-medium text-green-600">Insumos Disponibles:</h5>
                                <p class="text-gray-600">{{ count($insumos) }} insumos registrados</p>
                            </div>
                            <div>
                                <h5 class="font-medium text-blue-600">Herramientas Disponibles:</h5>
                                <p class="text-gray-600">{{ count($herramientas) }} herramientas registradas</p>
                            </div>
                            <div class="pt-2 border-t border-gray-200">
                                <p class="text-xs text-gray-500">
                                    <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    La unidad se llena automáticamente al seleccionar el item
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Proveedores Disponibles (para entradas y ventas) -->
                @if(count($proveedores) > 0)
                <div class="bg-white shadow rounded-lg" id="panelProveedores" style="display: {{ in_array($tipMovInv, ['entrada', 'venta']) ? 'block' : 'none' }};">
                    <div class="px-6 py-4 bg-gray-600 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                            </svg>
                            Proveedores Disponibles
                        </h3>
                    </div>
                    <div class="p-6">
                        @foreach($proveedores->take(5) as $proveedor)
                        <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-200' : '' }}">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $proveedor->nomProve }}</p>
                                <p class="text-xs text-gray-500">{{ $proveedor->tipSumProve ?? 'Proveedor general' }}</p>
                            </div>
                            <button type="button" 
                                    wire:click="$set('idProve', {{ $proveedor->idProve }})"
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Seleccionar
                            </button>
                        </div>
                        @endforeach
                        
                        @if(count($proveedores) > 5)
                        <div class="text-center mt-3">
                            <p class="text-xs text-gray-500">y {{ count($proveedores) - 5 }} más...</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Información Importante -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Importante</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Los movimientos afectan el stock automáticamente</li>
                                    <li>Las entradas suman al inventario</li>
                                    <li>Las salidas y consumos restan del inventario</li>
                                    <li>Registra siempre la fecha real del movimiento</li>
                                    <li>Los costos son opcionales pero recomendados</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Consejos específicos por tipo de movimiento
const consejosPorMovimiento = {
    'apertura': {
        titulo: 'Apertura de Inventario',
        icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        color: 'text-green-600',
        consejos: [
            'Establece el stock inicial del sistema',
            'Registra las cantidades exactas al momento de implementar',
            'Documenta la fecha de conteo físico',
            'Incluye todos los costos de adquisición iniciales'
        ]
    },
    'entrada': {
        titulo: 'Entrada de Inventario',
        icon: 'M12 6v6m0 0v6m0-6h6m-6 0H6',
        color: 'text-green-600',
        consejos: [
            'Verifica que las cantidades sean correctas',
            'Registra siempre el lote si está disponible',
            'Documenta la fecha de vencimiento',
            'Incluye el costo unitario para control financiero'
        ]
    },
    'salida': {
        titulo: 'Salida de Inventario',
        icon: 'M20 12H4',
        color: 'text-red-600',
        consejos: [
            'Verifica que hay stock disponible',
            'Documenta el destino o motivo',
            'Registra quien autoriza la salida',
            'Usa el lote más antiguo (FIFO)'
        ]
    },
    'consumo': {
        titulo: 'Consumo de Insumos',
        icon: 'M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4',
        color: 'text-orange-600',
        consejos: [
            'Especifica en qué animal o área se usó',
            'Registra la dosis aplicada',
            'Documenta quien aplicó el tratamiento',
            'Controla tiempo de retiro si aplica'
        ]
    },
    'prestamo_salida': {
        titulo: 'Préstamo - Salida',
        icon: 'M17 8l4 4m0 0l-4 4m4-4H3',
        color: 'text-blue-600',
        consejos: [
            'Identifica claramente quien recibe el préstamo',
            'Establece fecha tentativa de devolución',
            'Documenta el estado del item prestado',
            'Registra contacto del responsable'
        ]
    },
    'prestamo_retorno': {
        titulo: 'Préstamo - Retorno',
        icon: 'M7 16l-4-4m0 0l4-4m-4 4h18',
        color: 'text-indigo-600',
        consejos: [
            'Verifica el estado del item devuelto',
            'Compara con el estado inicial del préstamo',
            'Documenta cualquier daño o desgaste',
            'Confirma la devolución completa'
        ]
    },
    'perdida': {
        titulo: 'Pérdida de Inventario',
        icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z',
        color: 'text-gray-600',
        consejos: [
            'Explica detalladamente el motivo de la pérdida',
            'Documenta las causas (robo, deterioro, etc.)',
            'Registra quien reporta la pérdida',
            'Evalúa medidas preventivas para el futuro'
        ]
    },
    'ajuste_pos': {
        titulo: 'Ajuste Positivo',
        icon: 'M12 6v6m0 0v6m0-6h6m-6 0H6',
        color: 'text-green-600',
        consejos: [
            'Documenta el motivo del ajuste (conteo físico)',
            'Registra quien realizó la verificación',
            'Incluye fecha del conteo físico',
            'Explica la diferencia encontrada'
        ]
    },
    'ajuste_neg': {
        titulo: 'Ajuste Negativo',
        icon: 'M20 12H4',
        color: 'text-red-600',
        consejos: [
            'Explica claramente por qué se reduce el stock',
            'Documenta el conteo físico realizado',
            'Registra quien detectó la diferencia',
            'Investiga las posibles causas del faltante'
        ]
    },
    'mantenimiento': {
        titulo: 'Mantenimiento de Herramientas',
        icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
        color: 'text-purple-600',
        consejos: [
            'Especifica el tipo de mantenimiento realizado',
            'Documenta el estado antes y después',
            'Registra costos de repuestos o servicios',
            'Programa próximo mantenimiento preventivo'
        ]
    },
    'venta': {
        titulo: 'Venta de Productos',
        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2',
        color: 'text-indigo-600',
        consejos: [
            'Documenta el cliente o destino',
            'Registra el precio de venta',
            'Verifica que el producto esté en buen estado',
            'Emite la documentación correspondiente'
        ]
    }
};

// Escuchar eventos de Livewire para actualizar consejos
document.addEventListener('livewire:initialized', () => {
    Livewire.on('actualizar-consejos', ({ tipo }) => {
        const container = document.getElementById('consejosMovimientoContainer');
        const panelProveedores = document.getElementById('panelProveedores');
        
        // Mostrar/ocultar panel de proveedores
        if (panelProveedores) {
            panelProveedores.style.display = (tipo === 'entrada' || tipo === 'venta') ? 'block' : 'none';
        }
        
        if (tipo && consejosPorMovimiento[tipo]) {
            const consejo = consejosPorMovimiento[tipo];
            const html = `
                <div>
                    <h4 class="font-medium ${consejo.color} flex items-center mb-3">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${consejo.icon}"></path>
                        </svg>
                        ${consejo.titulo}
                    </h4>
                    <div class="space-y-2">
                        ${consejo.consejos.map(c => `
                            <div class="flex items-start text-sm text-gray-600">
                                <svg class="w-4 h-4 ${consejo.color} mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                ${c}
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="text-sm text-gray-600">Selecciona un tipo de movimiento para ver consejos específicos.</div>';
        }
    });
});
</script>