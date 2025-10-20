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

    public string $search = '';
    public string $tipo_movimiento = '';
    public string $tipo_item = '';
    public string $proveedor_id = '';
    public string $fecha_inicio = '';
    public string $fecha_fin = '';
    public int $perPage = 10; // Reduced to match insumos-index
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
    public $showDeleteModal = false;
    public $movimientoToDelete = null;
    public $estadisticas = [];
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

        if ($this->tipo_movimiento) {
            $query->where('tipMovInv', $this->tipo_movimiento);
        }

        if ($this->proveedor_id) {
            $query->where('idProve', $this->proveedor_id);
        }

        if ($this->tipo_item === 'herramientas') {
            $query->whereNotNull('idHer');
        } elseif ($this->tipo_item === 'insumos') {
            $query->whereNotNull('idIns');
        }

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
        $this->reset(['search', 'tipo_movimiento', 'tipo_item', 'proveedor_id', 'fecha_inicio', 'fecha_fin']);
        $this->resetPage();
        $this->calcularEstadisticas();
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'tipo_movimiento', 'tipo_item', 'proveedor_id', 'fecha_inicio', 'fecha_fin'])) {
            $this->resetPage();
        }
    }

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
        
        if ($data['costoUnitInv']) {
            $data['costoTotInv'] = $data['cantMovInv'] * $data['costoUnitInv'];
        }

        if ($data['tipo_item'] === 'insumo') {
            $data['idHer'] = null;
        } else {
            $data['idIns'] = null;
        }
        
        unset($data['tipo_item']);

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

@section('title', 'Gestión de Movimientos')

<div class="min-h-screen py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-4">
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl border border-white/20 p-4 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-blue-600/5"></div>
                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center space-x-2 mb-3 lg:mb-0">
                        <div class="p-2 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg transform rotate-3 hover:rotate-0 transition-transform duration-300">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-black bg-gradient-to-r from-gray-900 via-gray-800 to-blue-800 bg-clip-text text-transparent leading-tight">
                                Gestión de Movimientos
                            </h1>
                            <p class="text-gray-600 text-xs">Administra los movimientos de inventario y proveedores</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('inventario.movimientos.create') }}" wire:navigate
                           class="group relative inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-green-600 to-green-600 hover:from-green-700 hover:to-green-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                            <svg class="w-3.5 h-3.5 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span class="relative z-10 text-xs">Nuevo Movimiento</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-2 mb-3">
            <div class="bg-white border border-blue-200 rounded-lg p-2 text-center">
                <div class="text-sm font-bold text-blue-600">{{ $estadisticas['total_movimientos'] }}</div>
                <div class="text-xs text-gray-600">Total</div>
            </div>
            <div class="bg-white border border-green-200 rounded-lg p-2 text-center">
                <div class="text-sm font-bold text-green-600">{{ $estadisticas['total_entradas'] }}</div>
                <div class="text-xs text-gray-600">Entradas</div>
            </div>
            <div class="bg-white border border-red-200 rounded-lg p-2 text-center">
                <div class="text-sm font-bold text-red-600">{{ $estadisticas['total_salidas'] }}</div>
                <div class="text-xs text-gray-600">Salidas</div>
            </div>
            <div class="bg-white border border-yellow-200 rounded-lg p-2 text-center">
                <div class="text-sm font-bold text-yellow-600">{{ $estadisticas['total_consumos'] }}</div>
                <div class="text-xs text-gray-600">Consumos</div>
            </div>
            <div class="bg-white border border-purple-200 rounded-lg p-2 text-center">
                <div class="text-sm font-bold text-purple-600">{{ $estadisticas['movimientos_hoy'] }}</div>
                <div class="text-xs text-gray-600">Hoy</div>
            </div>
            <div class="bg-white border border-indigo-200 rounded-lg p-2 text-center">
                <div class="text-sm font-bold text-indigo-600">${{ number_format($estadisticas['valor_total_movimientos'], 0, ',', '.') }}</div>
                <div class="text-xs text-gray-600">Valor Total</div>
            </div>
            <div class="bg-white border border-teal-200 rounded-lg p-2 text-center">
                <div class="text-sm font-bold text-teal-600">{{ $estadisticas['movimientos_con_proveedor'] }}</div>
                <div class="text-xs text-gray-600">Con Proveedor</div>
            </div>
            <div class="bg-white border border-pink-200 rounded-lg p-2 text-center">
                <div class="text-sm font-bold text-pink-600">{{ $estadisticas['proveedores_activos'] }}</div>
                <div class="text-xs text-gray-600">Proveedores</div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-3 mb-3">
            <form wire:submit.prevent>
                <div class="flex flex-col sm:flex-row gap-2">
                    <div class="flex-1 relative">
                        <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input type="text"
                               wire:model.live.debounce.500ms="search"
                               placeholder="Buscar por item, proveedor, usuario..."
                               class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="relative">
                        <select wire:model.live="proveedor_id" class="w-full sm:w-32 px-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todos los proveedores</option>
                            @foreach($proveedores as $proveedor)
                                <option value="{{ $proveedor->idProve }}">{{ $proveedor->nomProve }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="relative">
                        <select wire:model.live="tipo_movimiento" class="w-full sm:w-32 px-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todos los tipos</option>
                            @foreach($tiposMovimiento as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="relative">
                        <select wire:model.live="tipo_item" class="w-full sm:w-28 px-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todos</option>
                            <option value="insumos">Insumos</option>
                            <option value="herramientas">Herramientas</option>
                        </select>
                    </div>
                    <div class="relative">
                        <input type="date" wire:model.live="fecha_inicio" 
                               class="w-full sm:w-32 px-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="relative">
                        <input type="date" wire:model.live="fecha_fin" 
                               class="w-full sm:w-32 px-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    @if($search || $tipo_movimiento || $tipo_item || $proveedor_id || $fecha_inicio || $fecha_fin)
                    <button wire:click="clearFilters"
                            class="cursor-pointer inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-medium rounded-lg shadow hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span class="text-xs">Limpiar</span>
                    </button>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tabla -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 overflow-hidden">
            @if($movimientos->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-black">
                        <tr>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">#</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Fecha</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Tipo</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Item</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Proveedor</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Cantidad</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Costo</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Usuario</th>
                            <th class="px-2 py-1.5 text-center text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($movimientos as $index => $movimiento)
                        <tr class="hover:bg-gray-50/50 transition-colors duration-200">
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs font-medium text-gray-900">
                                {{ ($movimientos->currentPage() - 1) * $movimientos->perPage() + $index + 1 }}
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                                <div class="text-xs font-medium text-gray-900">{{ $movimiento->fecMovInv->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap">
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
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium {{ $tipoInfo[0] }}">
                                    {{ ucfirst(str_replace('_', ' ', $movimiento->tipMovInv)) }}
                                </span>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    @php
                                        $iconoItem = $movimiento->idHer 
                                            ? ['bg-blue-100 text-blue-600', 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z']
                                            : ['bg-green-100 text-green-600', 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'];
                                    @endphp
                                    <div class="min-w-0">
                                        <p class="text-xs font-medium text-gray-900">
                                            @if($movimiento->idHer)
                                                {{ $movimiento->herramienta->nomHer ?? 'Herramienta eliminada' }}
                                            @else
                                                {{ $movimiento->insumo->nomIns ?? 'Insumo eliminado' }}
                                            @endif
                                            (
                                            {{ $movimiento->idHer ? 'Herramienta' : 'Insumo' }}
                                            @if($movimiento->loteInv)
                                                • Lote: {{ $movimiento->loteInv }}
                                            @endif
                                            )
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                                <div class="flex items-center gap-1">
                                    @if($movimiento->proveedor)
                                        <div class="min-w-0">
                                            <p class="text-xs font-medium text-gray-900">{{ $movimiento->proveedor->nomProve }}</p>
                                        </div>
                                    @else
                                        <p class="text-xs text-gray-400">Sin proveedor</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                                <div class="flex items-center gap-1">
                                    <p class="text-xs font-medium text-gray-900">{{ number_format($movimiento->cantMovInv, 2) }} {{ $movimiento->uniMovInv }}</p>
                                    @if($movimiento->fecVenceInv)
                                        <p class="text-xs text-gray-500">Vence: {{ $movimiento->fecVenceInv->format('d/m/Y') }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                                @if($movimiento->costoTotInv)
                                    <p class="text-xs font-medium text-green-600">${{ number_format($movimiento->costoTotInv, 0, ',', '.') }}</p>
                                @else
                                    <p class="text-xs text-gray-400">Sin costo</p>
                                @endif
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                                @if($movimiento->usuario)
                                    <p class="text-xs font-medium text-gray-900">{{ $movimiento->usuario->nomUsu }} {{ $movimiento->usuario->apeUsu }}</p>
                                @else
                                    <p class="text-xs text-gray-400">Sistema</p>
                                @endif
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('inventario.movimientos.show', $movimiento->idInv) }}" wire:navigate
                                       class="bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs font-medium py-1 px-1.5 rounded transition duration-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('inventario.movimientos.edit', $movimiento->idInv) }}" wire:navigate
                                       class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 text-xs font-medium py-1 px-1.5 rounded transition duration-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <button wire:click="confirmDelete({{ $movimiento->idInv }})"
                                            class="cursor-pointer bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium py-1 px-1.5 rounded transition duration-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($movimientos->hasPages())
            <div class="bg-white px-2 py-1.5 border-t border-gray-200 sm:px-3">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        {{ $movimientos->links() }}
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs text-gray-700">
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
            @else
            <div class="bg-white border border-gray-200 rounded-b-lg shadow-sm">
                <div class="text-center py-4 px-4">
                    <div class="w-8 h-8 bg-gray-100 rounded-full mx-auto mb-2 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 mb-1">
                        No hay movimientos registrados
                    </h3>
                    <p class="text-xs text-gray-600 mb-2">
                        @if($proveedor_id || $tipo_movimiento || $tipo_item || $search || $fecha_inicio || $fecha_fin)
                            No se encontraron movimientos con los filtros aplicados.
                        @else
                            Comienza registrando el primer movimiento de inventario.
                        @endif
                    </p>
                    @if($proveedor_id || $tipo_movimiento || $tipo_item || $search || $fecha_inicio || $fecha_fin)
                    <a href="{{ route('inventario.movimientos.create') }}" wire:navigate
                       class="cursor-pointer inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-green-600 to-green-800 hover:from-green-700 hover:to-green-900 text-white font-medium rounded-lg shadow hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span class="text-xs">Registrar Movimiento</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Modal Eliminar Movimiento -->
        @if($showDeleteModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50" wire:click.self="$set('showDeleteModal', false)">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-5 transform transition-all duration-300">
                <div class="text-center">
                    <div class="p-2.5 bg-gradient-to-br from-red-500 to-pink-600 rounded-full w-14 h-14 mx-auto mb-3 flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 mb-1.5">Confirmar Eliminación</h3>
                    <p class="text-xs text-gray-600 mb-3">¿Está seguro que desea eliminar este movimiento? Esta acción afectará el stock actual y no se puede deshacer.</p>
                    @if($movimientoToDelete)
                        @php
                            $movimiento = \App\Models\Inventario::find($movimientoToDelete);
                        @endphp
                        @if($movimiento)
                        <div class="bg-gray-50 rounded-lg p-2.5 mb-3 text-left">
                            <div class="space-y-1 text-xs">
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-700">Tipo:</span>
                                    <span class="text-gray-900">{{ ucfirst(str_replace('_', ' ', $movimiento->tipMovInv)) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-700">Cantidad:</span>
                                    <span class="text-gray-900">{{ $movimiento->cantMovInv }} {{ $movimiento->uniMovInv }}</span>
                                </div>
                                @if($movimiento->proveedor)
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-700">Proveedor:</span>
                                    <span class="text-gray-900">{{ $movimiento->proveedor->nomProve }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    @endif
                    <div class="flex space-x-2">
                        <button wire:click="$set('showDeleteModal', false)"
                                class="cursor-pointer flex-1 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg shadow hover:shadow transition-all duration-200 text-xs">
                            Cancelar
                        </button>
                        <button wire:click="deleteMovimiento"
                                class="cursor-pointer flex-1 px-3 py-1.5 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white font-medium rounded-lg shadow hover:shadow transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center text-xs">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
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
            });
        </script>