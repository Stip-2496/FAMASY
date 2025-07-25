<?php
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
                          ->orWhere('nitProve', 'like', '%'.$this->search.'%')
                          ->orWhere('conProve', 'like', '%'.$this->search.'%')
                          ->orWhere('tipSumProve', 'like', '%'.$this->search.'%');
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

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">Lista de Proveedores</h1>
        <p class="text-gray-600">Gestiona y administra todos tus proveedores</p>
    </div>

    <!-- Barra de búsqueda -->
    <div class="bg-white rounded-lg shadow-md border-2 border-gray-200 p-4 mb-6">
        <div class="flex gap-4 items-end">
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Buscar proveedores</label>
                <input type="text"
                wire:model.live.debounce.500ms="search"
                wire:keydown.enter="$set('search', $event.target.value)"
                wire:change="$set('search', $event.target.value)"
                placeholder="Buscar por nombre, NIT o tipo..."
                class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
            </div>
            <div class="flex gap-2">
                <a href="{{ route('proveedores.create') }}" wire:navigate
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-plus mr-2"></i>Registrar
                </a>
                @if($search)
                <button wire:click="clearSearch"
                    class="cursor-pointer bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-times mr-2"></i>Limpiar
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Tabla de proveedores -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        @if($proveedores->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-green-600">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">NIT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Contacto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Teléfono</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($proveedores as $index => $proveedor)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ ($proveedores->currentPage() - 1) * $proveedores->perPage() + $index + 1 }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $proveedor->nomProve }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $proveedor->nitProve }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $proveedor->conProve ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $proveedor->telProve ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $proveedor->emailProve ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex justify-center space-x-2">
                                <a href="{{ route('proveedores.show', $proveedor->idProve) }}" wire:navigate
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-sm transition duration-200">
                                    Detalles
                                </a>
                                <a href="{{ route('proveedores.edit', $proveedor->idProve) }}" wire:navigate
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1 px-3 rounded text-sm transition duration-200">
                                    Editar
                                </a>
                                <button wire:click="confirmDelete({{ $proveedor->idProve }})"
                                    class="cursor-pointer bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm transition duration-200">
                                    Eliminar
                                </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="px-6 py-4 bg-gray-50">
            {{ $proveedores->links() }}
        </div>
        @else
        <div class="px-6 py-12 text-center">
            <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No hay proveedores registrados</h3>
            <p class="text-gray-500 mb-4">Comience agregando su primer proveedor.</p>
            <a href="{{ route('proveedores.create') }}" wire:navigate
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                <i class="fas fa-plus mr-2"></i>Agregar Proveedor
            </a>
        </div>
        @endif
    </div>

    <!-- Modal Eliminar Proveedor -->
@if($showDeleteModal)
    <div class="fixed inset-0 bg-black/20 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg max-w-sm w-full">
            <h2 class="text-xl font-semibold mb-4">Confirmar eliminación</h2>
            <p class="mb-4 text-sm text-gray-600">
                ¿Está seguro que desea eliminar este proveedor? Esta acción no se puede deshacer.
            </p>
            
            <div class="mb-4">
                <p><strong>Proveedor:</strong> {{ Proveedor::find($proveedorToDelete)->nomProve ?? '' }}</p>
                <p><strong>NIT:</strong> {{ Proveedor::find($proveedorToDelete)->nitProve ?? '' }}</p>
            </div>
            
            <div class="flex justify-end gap-3">
                <button wire:click="$set('showDeleteModal', false)"
                        class="cursor-pointer px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">Cancelar</button>
                <button wire:click="deleteProveedor"
                        class="cursor-pointer px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">Confirmar Eliminación</button>
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