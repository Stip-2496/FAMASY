<?php
use App\Models\PrestamoHerramienta;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;

    public string $search = '';
    public string $filtroEstado = '';
    public string $filtroUsuario = '';
    public string $filtroSolicitante = '';
    public string $filtroHerramienta = '';
    public int $perPage = 10;
    public $prestamoParaDevolver = null;
    public $fechaDevolucion;
    public $observaciones;

    public function with(): array
    {
        // Actualizar primero los préstamos vencidos
        $this->actualizarPrestamosVencidos();

        // Obtener el usuario autenticado
        $user = Auth::user();
        $userId = $user->id;
        $userRol = $user->idRolUsu;

        return [
            'prestamos' => PrestamoHerramienta::query()
                ->with(['herramienta', 'usuario', 'solicitante'])
                ->when($userRol == 1, function ($query) use ($userId) {
                    $query->where('idUsuPre', $userId);
                })
                ->when($userRol == 3, function ($query) use ($userId) {
                    $query->where('idUsuSol', $userId);
                })
                ->when($this->filtroEstado, function ($query) {
                    $query->where('estPre', $this->filtroEstado);
                })
                ->when($this->filtroUsuario, function ($query) {
                    $query->whereHas('usuario', function($q) {
                        $q->where('nomUsu', 'like', '%'.$this->filtroUsuario.'%')
                          ->orWhere('apeUsu', 'like', '%'.$this->filtroUsuario.'%');
                    });
                })
                ->when($this->filtroSolicitante, function ($query) {
                    $query->whereHas('solicitante', function($q) {
                        $q->where('nomUsu', 'like', '%'.$this->filtroSolicitante.'%')
                          ->orWhere('apeUsu', 'like', '%'.$this->filtroSolicitante.'%')
                          ->orWhere('numDocUsu', 'like', '%'.$this->filtroSolicitante.'%');
                    });
                })
                ->when($this->filtroHerramienta, function ($query) {
                    $query->whereHas('herramienta', function($q) {
                        $q->where('nomHer', 'like', '%'.$this->filtroHerramienta.'%');
                    });
                })
                ->orderBy('fecPre', 'desc')
                ->paginate($this->perPage),
            
            'estadisticas' => [
                'prestados' => PrestamoHerramienta::query()
                    ->when($userRol == 1, function ($query) use ($userId) {
                        $query->where('idUsuPre', $userId);
                    })
                    ->when($userRol == 3, function ($query) use ($userId) {
                        $query->where('idUsuSol', $userId);
                    })
                    ->where('estPre', 'prestado')
                    ->count(),
                    
                'devueltos' => PrestamoHerramienta::query()
                    ->when($userRol == 1, function ($query) use ($userId) {
                        $query->where('idUsuPre', $userId);
                    })
                    ->when($userRol == 3, function ($query) use ($userId) {
                        $query->where('idUsuSol', $userId);
                    })
                    ->where('estPre', 'devuelto')
                    ->count(),
                    
                'vencidos' => PrestamoHerramienta::query()
                    ->when($userRol == 1, function ($query) use ($userId) {
                        $query->where('idUsuPre', $userId);
                    })
                    ->when($userRol == 3, function ($query) use ($userId) {
                        $query->where('idUsuSol', $userId);
                    })
                    ->where('estPre', 'vencido')
                    ->count(),
                    
                'total' => PrestamoHerramienta::query()
                    ->when($userRol == 1, function ($query) use ($userId) {
                        $query->where('idUsuPre', $userId);
                    })
                    ->when($userRol == 3, function ($query) use ($userId) {
                        $query->where('idUsuSol', $userId);
                    })
                    ->count()
            ]
        ];
    }

    protected function actualizarPrestamosVencidos(): void
    {
        PrestamoHerramienta::where('estPre', 'prestado')
            ->whereNotNull('fecDev')
            ->where('fecDev', '<', now())
            ->update(['estPre' => 'vencido']);
    }

    public function puedeEditar($prestamo): bool
    {
        return $prestamo->fecPre->isToday() && $prestamo->estPre !== 'devuelto';
    }

    public function clearFilters(): void
    {
        $this->reset(['filtroEstado', 'filtroUsuario', 'filtroHerramienta', 'filtroSolicitante']);
        $this->resetPage();
    }

    public function devolver($prestamoId, $fechaDevolucion, $observaciones = null): void
    {
        try {
            $prestamo = PrestamoHerramienta::findOrFail($prestamoId);
            
            if ($prestamo->estPre === 'devuelto') {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Este préstamo ya ha sido devuelto'
                ]);
                return;
            }
            
            $prestamo->update([
                'fecDev' => $fechaDevolucion,
                'estPre' => 'devuelto',
                'obsPre' => $observaciones
            ]);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Préstamo devuelto correctamente'
            ]);
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al devolver el préstamo: ' . $e->getMessage()
            ]);
        }
    }
    
    public function devolverPrestamo($prestamoId): void
    {
        $this->prestamoParaDevolver = $prestamoId;
        $this->fechaDevolucion = now()->format('Y-m-d\TH:i');
        $this->observaciones = null;
    }

    public function confirmarDevolucion(): void
    {
        $this->validate([
            'fechaDevolucion' => 'required|date_format:Y-m-d\TH:i',
        ]);
        
        $this->devolver($this->prestamoParaDevolver, $this->fechaDevolucion, $this->observaciones);
        $this->reset(['prestamoParaDevolver', 'fechaDevolucion', 'observaciones']);
    }
}; ?>

@section('title', 'Préstamos')

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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-black bg-gradient-to-r from-gray-900 via-gray-800 to-blue-800 bg-clip-text text-transparent leading-tight">
                                Préstamos de Herramientas
                            </h1>
                            <p class="text-gray-600 text-xs">Gestión y seguimiento de préstamos</p>
                        </div>
                    </div>
                    @can('admin')
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('inventario.prestamos.create') }}" wire:navigate
                           class="group relative inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-600 hover:from-blue-700 hover:to-blue-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                            <svg class="w-4 h-4 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span class="relative z-10 text-xs">Nuevo Préstamo</span>
                        </a>
                    </div>
                    @endcan
                </div>
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
                               wire:model.live.debounce.500ms="filtroHerramienta"
                               placeholder="Buscar por herramienta..."
                               class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="relative">
                        <select wire:model.live="filtroEstado" class="w-full sm:w-32 px-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todos los estados</option>
                            <option value="prestado">Prestado</option>
                            <option value="devuelto">Devuelto</option>
                            <option value="vencido">Vencido</option>
                        </select>
                    </div>
                    <div class="relative">
                        <input type="text"
                               wire:model.live.debounce.500ms="filtroUsuario"
                               placeholder="Encargado..."
                               class="w-full sm:w-32 px-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="relative">
                        <input type="text"
                               wire:model.live.debounce.500ms="filtroSolicitante"
                               placeholder="Solicitante..."
                               class="w-full sm:w-32 px-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    @if($filtroEstado || $filtroUsuario || $filtroSolicitante || $filtroHerramienta)
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

        @can('admin')
        <!-- Estadísticas rápidas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-3">
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                <div class="p-3 flex items-center">
                    <div class="p-2 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-600">Prestados</p>
                        <p class="text-lg font-bold text-gray-900">{{ $estadisticas['prestados'] }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                <div class="p-3 flex items-center">
                    <div class="p-2 bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-600">Devueltos</p>
                        <p class="text-lg font-bold text-gray-900">{{ $estadisticas['devueltos'] }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                <div class="p-3 flex items-center">
                    <div class="p-2 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl shadow">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-600">Vencidos</p>
                        <p class="text-lg font-bold text-gray-900">{{ $estadisticas['vencidos'] }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                <div class="p-3 flex items-center">
                    <div class="p-2 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl shadow">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-600">Total</p>
                        <p class="text-lg font-bold text-gray-900">{{ $estadisticas['total'] }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endcan

        <!-- Tabla -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 overflow-hidden">
            @if($prestamos->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-black">
                        <tr>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">#</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Herramienta</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Encargado</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Solicitante</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">F. Préstamo</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">F. Devolución</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Estado</th>
                            <th class="px-2 py-1.5 text-center text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($prestamos as $index => $prestamo)
                        <tr class="hover:bg-gray-50/50 transition-colors duration-200">
                            <!-- Columna Número -->
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs font-medium text-gray-900">
                                {{ ($prestamos->currentPage() - 1) * $prestamos->perPage() + $loop->iteration }}
                            </td>
                            <!-- Columna Herramienta -->
                            <td class="px-2 py-1.5 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    <div class="min-w-0">
                                        <p class="text-xs font-medium text-gray-900">
                                            {{ $prestamo->herramienta->nomHer . ' ( ' . ($prestamo->herramienta->catHer . ' )' ?? 'Sin tipo') }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <!-- Columna Encargado -->
                            <td class="px-2 py-1.5 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    <div class="min-w-0">
                                        <p class="text-xs font-medium text-gray-900">{{ $prestamo->usuario->nomUsu ?? '' }} {{ $prestamo->usuario->apeUsu ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <!-- Columna Solicitante -->
                            <td class="px-2 py-1.5 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    <div class="min-w-0">
                                        <p class="text-xs font-medium text-gray-900">{{ $prestamo->solicitante->nomUsu ?? '' }} {{ $prestamo->solicitante->apeUsu ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <!-- Fecha Préstamo -->
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                                <p class="text-xs font-medium text-gray-900">{{ $prestamo->fecPre->format('d/m/Y - H:i') }}</p>
                            </td>
                            <!-- Fecha Devolución -->
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                                    <p class="text-xs font-medium text-gray-900">{{ $prestamo->fecDev->format('d/m/Y - H:i') ?? 'Sin programar' }}</p>
                            </td>
                            <!-- Estado -->
                            <td class="px-2 py-1.5 whitespace-nowrap">
                                @php
                                    $estadoColors = [
                                        'prestado' => 'bg-blue-100 text-blue-800',
                                        'devuelto' => 'bg-green-100 text-green-800',
                                        'vencido' => 'bg-red-100 text-red-800'
                                    ];
                                    $colorClass = $estadoColors[$prestamo->estPre] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                    {{ ucfirst($prestamo->estPre) }}
                                </span>
                            </td>
                            <!-- Acciones -->
                            <td class="px-2 py-1.5 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('inventario.prestamos.show', $prestamo) }}" wire:navigate
                                       class="bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs font-medium py-1 px-1.5 rounded transition duration-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    @can('admin')
                                    @if($this->puedeEditar($prestamo))
                                    <a href="{{ route('inventario.prestamos.edit', $prestamo) }}" wire:navigate
                                       class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 text-xs font-medium py-1 px-1.5 rounded transition duration-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    @endif
                                    @if($prestamo->estPre === 'prestado')
                                    <button wire:click="devolverPrestamo({{ $prestamo->idPreHer }})"
                                            class="cursor-pointer bg-green-100 hover:bg-green-200 text-green-700 text-xs font-medium py-1 px-1.5 rounded transition duration-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                    @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Paginación -->
            @if($prestamos->hasPages())
            <div class="bg-white px-2 py-1.5 border-t border-gray-200 sm:px-3">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        {{ $prestamos->links() }}
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs text-gray-700">
                                Mostrando <span class="font-medium">{{ $prestamos->firstItem() }}</span> a 
                                <span class="font-medium">{{ $prestamos->lastItem() }}</span> de 
                                <span class="font-medium">{{ $prestamos->total() }}</span> préstamos
                            </p>
                        </div>
                        <div>
                            {{ $prestamos->links() }}
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 mb-1">No se encontraron préstamos</h3>
                    <p class="text-xs text-gray-600 mb-2">
                        @if($filtroEstado || $filtroUsuario || $filtroSolicitante || $filtroHerramienta)
                            No hay resultados para los filtros aplicados. Intenta con otros términos.
                        @else
                            No hay préstamos registrados en el sistema.
                        @endif
                    </p>
                    @can('admin')
                    <a href="{{ route('inventario.prestamos.create') }}" wire:navigate
                       class="cursor-pointer inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-700 hover:to-blue-900 text-white font-medium rounded-lg shadow hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span class="text-xs">Agregar Préstamo</span>
                    </a>
                    @endcan
                </div>
            </div>
            @endif
        </div>

        <!-- Modal Devolver Préstamo -->
        @if($prestamoParaDevolver)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50" wire:click.self="$set('prestamoParaDevolver', null)">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-5 transform transition-all duration-300">
                <div class="text-center">
                    <div class="p-2.5 bg-gradient-to-br from-green-500 to-green-600 rounded-full w-14 h-14 mx-auto mb-3 flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 mb-1.5">Devolver Préstamo</h3>
                    <p class="text-xs text-gray-600 mb-3">Confirma los detalles para devolver este préstamo.</p>
                    <div class="bg-gray-50 rounded-lg p-2.5 mb-3 text-left">
                        <div class="space-y-1 text-xs">
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Herramienta:</span>
                                <span class="text-gray-900">{{ PrestamoHerramienta::find($prestamoParaDevolver)->herramienta->nomHer ?? '' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Solicitante:</span>
                                <span class="text-gray-900">{{ PrestamoHerramienta::find($prestamoParaDevolver)->solicitante->nomUsu ?? '' }} {{ PrestamoHerramienta::find($prestamoParaDevolver)->solicitante->apeUsu ?? '' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 text-left">
                        <label for="fechaDevolucion" class="block text-xs font-medium text-gray-700 mb-1">Fecha y Hora de Devolución</label>
                        <input type="datetime-local" wire:model="fechaDevolucion" id="fechaDevolucion"
                               class="w-full px-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               value="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="mb-3 text-left">
                        <label for="observaciones" class="block text-xs font-medium text-gray-700 mb-1">Observaciones</label>
                        <textarea wire:model="observaciones" id="observaciones" rows="3"
                                  class="w-full px-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                    <div class="flex space-x-2">
                        <button wire:click="$set('prestamoParaDevolver', null)"
                                class="cursor-pointer flex-1 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg shadow hover:shadow transition-all duration-200 text-xs">
                            Cancelar
                        </button>
                        <button wire:click="confirmarDevolucion"
                                class="cursor-pointer flex-1 px-3 py-1.5 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-medium rounded-lg shadow hover:shadow transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center text-xs">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Confirmar
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