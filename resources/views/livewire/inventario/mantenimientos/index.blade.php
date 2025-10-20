<?php
use App\Models\Mantenimiento;
use App\Models\Herramienta;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;

    // Propiedades de estado
    public string $search = '';
    public string $filtroEstado = '';
    public string $filtroTipo = '';
    public string $buscarHerramienta = '';
    
    // Modales
    public $showCompletarModal = false;
    public $showDeleteModal = false;
    
    // IDs de elementos activos
    public $mantenimientoToComplete = null;
    public $mantenimientoToDelete = null;
    
    // Datos del modal completar
    public $resultado = '';
    public $observaciones = '';

    public function with(): array
    {
        $query = Mantenimiento::query()->with('herramienta');
        
        // Aplicar filtros
        if ($this->filtroEstado) {
            $query->where('estMan', $this->filtroEstado);
        }

        if ($this->filtroTipo) {
            $query->where('tipMan', $this->filtroTipo);
        }

        if ($this->buscarHerramienta) {
            $query->where(function($q) {
                $q->whereHas('herramienta', function($sub) {
                    $sub->where('nomHer', 'like', '%'.$this->buscarHerramienta.'%');
                })->orWhere('nomHerMan', 'like', '%'.$this->buscarHerramienta.'%');
            });
        }
        
        // Aplicar ordenamiento
        $query->orderBy('fecMan', 'desc');
        
        return [
            'mantenimientos' => $query->paginate(10),
            'estadisticas' => [
                'total' => Mantenimiento::count(),
                'pendientes' => Mantenimiento::where('estMan', 'pendiente')->count(),
                'en_proceso' => Mantenimiento::where('estMan', 'en proceso')->count(),
                'completados' => Mantenimiento::where('estMan', 'completado')->count(),
                'vencidos' => Mantenimiento::where('fecMan', '<', now()->toDateString())
                    ->where('estMan', '!=', 'completado')->count(),
                'proximos' => Mantenimiento::where('fecMan', '>=', now()->toDateString())
                    ->where('fecMan', '<=', now()->addDays(7)->toDateString())
                    ->where('estMan', '!=', 'completado')->count(),
                'esta_semana' => Mantenimiento::whereBetween('fecMan', [
                    now()->startOfWeek()->toDateString(),
                    now()->endOfWeek()->toDateString()
                ])->where('estMan', '!=', 'completado')->count()
            ]
        ];
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['filtroEstado', 'filtroTipo', 'buscarHerramienta']);
        $this->resetPage();
        session()->flash('success', 'Filtros limpiados correctamente');
    }

    public function confirmCompletar($id): void
    {
        $this->mantenimientoToComplete = $id;
        $this->showCompletarModal = true;
        $this->reset(['resultado', 'observaciones']);
    }

    public function completarMantenimiento(): void
    {
        $this->validate([
            'resultado' => 'required|string|max:100',
            'observaciones' => 'nullable|string|max:500'
        ]);

        try {
            $mantenimiento = Mantenimiento::findOrFail($this->mantenimientoToComplete);
            $mantenimiento->update([
                'estMan' => 'completado',
                'resMan' => $this->resultado,
                'obsMan' => $this->observaciones
            ]);
            
            session()->flash('success', '<i class="fa-solid fa-check-circle mr-2"></i> Mantenimiento completado exitosamente');
            
            $this->reset(['showCompletarModal', 'mantenimientoToComplete', 'resultado', 'observaciones']);
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al completar mantenimiento: ' . $e->getMessage());
        }
    }

    public function confirmDelete($id): void
    {
        $this->mantenimientoToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteMantenimiento(): void
    {
        try {
            $mantenimiento = Mantenimiento::findOrFail($this->mantenimientoToDelete);
            
            if ($mantenimiento->estMan !== 'pendiente') {
                session()->flash('error', '<i class="fa-solid fa-ban mr-2"></i> Solo se pueden eliminar mantenimientos pendientes');
                $this->cancelDelete();
                return;
            }
            
            $nombreHerramienta = $mantenimiento->getNombreHerramientaCompleto();
            $mantenimiento->delete();
            
            session()->flash('success', "<i class=\"fa-solid fa-trash-can mr-2\"></i> Mantenimiento de '{$nombreHerramienta}' eliminado correctamente");
            
            $this->cancelDelete();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar: ' . $e->getMessage());
            $this->cancelDelete();
        }
    }

    public function cancelDelete(): void
    {
        $this->reset(['showDeleteModal', 'mantenimientoToDelete']);
    }
}; ?>

@section('title', 'Gestión de mantenimientos')

<div class="min-h-screen py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @if(session('success'))
            <div class="mb-3 p-3 bg-emerald-50 border-l-4 border-emerald-400 rounded-r-lg shadow-sm">
                <div class="flex items-center">
                    <i class="fa-solid fa-check-circle h-4 w-4 text-emerald-400 mr-2"></i>
                    <p class="text-xs text-emerald-700 font-medium">{!! session('success') !!}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-3 p-3 bg-red-50 border-l-4 border-red-400 rounded-r-lg shadow-sm">
                <div class="flex items-center">
                    <i class="fa-solid fa-times-circle h-4 w-4 text-red-400 mr-2"></i>
                    <p class="text-xs text-red-700 font-medium">{!! session('error') !!}</p>
                </div>
            </div>
        @endif

        @if(session('info'))
            <div class="mb-3 p-3 bg-blue-50 border-l-4 border-blue-400 rounded-r-lg shadow-sm">
                <div class="flex items-center">
                    <i class="fa-solid fa-info-circle h-4 w-4 text-blue-400 mr-2"></i>
                    <p class="text-xs text-blue-700 font-medium">{!! session('info') !!}</p>
                </div>
            </div>
        @endif

        <!-- Header Compacto -->
        <div class="mb-3">
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-4 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-green-500/5 to-emerald-500/5"></div>
                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center space-x-2 mb-3 lg:mb-0">
                        <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg transform rotate-3 hover:rotate-0 transition-transform duration-300">
                            <i class="fa-solid fa-cogs w-5 h-5 text-white"></i>
                        </div>
                        <div>
                            <h1 class="text-xl font-black bg-gradient-to-r from-gray-900 via-gray-800 to-green-800 bg-clip-text text-transparent leading-tight">
                                Centro de Mantenimientos
                            </h1>
                            <p class="text-gray-600 text-xs">Gestión inteligente de mantenimientos</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('inventario.mantenimientos.create') }}" wire:navigate
                           class="group relative inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                            <i class="fa-solid fa-plus w-4 h-4 mr-1 relative z-10"></i>
                            <span class="relative z-10 text-xs">Nuevo Mantenimiento</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas Compactas -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-2 mb-3">
            @foreach([
                ['key' => 'total', 'label' => 'Total', 'icon' => 'fa-solid fa-chart-bar', 'color' => 'from-slate-500 to-slate-600', 'text' => 'text-slate-700'],
                ['key' => 'pendientes', 'label' => 'Pendientes', 'icon' => 'fa-solid fa-clock', 'color' => 'from-orange-500 to-red-500', 'text' => 'text-orange-700'],
                ['key' => 'en_proceso', 'label' => 'En Proceso', 'icon' => 'fa-solid fa-cog', 'color' => 'from-yellow-500 to-amber-500', 'text' => 'text-yellow-700'],
                ['key' => 'completados', 'label' => 'Completados', 'icon' => 'fa-solid fa-check-circle', 'color' => 'from-green-500 to-emerald-500', 'text' => 'text-green-700'],
                ['key' => 'vencidos', 'label' => 'Vencidos', 'icon' => 'fa-solid fa-exclamation-triangle', 'color' => 'from-red-500 to-pink-500', 'text' => 'text-red-700'],
                ['key' => 'proximos', 'label' => 'Próximos 7d', 'icon' => 'fa-solid fa-calendar-alt', 'color' => 'from-purple-500 to-indigo-500', 'text' => 'text-purple-700'],
                ['key' => 'esta_semana', 'label' => 'Esta Semana', 'icon' => 'fa-solid fa-calendar-week', 'color' => 'from-blue-500 to-cyan-500', 'text' => 'text-blue-700']
            ] as $stat)
            <div class="group relative bg-white/70 backdrop-blur-sm rounded-xl shadow border border-white/30 p-3 hover:shadow-lg hover:bg-white/90 transition-all duration-300 cursor-pointer transform hover:-translate-y-0.5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">{{ $stat['label'] }}</p>
                        <p class="text-lg font-black {{ $stat['text'] }} mt-0.5">{{ $estadisticas[$stat['key']] }}</p>
                    </div>
                    <div class="p-2 bg-gradient-to-br {{ $stat['color'] }} rounded-lg shadow group-hover:scale-110 transition-transform duration-300">
                        <i class="{{ $stat['icon'] }} w-4 h-4 text-white"></i>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Filtros Compactos -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-3 mb-3">
            <form wire:submit.prevent>
                <div class="flex flex-col sm:flex-row gap-2">
                    <div class="flex-1 relative">
                        <i class="fa-solid fa-search w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                        <input type="text"
                               wire:model.live.debounce.500ms="buscarHerramienta"
                               placeholder="Buscar por herramienta..."
                               class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div class="relative">
                        <select wire:model.live="filtroEstado" class="w-full sm:w-40 px-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">Todos los estados</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="en proceso">En Proceso</option>
                            <option value="completado">Completado</option>
                        </select>
                    </div>
                    <div class="relative">
                        <select wire:model.live="filtroTipo" class="w-full sm:w-40 px-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">Todos los tipos</option>
                            <option value="preventivo">Preventivo</option>
                            <option value="correctivo">Correctivo</option>
                            <option value="predictivo">Predictivo</option>
                        </select>
                    </div>
                    @if($buscarHerramienta || $filtroEstado || $filtroTipo)
                    <button wire:click="limpiarFiltros"
                            class="cursor-pointer inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-medium rounded-lg shadow hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                        <i class="fa-solid fa-times w-3 h-3 mr-1"></i>
                        <span class="text-xs">Limpiar</span>
                    </button>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tabla de Mantenimientos -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 overflow-hidden">
            @if($mantenimientos->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-black">
                        <tr>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">#</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Herramienta</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Tipo</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Estado</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Fecha</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Descripción</th>
                            <th class="px-2 py-1.5 text-center text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($mantenimientos as $index => $mantenimiento)
                        <tr class="hover:bg-gray-50/50 transition-colors duration-200">
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs font-medium text-gray-900">
                                {{ ($mantenimientos->currentPage() - 1) * $mantenimientos->perPage() + $index + 1 }}
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    <div class="min-w-0">
                                        @if($mantenimiento->herramienta && $mantenimiento->herramienta->catHer)
                                            <p class="text-xs font-medium text-gray-900">
                                                {{ $mantenimiento->herramienta->nomHer . ' ( ' . $mantenimiento->herramienta->catHer . ' ) ' }}
                                            </p>
                                        @else
                                            <p class="text-xs font-medium text-gray-900">
                                                {{ $mantenimiento->nomHerMan ?? 'Sin herramienta' }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap">
                                @php
                                    $tipoConfig = [
                                        'preventivo' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'icon' => 'fa-solid fa-shield-alt'],
                                        'correctivo' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'icon' => 'fa-solid fa-tools'],
                                        'predictivo' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'icon' => 'fa-solid fa-chart-line']
                                    ];
                                    $config = $tipoConfig[$mantenimiento->tipMan] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => 'fa-solid fa-question-circle'];
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $config['bg'] }} {{ $config['text'] }}">
                                    <i class="{{ $config['icon'] }} mr-1"></i>
                                    {{ ucfirst($mantenimiento->tipMan) }}
                                </span>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap">
                                @php
                                    $estadoConfig = [
                                        'pendiente' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'icon' => 'fa-solid fa-clock'],
                                        'en proceso' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'icon' => 'fa-solid fa-cog'],
                                        'completado' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'icon' => 'fa-solid fa-check-circle']
                                    ];
                                    $config = $estadoConfig[$mantenimiento->estMan] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => 'fa-solid fa-question-circle'];
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $config['bg'] }} {{ $config['text'] }}">
                                    <i class="{{ $config['icon'] }} mr-1"></i>
                                    {{ ucfirst($mantenimiento->estMan) }}
                                </span>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap">
                                <span class="text-xs {{ $mantenimiento->fecMan < now()->toDateString() && $mantenimiento->estMan !== 'completado' ? 'text-gray-600 font-semibold' : 'text-gray-900' }}">
                                    <i class="fa-solid fa-calendar-day mr-1"></i> {{ \Carbon\Carbon::parse($mantenimiento->fecMan)->format('d/m/Y') }}
                                </span>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap">
                                <p class="text-xs text-gray-600 max-w-xs truncate">{{ $mantenimiento->desMan ?? '-' }}</p>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('inventario.mantenimientos.show', $mantenimiento->idMan) }}" wire:navigate
                                       class="bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs font-medium py-1 px-1.5 rounded transition duration-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('inventario.mantenimientos.edit', $mantenimiento->idMan) }}" wire:navigate
                                       class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 text-xs font-medium py-1 px-1.5 rounded transition duration-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <button wire:click="confirmDelete({{ $mantenimiento->idMan }})"
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
            @if($mantenimientos->hasPages())
            <div class="bg-white px-2 py-1.5 border-t border-gray-200 sm:px-3">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        {{ $mantenimientos->links() }}
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs text-gray-700">
                                Mostrando <span class="font-medium">{{ $mantenimientos->firstItem() }}</span> a 
                                <span class="font-medium">{{ $mantenimientos->lastItem() }}</span> de 
                                <span class="font-medium">{{ $mantenimientos->total() }}</span> mantenimientos
                            </p>
                        </div>
                        <div>
                            {{ $mantenimientos->links() }}
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @else
            <div class="bg-white border border-gray-200 rounded-b-lg shadow-sm">
                <div class="text-center py-4 px-4">
                    <div class="w-8 h-8 bg-gray-100 rounded-full mx-auto mb-2 flex items-center justify-center">
                        <i class="fa-solid fa-cogs w-4 h-4 text-gray-400"></i>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 mb-1">No se encontraron mantenimientos</h3>
                    <p class="text-xs text-gray-600 mb-2">
                        @if($buscarHerramienta || $filtroEstado || $filtroTipo)
                            No hay resultados para los filtros aplicados. Intenta con otros términos.
                        @else
                            No hay mantenimientos registrados en el sistema.
                        @endif
                    </p>
                    <a href="{{ route('inventario.mantenimientos.create') }}" wire:navigate
                       class="cursor-pointer inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-green-600 to-emerald-800 hover:from-green-700 hover:to-emerald-900 text-white font-medium rounded-lg shadow hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                        <i class="fa-solid fa-plus w-3 h-3 mr-1"></i>
                        <span class="text-xs">Agregar Mantenimiento</span>
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Modal Completar Mantenimiento -->
    @if($showCompletarModal)
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50" wire:click.self="$set('showCompletarModal', false)">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-5 transform transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2">
                    <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg">
                        <i class="fa-solid fa-check w-5 h-5 text-white"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Completar Mantenimiento</h3>
                </div>
                <button wire:click="$set('showCompletarModal', false)" 
                        class="cursor-pointer text-gray-400 hover:text-gray-600 transition-colors duration-200">
                    <i class="fa-solid fa-times w-5 h-5"></i>
                </button>
            </div>

            <form wire:submit="completarMantenimiento" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Resultado del Mantenimiento *</label>
                    <input wire:model="resultado" type="text" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 text-xs"
                           placeholder="Ej: Mantenimiento realizado correctamente" maxlength="100" required>
                    @error('resultado') <p class="text-red-500 text-xs mt-0.5">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Observaciones Adicionales</label>
                    <textarea wire:model="observaciones" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 resize-none text-xs"
                              placeholder="Detalles adicionales, repuestos utilizados, recomendaciones..." maxlength="500"></textarea>
                    @error('observaciones') <p class="text-red-500 text-xs mt-0.5">{{ $message }}</p> @enderror
                </div>

                <div class="flex space-x-2 pt-3">
                    <button type="button" wire:click="$set('showCompletarModal', false)"
                            class="cursor-pointer flex-1 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg shadow hover:shadow transition-all duration-200 text-xs">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="cursor-pointer flex-1 px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-medium rounded-lg shadow hover:shadow transform hover:-translate-y-0.5 transition-all duration-200 text-xs">
                        <i class="fa-solid fa-check mr-1"></i> Completar
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Modal Confirmar Eliminación -->
    @if($showDeleteModal)
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50" wire:click.self="$set('showDeleteModal', false)">
        <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-5 transform transition-all duration-300">
            <div class="text-center">
                <div class="p-2.5 bg-gradient-to-br from-red-500 to-pink-600 rounded-full w-16 h-16 mx-auto mb-3 flex items-center justify-center">
                    <i class="fa-solid fa-trash-can w-8 h-8 text-white"></i>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-2">Confirmar Eliminación</h3>
                <p class="text-xs text-gray-600 mb-4">¿Estás seguro de que deseas eliminar este mantenimiento? Esta acción no se puede deshacer.</p>
                
                <div class="flex space-x-2">
                    <button wire:click="$set('showDeleteModal', false)" 
                            class="cursor-pointer flex-1 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg shadow hover:shadow transition-all duration-200 text-xs">
                        Cancelar
                    </button>
                    <button wire:click="deleteMantenimiento" 
                            class="cursor-pointer flex-1 px-3 py-2 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white font-medium rounded-lg shadow hover:shadow transform hover:-translate-y-0.5 transition-all duration-200 text-xs">
                        <i class="fa-solid fa-trash-can mr-1"></i> Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>