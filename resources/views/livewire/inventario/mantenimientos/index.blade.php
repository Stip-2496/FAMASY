<?php
use App\Models\Mantenimiento;
use App\Models\Herramienta;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;

    public string $search = '';
    public string $filtroEstado = '';
    public string $filtroTipo = '';
    public string $buscarHerramienta = '';
    public $showCompletarModal = false;
    public $mantenimientoToComplete = null;
    public $resultado = '';
    public $observaciones = '';

    public function with(): array
    {
        return [
            'mantenimientos' => Mantenimiento::query()
                ->with('herramienta')
                ->when($this->filtroEstado, function ($query) {
                    $query->where('estMan', $this->filtroEstado);
                })
                ->when($this->filtroTipo, function ($query) {
                    $query->where('tipMan', $this->filtroTipo);
                })
                ->when($this->buscarHerramienta, function ($query) {
                    $query->whereHas('herramienta', function($q) {
                        $q->where('nomHer', 'like', '%'.$this->buscarHerramienta.'%');
                    })->orWhere('nomHerMan', 'like', '%'.$this->buscarHerramienta.'%');
                })
                ->orderBy('fecMan', 'desc')
                ->paginate(10),
            'estadisticas' => [
                'pendientes' => Mantenimiento::where('estMan', 'pendiente')->count(),
                'en_proceso' => Mantenimiento::where('estMan', 'en proceso')->count(),
                'completados' => Mantenimiento::where('estMan', 'completado')->count(),
                'total' => Mantenimiento::count()
            ]
        ];
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['filtroEstado', 'filtroTipo', 'buscarHerramienta']);
        $this->resetPage();
    }

    public function confirmCompletar($id): void
    {
        $this->mantenimientoToComplete = $id;
        $this->showCompletarModal = true;
    }

    public function completarMantenimiento(): void
    {
        $this->validate([
            'resultado' => 'required|string|max:100',
            'observaciones' => 'nullable|string'
        ]);

        try {
            $mantenimiento = Mantenimiento::findOrFail($this->mantenimientoToComplete);
            $mantenimiento->update([
                'estMan' => 'completado',
                'resMan' => $this->resultado,
                'obsMan' => $this->observaciones
            ]);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Mantenimiento completado exitosamente'
            ]);
            
            $this->reset(['showCompletarModal', 'mantenimientoToComplete', 'resultado', 'observaciones']);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al completar mantenimiento: ' . $e->getMessage()
            ]);
        }
    }
}; ?>

<div>
    <!-- Header -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                    <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Mantenimientos
                </h1>
                <p class="mt-1 text-sm text-gray-600">Gestión y seguimiento de mantenimientos de herramientas</p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-3">
                <a href="{{ route('inventario.mantenimientos.create') }}" wire:navigate
                   class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Programar Mantenimiento
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros y búsqueda -->
    <div class="mb-6 bg-white p-6 rounded-lg shadow">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Filtro por Estado -->
            <div>
                <label for="filtro_estado" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                <select 
                    wire:model.live="filtroEstado"
                    id="filtro_estado" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">Todos los estados</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="en proceso">En Proceso</option>
                    <option value="completado">Completado</option>
                </select>
            </div>

            <!-- Filtro por Tipo -->
            <div>
                <label for="filtro_tipo" class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                <select 
                    wire:model.live="filtroTipo"
                    id="filtro_tipo" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">Todos los tipos</option>
                    <option value="preventivo">Preventivo</option>
                    <option value="correctivo">Correctivo</option>
                    <option value="predictivo">Predictivo</option>
                </select>
            </div>

            <!-- Búsqueda por herramienta -->
            <div>
                <label for="buscar" class="block text-sm font-medium text-gray-700 mb-2">Buscar herramienta</label>
                <input 
                    wire:model.live.debounce.500ms="buscarHerramienta"
                    type="text" 
                    id="buscar" 
                    placeholder="Nombre de herramienta..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>

            <!-- Botón limpiar filtros -->
            <div class="flex items-end">
                <button 
                    wire:click="limpiarFiltros"
                    class="w-full px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out">
                    Limpiar Filtros
                </button>
            </div>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Pendientes</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $estadisticas['pendientes'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">En Proceso</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $estadisticas['en_proceso'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Completados</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $estadisticas['completados'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $estadisticas['total'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de mantenimientos -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-6 py-4 bg-green-600">
            <h3 class="text-lg font-medium text-white">Lista de Mantenimientos</h3>
        </div>
        
        @if($mantenimientos->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Herramienta
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Fecha
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tipo
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Descripción
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($mantenimientos as $mantenimiento)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $mantenimiento->herramienta->nomHer ?? $mantenimiento->nomHerMan ?? 'Sin herramienta' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $mantenimiento->herramienta->catHer ?? '' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $mantenimiento->fecMan->format('d/m/Y') }}</div>
                            <div class="text-sm text-gray-500">{{ $mantenimiento->fecMan->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $tipoColors = [
                                    'preventivo' => 'bg-green-100 text-green-800',
                                    'correctivo' => 'bg-red-100 text-red-800',
                                    'predictivo' => 'bg-blue-100 text-blue-800'
                                ];
                                $colorClass = $tipoColors[$mantenimiento->tipMan] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                {{ ucfirst($mantenimiento->tipMan) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $estadoColors = [
                                    'pendiente' => 'bg-red-100 text-red-800',
                                    'en proceso' => 'bg-yellow-100 text-yellow-800',
                                    'completado' => 'bg-green-100 text-green-800'
                                ];
                                $colorClass = $estadoColors[$mantenimiento->estMan] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                {{ ucfirst($mantenimiento->estMan) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 max-w-xs truncate">
                                {{ $mantenimiento->desMan ?? 'Sin descripción' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('inventario.mantenimientos.show', $mantenimiento) }}" wire:navigate
                                   class="text-green-600 hover:text-green-900" title="Ver detalles">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                
                                @if($mantenimiento->estMan !== 'completado')
                                <a href="{{ route('inventario.mantenimientos.edit', $mantenimiento) }}" wire:navigate
                                   class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                @endif

                                @if($mantenimiento->estMan === 'en proceso')
                                <button wire:click="confirmCompletar({{ $mantenimiento->idMan }})" 
                                        class="text-green-600 hover:text-green-900" title="Completar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </button>
                                @endif

                                @if($mantenimiento->estMan === 'pendiente')
                                <form  method="POST" 
                                      onsubmit="return confirm('¿Está seguro de eliminar este mantenimiento?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            {{ $mantenimientos->links() }}
        </div>
        @else
        <div class="px-6 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay mantenimientos</h3>
            <p class="mt-1 text-sm text-gray-500">Comienza programando tu primer mantenimiento.</p>
            <div class="mt-6">
                <a href="{{ route('inventario.mantenimientos.create') }}" wire:navigate
                   class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Programar Mantenimiento
                </a>
            </div>
        </div>
        @endif
    </div>

    <!-- Modal Completar Mantenimiento -->
    @if($showCompletarModal)
        <div class="fixed inset-0 bg-black/20 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg max-w-sm w-full">
                <h2 class="text-xl font-semibold mb-4">Completar Mantenimiento</h2>
                
                <div class="mb-4">
                    <label for="resultado" class="block text-sm font-medium text-gray-700 mb-1">Resultado</label>
                    <input 
                        wire:model="resultado"
                        type="text" 
                        id="resultado" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="Ingrese el resultado del mantenimiento">
                    @error('resultado') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div class="mb-4">
                    <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                    <textarea 
                        wire:model="observaciones"
                        id="observaciones" 
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="Observaciones adicionales (opcional)"></textarea>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button wire:click="$set('showCompletarModal', false)"
                            class="cursor-pointer px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">Cancelar</button>
                    <button wire:click="completarMantenimiento"
                            class="cursor-pointer px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">Confirmar</button>
                </div>
            </div>
        </div>
    @endif
</div>