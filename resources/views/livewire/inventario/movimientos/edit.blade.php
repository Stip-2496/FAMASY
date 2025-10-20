<?php
// resources/views/livewire/inventario/movimientos/edit.blade.php

use App\Models\Inventario;
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

    // Para búsqueda de proveedores
    public $busquedaProveedor = '';
    public $proveedorSeleccionado = null;
    public $mostrarListaProveedores = false;
    public $proveedoresFiltrados = [];

    // Para mensajes
    public $showSuccess = false;
    public $showError = false;
    public $showCancel = false;

    // Selects
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
            'tipMovInv' => $movimiento->tipMovInv,
            'fecMovInv' => $movimiento->fecMovInv->format('Y-m-d'),
            'cantMovInv' => $movimiento->cantMovInv,
            'uniMovInv' => $movimiento->uniMovInv,
            'costoUnitInv' => $movimiento->costoUnitInv,
            'costoTotInv' => $movimiento->costoTotInv,
            'loteInv' => $movimiento->loteInv,
            'fecVenceInv' => $movimiento->fecVenceInv?->format('Y-m-d'),
            'idProve' => $movimiento->idProve,
            'obsInv' => $movimiento->obsInv,
        ]);

        if ($this->idProve) {
            $proveedor = Proveedor::find($this->idProve);
            $this->proveedorSeleccionado = $proveedor;
            $this->busquedaProveedor = $proveedor ? $proveedor->nomProve : '';
        }
    }

    public function buscarProveedores()
    {
        if (strlen($this->busquedaProveedor) >= 2) {
            $this->proveedoresFiltrados = Proveedor::where(function($query) {
                $query->where('nomProve', 'like', '%' . $this->busquedaProveedor . '%')
                      ->orWhere('nitProve', 'like', '%' . $this->busquedaProveedor . '%');
            })
            ->orderBy('nomProve')
            ->limit(10)
            ->get();
            $this->mostrarListaProveedores = true;
        } else {
            $this->proveedoresFiltrados = [];
            $this->mostrarListaProveedores = false;
        }
    }

    public function seleccionarProveedor($proveedorId)
    {
        $proveedor = Proveedor::find($proveedorId);
        if ($proveedor) {
            $this->proveedorSeleccionado = $proveedor;
            $this->idProve = $proveedor->idProve;
            $this->busquedaProveedor = $proveedor->nomProve;
            $this->mostrarListaProveedores = false;
        }
    }

    public function limpiarProveedor()
    {
        $this->proveedorSeleccionado = null;
        $this->idProve = '';
        $this->busquedaProveedor = '';
        $this->mostrarListaProveedores = false;
    }

    public function resetForm(): void
    {
        $this->fill([
            'tipMovInv' => $this->inventario->tipMovInv,
            'fecMovInv' => $this->inventario->fecMovInv->format('Y-m-d'),
            'cantMovInv' => $this->inventario->cantMovInv,
            'uniMovInv' => $this->inventario->uniMovInv,
            'costoUnitInv' => $this->inventario->costoUnitInv,
            'costoTotInv' => $this->inventario->costoTotInv,
            'loteInv' => $this->inventario->loteInv,
            'fecVenceInv' => $this->inventario->fecVenceInv?->format('Y-m-d'),
            'idProve' => $this->inventario->idProve,
            'obsInv' => $this->inventario->obsInv,
        ]);

        if ($this->idProve) {
            $proveedor = Proveedor::find($this->idProve);
            $this->proveedorSeleccionado = $proveedor;
            $this->busquedaProveedor = $proveedor ? $proveedor->nomProve : '';
        } else {
            $this->proveedorSeleccionado = null;
            $this->busquedaProveedor = '';
        }

        $this->mostrarListaProveedores = false;
        $this->resetErrorBag();
        $this->showCancel = true;
        $this->showSuccess = false;
        $this->showError = false;
        $this->dispatch('hide-cancel-message');
    }

    public function update(): void
    {
        $this->validate([
            'tipMovInv' => 'required|in:' . implode(',', array_keys($this->tiposMovimiento)),
            'cantMovInv' => 'required|numeric|min:0.01',
            'uniMovInv' => 'required|string|max:50',
            'costoUnitInv' => 'nullable|numeric|min:0',
            'fecMovInv' => 'required|date',
            'loteInv' => 'nullable|string|max:100',
            'fecVenceInv' => 'nullable|date|after:today',
            'idProve' => 'nullable|exists:proveedores,idProve',
            'obsInv' => 'nullable|string',
        ]);

        $this->costoTotInv = $this->costoUnitInv ? $this->cantMovInv * $this->costoUnitInv : null;

        try {
            $this->inventario->update([
                'tipMovInv' => $this->tipMovInv,
                'fecMovInv' => $this->fecMovInv,
                'cantMovInv' => $this->cantMovInv,
                'uniMovInv' => $this->uniMovInv,
                'costoUnitInv' => $this->costoUnitInv,
                'costoTotInv' => $this->costoTotInv,
                'loteInv' => $this->loteInv,
                'fecVenceInv' => $this->fecVenceInv,
                'idProve' => $this->idProve,
                'obsInv' => $this->obsInv,
            ]);

            $this->showSuccess = true;
            $this->showError = false;
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Movimiento de inventario actualizado exitosamente.'
            ]);

        } catch (\Exception $e) {
            $this->showError = true;
            $this->showSuccess = false;
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar el movimiento: ' . $e->getMessage()
            ]);
        }
    }

    public function updatedBusquedaProveedor()
    {
        $this->buscarProveedores();
    }
}; ?>

@section('title', 'Editar Movimiento')

<div class="flex items-center justify-center min-h-screen py-3">
    <div class="w-full max-w-7xl mx-auto bg-white/80 backdrop-blur-xl shadow rounded-3xl p-3 relative border border-white/20">
        <!-- Botón Volver -->
        <div class="absolute top-2 right-2">
            <a href="{{ route('inventario.movimientos.index') }}" wire:navigate
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
             x-data="{ 
                 showSuccess: @entangle('showSuccess'),
                 showError: @entangle('showError'), 
                 showCancel: @entangle('showCancel')
             }"
             x-init="$watch('showCancel', value => { if(value) setTimeout(() => showCancel = false, 3000) })">
            <div class="flex justify-center mb-1">
                <div class="p-2 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <div class="w-4 h-4 text-white flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <h1 class="text-base font-black bg-gradient-to-r from-gray-900 via-gray-800 to-blue-800 bg-clip-text text-transparent mb-1">
                Editar Movimiento
            </h1>

            <template x-if="showSuccess">
                <div class="rounded bg-green-100 px-2 py-1 text-green-800 border border-green-400 text-xs mb-1 font-semibold">
                    ¡Movimiento actualizado exitosamente!
                </div>
            </template>

            <template x-if="showError">
                <div class="rounded bg-red-100 px-2 py-1 text-red-800 border border-red-400 text-xs mb-1 font-semibold">
                    Error al actualizar. Verifique los datos.
                </div>
            </template>

            <template x-if="showCancel">
                <div class="rounded bg-yellow-100 px-2 py-1 text-yellow-800 border border-yellow-400 text-xs mb-1 font-semibold">
                    Cambios descartados. Los datos se han restablecido.
                </div>
            </template>

            <template x-if="!showSuccess && !showError && !showCancel">
                <p class="text-gray-600 text-xs">Actualiza los datos del movimiento de inventario</p>
            </template>
        </div>

        <!-- Formulario -->
        <form wire:submit="update" class="space-y-2">
            <!-- Fila 1: Información Básica -->
            <div class="flex flex-col md:flex-row gap-2">
                <!-- Información del Movimiento -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#2563eb]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Información del Movimiento</h2>
                                <p class="text-gray-600 text-[10px]">Datos principales del movimiento</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <!-- Tipo de Movimiento -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Tipo de Movimiento <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <select wire:model="tipMovInv"
                                            class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('tipMovInv') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                        <option value="">Seleccione un tipo</option>
                                        @foreach($tiposMovimiento as $valor => $etiqueta)
                                            <option value="{{ $valor }}">{{ $etiqueta }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('tipMovInv')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Fecha del Movimiento -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Fecha del Movimiento <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="date"
                                           wire:model="fecMovInv"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('fecMovInv') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                           required>
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('fecMovInv')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Cantidad -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Cantidad <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="number"
                                           wire:model="cantMovInv"
                                           step="0.01"
                                           min="0.01"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('cantMovInv') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                           required>
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('cantMovInv')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Unidad -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Unidad <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="text"
                                           wire:model="uniMovInv"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('uniMovInv') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                           placeholder="ej: kg, unidades, litros"
                                           maxlength="50"
                                           required>
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('uniMovInv')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Costo Unitario -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Costo Unitario
                                </label>
                                <div class="relative group">
                                    <span class="absolute left-3 top-2 text-gray-500 text-xs">$</span>
                                    <input type="number"
                                           wire:model="costoUnitInv"
                                           step="0.01"
                                           min="0"
                                           class="w-full pl-6 pr-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('costoUnitInv') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('costoUnitInv')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Costo Total -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Costo Total (calculado)
                                </label>
                                <div class="relative group">
                                    <span class="absolute left-3 top-2 text-gray-500 text-xs">$</span>
                                    <input type="number"
                                           wire:model="costoTotInv"
                                           readonly
                                           class="w-full pl-6 pr-1.5 py-1 bg-gray-100/50 border-2 border-gray-200 rounded-2xl shadow-lg cursor-not-allowed text-xs">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Item Asociado -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#2563eb] to-[#000000]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Item Asociado</h2>
                                <p class="text-gray-600 text-[10px]">Información del item asociado</p>
                            </div>
                        </div>

                        @if($inventario->insumo)
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-800 mr-2">
                                    Insumo
                                </span>
                                <div>
                                    <p class="font-medium text-gray-900 text-xs">{{ $inventario->insumo->nomIns }}</p>
                                    <p class="text-[10px] text-gray-500">{{ $inventario->insumo->tipIns ?? '' }} - {{ $inventario->insumo->marIns ?? '' }}</p>
                                </div>
                            </div>
                        @elseif($inventario->herramienta)
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-blue-100 text-blue-800 mr-2">
                                    Herramienta
                                </span>
                                <div>
                                    <p class="font-medium text-gray-900 text-xs">{{ $inventario->herramienta->nomHer }}</p>
                                    <p class="text-[10px] text-gray-500">{{ $inventario->herramienta->catHer ?? '' }}</p>
                                </div>
                            </div>
                        @else
                            <p class="text-gray-500 text-xs">Item no especificado</p>
                        @endif
                        <div class="mt-1 text-[10px] text-gray-600">
                            <p><strong>Nota:</strong> El item asociado no se puede cambiar.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fila 2: Información Adicional y Proveedor -->
            <div class="flex flex-col md:flex-row gap-2">
                <!-- Información Adicional -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#2563eb]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Información Adicional</h2>
                                <p class="text-gray-600 text-[10px]">Detalles adicionales del movimiento</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <!-- Lote/Serie -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Lote/Serie
                                </label>
                                <div class="relative group">
                                    <input type="text"
                                           wire:model="loteInv"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('loteInv') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                           placeholder="Número de lote o serie"
                                           maxlength="100">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('loteInv')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Fecha de Vencimiento -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Fecha de Vencimiento
                                </label>
                                <div class="relative group">
                                    <input type="date"
                                           wire:model="fecVenceInv"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('fecVenceInv') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('fecVenceInv')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="mt-2">
                            <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                Observaciones
                            </label>
                            <div class="relative group">
                                <textarea wire:model="obsInv"
                                          rows="4"
                                          class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('obsInv') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                          placeholder="Observaciones adicionales sobre el movimiento..."></textarea>
                                <div class="absolute inset-0 bg-gradient-to-r from-purple-500/5 to-pink-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                            </div>
                            @error('obsInv')
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

                <!-- Proveedor -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#2563eb] to-[#000000]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Proveedor</h2>
                                <p class="text-gray-600 text-[10px]">Asigna un proveedor para mejor trazabilidad</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">Buscar Proveedor</label>
                                <div class="flex gap-1">
                                    <div class="flex-1 relative group">
                                        <input type="text"
                                               wire:model.live="busquedaProveedor"
                                               placeholder="Buscar por nombre o NIT..."
                                               class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl text-xs">
                                        @if($mostrarListaProveedores && count($proveedoresFiltrados) > 0)
                                            <div class="absolute z-10 w-full bg-white border-2 border-gray-200 rounded-2xl mt-1 max-h-32 overflow-y-auto shadow-xl">
                                                @foreach($proveedoresFiltrados as $proveedor)
                                                    <button type="button"
                                                            wire:click="seleccionarProveedor({{ $proveedor->idProve }})"
                                                            class="w-full text-left px-2 py-1 hover:bg-gray-100 border-b border-gray-100 last:border-b-0 text-xs">
                                                        <div class="font-medium">{{ $proveedor->nomProve }}</div>
                                                        <div class="text-[10px] text-gray-500">NIT: {{ $proveedor->nitProve }}</div>
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @error('idProve')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            @if($proveedorSeleccionado)
                                <div class="bg-blue-50 border-2 border-blue-200 rounded-2xl p-2">
                                    <h4 class="font-medium text-blue-900 text-xs mb-1">Proveedor Seleccionado</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-1 text-[10px]">
                                        <div><strong>Nombre:</strong> {{ $proveedorSeleccionado->nomProve }}</div>
                                        <div><strong>NIT:</strong> {{ $proveedorSeleccionado->nitProve }}</div>
                                        @if($proveedorSeleccionado->telProve)
                                            <div><strong>Teléfono:</strong> {{ $proveedorSeleccionado->telProve }}</div>
                                        @endif
                                        @if($proveedorSeleccionado->emaProve)
                                            <div><strong>Email:</strong> {{ $proveedorSeleccionado->emaProve }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-center space-x-2 pt-2">
                <button type="button"
                        wire:click="resetForm"
                        class="cursor-pointer group relative inline-flex items-center justify-center px-2.5 py-1 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                    <svg class="w-3 h-3 mr-1.5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span class="relative z-10 text-xs">Cancelar</span>
                </button>
                <button type="submit"
                        class="cursor-pointer group relative inline-flex items-center justify-center px-2.5 py-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                    <svg class="w-3 h-3 mr-1.5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="relative z-10 text-xs">Actualizar Movimiento</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Script para notificaciones -->
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

        Livewire.on('hide-cancel-message', () => {
            setTimeout(() => {
                @this.set('showCancel', false);
            }, 3000);
        });
    });
</script>