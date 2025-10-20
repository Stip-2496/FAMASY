<?php
// resources/views/livewire/proveedores/index.blade.php

use App\Models\Proveedor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;
    public $showDeleteModal = false;
    public $proveedorToDelete = null;

    public function with(): array
{
    return [
        'proveedores' => Proveedor::query()
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('nomProve', 'like', '%'.$this->search.'%')
                      ->orWhere('apeProve', 'like', '%'.$this->search.'%')
                      ->orWhere('nitProve', 'like', '%'.$this->search.'%')
                      ->orWhere('conProve', 'like', '%'.$this->search.'%')
                      ->orWhere('telProve', 'like', '%'.$this->search.'%')
                      ->orWhere('emailProve', 'like', '%'.$this->search.'%')
                      ->orWhere('dirProve', 'like', '%'.$this->search.'%')
                      ->orWhere('ciuProve', 'like', '%'.$this->search.'%')
                      ->orWhere('depProve', 'like', '%'.$this->search.'%')
                      ->orWhere('tipSumProve', 'like', '%'.$this->search.'%')
                      ->orWhere('obsProve', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('nomProve', 'asc')
            ->paginate($this->perPage)
    ];
}

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function updatedSearch($value): void
{
    $this->resetPage();
}

    public function confirmDelete($id): void
    {
        $this->proveedorToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteProveedor(): void
    {
        try {
            $proveedor = Proveedor::findOrFail($this->proveedorToDelete);
            $proveedor->delete();
            
            $this->showDeleteModal = false;
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Proveedor eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar el proveedor: ' . $e->getMessage()
            ]);
        } finally {
            $this->proveedorToDelete = null;
        }
    }
}; ?>

@section('title', 'Proveedores')

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
                                <i class="fas fa-truck text-base"></i>
                            </div>
                        </div>
                        <div>
                            <h1 class="text-xl font-black bg-gradient-to-r from-gray-900 via-gray-800 to-blue-800 bg-clip-text text-transparent leading-tight">
                                Lista de Proveedores
                            </h1>
                            <p class="text-gray-600 text-xs">Gestiona y administra todos tus proveedores</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('proveedores.create') }}" wire:navigate
                           class="group relative inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-600 hover:from-blue-700 hover:to-blue-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                            <svg class="w-4 h-4 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span class="relative z-10 text-xs">Nuevo Proveedor</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barra de búsqueda -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-3 mb-3">
            <div class="flex flex-col sm:flex-row gap-2">
                <div class="flex-1 relative">
                    <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input type="text"
                           wire:model.live.debounce.500ms="search"
                           wire:keydown.enter="$set('search', $event.target.value)"
                           wire:change="$set('search', $event.target.value)"
                           placeholder="Buscar por nombre, NIT o tipo..."
                           class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                @if($search)
                <button wire:click="clearSearch"
                        class="cursor-pointer inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-medium rounded-lg shadow hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span class="text-xs">Limpiar</span>
                </button>
                @endif
            </div>
        </div>

        <!-- Tabla de proveedores -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 overflow-hidden">
            @if($proveedores->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-black">
                        <tr>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">#</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Nombre</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">NIT</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Dirección</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Teléfono</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Email</th>
                            <th class="px-2 py-1.5 text-center text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($proveedores as $index => $proveedor)
                        <tr class="hover:bg-gray-50/50 transition-colors duration-200">
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs font-medium text-gray-900">
                                {{ ($proveedores->currentPage() - 1) * $proveedores->perPage() + $index + 1 }}
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    <div class="min-w-0">
                                        <p class="text-xs font-medium text-gray-900">{{ $proveedor->nomProve }} {{ $proveedor->apeProve }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                                <div class="flex items-center gap-1">
                                    <span class="truncate max-w-[100px]">{{ $proveedor->nitProve }}</span>
                                </div>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                                <div class="flex items-center gap-1">
                                    <span class="truncate max-w-[100px]">{{ $proveedor->dirProve ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                                <div class="flex items-center gap-1">
                                    <span class="truncate max-w-[80px]">{{ $proveedor->telProve ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                                <div class="flex items-center gap-1">
                                    <span class="text-xs text-gray-700">{{ $proveedor->emailProve ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('proveedores.show', $proveedor->idProve) }}" wire:navigate
                                       class="bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs font-medium py-1 px-1.5 rounded transition duration-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('proveedores.edit', $proveedor->idProve) }}" wire:navigate
                                       class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 text-xs font-medium py-1 px-1.5 rounded transition duration-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <button wire:click="confirmDelete({{ $proveedor->idProve }})"
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
            @if($proveedores->hasPages())
            <div class="bg-white px-2 py-1.5 border-t border-gray-200 sm:px-3">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        {{ $proveedores->links() }}
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs text-gray-700">
                                Mostrando <span class="font-medium">{{ $proveedores->firstItem() }}</span> a 
                                <span class="font-medium">{{ $proveedores->lastItem() }}</span> de 
                                <span class="font-medium">{{ $proveedores->total() }}</span> proveedores
                            </p>
                        </div>
                        <div>
                            {{ $proveedores->links() }}
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 mb-1">No se encontraron proveedores</h3>
                    <p class="text-xs text-gray-600 mb-2">
                        @if($search)
                            No hay resultados para "{{ $search }}". Intenta con otros términos de búsqueda.
                        @else
                            No hay proveedores registrados en el sistema.
                        @endif
                    </p>
                    <a href="{{ route('proveedores.create') }}" wire:navigate
                       class="cursor-pointer inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-700 hover:to-blue-900 text-white font-medium rounded-lg shadow hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span class="text-xs">Registrar Proveedor</span>
                    </a>
                </div>
            </div>
            @endif
        </div>

        <!-- Modal Eliminar Proveedor -->
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
                    <p class="text-xs text-gray-600 mb-3">¿Está seguro que desea eliminar este proveedor? Esta acción no se puede deshacer.</p>
                    <div class="bg-gray-50 rounded-lg p-2.5 mb-3 text-left">
                        <div class="space-y-1 text-xs">
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Proveedor:</span>
                                <span class="text-gray-900">{{ Proveedor::find($proveedorToDelete)->nomProve ?? '' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">NIT:</span>
                                <span class="text-gray-900">{{ Proveedor::find($proveedorToDelete)->nitProve ?? '' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button wire:click="$set('showDeleteModal', false)"
                                class="flex-1 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg shadow hover:shadow transition-all duration-200 text-xs">
                            Cancelar
                        </button>
                        <button wire:click="deleteProveedor"
                                class="flex-1 px-3 py-1.5 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white font-medium rounded-lg shadow hover:shadow transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center text-xs">
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