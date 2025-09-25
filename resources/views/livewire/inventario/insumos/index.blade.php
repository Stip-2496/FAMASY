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
    public ?string $proveedor = null; // Nuevo filtro por proveedor

    public bool $verPapelera = false;
    public bool $showDeleteModal = false;
    public $insumoToDelete = null;
    public bool $mostrarVencimientos = false;

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

        $insumos = $query->orderBy('nomIns')->paginate(10);

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
        $this->showDeleteModal = true;
    }

    public function deleteInsumo(): void
    {
        try {
            $insumo = Insumo::findOrFail($this->insumoToDelete);

            if (!$insumo->puedeEliminar()) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'No se puede eliminar: tiene movimientos de inventario asociados'
                ]);
                return;
            }

            $insumo->delete();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Insumo eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ]);
        } finally {
            $this->showDeleteModal = false;
            $this->insumoToDelete = null;
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

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                        </svg>
                        Gestión de Insumos
                    </h1>
                    <p class="mt-2 text-gray-600">Administra tu inventario de insumos y sus proveedores</p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <button wire:click="verificarVencimientos" 
                            class="cursor-pointer inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        Vencimientos
                    </button>
                    <button wire:click="togglePapelera" 
                            class="cursor-pointer inline-flex items-center px-4 py-2 {{ $verPapelera ? 'bg-gray-600 hover:bg-gray-700' : 'bg-gray-500 hover:bg-gray-600' }} text-white font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        {{ $verPapelera ? 'Ver Activos' : 'Papelera' }}
                    </button>
                    <a href="{{ route('inventario.insumos.create') }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Nuevo Insumo
                    </a>
                </div>
            </div>
        </div>

        <!-- Alertas de Vencimiento -->
        @if($proximosVencer->count() > 0 && !$verPapelera)
            <div class="mb-6 bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <strong>¡Atención!</strong> Tienes {{ $proximosVencer->count() }} insumos próximos a vencer en los próximos 30 días.
                </div>
                <button wire:click="verificarVencimientos" class="cursor-pointer bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded text-sm font-medium">
                    Ver detalles
                </button>
            </div>
        @endif

        <!-- Detalles de Vencimientos -->
        @if($mostrarVencimientos && $proximosVencer->count() > 0)
            <div class="mb-6 bg-white border border-yellow-200 rounded-lg shadow overflow-x-auto">
                <div class="px-4 py-3 bg-yellow-100 border-b border-yellow-200">
                    <h3 class="text-lg font-medium text-yellow-800 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        Insumos Próximos a Vencer
                    </h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-700">Insumo</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-700">Proveedor</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-700">Tipo</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-700">Vencimiento</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-700">Días restantes</th>
                            <th class="px-4 py-2 text-center font-medium text-gray-700">Acciones</th>
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
                                <td class="px-4 py-2 font-medium">{{ $insumo->nomIns }}</td>
                                <td class="px-4 py-2 text-gray-600">
                                    {{ $insumo->proveedor?->nomProve ?? 'Sin proveedor' }}
                                </td>
                                <td class="px-4 py-2 capitalize">{{ $insumo->tipIns }}</td>
                                <td class="px-4 py-2">{{ \Carbon\Carbon::parse($insumo->fecVenIns)->format('d/m/Y') }}</td>
                                <td class="px-4 py-2 font-medium">
                                    {{ $diasRestantes < 0 ? 'Vencido hace '.abs($diasRestantes).' días' : $diasRestantes . ' días' }}
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <a href="{{ route('inventario.insumos.edit', $insumo->idIns) }}" wire:navigate
                                       class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                        Editar
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Filtros -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                    <!-- Búsqueda -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                        <input type="text" 
                               wire:model.live.debounce.500ms="search"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
                               placeholder="Nombre, marca, proveedor...">
                    </div>

                    <!-- Filtro por Proveedor -->
                    <div>
                        <label for="proveedor" class="block text-sm font-medium text-gray-700 mb-1">Proveedor</label>
                        <select wire:model.live="proveedor" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                            <option value="">Todos los proveedores</option>
                            @foreach($proveedores as $prov)
                                <option value="{{ $prov->idProve }}">{{ $prov->nomProve }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro por Tipo -->
                    <div>
                        <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                        <select wire:model.live="tipo" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                            <option value="">Todos los tipos</option>
                            @foreach($tipos as $tipoOption)
                                <option value="{{ $tipoOption }}">{{ ucfirst($tipoOption) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro por Estado -->
                    <div>
                        <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select wire:model.live="estado" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                            <option value="">Todos</option>
                            <option value="disponible">Disponible</option>
                            <option value="agotado">Agotado</option>
                            <option value="vencido">Vencido</option>
                        </select>
                    </div>

                    <!-- Filtro por Vencimiento -->
                    <div>
                        <label for="vencimiento" class="block text-sm font-medium text-gray-700 mb-1">Vencimiento</label>
                        <select wire:model.live="vencimiento" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                            <option value="">Todos</option>
                            <option value="vencido">Vencidos</option>
                            <option value="urgente">Próximos 7 días</option>
                            <option value="critico">Próximos 30 días</option>
                        </select>
                    </div>

                    <!-- Botón Limpiar -->
                    <div class="flex items-end">
                        <button wire:click="clearFilters" 
                                class="cursor-pointer w-full px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out">
                            Limpiar Filtros
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        @if(!$verPapelera)
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
                <div class="bg-white border border-green-200 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $estadisticas['total'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Total Insumos</div>
                </div>
                <div class="bg-white border border-blue-200 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $estadisticas['disponibles'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Disponibles</div>
                </div>
                <div class="bg-white border border-red-200 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $estadisticas['stock_critico'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Stock Crítico</div>
                </div>
                <div class="bg-white border border-yellow-200 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-yellow-600">{{ $estadisticas['por_vencer'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Por Vencer</div>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-gray-600">{{ $estadisticas['vencidos'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Vencidos</div>
                </div>
                <div class="bg-white border border-purple-200 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ $estadisticas['con_proveedor'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Con Proveedor</div>
                </div>
            </div>
        @endif

        <!-- Tabla -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-green-600 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                        {{ $verPapelera ? 'Insumos Eliminados' : 'Lista de Insumos' }}
                    </h3>
                    <span class="bg-green-100 text-green-800 text-sm font-medium px-2.5 py-0.5 rounded">
                        {{ $insumos->total() }} insumos
                    </span>
                </div>
            </div>

            @if($insumos->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Insumo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proveedor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimiento</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($insumos as $insumo)
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
                            <tr class="hover:bg-gray-50 {{ $claseFilaVencimiento }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $insumo->idIns }}</td>
                                
                                <!-- Insumo con ícono -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @php
                                                $iconoColor = match(strtolower($insumo->tipIns)) {
                                                    'medicamento veterinario', 'medicamento' => 'bg-blue-100 text-blue-600',
                                                    'concentrado' => 'bg-green-100 text-green-600',
                                                    'vacuna' => 'bg-red-100 text-red-600',
                                                    'vitamina' => 'bg-yellow-100 text-yellow-600',
                                                    'suplemento' => 'bg-purple-100 text-purple-600',
                                                    default => 'bg-gray-100 text-gray-600'
                                                };
                                                $icono = match(strtolower($insumo->tipIns)) {
                                                    'medicamento veterinario', 'medicamento' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
                                                    'concentrado' => 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z',
                                                    'vacuna' => 'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2m16-6a5 5 0 100-10 5 5 0 000 10zm-8-3a3 3 0 100-6 3 3 0 000 6z',
                                                    'vitamina' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
                                                    'suplemento' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
                                                    default => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'
                                                };
                                            @endphp
                                            <div class="h-10 w-10 rounded-full {{ $iconoColor }} flex items-center justify-center">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icono }}"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $insumo->nomIns }}</div>
                                            @if($insumo->marIns)
                                                <div class="text-xs text-gray-500">{{ $insumo->marIns }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Proveedor -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($insumo->proveedor)
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ $insumo->proveedor->nomProve }}</div>
                                                @if($insumo->proveedor->telProve)
                                                    <div class="text-xs text-gray-500">{{ $insumo->proveedor->telProve }}</div>
                                                @endif
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

                                <!-- Tipo -->
                                <td class="px-6 py-4 whitespace-nowrap">
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
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $tipoColor }}">
                                        {{ ucfirst($insumo->tipIns) }}
                                    </span>
                                </td>

                                <!-- Stock -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <div class="font-medium">{{ number_format($insumo->canIns ?? 0, 2) }} {{ $insumo->uniIns }}</div>
                                        @if($insumo->stockMinIns)
                                            <div class="text-xs text-gray-500">
                                                Mín: {{ number_format($insumo->stockMinIns, 0) }} 
                                                @if($insumo->stockMaxIns)
                                                    | Máx: {{ number_format($insumo->stockMaxIns, 0) }}
                                                @endif
                                            </div>
                                        @else
                                            <div class="text-xs text-gray-400">Sin límites definidos</div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Vencimiento -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($insumo->fecVenIns)
                                        @php
                                            $diasParaVencer = now()->diffInDays($insumo->fecVenIns, false);
                                            $colorVencimiento = 'text-green-600';
                                            $bgVencimiento = 'bg-green-100';
                                            $textoVencimiento = '';
                                            
                                            if ($diasParaVencer < 0) {
                                                $colorVencimiento = 'text-gray-600';
                                                $bgVencimiento = 'bg-gray-100';
                                                $textoVencimiento = 'Vencido';
                                            } elseif ($diasParaVencer <= 7) {
                                                $colorVencimiento = 'text-red-600';
                                                $bgVencimiento = 'bg-red-100';
                                                $textoVencimiento = $diasParaVencer . ' días';
                                            } elseif ($diasParaVencer <= 30) {
                                                $colorVencimiento = 'text-yellow-600';
                                                $bgVencimiento = 'bg-yellow-100';
                                                $textoVencimiento = $diasParaVencer . ' días';
                                            } else {
                                                $textoVencimiento = 'Normal';
                                            }
                                        @endphp
                                        <div class="text-center">
                                            <div class="{{ $colorVencimiento }} text-xs font-medium">
                                                {{ $insumo->fecVenIns->format('d/m/Y') }}
                                            </div>
                                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $bgVencimiento }} {{ $colorVencimiento }}">
                                                {{ $textoVencimiento }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-xs">Sin vencimiento</span>
                                    @endif
                                </td>

                                <!-- Estado -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $estadoColor = match($insumo->estIns) {
                                            'disponible' => 'bg-green-100 text-green-800',
                                            'agotado' => 'bg-red-100 text-red-800',
                                            'vencido' => 'bg-gray-100 text-gray-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    @endphp
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $estadoColor }}">
                                        {{ ucfirst($insumo->estIns) }}
                                    </span>
                                </td>

                                <!-- Acciones -->
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    @if($verPapelera)
                                        <div class="flex items-center justify-center space-x-2">
                                            <button wire:click="restaurar({{ $insumo->idIns }})"
                                                    class="cursor-pointer text-green-600 hover:text-green-900 p-1" title="Restaurar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                </svg>
                                            </button>
                                            <button wire:click="eliminarPermanente({{ $insumo->idIns }})"
                                                    class="cursor-pointer text-red-600 hover:text-red-900 p-1" title="Eliminar permanentemente">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-center space-x-2">
                                            <a href="{{ route('inventario.insumos.show', $insumo->idIns) }}" wire:navigate
                                               class="text-indigo-600 hover:text-indigo-900 p-1" title="Ver detalles">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </a>
                                            <a href="{{ route('inventario.insumos.edit', $insumo->idIns) }}" wire:navigate
                                               class="text-blue-600 hover:text-blue-900 p-1" title="Editar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                            <button wire:click="confirmDelete({{ $insumo->idIns }})"
                                                    class="cursor-pointer text-red-600 hover:text-red-900 p-1" title="Eliminar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">
                        {{ $verPapelera ? 'No hay insumos eliminados' : 'No hay insumos registrados' }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $verPapelera ? 'Todos tus insumos están activos.' : 'Comienza agregando tu primer insumo al inventario.' }}
                    </p>
                    @if(!$verPapelera)
                        <div class="mt-6">
                            <a href="{{ route('inventario.insumos.create') }}" wire:navigate
                               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Agregar Insumo
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Paginación -->
            @if($insumos->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            {{ $insumos->links() }}
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
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
        </div>
    </div>

    <!-- Modal Eliminar Insumo -->
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
                    ¿Está seguro que desea eliminar este insumo? Esta acción se puede deshacer desde la papelera.
                </p>
                
                @if($insumoToDelete)
                    @php
                        $insumo = Insumo::find($insumoToDelete);
                    @endphp
                    @if($insumo)
                        <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                            <p class="text-sm"><strong>Insumo:</strong> {{ $insumo->nomIns }}</p>
                            <p class="text-sm"><strong>Tipo:</strong> {{ $insumo->tipIns }}</p>
                            @if($insumo->proveedor)
                                <p class="text-sm"><strong>Proveedor:</strong> {{ $insumo->proveedor->nomProve }}</p>
                            @endif
                        </div>
                    @endif
                @endif
                
                <div class="flex justify-end space-x-3">
                    <button wire:click="$set('showDeleteModal', false)"
                            class="cursor-pointer px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg transition">
                        Cancelar
                    </button>
                    <button wire:click="deleteInsumo"
                            class="cursor-pointer px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    @endif
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
});
</script>