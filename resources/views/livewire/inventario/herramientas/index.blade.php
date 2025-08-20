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

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Gestión de Herramientas
                    </h1>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('inventario.herramientas.create') }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Nueva Herramienta
                    </a>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4">
                <form wire:submit.prevent>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                            <input type="text" 
                                   wire:model.live.debounce.500ms="search"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Nombre, categoría...">
                        </div>
                        <div>
                            <label for="categoria" class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                            <select wire:model.live="categoria" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Todas</option>
                                @foreach($categorias as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select wire:model.live="estado" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Todos</option>
                                <option value="bueno">Bueno</option>
                                <option value="regular">Regular</option>
                                <option value="malo">Malo</option>
                            </select>
                        </div>
                        <div>
                            <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">Existencias</label>
                            <select wire:model.live="stock" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Todos</option>
                                <option value="critico">Crítico</option>
                                <option value="bajo">Bajo</option>
                                <option value="normal">Normal</option>
                            </select>
                        </div>
                        <div class="flex items-end space-x-2">
                            <button type="button" 
                                    wire:click="clearFilters"
                                    class="cursor-pointer px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out">
                                Limpiar
                            </button>
                        </div>
                    </div>
                </form>
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
                        Lista de Herramientas
                    </h3>
                    <span class="bg-blue-100 text-blue-800 text-sm font-medium px-2.5 py-0.5 rounded">
                        {{ $herramientas->total() }} herramientas
                    </span>
                </div>
            </div>

            @if($herramientas->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Herramienta</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Proveedor</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Ubicación</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Existencias</th>
                                <th class="px-3 py-2 text-center font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($herramientas as $herramienta)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 whitespace-nowrap text-gray-900">{{ $herramienta->idHer }}</td>
                                
                                <!-- Columna Herramienta -->
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-2">
                                            <div class="font-medium text-gray-900">{{ $herramienta->nomHer }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Columna Proveedor -->
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-6 h-6 bg-orange-100 rounded-full flex items-center justify-center">
                                            <svg class="w-3 h-3 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-2">
                                            @if($herramienta->proveedor)
                                                <div class="font-medium text-gray-900">{{ $herramienta->proveedor->nomProve }}</div>
                                                <div class="text-gray-500">{{ $herramienta->proveedor->nitProve }}</div>
                                            @else
                                                <div class="text-gray-400">Sin proveedor</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Categoría -->
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xxs font-medium bg-blue-100 text-blue-800">
                                        {{ ucfirst($herramienta->catHer) }}
                                    </span>
                                </td>

                                <!-- Estado -->
                                <td class="px-3 py-2 whitespace-nowrap">
                                    @php
                                        $estadoClasses = match($herramienta->estHer) {
                                            'bueno' => 'bg-green-100 text-green-800',
                                            'regular' => 'bg-yellow-100 text-yellow-800', 
                                            'malo' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xxs font-medium {{ $estadoClasses }}">
                                        {{ ucfirst($herramienta->estHer) }}
                                    </span>
                                </td>

                                <!-- Ubicación -->
                                <td class="px-3 py-2 whitespace-nowrap text-gray-900">
                                    {{ $herramienta->ubiHer ?? 'No especificada' }}
                                </td>

                                <!-- Existencias -->
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <div class="text-gray-900">
                                        @php
                                            // Calcular stock actual desde movimientos de inventario si existe
                                            $stockActual = 0; // Este debería calcularse desde los movimientos
                                        @endphp
                                        <div class="font-medium">{{ $stockActual }} unidades</div>
                                        @if($herramienta->stockMinHer)
                                            <div class="text-gray-500">Mín: {{ $herramienta->stockMinHer }}</div>
                                        @endif
                                        @if($herramienta->stockMaxHer)
                                            <div class="text-gray-500">Máx: {{ $herramienta->stockMaxHer }}</div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Acciones -->
                                <td class="px-3 py-2 whitespace-nowrap font-medium">
                                    <div class="flex space-x-1">
                                        <a href="{{ route('inventario.herramientas.show', $herramienta->idHer) }}" wire:navigate
                                           class="text-blue-600 hover:text-blue-900" title="Ver detalles">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        <a href="{{ route('inventario.herramientas.edit', $herramienta->idHer) }}" wire:navigate
                                           class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                        <button wire:click="confirmDelete({{ $herramienta->idHer }})"
                                                class="cursor-pointer text-red-600 hover:text-red-900" title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No hay herramientas registradas</h3>
                    <p class="mt-1 text-sm text-gray-500">Comienza agregando tu primera herramienta al inventario.</p>
                    <div class="mt-6">
                        <a href="{{ route('inventario.herramientas.create') }}" wire:navigate
                           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Agregar Herramienta
                        </a>
                    </div>
                </div>
            @endif

            @if($herramientas->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            {{ $herramientas->links() }}
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
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
        </div>
    </div>

    <!-- Modal de Confirmación -->
    @if($showDeleteModal)
    <div class="fixed inset-0 bg-black/20 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Confirmar eliminación</h3>
                <button wire:click="$set('showDeleteModal', false)" class="text-gray-500 hover:text-gray-700">
                    <svg class="cursor-pointer w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <p class="text-gray-600 mb-4">¿Estás seguro de que deseas eliminar esta herramienta?</p>
            
            @if($deleteError)
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">{{ $deleteError }}</p>
                    </div>
                </div>
            </div>
            @endif
            
            <div class="flex justify-end space-x-3">
                <button wire:click="$set('showDeleteModal', false)" 
                        class="cursor-pointer px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg">
                    Cancelar
                </button>
                <button wire:click="delete" 
                        class="cursor-pointer px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                    Eliminar
                </button>
            </div>
        </div>
    </div>
    @endif
</div>