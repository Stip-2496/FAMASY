<?php
use App\Models\Inventario;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;

    public string $search = '';
    public ?string $tipo_movimiento = null;
    public ?string $tipo_item = null;
    public ?string $fecha_inicio = null;
    public ?string $fecha_fin = null;
    public int $perPage = 10;
    public $showDeleteModal = false;
    public $movimientoToDelete = null;

    public function mount(): void
    {
        $this->fecha_inicio = now()->subMonth()->format('Y-m-d');
        $this->fecha_fin = now()->format('Y-m-d');
    }

    public function with(): array
    {
        $query = Inventario::with(['insumo', 'herramienta', 'proveedor', 'usuario'])
            ->orderBy('fecMovInv', 'desc');

        // Aplicar filtros
        if ($this->search) {
            $query->where(function($q) {
                $q->whereHas('insumo', function($q) {
                    $q->where('nomIns', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('herramienta', function($q) {
                    $q->where('nomHer', 'like', '%'.$this->search.'%');
                })
                ->orWhere('loteInv', 'like', '%'.$this->search.'%')
                ->orWhere('obsInv', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->tipo_movimiento) {
            $query->where('tipMovInv', $this->tipo_movimiento);
        }

        if ($this->tipo_item === 'herramientas') {
            $query->whereNotNull('idHer');
        } elseif ($this->tipo_item === 'insumos') {
            $query->whereNotNull('idIns');
        }

        if ($this->fecha_inicio && $this->fecha_fin) {
            $query->whereBetween('fecMovInv', [
                $this->fecha_inicio, 
                $this->fecha_fin
            ]);
        }

        $movimientos = $query->paginate($this->perPage);

        // Calcular estadísticas
        $estadisticas = [
            'total_movimientos' => Inventario::count(),
            'total_entradas' => Inventario::where('tipMovInv', 'entrada')->count(),
            'total_salidas' => Inventario::where('tipMovInv', 'salida')->count(),
            'total_consumos' => Inventario::where('tipMovInv', 'consumo')->count(),
            'movimientos_hoy' => Inventario::whereDate('fecMovInv', today())->count(),
            'valor_total_movimientos' => Inventario::sum('costoTotInv'),
        ];

        return [
            'movimientos' => $movimientos,
            'estadisticas' => $estadisticas,
        ];
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'tipo_movimiento', 'tipo_item', 'fecha_inicio', 'fecha_fin']);
        $this->resetPage();
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
}; ?>

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Movimientos de Inventario
                    </h1>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <button onclick="exportarMovimientos()" 
                            class="inline-flex items-center px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
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
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                        <input type="text" wire:model.live.debounce.500ms="search" id="search" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Item, usuario, observaciones...">
                    </div>
                    <div>
                        <label for="tipo_movimiento" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                        <select wire:model.live="tipo_movimiento" id="tipo_movimiento" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todos</option>
                            <option value="entrada">Entrada</option>
                            <option value="salida">Salida</option>
                            <option value="consumo">Consumo</option>
                            <option value="aplicacion">Aplicación</option>
                            <option value="compra">Compra</option>
                            <option value="venta">Venta</option>
                            <option value="donacion">Donación</option>
                            <option value="perdida">Pérdida</option>
                            <option value="vencimiento">Vencimiento</option>
                        </select>
                    </div>
                    <div>
                        <label for="tipo_item" class="block text-sm font-medium text-gray-700 mb-1">Item</label>
                        <select wire:model.live="tipo_item" id="tipo_item" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todos</option>
                            <option value="herramientas">Herramientas</option>
                            <option value="insumos">Insumos</option>
                        </select>
                    </div>
                    <div>
                        <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                        <input type="date" wire:model.live="fecha_inicio" id="fecha_inicio" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                        <input type="date" wire:model.live="fecha_fin" id="fecha_fin" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex items-end space-x-2">
                        <button wire:click="clearFilters" 
                                class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out">
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
            <div class="bg-white border border-blue-200 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $estadisticas['total_movimientos'] }}</div>
                <div class="text-sm text-gray-600">Total Movimientos</div>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Costo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($movimientos as $movimiento)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $movimiento->id }}</td>
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
                                            'aplicacion' => ['bg-blue-100 text-blue-800', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                            'compra' => ['bg-purple-100 text-purple-800', 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
                                            'venta' => ['bg-indigo-100 text-indigo-800', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2'],
                                            'donacion' => ['bg-pink-100 text-pink-800', 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
                                            'perdida' => ['bg-gray-100 text-gray-800', 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z'],
                                            'vencimiento' => ['bg-yellow-100 text-yellow-800', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
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
                                                {{ ucfirst($movimiento->tipMovInv) }}
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
                                                    {{ $movimiento->herramienta->nomHer ?? 'Herramienta' }}
                                                @else
                                                    {{ $movimiento->insumo->nomIns ?? 'Insumo' }}
                                                @endif
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $movimiento->idHer ? 'Herramienta' : 'Insumo' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <div class="font-medium">{{ $movimiento->cantMovInv }} {{ $movimiento->uniMovInv }}</div>
                                        @if($movimiento->loteInv)
                                            <div class="text-xs text-gray-500">Lote: {{ $movimiento->loteInv }}</div>
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
                                    {{ $movimiento->usuario->name ?? 'Sistema' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex items-center justify-center space-x-2">
                                        <!-- Ver -->
                                        <a href="{{ route('inventario.movimientos.show', $movimiento) }}" wire:navigate
                                           class="text-indigo-600 hover:text-indigo-900 p-1" title="Ver detalles">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        
                                        <!-- Editar -->
                                        <a href="{{ route('inventario.movimientos.edit', $movimiento) }}" wire:navigate
                                           class="text-blue-600 hover:text-blue-900 p-1" title="Editar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                        
                                        <!-- Eliminar -->
                                        <button wire:click="confirmDelete({{ $movimiento->id }})" 
                                                class="text-red-600 hover:text-red-900 p-1" title="Eliminar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                        
                                        <!-- Dropdown (opciones adicionales) -->
                                        <div class="relative inline-block text-left">
                                            <button type="button" onclick="toggleDropdown({{ $movimiento->id }})" 
                                                    class="text-gray-400 hover:text-gray-600 p-1" title="Más opciones">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                                </svg>
                                            </button>
                                            <div id="dropdown-{{ $movimiento->id }}" 
                                                 class="hidden absolute right-0 z-10 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                                <div class="py-1">
                                                    <a href="{{ route('inventario.movimientos.create') }}?tipo={{ $movimiento->tipMovInv }}&item={{ $movimiento->idHer ? 'herramienta' : 'insumo' }}&item_id={{ $movimiento->idHer ?? $movimiento->idIns }}" wire:navigate
                                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                        </svg>
                                                        Duplicar Movimiento
                                                    </a>
                                                    <a href="#" onclick="generarReporte({{ $movimiento->id }})" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                        Generar Reporte
                                                    </a>
                                                    <a href="#" onclick="verHistorial('{{ $movimiento->idHer ? 'herramienta' : 'insumo' }}', {{ $movimiento->idHer ?? $movimiento->idIns }})" 
                                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        Ver Historial del Item
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
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
                    <p class="mt-1 text-sm text-gray-500">Comienza registrando el primer movimiento de inventario.</p>
                    <div class="mt-6">
                        <a href="{{ route('inventario.movimientos.create') }}" wire:navigate
                           class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Registrar Primer Movimiento
                        </a>
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
    <div class="fixed inset-0 bg-black/20 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg max-w-sm w-full">
            <h2 class="text-xl font-semibold mb-4">Confirmar eliminación</h2>
            <p class="mb-4 text-sm text-gray-600">
                ¿Está seguro que desea eliminar este movimiento de inventario? Esta acción afectará el stock actual y no se puede deshacer.
            </p>
            
            <div class="flex justify-end gap-3">
                <button wire:click="$set('showDeleteModal', false)"
                        class="cursor-pointer px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">Cancelar</button>
                <button wire:click="deleteMovimiento"
                        class="cursor-pointer px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">Confirmar Eliminación</button>
            </div>
        </div>
    </div>
@endif

<script>
function toggleDropdown(id) {
    const dropdown = document.getElementById(`dropdown-${id}`);
    const allDropdowns = document.querySelectorAll('[id^="dropdown-"]');
    
    // Cerrar todos los otros dropdowns
    allDropdowns.forEach(dd => {
        if (dd.id !== `dropdown-${id}`) {
            dd.classList.add('hidden');
        }
    });
    
    // Toggle el dropdown actual
    dropdown.classList.toggle('hidden');
}

// Cerrar dropdowns al hacer click fuera
document.addEventListener('click', function(event) {
    const dropdowns = document.querySelectorAll('[id^="dropdown-"]');
    dropdowns.forEach(dropdown => {
        if (!dropdown.contains(event.target) && !event.target.closest('[onclick*="toggleDropdown"]')) {
            dropdown.classList.add('hidden');
        }
    });
});

function exportarMovimientos() {
    // Función para exportar movimientos
    alert('Función de exportación - Por implementar');
}

function generarReporte(movimientoId) {
    // Función para generar reporte específico
    alert(`Generando reporte para movimiento ${movimientoId}`);
}

function verHistorial(tipoItem, itemId) {
    // Función para ver historial completo del item
    const url = `/inventario/movimientos?tipo_item=${tipoItem}&item_id=${itemId}`;
    window.location.href = url;
}
</script>