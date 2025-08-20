<?php
use App\Models\Inventario;
use App\Models\Insumo;
use App\Models\Herramienta;
use App\Models\Proveedor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;

    // Propiedades para listado y filtros
    public string $search = '';
    public string $tipo_movimiento = '';
    public string $tipo_item = '';
    public string $proveedor_id = ''; // Nuevo filtro por proveedor
    public string $fecha_inicio = '';
    public string $fecha_fin = '';
    public int $perPage = 15;
    
    // Propiedades para creación/edición
    public $showFormModal = false;
    public $editingId = null;
    public $form = [
        'tipo_item' => 'insumo',
        'idIns' => null,
        'idHer' => null,
        'tipMovInv' => '',
        'cantMovInv' => '',
        'uniMovInv' => '',
        'costoUnitInv' => null,
        'fecMovInv' => '',
        'loteInv' => null,
        'fecVenceInv' => null,
        'idProve' => null,
        'obsInv' => ''
    ];
    
    // Propiedades para eliminación
    public $showDeleteModal = false;
    public $movimientoToDelete = null;
    
    // Estadísticas
    public $estadisticas = [];
    
    // Opciones para selects
    public $tiposMovimiento = [
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
    
    public $insumos = [];
    public $herramientas = [];
    public $proveedores = [];

    public function mount(): void
    {
        $this->insumos = Insumo::orderBy('nomIns')->get();
        $this->herramientas = Herramienta::orderBy('nomHer')->get();
        $this->proveedores = Proveedor::orderBy('nomProve')->get();
        $this->calcularEstadisticas();
    }

    public function with(): array
    {
        return [
            'movimientos' => $this->getMovimientos(),
            'estadisticas' => $this->estadisticas,
            'proveedores' => $this->proveedores
        ];
    }

    public function getMovimientos()
    {
        $query = Inventario::with(['insumo', 'herramienta', 'proveedor', 'usuario'])
            ->orderBy('fecMovInv', 'desc');

        // Filtro por búsqueda general
        if ($this->search) {
            $query->where(function($q) {
                $q->whereHas('insumo', function($subq) {
                    $subq->where('nomIns', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('herramienta', function($subq) {
                    $subq->where('nomHer', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('proveedor', function($subq) {
                    $subq->where('nomProve', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('usuario', function($subq) {
                    $subq->where('nomUsu', 'like', '%'.$this->search.'%')
                          ->orWhere('apeUsu', 'like', '%'.$this->search.'%');
                })
                ->orWhere('obsInv', 'like', '%'.$this->search.'%')
                ->orWhere('loteInv', 'like', '%'.$this->search.'%');
            });
        }

        // Filtro por tipo de movimiento
        if ($this->tipo_movimiento) {
            $query->where('tipMovInv', $this->tipo_movimiento);
        }

        // Filtro por proveedor
        if ($this->proveedor_id) {
            $query->where('idProve', $this->proveedor_id);
        }

        // Filtro por tipo de item
        if ($this->tipo_item === 'herramientas') {
            $query->whereNotNull('idHer');
        } elseif ($this->tipo_item === 'insumos') {
            $query->whereNotNull('idIns');
        }

        // Filtro por fechas
        if ($this->fecha_inicio) {
            $query->whereDate('fecMovInv', '>=', $this->fecha_inicio);
        }
        if ($this->fecha_fin) {
            $query->whereDate('fecMovInv', '<=', $this->fecha_fin);
        }

        return $query->paginate($this->perPage);
    }

    public function calcularEstadisticas(): void
    {
        $this->estadisticas = [
            'total_movimientos' => Inventario::count(),
            'total_entradas' => Inventario::where('tipMovInv', 'entrada')->count(),
            'total_salidas' => Inventario::where('tipMovInv', 'salida')->count(),
            'total_consumos' => Inventario::where('tipMovInv', 'consumo')->count(),
            'movimientos_hoy' => Inventario::whereDate('fecMovInv', today())->count(),
            'valor_total_movimientos' => Inventario::sum('costoTotInv') ?? 0,
            'movimientos_con_proveedor' => Inventario::whereNotNull('idProve')->count(),
            'proveedores_activos' => Inventario::whereNotNull('idProve')->distinct('idProve')->count()
        ];
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->tipo_movimiento = '';
        $this->tipo_item = '';
        $this->proveedor_id = '';
        $this->fecha_inicio = '';
        $this->fecha_fin = '';
        $this->resetPage();
        $this->calcularEstadisticas();
    }

    // Métodos para resetear paginación al filtrar
    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedTipoMovimiento(): void { $this->resetPage(); }
    public function updatedTipoItem(): void { $this->resetPage(); }
    public function updatedProveedorId(): void { $this->resetPage(); }
    public function updatedFechaInicio(): void { $this->resetPage(); }
    public function updatedFechaFin(): void { $this->resetPage(); }

    // Métodos para CRUD
    public function create(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function edit($id): void
    {
        $this->editingId = $id;
        $movimiento = Inventario::findOrFail($id);
        
        $this->form = [
            'tipo_item' => $movimiento->idIns ? 'insumo' : 'herramienta',
            'idIns' => $movimiento->idIns,
            'idHer' => $movimiento->idHer,
            'tipMovInv' => $movimiento->tipMovInv,
            'cantMovInv' => $movimiento->cantMovInv,
            'uniMovInv' => $movimiento->uniMovInv,
            'costoUnitInv' => $movimiento->costoUnitInv,
            'fecMovInv' => $movimiento->fecMovInv->format('Y-m-d'),
            'loteInv' => $movimiento->loteInv,
            'fecVenceInv' => $movimiento->fecVenceInv?->format('Y-m-d'),
            'idProve' => $movimiento->idProve,
            'obsInv' => $movimiento->obsInv
        ];
        
        $this->showFormModal = true;
    }

    public function save(): void
    {
        $rules = [
            'form.tipo_item' => 'required|in:insumo,herramienta',
            'form.tipMovInv' => 'required|in:apertura,entrada,salida,consumo,prestamo_salida,prestamo_retorno,perdida,ajuste_pos,ajuste_neg,mantenimiento,venta',
            'form.cantMovInv' => 'required|numeric|min:0.01',
            'form.uniMovInv' => 'required|string|max:50',
            'form.costoUnitInv' => 'nullable|numeric|min:0',
            'form.fecMovInv' => 'required|date',
            'form.loteInv' => 'nullable|string|max:100',
            'form.fecVenceInv' => 'nullable|date|after:today',
            'form.idProve' => 'nullable|exists:proveedores,idProve',
            'form.obsInv' => 'nullable|string'
        ];

        if ($this->form['tipo_item'] === 'insumo') {
            $rules['form.idIns'] = 'required|exists:insumos,idIns';
        } else {
            $rules['form.idHer'] = 'required|exists:herramientas,idHer';
        }

        $this->validate($rules);
        
        $data = $this->form;
        $data['idUsuReg'] = Auth::id();
        
        // Calcular costo total si se proporciona costo unitario
        if ($data['costoUnitInv']) {
            $data['costoTotInv'] = $data['cantMovInv'] * $data['costoUnitInv'];
        }

        // Limpiar IDs no utilizados
        if ($data['tipo_item'] === 'insumo') {
            $data['idHer'] = null;
        } else {
            $data['idIns'] = null;
        }
        
        unset($data['tipo_item']); // No es un campo de la tabla

        if ($this->editingId) {
            $movimiento = Inventario::findOrFail($this->editingId);
            $movimiento->update($data);
            $message = 'Movimiento actualizado correctamente';
        } else {
            Inventario::create($data);
            $message = 'Movimiento creado correctamente';
        }

        $this->showFormModal = false;
        $this->calcularEstadisticas();
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $message
        ]);
    }

    public function show($id): void
    {
        $this->redirect(route('inventario.movimientos.show', $id));
    }

    public function confirmDelete($id): void
    {
        $this->movimientoToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteMovimiento(): void
    {
        try {
            $movimiento = Inventario::findOrFail($this->movimientoToDelete);
            $movimiento->delete();
            
            $this->showDeleteModal = false;
            $this->calcularEstadisticas();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Movimiento eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar el movimiento: ' . $e->getMessage()
            ]);
        } finally {
            $this->movimientoToDelete = null;
        }
    }

    public function resetForm(): void
    {
        $this->form = [
            'tipo_item' => 'insumo',
            'idIns' => null,
            'idHer' => null,
            'tipMovInv' => '',
            'cantMovInv' => '',
            'uniMovInv' => '',
            'costoUnitInv' => null,
            'fecMovInv' => now()->format('Y-m-d'),
            'loteInv' => null,
            'fecVenceInv' => null,
            'idProve' => null,
            'obsInv' => ''
        ];
        $this->editingId = null;
    }

    public function duplicarMovimiento($id): void
    {
        try {
            $movimiento = Inventario::findOrFail($id);
            
            $this->form = [
                'tipo_item' => $movimiento->idIns ? 'insumo' : 'herramienta',
                'idIns' => $movimiento->idIns,
                'idHer' => $movimiento->idHer,
                'tipMovInv' => $movimiento->tipMovInv,
                'cantMovInv' => $movimiento->cantMovInv,
                'uniMovInv' => $movimiento->uniMovInv,
                'costoUnitInv' => $movimiento->costoUnitInv,
                'fecMovInv' => now()->format('Y-m-d'),
                'loteInv' => $movimiento->loteInv,
                'fecVenceInv' => $movimiento->fecVenceInv?->format('Y-m-d'),
                'idProve' => $movimiento->idProve,
                'obsInv' => $movimiento->obsInv
            ];
            
            $this->showFormModal = true;
            $this->editingId = null;
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al duplicar movimiento: ' . $e->getMessage()
            ]);
        }
    }
}; ?>

@section('title', 'Gestión de movimientos')

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2 2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Movimientos de Inventario
                    </h1>
                    <p class="mt-2 text-gray-600">Gestiona todos los movimientos de inventario con proveedores</p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <button onclick="exportarMovimientos()" 
                            class="cursor-pointer inline-flex items-center px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Exportar
                    </button>
                    <a href="{{ route('inventario.movimientos.create') }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Nuevo Movimiento
                    </a>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-7">
                    <!-- Búsqueda -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                        <input type="text" wire:model.live.debounce.500ms="search" id="search" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Item, proveedor, usuario...">
                    </div>

                    <!-- Filtro por Proveedor -->
                    <div>
                        <label for="proveedor_id" class="block text-sm font-medium text-gray-700 mb-1">Proveedor</label>
                        <select wire:model.live="proveedor_id" id="proveedor_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todos los proveedores</option>
                            @foreach($proveedores as $proveedor)
                                <option value="{{ $proveedor->idProve }}">{{ $proveedor->nomProve }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tipo de Movimiento -->
                    <div>
                        <label for="tipo_movimiento" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                        <select wire:model.live="tipo_movimiento" id="tipo_movimiento" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todos</option>
                            <option value="entrada">Entrada</option>
                            <option value="salida">Salida</option>
                            <option value="consumo">Consumo</option>
                            <option value="ajuste_pos">Ajuste +</option>
                            <option value="ajuste_neg">Ajuste -</option>
                            <option value="perdida">Pérdida</option>
                            <option value="venta">Venta</option>
                        </select>
                    </div>

                    <!-- Tipo de Item -->
                    <div>
                        <label for="tipo_item" class="block text-sm font-medium text-gray-700 mb-1">Item</label>
                        <select wire:model.live="tipo_item" id="tipo_item" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todos</option>
                            <option value="insumos">Insumos</option>
                            <option value="herramientas">Herramientas</option>
                        </select>
                    </div>

                    <!-- Fecha Inicio -->
                    <div>
                        <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                        <input type="date" wire:model.live="fecha_inicio" id="fecha_inicio" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Fecha Fin -->
                    <div>
                        <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                        <input type="date" wire:model.live="fecha_fin" id="fecha_fin" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Botón Limpiar -->
                    <div class="flex items-end">
                        <button wire:click="clearFilters" 
                                class="cursor-pointer w-full px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out">
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="grid grid-cols-2 md:grid-cols-8 gap-4 mb-6">
            <div class="bg-white border border-blue-200 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $estadisticas['total_movimientos'] }}</div>
                <div class="text-sm text-gray-600">Total</div>
            </div>
            <div class="bg-white border border-green-200 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-green-600">{{ $estadisticas['total_entradas'] }}</div>
                <div class="text-sm text-gray-600">Entradas</div>
            </div>
            <div class="bg-white border border-red-200 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-red-600">{{ $estadisticas['total_salidas'] }}</div>
                <div class="text-sm text-gray-600">Salidas</div>
            </div>
            <div class="bg-white border border-yellow-200 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-yellow-600">{{ $estadisticas['total_consumos'] }}</div>
                <div class="text-sm text-gray-600">Consumos</div>
            </div>
            <div class="bg-white border border-purple-200 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-purple-600">{{ $estadisticas['movimientos_hoy'] }}</div>
                <div class="text-sm text-gray-600">Hoy</div>
            </div>
            <div class="bg-white border border-indigo-200 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-indigo-600">${{ number_format($estadisticas['valor_total_movimientos'], 0, ',', '.') }}</div>
                <div class="text-sm text-gray-600">Valor Total</div>
            </div>
            <div class="bg-white border border-teal-200 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-teal-600">{{ $estadisticas['movimientos_con_proveedor'] }}</div>
                <div class="text-sm text-gray-600">Con Proveedor</div>
            </div>
            <div class="bg-white border border-pink-200 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-pink-600">{{ $estadisticas['proveedores_activos'] }}</div>
                <div class="text-sm text-gray-600">Proveedores</div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-blue-600 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                        Registro de Movimientos
                    </h3>
                    <span class="bg-blue-100 text-blue-800 text-sm font-medium px-2.5 py-0.5 rounded">
                        {{ $movimientos->total() }} movimientos
                    </span>
                </div>
            </div>

            @if($movimientos->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha/Hora</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proveedor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Costo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($movimientos as $movimiento)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $movimiento->idInv }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $movimiento->fecMovInv->format('d/m/Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $movimiento->fecMovInv->format('H:i:s') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $tipoInfo = match($movimiento->tipMovInv) {
                                            'entrada' => ['bg-green-100 text-green-800', 'M12 6v6m0 0v6m0-6h6m-6 0H6'],
                                            'salida' => ['bg-red-100 text-red-800', 'M20 12H4'],
                                            'consumo' => ['bg-orange-100 text-orange-800', 'M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4'],
                                            'ajuste_pos' => ['bg-blue-100 text-blue-800', 'M12 6v6m0 0v6m0-6h6m-6 0H6'],
                                            'ajuste_neg' => ['bg-yellow-100 text-yellow-800', 'M20 12H4'],
                                            'perdida' => ['bg-gray-100 text-gray-800', 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z'],
                                            'venta' => ['bg-indigo-100 text-indigo-800', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2'],
                                            'apertura' => ['bg-purple-100 text-purple-800', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                            default => ['bg-gray-100 text-gray-800', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z']
                                        };
                                    @endphp
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div class="h-8 w-8 rounded-full {{ $tipoInfo[0] }} flex items-center justify-center">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tipoInfo[1] }}"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $tipoInfo[0] }}">
                                                {{ ucfirst(str_replace('_', ' ', $movimiento->tipMovInv)) }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @php
                                                $iconoItem = $movimiento->idHer 
                                                    ? ['bg-blue-100 text-blue-600', 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z']
                                                    : ['bg-green-100 text-green-600', 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'];
                                            @endphp
                                            <div class="h-10 w-10 rounded-full {{ $iconoItem[0] }} flex items-center justify-center">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconoItem[1] }}"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                @if($movimiento->idHer)
                                                    {{ $movimiento->herramienta->nomHer ?? 'Herramienta eliminada' }}
                                                @else
                                                    {{ $movimiento->insumo->nomIns ?? 'Insumo eliminado' }}
                                                @endif
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $movimiento->idHer ? 'Herramienta' : 'Insumo' }}
                                                @if($movimiento->loteInv)
                                                    • Lote: {{ $movimiento->loteInv }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Columna Proveedor -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($movimiento->proveedor)
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ $movimiento->proveedor->nomProve }}</div>
                                                <div class="text-xs text-gray-500">{{ $movimiento->proveedor->nitProve }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex items-center text-gray-400">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                            </svg>
                                            <span class="text-sm">Sin proveedor</span>
                                        </div>
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <div class="font-medium">{{ number_format($movimiento->cantMovInv, 2) }} {{ $movimiento->uniMovInv }}</div>
                                        @if($movimiento->fecVenceInv)
                                            <div class="text-xs text-gray-500">Vence: {{ $movimiento->fecVenceInv->format('d/m/Y') }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($movimiento->costoTotInv)
                                        <div class="text-center">
                                            <div class="text-green-600 font-medium">
                                                ${{ number_format($movimiento->costoTotInv, 0, ',', '.') }}
                                            </div>
                                            @if($movimiento->costoUnitInv)
                                                <div class="text-xs text-gray-500">
                                                    ${{ number_format($movimiento->costoUnitInv, 2, ',', '.') }} c/u
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-xs">Sin costo</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($movimiento->usuario)
                                        <div class="flex items-center">
                                            <div class="text-sm font-medium text-gray-900">{{ $movimiento->usuario->nomUsu }} {{ $movimiento->usuario->apeUsu }}</div>
                                        </div>
                                    @else
                                        <span class="text-gray-400">Sistema</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex items-center justify-center space-x-2">
                                        <!-- Ver -->
                                        <a href="{{ route('inventario.movimientos.show', $movimiento->idInv) }}" wire:navigate
                                           class="text-indigo-600 hover:text-indigo-900 p-1" title="Ver detalles">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        
                                        <!-- Editar -->
                                        <a href="{{ route('inventario.movimientos.edit', $movimiento->idInv) }}" wire:navigate
                                           class="text-blue-600 hover:text-blue-900 p-1" title="Editar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                        
                                        <!-- Eliminar -->
                                        <button wire:click="confirmDelete({{ $movimiento->idInv }})" 
                                                class="cursor-pointer text-red-600 hover:text-red-900 p-1" title="Eliminar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>

                                        <!-- Duplicar -->
                                        <button wire:click="duplicarMovimiento({{ $movimiento->idInv }})" 
                                                class="cursor-pointer text-green-600 hover:text-green-900 p-1" title="Duplicar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No hay movimientos registrados</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        @if($proveedor_id || $tipo_movimiento || $tipo_item || $search)
                            No se encontraron movimientos con los filtros aplicados.
                        @else
                            Comienza registrando el primer movimiento de inventario.
                        @endif
                    </p>
                    <div class="mt-6">
                        @if($proveedor_id || $tipo_movimiento || $tipo_item || $search)
                            <button wire:click="clearFilters"
                                   class="cursor-pointer inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Limpiar Filtros
                            </button>
                        @else
                            <a href="{{ route('inventario.movimientos.create') }}" wire:navigate
                               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Registrar Primer Movimiento
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            @if($movimientos->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            {{ $movimientos->links() }}
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Mostrando <span class="font-medium">{{ $movimientos->firstItem() }}</span> a 
                                    <span class="font-medium">{{ $movimientos->lastItem() }}</span> de 
                                    <span class="font-medium">{{ $movimientos->total() }}</span> movimientos
                                </p>
                            </div>
                            <div>
                                {{ $movimientos->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Eliminar Movimiento -->
@if($showDeleteModal)
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg max-w-md w-full mx-4">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="ml-3 text-lg font-medium text-gray-900">Confirmar eliminación</h3>
            </div>
            <p class="mb-4 text-sm text-gray-600">
                ¿Está seguro que desea eliminar este movimiento de inventario? Esta acción afectará el stock actual y no se puede deshacer.
            </p>
            
            @if($movimientoToDelete)
                @php
                    $movimiento = \App\Models\Inventario::find($movimientoToDelete);
                @endphp
                @if($movimiento)
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                        <p class="text-sm"><strong>Tipo:</strong> {{ ucfirst(str_replace('_', ' ', $movimiento->tipMovInv)) }}</p>
                        <p class="text-sm"><strong>Cantidad:</strong> {{ $movimiento->cantMovInv }} {{ $movimiento->uniMovInv }}</p>
                        @if($movimiento->proveedor)
                            <p class="text-sm"><strong>Proveedor:</strong> {{ $movimiento->proveedor->nomProve }}</p>
                        @endif
                    </div>
                @endif
            @endif
            
            <div class="flex justify-end space-x-3">
                <button wire:click="$set('showDeleteModal', false)"
                        class="cursor-pointer px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg transition">
                    Cancelar
                </button>
                <button wire:click="deleteMovimiento"
                        class="cursor-pointer px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                    Eliminar
                </button>
            </div>
        </div>
    </div>
@endif

<script>
function exportarMovimientos() {
    // Función para exportar movimientos
    alert('Función de exportación - Por implementar');
}

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