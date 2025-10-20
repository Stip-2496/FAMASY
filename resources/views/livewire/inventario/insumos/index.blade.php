<?php
// resources/views/livewire/inventario/insumos/index.php

use App\Models\Insumo;
use App\Models\Proveedor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;

    public string $search = '';
    public ?string $tipo = null;
    public ?string $estado = null;
    public ?string $stock = null;
    public ?string $vencimiento = null;
    public ?string $proveedor = null;
    public int $perPage = 10;
    public bool $verPapelera = false;
    public bool $showDeleteModal = false;
    public $insumoToDelete = null;
    public bool $mostrarVencimientos = false;
    public $deleteError = null;

    public array $vencimientoStats = [
        'vencidos' => 0,
        'proximos_vencer' => 0,
    ];

    public function with(): array
    {
        $query = Insumo::query()->with('proveedor');

        $this->verPapelera
            ? $query->onlyTrashed()
            : $query->whereNull('deleted_at');

        // Filtro de búsqueda
        if ($this->search) {
            $query->where(function($q) {
                $q->where('nomIns', 'like', "%{$this->search}%")
                  ->orWhere('marIns', 'like', "%{$this->search}%")
                  ->orWhere('tipIns', 'like', "%{$this->search}%")
                  ->orWhereHas('proveedor', function($subQ) {
                      $subQ->where('nomProve', 'like', "%{$this->search}%");
                  });
            });
        }

        // Filtro por tipo
        if ($this->tipo) {
            $query->where('tipIns', $this->tipo);
        }

        // Filtro por estado
        if ($this->estado) {
            $query->where('estIns', $this->estado);
        }

        // Filtro por proveedor
        if ($this->proveedor) {
            $query->where('idProveIns', $this->proveedor);
        }

        // Filtro por stock
        if ($this->stock) {
            switch ($this->stock) {
                case 'critico':
                    $query->whereRaw('canIns <= stockMinIns')
                          ->whereNotNull('stockMinIns');
                    break;
                case 'bajo':
                    $query->whereRaw('canIns <= (stockMinIns * 1.2)')
                          ->whereNotNull('stockMinIns');
                    break;
                case 'normal':
                    $query->whereRaw('canIns > (stockMinIns * 1.2)')
                          ->whereNotNull('stockMinIns');
                    break;
            }
        }

        // Filtro por vencimiento
        if ($this->vencimiento) {
            switch ($this->vencimiento) {
                case 'vencido':
                    $query->where('fecVenIns', '<', now());
                    break;
                case 'urgente':
                    $query->whereBetween('fecVenIns', [now(), now()->addDays(7)]);
                    break;
                case 'critico':
                    $query->whereBetween('fecVenIns', [now(), now()->addDays(30)]);
                    break;
            }
        }

        $insumos = $query->orderBy('nomIns')->paginate($this->perPage);

        // Estadísticas
        $estadisticas = !$this->verPapelera ? [
            'total' => Insumo::whereNull('deleted_at')->count(),
            'disponibles' => Insumo::whereNull('deleted_at')->where('estIns', 'disponible')->count(),
            'stock_critico' => Insumo::whereNull('deleted_at')
                ->whereRaw('canIns <= stockMinIns')
                ->whereNotNull('stockMinIns')
                ->count(),
            'por_vencer' => Insumo::whereNull('deleted_at')
                ->whereBetween('fecVenIns', [now(), now()->addDays(30)])
                ->count(),
            'vencidos' => Insumo::whereNull('deleted_at')
                ->where('fecVenIns', '<', now())
                ->count(),
            'con_proveedor' => Insumo::whereNull('deleted_at')
                ->whereNotNull('idProveIns')
                ->count(),
        ] : [];

        // Próximos a vencer
        $proximosVencer = !$this->verPapelera
            ? Insumo::whereNull('deleted_at')
                ->with('proveedor')
                ->whereBetween('fecVenIns', [now(), now()->addDays(30)])
                ->orderBy('fecVenIns')
                ->get()
            : collect();

        // Lista de proveedores para el filtro
        $proveedores = Proveedor::orderBy('nomProve')->get();

        // Tipos únicos para el filtro
        $tipos = Insumo::whereNull('deleted_at')
            ->whereNotNull('tipIns')
            ->distinct()
            ->pluck('tipIns')
            ->sort();

        return [
            'insumos' => $insumos,
            'estadisticas' => $estadisticas,
            'proximosVencer' => $proximosVencer,
            'proveedores' => $proveedores,
            'tipos' => $tipos
        ];
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'tipo', 'estado', 'stock', 'vencimiento', 'proveedor'])) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'tipo', 'estado', 'stock', 'vencimiento', 'proveedor']);
        $this->resetPage();
    }

    public function verificarVencimientos(): void
    {
        $this->mostrarVencimientos = ! $this->mostrarVencimientos;
    }

    public function togglePapelera(): void
    {
        $this->verPapelera = ! $this->verPapelera;
        $this->resetPage();
    }

    public function confirmDelete($id): void
    {
        $this->insumoToDelete = $id;
        $this->deleteError = null;
        $this->showDeleteModal = true;
    }

    public function deleteInsumo(): void
    {
        try {
            $insumo = Insumo::findOrFail($this->insumoToDelete);

            if (!$insumo->puedeEliminar()) {
                $this->deleteError = 'No se puede eliminar: tiene movimientos de inventario asociados';
                return;
            }

            $insumo->delete();

            $this->showDeleteModal = false;
            $this->resetPage();
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Insumo eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            $this->deleteError = 'Error al eliminar: ' . $e->getMessage();
        }
    }

    public function restaurar($id): void
    {
        $insumo = Insumo::onlyTrashed()->findOrFail($id);
        $insumo->restore();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Insumo restaurado exitosamente.'
        ]);
    }

    public function eliminarPermanente($id): void
    {
        $insumo = Insumo::onlyTrashed()->findOrFail($id);

        if ($insumo->movimientosInventario()->count() > 0) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No se puede eliminar permanentemente porque tiene movimientos de inventario.'
            ]);
            return;
        }

        $insumo->forceDelete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Insumo eliminado permanentemente.'
        ]);
    }
};
?>

@section('title', 'Gestión de insumos')

<div class="min-h-screen py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-4">
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl border border-white/20 p-4 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-green-500/5 to-green-600/5"></div>
                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center space-x-2 mb-3 lg:mb-0">
                        <div class="p-2 bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg transform rotate-3 hover:rotate-0 transition-transform duration-300">
                            <div class="w-5 h-5 text-white flex items-center justify-center">
                                <i class="fas fa-boxes text-sm"></i>
                            </div>
                        </div>
                        <div>
                            <h1 class="text-xl font-black bg-gradient-to-r from-gray-900 via-gray-800 to-green-800 bg-clip-text text-transparent leading-tight">
                                Gestión de Insumos
                            </h1>
                            <p class="text-gray-600 text-xs">Administra tu inventario de insumos y sus proveedores</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button wire:click="togglePapelera"
                                class="cursor-pointer group relative inline-flex items-center px-3 py-1.5 bg-gradient-to-r {{ $verPapelera ? 'from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800' : 'from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700' }} text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                            <svg class="w-3.5 h-3.5 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            <span class="relative z-10 text-xs">{{ $verPapelera ? 'Volver' : 'Papelera' }}</span>
                        </button>
                        <a href="{{ route('inventario.insumos.create') }}" wire:navigate
                           class="group relative inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-green-600 hover:from-green-700 hover:to-green-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                            <svg class="w-4 h-4 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span class="relative z-10 text-xs">Nuevo Insumo</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertas de Vencimiento -->
        @if($proximosVencer->count() > 0 && !$verPapelera)
            <div class="mb-3 bg-yellow-50 border border-yellow-200 text-yellow-800 px-3 py-2 rounded-lg flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-xs"><strong>¡Atención!</strong> Tienes {{ $proximosVencer->count() }} insumos próximos a vencer en los próximos 30 días.</span>
                </div>
                <button wire:click="verificarVencimientos" class="cursor-pointer bg-yellow-600 hover:bg-yellow-700 text-white px-2 py-1 rounded text-xs font-medium">
                    Ver detalles
                </button>
            </div>
        @endif

        <!-- Detalles de Vencimientos -->
        @if($mostrarVencimientos && $proximosVencer->count() > 0)
            <div class="mb-3 bg-white border border-yellow-200 rounded-lg shadow overflow-x-auto">
                <div class="px-3 py-2 bg-yellow-100 border-b border-yellow-200">
                    <h3 class="text-sm font-medium text-yellow-800 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        Insumos Próximos a Vencer
                    </h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-2 py-1 text-left font-medium text-gray-700">Insumo</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-700">Proveedor</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-700">Tipo</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-700">Vencimiento</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-700">Días restantes</th>
                            <th class="px-2 py-1 text-center font-medium text-gray-700">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($proximosVencer as $insumo)
                            @php
                                $diasRestantes = now()->diffInDays($insumo->fecVenIns, false);
                                $nivel = $diasRestantes < 0 ? 'Vencido' : ($diasRestantes <= 7 ? 'Urgente' : 'Crítico');
                                $clase = $diasRestantes < 0 ? 'text-gray-600 bg-gray-50' : ($diasRestantes <= 7 ? 'text-red-600 bg-red-50' : 'text-yellow-600 bg-yellow-50');
                            @endphp
                            <tr class="hover:bg-gray-50 {{ $clase }}">
                                <td class="px-2 py-1 font-medium">{{ $insumo->nomIns }}</td>
                                <td class="px-2 py-1 text-gray-600">
                                    {{ $insumo->proveedor?->nomProve ?? 'Sin proveedor' }}
                                </td>
                                <td class="px-2 py-1 capitalize">{{ $insumo->tipIns }}</td>
                                <td class="px-2 py-1">{{ \Carbon\Carbon::parse($insumo->fecVenIns)->format('d/m/Y') }}</td>
                                <td class="px-2 py-1 font-medium">
                                    {{ $diasRestantes < 0 ? 'Vencido hace '.abs($diasRestantes).' días' : $diasRestantes . ' días' }}
                                </td>
                                <td class="px-2 py-1 text-center">
                                    <a href="{{ route('inventario.insumos.edit', $insumo->idIns) }}" wire:navigate
                                       class="text-blue-600 hover:text-blue-900 text-xs font-medium">
                                        Editar
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Estadísticas -->
        @if(!$verPapelera)
            <div class="grid grid-cols-3 md:grid-cols-6 gap-2 mb-3">
                <div class="bg-white border border-green-200 rounded-lg p-2 text-center">
                    <div class="text-sm font-bold text-green-600">{{ $estadisticas['total'] ?? 0 }}</div>
                    <div class="text-xs text-gray-600">Total</div>
                </div>
                <div class="bg-white border border-blue-200 rounded-lg p-2 text-center">
                    <div class="text-sm font-bold text-blue-600">{{ $estadisticas['disponibles'] ?? 0 }}</div>
                    <div class="text-xs text-gray-600">Disponibles</div>
                </div>
                <div class="bg-white border border-red-200 rounded-lg p-2 text-center">
                    <div class="text-sm font-bold text-red-600">{{ $estadisticas['stock_critico'] ?? 0 }}</div>
                    <div class="text-xs text-gray-600">Stock Crítico</div>
                </div>
                <div class="bg-white border border-yellow-200 rounded-lg p-2 text-center">
                    <div class="text-sm font-bold text-yellow-600">{{ $estadisticas['por_vencer'] ?? 0 }}</div>
                    <div class="text-xs text-gray-600">Por Vencer</div>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg p-2 text-center">
                    <div class="text-sm font-bold text-gray-600">{{ $estadisticas['vencidos'] ?? 0 }}</div>
                    <div class="text-xs text-gray-600">Vencidos</div>
                </div>
                <div class="bg-white border border-purple-200 rounded-lg p-2 text-center">
                    <div class="text-sm font-bold text-purple-600">{{ $estadisticas['con_proveedor'] ?? 0 }}</div>
                    <div class="text-xs text-gray-600">Con Proveedor</div>
                </div>
            </div>
        @endif

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
                               placeholder="Buscar por nombre, marca, proveedor..."
                               class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div class="relative">
                        <select wire:model.live="proveedor" class="w-full sm:w-32 px-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">Todos los proveedores</option>
                            @foreach($proveedores as $prov)
                                <option value="{{ $prov->idProve }}">{{ $prov->nomProve }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="relative">
                        <select wire:model.live="tipo" class="w-full sm:w-32 px-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">Todos los tipos</option>
                            @foreach($tipos as $tipoOption)
                                <option value="{{ $tipoOption }}">{{ ucfirst($tipoOption) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="relative">
                        <select wire:model.live="estado" class="w-full sm:w-28 px-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">Todos</option>
                            <option value="disponible">Disponible</option>
                            <option value="agotado">Agotado</option>
                            <option value="vencido">Vencido</option>
                        </select>
                    </div>
                    <div class="relative">
                        <select wire:model.live="vencimiento" class="w-full sm:w-32 px-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">Todos</option>
                            <option value="vencido">Vencidos</option>
                            <option value="urgente">Próximos 7 días</option>
                            <option value="critico">Próximos 30 días</option>
                        </select>
                    </div>
                    @if($search || $tipo || $estado || $stock || $vencimiento || $proveedor)
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
            @if($insumos->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-black">
                        <tr>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">#</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Insumo</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Proveedor</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Tipo</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Cantidad</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Unidad</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Vencimiento</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Estado</th>
                            <th class="px-2 py-1.5 text-center text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($insumos as $index => $insumo)
                        @php
                            // Determinar clase de fila según vencimiento
                            $claseFilaVencimiento = '';
                            if ($insumo->fecVenIns) {
                                $diasParaVencer = now()->diffInDays($insumo->fecVenIns, false);
                                if ($diasParaVencer < 0) {
                                    $claseFilaVencimiento = 'bg-gray-100'; // Vencido
                                } elseif ($diasParaVencer <= 7) {
                                    $claseFilaVencimiento = 'bg-red-50'; // Vence en 7 días
                                } elseif ($diasParaVencer <= 30) {
                                    $claseFilaVencimiento = 'bg-yellow-50'; // Vence en 30 días
                                }
                            }
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors duration-200 {{ $claseFilaVencimiento }}">
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs font-medium text-gray-900">
                                {{ ($insumos->currentPage() - 1) * $insumos->perPage() + $index + 1 }}
                            </td>
                            
                            <!-- Insumo -->
                            <td class="px-2 py-1.5 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    @php
                                        $iconoColor = match(strtolower($insumo->tipIns)) {
                                            'medicamento veterinario', 'medicamento' => 'text-blue-600',
                                            'concentrado' => 'text-green-600',
                                            'vacuna' => 'text-red-600',
                                            'vitamina' => 'text-yellow-600',
                                            'suplemento' => 'text-purple-600',
                                            default => 'text-gray-600'
                                        };
                                    @endphp
                                    <div class="min-w-0">
                                        <p class="text-xs font-medium text-gray-900">{{ $insumo->nomIns }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Proveedor -->
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                                <div class="flex items-center gap-1">
                                    @if($insumo->proveedor)
                                        <div class="min-w-0">
                                            <p class="text-xs font-medium text-gray-900">{{ $insumo->proveedor->nomProve }}</p>
                                        </div>
                                    @else
                                        <p class="text-xs text-gray-400">Sin proveedor</p>
                                    @endif
                                </div>
                            </td>

                            <!-- Tipo -->
                            <td class="px-2 py-1.5 whitespace-nowrap">
                                @php
                                    $tipoColor = match(strtolower($insumo->tipIns)) {
                                        'medicamento veterinario', 'medicamento' => 'bg-blue-100 text-blue-800',
                                        'concentrado' => 'bg-green-100 text-green-800',
                                        'vacuna' => 'bg-red-100 text-red-800',
                                        'vitamina' => 'bg-yellow-100 text-yellow-800',
                                        'suplemento' => 'bg-purple-100 text-purple-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium {{ $tipoColor }}">
                                    {{ ucfirst($insumo->tipIns) }}
                                </span>
                            </td>

                            <!-- Stock --->
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                                <div class="flex items-center gap-1">
                                    <p class="text-xs font-medium text-gray-900">{{ number_format($insumo->canIns ?? 0, 2) }}</p>
                                </div>
                            </td>

                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                                <div class="flex items-center gap-1">
                                    <p class="text-xs font-medium text-gray-900">{{ $insumo->uniIns }}</p>
                                </div>
                            </td>

                            <!-- Vencimiento -->
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs">
                                @if($insumo->fecVenIns)
                                    @php
                                        $diasParaVencer = now()->diffInDays($insumo->fecVenIns, false);
                                        $colorVencimiento = 'text-green-600';
                                        $bgVencimiento = 'bg-green-100';
                                        $textoVencimiento = '';
                                        
                                        if ($diasParaVencer < 0) {
                                            $colorVencimiento = 'text-gray-600';
                                            $bgVencimiento = 'bg-gray-100';
                                        } elseif ($diasParaVencer <= 7) {
                                            $colorVencimiento = 'text-red-600';
                                            $bgVencimiento = 'bg-red-100';

                                        } elseif ($diasParaVencer <= 30) {
                                            $colorVencimiento = 'text-yellow-600';
                                            $bgVencimiento = 'bg-yellow-100';
                                        } 
                                    @endphp
                                    <div>
                                        <div class="{{ $colorVencimiento }} text-xs font-medium">
                                            {{ $insumo->fecVenIns->format('d/m/Y') }}
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400 text-xs">Sin vencimiento</span>
                                @endif
                            </td>

                            <!-- Estado -->
                            <td class="px-2 py-1.5 whitespace-nowrap">
                                @php
                                    $estadoColor = match($insumo->estIns) {
                                        'disponible' => 'bg-green-100 text-green-800',
                                        'agotado' => 'bg-red-100 text-red-800',
                                        'vencido' => 'bg-gray-100 text-gray-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium {{ $estadoColor }}">
                                    {{ ucfirst($insumo->estIns) }}
                                </span>
                            </td>

                            <!-- Acciones -->
                            <td class="px-2 py-1.5 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1">
                                    @if($verPapelera)
                                        <button wire:click="restaurar({{ $insumo->idIns }})"
                                                class="cursor-pointer bg-green-100 hover:bg-green-200 text-green-700 text-xs font-medium py-1 px-1.5 rounded transition duration-200">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                        </button>
                                        <button wire:click="eliminarPermanente({{ $insumo->idIns }})"
                                                class="cursor-pointer bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium py-1 px-1.5 rounded transition duration-200">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    @else
                                        <a href="{{ route('inventario.insumos.show', $insumo->idIns) }}" wire:navigate
                                           class="bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs font-medium py-1 px-1.5 rounded transition duration-200">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        <a href="{{ route('inventario.insumos.edit', $insumo->idIns) }}" wire:navigate
                                           class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 text-xs font-medium py-1 px-1.5 rounded transition duration-200">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                        <button wire:click="confirmDelete({{ $insumo->idIns }})"
                                                class="cursor-pointer bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium py-1 px-1.5 rounded transition duration-200">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($insumos->hasPages())
            <div class="bg-white px-2 py-1.5 border-t border-gray-200 sm:px-3">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        {{ $insumos->links() }}
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs text-gray-700">
                                Mostrando <span class="font-medium">{{ $insumos->firstItem() }}</span> a 
                                <span class="font-medium">{{ $insumos->lastItem() }}</span> de 
                                <span class="font-medium">{{ $insumos->total() }}</span> insumos
                            </p>
                        </div>
                        <div>
                            {{ $insumos->links() }}
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 mb-1">
                        {{ $verPapelera ? 'No hay insumos eliminados' : 'No hay insumos registrados' }}
                    </h3>
                    <p class="text-xs text-gray-600 mb-2">
                        @if($search || $tipo || $estado || $stock || $vencimiento || $proveedor)
                            No hay resultados para los filtros aplicados. Intenta con otros términos.
                        @else
                            {{ $verPapelera ? 'Todos tus insumos están activos.' : 'Comienza agregando tu primer insumo al inventario.' }}
                        @endif
                    </p>
                    @if(!$verPapelera)
                    <a href="{{ route('inventario.insumos.create') }}" wire:navigate
                       class="cursor-pointer inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-green-600 to-green-800 hover:from-green-700 hover:to-green-900 text-white font-medium rounded-lg shadow hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span class="text-xs">Agregar Insumo</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Modal Eliminar Insumo -->
        @if($showDeleteModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50" wire:click.self="$set('showDeleteModal', false)">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-5 transform transition-all duration-300">
                <div class="text-center">
                    <div class="p-2.5 bg-gradient-to-br from-red-500 to-pink-600 rounded-full w-14 h-14 mx-auto mb-3 flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 mb-1.5">Confirmar Eliminación</h3>
                    <p class="text-xs text-gray-600 mb-3">¿Está seguro que desea eliminar este insumo? Esta acción se puede deshacer desde la papelera.</p>
                    @if($deleteError)
                    <div class="bg-red-50 border-l-4 border-red-500 p-2.5 mb-3 text-left">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-4 w-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-2">
                                <p class="text-xs text-red-700">{{ $deleteError }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($insumoToDelete)
                        @php
                            $insumo = Insumo::find($insumoToDelete);
                        @endphp
                        @if($insumo)
                        <div class="bg-gray-50 rounded-lg p-2.5 mb-3 text-left">
                            <div class="space-y-1 text-xs">
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-700">Insumo:</span>
                                    <span class="text-gray-900">{{ $insumo->nomIns }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-700">Tipo:</span>
                                    <span class="text-gray-900">{{ ucfirst($insumo->tipIns) }}</span>
                                </div>
                                @if($insumo->proveedor)
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-700">Proveedor:</span>
                                    <span class="text-gray-900">{{ $insumo->proveedor->nomProve }}</span>
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
                        <button wire:click="deleteInsumo"
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
    </div>
</div>