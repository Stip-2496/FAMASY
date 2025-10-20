<?php
use App\Models\Herramienta;
use App\Models\Proveedor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;

    public string $search = '';
    public ?string $categoria = null;
    public ?string $estado = null;
    public ?string $stock = null;
    public int $perPage = 10;
    public $showDeleteModal = false;
    public $herramientaToDelete = null;
    public $deleteError = null;

    public function with(): array
    {
        $query = Herramienta::with('proveedor')
            ->activas();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('nomHer', 'like', "%{$this->search}%")
                  ->orWhere('catHer', 'like', "%{$this->search}%")
                  ->orWhere('ubiHer', 'like', "%{$this->search}%");
            });
        }

        if ($this->categoria) {
            $query->byCategoria($this->categoria);
        }

        if ($this->estado) {
            $query->byEstado($this->estado);
        }

        return [
            'herramientas' => $query->paginate($this->perPage),
            'categorias' => [
                'veterinaria' => 'Veterinaria',
                'ganadera' => 'Ganadera',
                'agricola' => 'Agrícola',
                'mantenimiento' => 'Mantenimiento',
                'transporte' => 'Transporte',
                'seguridad' => 'Seguridad'
            ]
        ];
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->categoria = null;
        $this->estado = null;
        $this->stock = null;
        $this->resetPage();
    }

    public function confirmDelete($id): void
    {
        $this->herramientaToDelete = $id;
        $this->deleteError = null;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        try {
            $herramienta = Herramienta::findOrFail($this->herramientaToDelete);
        
            if (!$herramienta->puedeEliminar()) {
                $this->deleteError = 'No se puede eliminar porque ' . $herramienta->razonNoEliminar();
                return;
            }

            $herramienta->delete();
        
            $this->showDeleteModal = false;
            $this->resetPage();
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Herramienta eliminada exitosamente.'
            ]);
        } catch (\Exception $e) {
            $this->deleteError = 'Error al eliminar: ' . $e->getMessage();
        }
    }
}; ?>

@section('title', 'Gestión de herramientas')

<div class="min-h-screen py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-4">
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl border border-white/20 p-4 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-blue-600/5"></div>
                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center space-x-2 mb-3 lg:mb-0">
                        <div class="p-2 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg transform rotate-3 hover:rotate-0 transition-transform duration-300">
                            <div class="w-5 h-5 text-white flex items-center justify-center">
                                    <i class="fas fa-tools text-sm"></i>
                            </div>
                        </div>
                        <div>
                            <h1 class="text-xl font-black bg-gradient-to-r from-gray-900 via-gray-800 to-blue-800 bg-clip-text text-transparent leading-tight">
                                Gestión de Herramientas
                            </h1>
                            <p class="text-gray-600 text-xs">Administra tu inventario de herramientas</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('inventario.herramientas.create') }}" wire:navigate
                           class="group relative inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-600 hover:from-blue-700 hover:to-blue-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                            <svg class="w-4 h-4 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span class="relative z-10 text-xs">Nueva Herramienta</span>
                        </a>
                    </div>
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
                               wire:model.live.debounce.500ms="search"
                               wire:keydown.enter="$set('search', $event.target.value)"
                               wire:change="$set('search', $event.target.value)"
                               placeholder="Buscar por nombre, categoría..."
                               class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="relative">
                        <select wire:model.live="categoria" class="w-full sm:w-40 px-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todas las categorías</option>
                            @foreach($categorias as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="relative">
                        <select wire:model.live="estado" class="w-full sm:w-32 px-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todos los estados</option>
                            <option value="bueno">Bueno</option>
                            <option value="regular">Regular</option>
                            <option value="malo">Malo</option>
                        </select>
                    </div>
                    <div class="relative">
                        <select wire:model.live="stock" class="w-full sm:w-32 px-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todas las existencias</option>
                            <option value="critico">Crítico</option>
                            <option value="bajo">Bajo</option>
                            <option value="normal">Normal</option>
                        </select>
                    </div>
                    @if($search || $categoria || $estado || $stock)
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
            @if($herramientas->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-black">
                        <tr>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">#</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Herramienta</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Proveedor</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Categoría</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Estado</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Ubicación</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Cantidad</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Stock minimo</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Stock maximo</th>
                            <th class="px-2 py-1.5 text-center text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($herramientas as $index => $herramienta)
                        <tr class="hover:bg-gray-50/50 transition-colors duration-200">
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs font-medium text-gray-900">
                                {{ ($herramientas->currentPage() - 1) * $herramientas->perPage() + $index + 1 }}
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    <div class="min-w-0">
                                        <p class="text-xs font-medium text-gray-900">{{ $herramienta->nomHer }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                                <div class="flex items-center gap-1">
                                    <div class="min-w-0">
                                        @if($herramienta->proveedor)
                                            <p class="text-xs font-medium text-gray-900">{{ $herramienta->proveedor->nomProve }}</p>
                                            <p class="text-xs text-gray-500">{{ $herramienta->proveedor->nitProve }}</p>
                                        @else
                                            <p class="text-xs text-gray-400">Sin proveedor</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ ucfirst($herramienta->catHer) }}
                                </span>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap">
                                @php
                                    $estadoClasses = match($herramienta->estHer) {
                                        'bueno' => 'bg-green-100 text-green-800',
                                        'regular' => 'bg-yellow-100 text-yellow-800', 
                                        'malo' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $estadoClasses }}">
                                    {{ ucfirst($herramienta->estHer) }}
                                </span>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                                <div class="flex items-center gap-1">
                                    <span class="truncate max-w-[100px]">{{ $herramienta->ubiHer ?? 'No especificada' }}</span>
                                </div>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                                <div class="flex items-center gap-1">
                                    <p class="text-xs font-medium text-gray-900">{{ $herramienta->canHer ?? 0 }} unidades</p>
                                </div>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                                <div class="flex items-center gap-1">
                                    @if($herramienta->stockMinHer)
                                        <p class="text-xs text-gray-500">Mín: {{ $herramienta->stockMinHer }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                                <div class="flex items-center gap-1">
                                    @if($herramienta->stockMaxHer)
                                        <p class="text-xs text-gray-500">Máx: {{ $herramienta->stockMaxHer }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('inventario.herramientas.show', $herramienta->idHer) }}" wire:navigate
                                       class="bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs font-medium py-1 px-1.5 rounded transition duration-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('inventario.herramientas.edit', $herramienta->idHer) }}" wire:navigate
                                       class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 text-xs font-medium py-1 px-1.5 rounded transition duration-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <button wire:click="confirmDelete({{ $herramienta->idHer }})"
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
            @if($herramientas->hasPages())
            <div class="bg-white px-2 py-1.5 border-t border-gray-200 sm:px-3">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        {{ $herramientas->links() }}
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs text-gray-700">
                                Mostrando <span class="font-medium">{{ $herramientas->firstItem() }}</span> a 
                                <span class="font-medium">{{ $herramientas->lastItem() }}</span> de 
                                <span class="font-medium">{{ $herramientas->total() }}</span> herramientas
                            </p>
                        </div>
                        <div>
                            {{ $herramientas->links() }}
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 mb-1">No se encontraron herramientas</h3>
                    <p class="text-xs text-gray-600 mb-2">
                        @if($search || $categoria || $estado || $stock)
                            No hay resultados para los filtros aplicados. Intenta con otros términos.
                        @else
                            No hay herramientas registradas en el sistema.
                        @endif
                    </p>
                    <a href="{{ route('inventario.herramientas.create') }}" wire:navigate
                       class="cursor-pointer inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-700 hover:to-blue-900 text-white font-medium rounded-lg shadow hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span class="text-xs">Agregar Herramienta</span>
                    </a>
                </div>
            </div>
            @endif
        </div>

        <!-- Modal Eliminar Herramienta -->
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
                    <p class="text-xs text-gray-600 mb-3">¿Está seguro que desea eliminar esta herramienta? Esta acción no se puede deshacer.</p>
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
                    <div class="bg-gray-50 rounded-lg p-2.5 mb-3 text-left">
                        <div class="space-y-1 text-xs">
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Herramienta:</span>
                                <span class="text-gray-900">{{ Herramienta::find($herramientaToDelete)->nomHer ?? '' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Categoría:</span>
                                <span class="text-gray-900">{{ ucfirst(Herramienta::find($herramientaToDelete)->catHer ?? '') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button wire:click="$set('showDeleteModal', false)"
                                class="cursor-pointer flex-1 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg shadow hover:shadow transition-all duration-200 text-xs">
                            Cancelar
                        </button>
                        <button wire:click="delete"
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