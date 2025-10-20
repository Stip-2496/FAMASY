<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Inventario;
use App\Models\Insumo;
use App\Models\Herramienta;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.auth')] class extends Component {

    // Campos del movimiento
    public $fecMovInv = '';
    public $tipMovInv = '';
    public $canMovInv = '';
    public $obsMovInv = '';
    public $idInsMovInv = '';
    public $idHerMovInv = '';
    public $idProve = '';
    public $idUsuMovInv = '';

    // Para selectores
    public $insumos = [];
    public $herramientas = [];
    public $usuarios = [];
    public $tiposMovimiento = [
        'apertura',
        'entrada',
        'salida',
        'consumo',
        'prestamo_salida',
        'prestamo_retorno',
        'perdida',
        'ajuste_pos',
        'ajuste_neg',
        'mantenimiento',
        'venta'
    ];

    // Para búsqueda de proveedores
    public string $searchProveedor = '';

    // Para mensajes
    public $showSuccess = false;
    public $showError = false;
    public $showCancel = false;

    public function mount()
    {
        if (!Auth::check()) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Usuario no autenticado'
            ]);
            return redirect()->route('login');
        }

        $this->fecMovInv = now()->format('Y-m-d');
        $this->idUsuMovInv = Auth::id();
        
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        $this->insumos = Insumo::whereNull('deleted_at')
            ->orderBy('nomIns')
            ->get(['idIns', 'nomIns', 'canIns', 'uniIns']);
            
        $this->herramientas = Herramienta::whereNull('deleted_at')
            ->orderBy('nomHer')
            ->get(['idHer', 'nomHer', 'canHer']);
            
        $this->usuarios = User::orderBy('nomUsu')
            ->get(['id', 'nomUsu', 'apeUsu']);
    }

    public function with(): array
    {
        $query = Proveedor::query();
        
        if ($this->searchProveedor) {
            $query->where('nomProve', 'like', "%{$this->searchProveedor}%")
                  ->orWhere('nitProve', 'like', "%{$this->searchProveedor}%");
        }
        
        $proveedoresFiltrados = $query->orderBy('nomProve')->get();
        
        return [
            'usuarioActual' => Auth::user(),
            'proveedoresFiltrados' => $proveedoresFiltrados,
        ];
    }

    public function rules(): array
    {
        return [
            'fecMovInv' => 'required|date',
            'tipMovInv' => 'required|in:apertura,entrada,salida,consumo,prestamo_salida,prestamo_retorno,perdida,ajuste_pos,ajuste_neg,mantenimiento,venta',
            'canMovInv' => 'required|numeric|min:0.01',
            'obsMovInv' => 'nullable|string|max:500',
            'idUsuMovInv' => 'required|exists:users,id',
            'idInsMovInv' => 'nullable|exists:insumos,idIns',
            'idHerMovInv' => 'nullable|exists:herramientas,idHer',
            'idProve' => 'nullable|exists:proveedores,idProve',
        ];
    }

    public function validateField($fieldName)
    {
        $this->validateOnly($fieldName, $this->rules());
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules());
    }

    public function selectProveedor($proveedorId): void
    {
        $this->idProve = $proveedorId;
        $this->searchProveedor = '';
    }

    public function clearProveedor(): void
    {
        $this->idProve = null;
        $this->searchProveedor = '';
    }

    public function limpiarFormulario(): void
    {
        $this->fecMovInv = now()->format('Y-m-d');
        $this->tipMovInv = '';
        $this->canMovInv = '';
        $this->obsMovInv = '';
        $this->idInsMovInv = '';
        $this->idHerMovInv = '';
        $this->idProve = '';
        $this->searchProveedor = '';
        $this->idUsuMovInv = Auth::id();
        
        $this->resetErrorBag();
    }

    public function cancelarRegistro(): void
    {
        $this->limpiarFormulario();
        $this->dispatch('registro-cancelado');
    }

    public function save()
    {
        $rules = $this->rules();

        // Validar que se seleccione al menos insumo o herramienta
        if (empty($this->idInsMovInv) && empty($this->idHerMovInv)) {
            $this->addError('general', 'Debe seleccionar al menos un insumo o una herramienta');
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Debe seleccionar al menos un insumo o una herramienta'
            ]);
            return;
        }

        // Validar stock para Salida
        if (in_array($this->tipMovInv, ['salida', 'consumo', 'prestamo_salida', 'perdida', 'ajuste_neg']) && $this->canMovInv) {
            if ($this->idInsMovInv) {
                $insumo = $this->insumos->firstWhere('idIns', $this->idInsMovInv);
                if ($insumo && $this->canMovInv > $insumo->canIns) {
                    $this->addError('canMovInv', 'La cantidad solicitada excede el stock disponible (' . $insumo->canIns . ')');
                    $this->dispatch('notify', [
                        'type' => 'error',
                        'message' => 'La cantidad solicitada excede el stock disponible'
                    ]);
                    return;
                }
            } elseif ($this->idHerMovInv) {
                $herramienta = $this->herramientas->firstWhere('idHer', $this->idHerMovInv);
                if ($herramienta && $this->canMovInv > $herramienta->canHer) {
                    $this->addError('canMovInv', 'La cantidad solicitada excede el stock disponible (' . $herramienta->canHer . ')');
                    $this->dispatch('notify', [
                        'type' => 'error',
                        'message' => 'La cantidad solicitada excede el stock disponible'
                    ]);
                    return;
                }
            }
        }

        $validated = $this->validate($rules, [
            'fecMovInv.required' => 'La fecha es obligatoria',
            'fecMovInv.date' => 'La fecha debe ser válida',
            'tipMovInv.required' => 'El tipo de movimiento es obligatorio',
            'tipMovInv.in' => 'El tipo de movimiento no es válido',
            'canMovInv.required' => 'La cantidad es obligatoria',
            'canMovInv.numeric' => 'La cantidad debe ser un número',
            'canMovInv.min' => 'La cantidad debe ser mayor a 0',
            'idInsMovInv.exists' => 'El insumo seleccionado no existe',
            'idHerMovInv.exists' => 'La herramienta seleccionada no existe',
            'idProve.exists' => 'El proveedor seleccionado no existe',
            'idUsuMovInv.required' => 'El usuario es obligatorio',
            'idUsuMovInv.exists' => 'El usuario seleccionado no existe',
        ]);

        try {
            // Obtener la unidad de medida según el tipo de item
            $unidad = 'unidad'; // Valor por defecto
            if ($this->idInsMovInv) {
                $insumo = Insumo::find($this->idInsMovInv);
                $unidad = $insumo->uniIns ?? 'unidad';
            }

            // Crear el registro en la tabla inventario
            Inventario::create([
                'idIns' => $this->idInsMovInv ?: null,
                'idHer' => $this->idHerMovInv ?: null,
                'tipMovInv' => $this->tipMovInv,
                'cantMovInv' => $this->canMovInv,
                'uniMovInv' => $unidad,
                'costoUnitInv' => 0, // Valor por defecto, ajusta según tu lógica
                'costoTotInv' => 0,  // Valor por defecto, ajusta según tu lógica
                'fecMovInv' => $this->fecMovInv,
                'idProve' => $this->idProve ?: null,
                'idUsuReg' => $this->idUsuMovInv,
                'obsInv' => $this->obsMovInv,
            ]);

            // Actualizar el stock en insumos o herramientas
            if ($this->idInsMovInv) {
                $insumo = Insumo::find($this->idInsMovInv);
                if (in_array($this->tipMovInv, ['entrada', 'prestamo_retorno', 'ajuste_pos'])) {
                    $insumo->canIns += $this->canMovInv;
                } elseif (in_array($this->tipMovInv, ['salida', 'consumo', 'prestamo_salida', 'perdida', 'ajuste_neg'])) {
                    $insumo->canIns -= $this->canMovInv;
                }
                $insumo->save();
            } elseif ($this->idHerMovInv) {
                $herramienta = Herramienta::find($this->idHerMovInv);
                if (in_array($this->tipMovInv, ['entrada', 'prestamo_retorno', 'ajuste_pos'])) {
                    $herramienta->canHer += $this->canMovInv;
                } elseif (in_array($this->tipMovInv, ['salida', 'consumo', 'prestamo_salida', 'perdida', 'ajuste_neg'])) {
                    $herramienta->canHer -= $this->canMovInv;
                }
                $herramienta->save();
            }

            $this->limpiarFormulario();
            $this->showSuccess = true;
            $this->dispatch('registro-exitoso');

        } catch (\Exception $e) {
            \Log::error('Error al crear movimiento: ' . $e->getMessage());
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al crear el movimiento: ' . $e->getMessage()
            ]);
        }
    }

    public function updatedIdInsMovInv()
    {
        if (!empty($this->idInsMovInv)) {
            $this->idHerMovInv = '';
        }
        $this->validateField('idInsMovInv');
    }

    public function updatedIdHerMovInv()
    {
        if (!empty($this->idHerMovInv)) {
            $this->idInsMovInv = '';
        }
        $this->validateField('idHerMovInv');
    }

    public function getStockActual($tipo, $id)
    {
        if ($tipo === 'insumo' && $id) {
            $insumo = $this->insumos->firstWhere('idIns', $id);
            return $insumo ? $insumo->canIns : 0;
        }
        
        if ($tipo === 'herramienta' && $id) {
            $herramienta = $this->herramientas->firstWhere('idHer', $id);
            return $herramienta ? $herramienta->canHer : 0;
        }
        
        return 0;
    }
}; ?>

@section('title', 'Nuevo Movimiento')

<div class="flex items-center justify-center min-h-screen py-3">
    <div class="w-full max-w-7xl mx-auto bg-white/80 backdrop-blur-xl shadow rounded-3xl p-3 relative border border-white/20">
        <!-- Botón Volver -->
        <div class="absolute top-2 right-2">
            <a href="/inventario/movimientos" wire:navigate
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
             x-init="
                $watch('showSuccess', value => { if(value) setTimeout(() => showSuccess = false, 3000) });
                $watch('showCancel', value => { if(value) setTimeout(() => showCancel = false, 3000) });
             ">
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
                Registrar Nuevo Movimiento
            </h1>
            
            <template x-if="showSuccess">
                <div class="rounded bg-green-100 px-2 py-1 text-green-800 border border-green-400 text-xs mb-1 font-semibold">
                    ¡Movimiento registrado exitosamente!
                </div>
            </template>

            <template x-if="showError">
                <div class="rounded bg-red-100 px-2 py-1 text-red-800 border border-red-400 text-xs mb-1 font-semibold">
                    Error en el registro. Verifique los datos.
                </div>
            </template>

            <template x-if="showCancel">
                <div class="rounded bg-yellow-100 px-2 py-1 text-yellow-800 border border-yellow-400 text-xs mb-1 font-semibold">
                    Formulario limpiado. Puede registrar un nuevo movimiento.
                </div>
            </template>

            <template x-if="!showSuccess && !showError && !showCancel">
                <p class="text-gray-600 text-xs">Complete los datos del nuevo movimiento de inventario</p>
            </template>
        </div>

        <!-- Formulario -->
        <form wire:submit="save" class="space-y-2">
            <!-- Fila 1: Información Básica -->
            <div class="flex flex-col md:flex-row gap-2">
                <!-- Información Básica -->
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
                                <h2 class="text-xs font-bold text-gray-900">Información Básica</h2>
                                <p class="text-gray-600 text-[10px]">Datos principales del movimiento</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <!-- Fecha del Movimiento -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Fecha del Movimiento <span class="text-red-500">*</span>
                                </label>
                                <div class="w-full px-1.5 py-1 bg-gray-100/50 border-2 border-gray-200 rounded-2xl shadow-lg text-xs">
                                    {{ $fecMovInv }}
                                </div>
                                <input type="hidden" wire:model="fecMovInv">
                                @error('fecMovInv')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Tipo de Movimiento -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Tipo de Movimiento <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <select wire:model="tipMovInv"
                                            class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('tipMovInv') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                        <option value="">Seleccionar tipo</option>
                                        @foreach($tiposMovimiento as $tipo)
                                            <option value="{{ $tipo }}">{{ ucwords(str_replace('_', ' ', $tipo)) }}</option>
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

                            <!-- Cantidad -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Cantidad <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="number"
                                           wire:model="canMovInv"
                                           step="0.01"
                                           min="0.01"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('canMovInv') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                           placeholder="0.00"
                                           required>
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('canMovInv')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Usuario -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Encargado
                                </label>
                                <div class="w-full px-1.5 py-1 bg-gray-100/50 border-2 border-gray-200 rounded-2xl shadow-lg text-xs">
                                    {{ $usuarioActual->nomUsu }} {{ $usuarioActual->apeUsu }}
                                </div>
                                <input type="hidden" wire:model="idUsuMovInv">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selección de Item -->
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
                                <h2 class="text-xs font-bold text-gray-900">Selección de Item</h2>
                                <p class="text-gray-600 text-[10px]">Seleccione insumo o herramienta</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-2">
                           <!-- Insumo -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Insumo
                                </label>
                                <div class="relative group">
                                    <select wire:model.live="idInsMovInv"
                                            class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('idInsMovInv') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                        <option value="">Seleccionar insumo</option>
                                        @foreach($insumos as $insumo)
                                            <option value="{{ $insumo->idIns }}">
                                                {{ $insumo->nomIns }} (Stock: {{ $insumo->canIns }} {{ $insumo->uniIns }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('idInsMovInv')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                                
                                @if($idInsMovInv)
                                    <div class="mt-1 text-[10px] text-blue-600 font-medium">
                                        Stock actual: {{ $this->getStockActual('insumo', $idInsMovInv) }} {{ $insumos->firstWhere('idIns', $idInsMovInv)->uniIns ?? 'unidades' }}
                                    </div>
                                @endif
                            </div>

                            <!-- Herramienta -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Herramienta
                                </label>
                                <div class="relative group">
                                    <select wire:model.live="idHerMovInv"
                                            class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('idHerMovInv') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
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
                                @error('idHerMovInv')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                                
                                @if($idHerMovInv)
                                    <div class="mt-1 text-[10px] text-blue-600 font-medium">
                                        Stock actual: {{ $this->getStockActual('herramienta', $idHerMovInv) }} unidades
                                    </div>
                                @endif
                            </div>
                        </div>

                        @error('general') 
                            <div class="mt-1 text-[10px] text-red-600 flex items-center">
                                <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </div>
                        @enderror

                        <div class="mt-1 text-[10px] text-gray-600">
                            Debe seleccionar exactamente un insumo o una herramienta, no ambos.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fila 2: Proveedor y Observaciones -->
            <div class="flex flex-col md:flex-row gap-2">
                <!-- Proveedor -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#2563eb]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Proveedor (Opcional)</h2>
                                <p class="text-gray-600 text-[10px]">Asigna un proveedor para mejor trazabilidad</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-2">
                            <!-- Proveedor Seleccionado -->
                            @if($idProve)
                                @php
                                    $proveedorActual = \App\Models\Proveedor::find($idProve);
                                @endphp
                                @if($proveedorActual)
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
                                                    <p class="text-xs font-medium text-blue-800">{{ $proveedorActual->nomProve }}</p>
                                                    <p class="text-[10px] text-blue-600">{{ $proveedorActual->nitProve }}</p>
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
                                    </div>

                                    <!-- Lista de Proveedores -->
                                    @if($searchProveedor && $proveedoresFiltrados->count() > 0)
                                        <div class="border border-gray-300 rounded-2xl max-h-32 overflow-y-auto">
                                            @foreach($proveedoresFiltrados as $proveedor)
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
                                    @elseif($searchProveedor && $proveedoresFiltrados->count() === 0)
                                        <div class="text-center py-2 text-gray-500 text-[10px]">
                                            No se encontraron proveedores con ese criterio
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @error('idProve')
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

                <!-- Observaciones -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#2563eb] to-[#000000]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl shadow-lg">
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
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Notas adicionales
                                </label>
                                <div class="relative group">
                                    <textarea wire:model="obsMovInv" 
                                              rows="4"
                                              class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('obsMovInv') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                              placeholder="Ingrese cualquier observación relevante..."></textarea>
                                    <div class="absolute inset-0 bg-gradient-to-r from-purple-500/5 to-pink-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('obsMovInv')
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
                <button type="button" 
                        wire:click="cancelarRegistro"
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
                    <span class="relative z-10 text-xs">Registrar Movimiento</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Script para notificaciones -->
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('notify', (event) => {
            console.log('Notify event:', event); // Para depuración
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
                icon: event[0].type,
                title: event[0].message
            });
        });

        Livewire.on('registro-exitoso', () => {
            console.log('Registro exitoso'); // Para depuración
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            
            Toast.fire({
                icon: 'success',
                title: '¡Movimiento registrado exitosamente!'
            });
        });
    });
</script>