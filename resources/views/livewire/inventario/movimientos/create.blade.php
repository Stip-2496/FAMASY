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
    public $proveedores = [];
    public $usuarios = [];
    public $tiposMovimiento = ['Entrada', 'Salida', 'Ajuste', 'Transferencia'];

    // Para búsqueda de proveedores
    public $busquedaProveedor = '';
    public $proveedorSeleccionado = null;
    public $mostrarListaProveedores = false;
    public $proveedoresFiltrados = [];

    // Para crear proveedor
    public $mostrarCrearProveedor = false;
    public $nuevoProveedor = [
        'nomProve' => '',
        'nitProve' => '',
        'telProve' => '',
        'dirProve' => '',
        'emaProve' => ''
    ];

    public function mount()
    {
        $this->fecMovInv = now()->format('Y-m-d');
        $this->idUsuMovInv = Auth::id();
        
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        $this->insumos = Insumo::where('estIns', 'Activo')
            ->orderBy('nomIns')
            ->get(['idIns', 'nomIns', 'canIns']);
            
        $this->herramientas = Herramienta::where('estHer', 'Activo')
            ->orderBy('nomHer')
            ->get(['idHer', 'nomHer', 'canHer']);
            
        $this->usuarios = User::orderBy('nomUsu')
            ->get(['id', 'nomUsu', 'apeUsu']);
            
        $this->proveedores = Proveedor::orderBy('nomProve')->get();
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

    public function abrirCrearProveedor()
    {
        $this->mostrarCrearProveedor = true;
        $this->nuevoProveedor = [
            'nomProve' => '',
            'nitProve' => '',
            'telProve' => '',
            'dirProve' => '',
            'emaProve' => ''
        ];
    }

    public function cerrarCrearProveedor()
    {
        $this->mostrarCrearProveedor = false;
        $this->reset('nuevoProveedor');
    }

    public function crearProveedor()
    {
        $this->validate([
            'nuevoProveedor.nomProve' => 'required|string|max:100|unique:proveedores,nomProve',
            'nuevoProveedor.nitProve' => 'required|string|max:20|unique:proveedores,nitProve',
            'nuevoProveedor.telProve' => 'nullable|string|max:15',
            'nuevoProveedor.dirProve' => 'nullable|string|max:200',
            'nuevoProveedor.emaProve' => 'nullable|email|max:100',
        ], [
            'nuevoProveedor.nomProve.required' => 'El nombre del proveedor es obligatorio',
            'nuevoProveedor.nomProve.unique' => 'Ya existe un proveedor con este nombre',
            'nuevoProveedor.nitProve.required' => 'El NIT es obligatorio',
            'nuevoProveedor.nitProve.unique' => 'Ya existe un proveedor con este NIT',
            'nuevoProveedor.emaProve.email' => 'El email debe tener un formato válido',
        ]);

        try {
            $proveedor = Proveedor::create($this->nuevoProveedor);
            
            $this->seleccionarProveedor($proveedor->idProve);
            $this->cargarDatos();
            $this->cerrarCrearProveedor();
            
            session()->flash('success', 'Proveedor creado y seleccionado exitosamente');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al crear el proveedor: ' . $e->getMessage());
        }
    }

    public function save()
    {
        $rules = [
            'fecMovInv' => 'required|date',
            'tipMovInv' => 'required|in:Entrada,Salida,Ajuste,Transferencia',
            'canMovInv' => 'required|numeric|min:0.01',
            'obsMovInv' => 'nullable|string|max:500',
            'idUsuMovInv' => 'required|exists:users,id',
        ];

        // Validar que se seleccione al menos insumo o herramienta
        if (empty($this->idInsMovInv) && empty($this->idHerMovInv)) {
            $this->addError('general', 'Debe seleccionar al menos un insumo o una herramienta');
            return;
        }

        // Si se selecciona insumo
        if (!empty($this->idInsMovInv)) {
            $rules['idInsMovInv'] = 'required|exists:insumos,idIns';
        }

        // Si se selecciona herramienta
        if (!empty($this->idHerMovInv)) {
            $rules['idHerMovInv'] = 'required|exists:herramientas,idHer';
        }

        // Proveedor opcional pero debe existir si se proporciona
        if (!empty($this->idProve)) {
            $rules['idProve'] = 'exists:proveedores,idProve';
        }

        $this->validate($rules, [
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
            $movimiento = Inventario::create([
                'fecMovInv' => $this->fecMovInv,
                'tipMovInv' => $this->tipMovInv,
                'canMovInv' => $this->canMovInv,
                'obsMovInv' => $this->obsMovInv,
                'idInsMovInv' => $this->idInsMovInv ?: null,
                'idHerMovInv' => $this->idHerMovInv ?: null,
                'idProve' => $this->idProve ?: null,
                'idUsuMovInv' => $this->idUsuMovInv,
            ]);

            session()->flash('success', 'Movimiento de inventario registrado exitosamente');

            return redirect('/inventario/movimientos');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al crear el movimiento: ' . $e->getMessage());
        }
    }

    public function updatedBusquedaProveedor()
    {
        $this->buscarProveedores();
    }

    public function updatedIdInsMovInv()
    {
        if (!empty($this->idInsMovInv)) {
            $this->idHerMovInv = '';
        }
    }

    public function updatedIdHerMovInv()
    {
        if (!empty($this->idHerMovInv)) {
            $this->idInsMovInv = '';
        }
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
}; // <-- Aquí estaba el error: faltaba cerrar la clase y el componente
?>

<div class="max-w-4xl mx-auto p-6">
    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Registrar Movimiento de Inventario</h1>
                <p class="text-sm text-gray-600 mt-1">Complete la información del movimiento</p>
            </div>
            <a href="/inventario/movimientos" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                Volver
            </a>
        </div>
    </div>

    <!-- Formulario -->
    <form wire:submit="save" class="space-y-6">
        
        <!-- Información Básica -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Información Básica</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Fecha -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha del Movimiento *</label>
                    <input type="date" wire:model="fecMovInv" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('fecMovInv') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Tipo de Movimiento -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Movimiento *</label>
                    <select wire:model="tipMovInv" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Seleccione un tipo</option>
                        @foreach($tiposMovimiento as $tipo)
                            <option value="{{ $tipo }}">{{ $tipo }}</option>
                        @endforeach
                    </select>
                    @error('tipMovInv') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Cantidad -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cantidad *</label>
                    <input type="number" wire:model="canMovInv" step="0.01" min="0.01"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="0.00">
                    @error('canMovInv') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Usuario -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Usuario *</label>
                    <select wire:model="idUsuMovInv" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Seleccione un usuario</option>
                        @foreach($usuarios as $usuario)
                            <option value="{{ $usuario->id }}">{{ $usuario->nomUsu }} {{ $usuario->apeUsu }}</option>
                        @endforeach
                    </select>
                    @error('idUsuMovInv') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Selección de Item -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Selección de Item</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Insumo -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Insumo</label>
                    <select wire:model="idInsMovInv" wire:change="updatedIdInsMovInv"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Seleccione un insumo</option>
                        @foreach($insumos as $insumo)
                            <option value="{{ $insumo->idIns }}">
                                {{ $insumo->nomIns }} (Stock: {{ $insumo->canIns }})
                            </option>
                        @endforeach
                    </select>
                    @error('idInsMovInv') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    
                    @if($idInsMovInv)
                        <div class="mt-2 text-sm text-blue-600">
                            Stock actual: {{ $this->getStockActual('insumo', $idInsMovInv) }} unidades
                        </div>
                    @endif
                </div>

                <!-- Herramienta -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Herramienta</label>
                    <select wire:model="idHerMovInv" wire:change="updatedIdHerMovInv"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Seleccione una herramienta</option>
                        @foreach($herramientas as $herramienta)
                            <option value="{{ $herramienta->idHer }}">
                                {{ $herramienta->nomHer }} (Stock: {{ $herramienta->canHer }})
                            </option>
                        @endforeach
                    </select>
                    @error('idHerMovInv') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    
                    @if($idHerMovInv)
                        <div class="mt-2 text-sm text-blue-600">
                            Stock actual: {{ $this->getStockActual('herramienta', $idHerMovInv) }} unidades
                        </div>
                    @endif
                </div>
            </div>

            @error('general') 
                <div class="mt-3 text-red-500 text-sm">{{ $message }}</div>
            @enderror

            <div class="mt-3 text-sm text-gray-600">
                Debe seleccionar exactamente un insumo O una herramienta, no ambos.
            </div>
        </div>

        <!-- Proveedor -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Proveedor (Opcional)</h2>

            <!-- Buscar Proveedor -->
            <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar Proveedor</label>
                <div class="flex gap-2">
                    <div class="flex-1 relative">
                        <input type="text" wire:model.live="busquedaProveedor" 
                               placeholder="Buscar por nombre o NIT..."
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        
                        @if($mostrarListaProveedores && count($proveedoresFiltrados) > 0)
                            <div class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg mt-1 max-h-48 overflow-y-auto shadow-lg">
                                @foreach($proveedoresFiltrados as $proveedor)
                                    <button type="button" 
                                            wire:click="seleccionarProveedor({{ $proveedor->idProve }})"
                                            class="w-full text-left px-4 py-2 hover:bg-gray-100 border-b border-gray-100 last:border-b-0">
                                        <div class="font-medium">{{ $proveedor->nomProve }}</div>
                                        <div class="text-sm text-gray-500">NIT: {{ $proveedor->nitProve }}</div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    
                    @if($proveedorSeleccionado)
                        <button type="button" wire:click="limpiarProveedor"
                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg transition-colors">
                            ✕
                        </button>
                    @endif
                    
                    <button type="button" wire:click="abrirCrearProveedor"
                            class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-lg transition-colors">
                        + Nuevo
                    </button>
                </div>
                @error('idProve') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Proveedor Seleccionado -->
            @if($proveedorSeleccionado)
                <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-medium text-blue-900 mb-2">Proveedor Seleccionado</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
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

        <!-- Observaciones -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Observaciones</h2>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Notas adicionales</label>
                <textarea wire:model="obsMovInv" rows="3" 
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Ingrese cualquier observación relevante..."></textarea>
                @error('obsMovInv') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Botones -->
        <div class="flex justify-end gap-3">
            <a href="/inventario/movimientos" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors">
                Cancelar
            </a>
            <button type="submit" 
                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors">
                Registrar Movimiento
            </button>
        </div>
    </form>

    <!-- Modal Crear Proveedor -->
    @if($mostrarCrearProveedor)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Crear Nuevo Proveedor</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                        <input type="text" wire:model="nuevoProveedor.nomProve" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('nuevoProveedor.nomProve') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NIT *</label>
                        <input type="text" wire:model="nuevoProveedor.nitProve" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('nuevoProveedor.nitProve') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                        <input type="text" wire:model="nuevoProveedor.telProve" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('nuevoProveedor.telProve') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                        <input type="text" wire:model="nuevoProveedor.dirProve" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('nuevoProveedor.dirProve') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" wire:model="nuevoProveedor.emaProve" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('nuevoProveedor.emaProve') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" wire:click="cerrarCrearProveedor"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button type="button" wire:click="crearProveedor"
                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors">
                        Crear Proveedor
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>