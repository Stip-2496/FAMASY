<?php
use App\Models\Animal;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;
    public $showDeleteModal = false;
    public $animalToDelete = null;

    public function with(): array
    {
        return [
            'animales' => Animal::query()
                ->when($this->search, function ($query) {
                    $query->where(function($q) {
                        $q->where('nomAni', 'like', '%'.$this->search.'%')
                          ->orWhere('espAni', 'like', '%'.$this->search.'%')
                          ->orWhere('razAni', 'like', '%'.$this->search.'%')
                          ->orWhere('idAni', 'like', '%'.$this->search.'%');
                    });
                })
                ->orderBy('nomAni', 'asc')
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
        $this->animalToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteAnimal(): void
    {
        try {
            $animal = Animal::findOrFail($this->animalToDelete);
            $animal->delete();
            
            $this->showDeleteModal = false;
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Animal eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar el animal: ' . $e->getMessage()
            ]);
        } finally {
            $this->animalToDelete = null;
        }
    }
}; ?>

@section('title', 'Dashboard pecuario')

<div class="px-6 py-4">
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gestión de Animales</h1>
        <a href="{{ route('pecuario.animales.create') }}" wire:navigate
           class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded shadow inline-flex items-center">
            <i class="fas fa-plus mr-2"></i> Nuevo Animal
        </a>
    </div>

    <!-- Tarjeta contenedora -->
    <div class="bg-white shadow rounded-lg border border-green-400">
        <div class="bg-green-500 text-white px-4 py-2 rounded-t-lg">
            <h2 class="text-lg font-semibold">Listado de Animales</h2>
        </div>

        <div class="p-4">
            <!-- Barra de búsqueda -->
            <div class="mb-4">
                <div class="flex">
                    <input type="text" wire:model.live.debounce.500ms="search" 
                           placeholder="Buscar por nombre o ID..."
                           class="w-full rounded-l border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                    @if($search)
                    <button wire:click="clearSearch"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 rounded-r">
                        <i class="fas fa-times mr-1"></i> Limpiar
                    </button>
                    @else
                    <button class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 rounded-r">
                        <i class="fas fa-search mr-1"></i> Buscar
                    </button>
                    @endif
                </div>
            </div>

            <!-- Tabla -->
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200 rounded">
                    <thead class="bg-green-100 text-green-800 text-sm uppercase">
                        <tr>
                            <th class="px-4 py-2 text-center border">ID</th>
                            <th class="px-4 py-2 text-left border">Nombre</th>
                            <th class="px-4 py-2 text-left border">Especie / Raza</th>
                            <th class="px-4 py-2 text-center border">Sexo</th>
                            <th class="px-4 py-2 text-center border">Edad</th>
                            <th class="px-4 py-2 text-center border">Estado</th>
                            <th class="px-4 py-2 text-center border">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($animales as $animal)
                            <tr class="border-t hover:bg-gray-50">
                                <td class="px-4 py-2 text-center">{{ $animal->idAni }}</td>
                                <td class="px-4 py-2">{{ $animal->nomAni ?? 'N/A' }}</td>
                                <td class="px-4 py-2">
                                    <div class="font-medium">{{ $animal->espAni }}</div>
                                    <div class="text-sm text-gray-500">{{ $animal->razAni ?? 'Sin raza' }}</div>
                                </td>
                                <td class="px-4 py-2 text-center">
                                    @if($animal->sexAni === 'Hembra')
                                        <span class="bg-pink-500 text-white px-2 py-1 rounded text-xs">Hembra</span>
                                    @else
                                        <span class="bg-blue-500 text-white px-2 py-1 rounded text-xs">Macho</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-center">
                                    @if($animal->fecNacAni)
                                        {{ \Carbon\Carbon::parse($animal->fecNacAni)->age }} años
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-center">
                                    @switch($animal->estAni)
                                        @case('vivo')
                                            <span class="bg-green-500 text-white px-2 py-1 rounded text-xs">Vivo</span>
                                            @break
                                        @case('muerto')
                                            <span class="bg-red-500 text-white px-2 py-1 rounded text-xs">Muerto</span>
                                            @break
                                        @default
                                            <span class="bg-yellow-400 text-black px-2 py-1 rounded text-xs">Vendido</span>
                                    @endswitch
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <div class="flex justify-center gap-2 flex-wrap">
                                        <a href="{{ route('pecuario.animales.show', $animal->idAni) }}" wire:navigate
                                           class="inline-flex items-center bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs font-medium">
                                            <i class="fas fa-eye mr-1"></i> Ver
                                        </a>
                                        <a href="{{ route('pecuario.animales.edit', $animal->idAni) }}" wire:navigate
                                           class="inline-flex items-center bg-yellow-400 hover:bg-yellow-500 text-black px-3 py-1 rounded text-xs font-medium">
                                            <i class="fas fa-edit mr-1"></i> Editar
                                        </a>
                                        <button wire:click="confirmDelete({{ $animal->idAni }})"
                                                class="inline-flex items-center bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-medium">
                                            <i class="fas fa-trash-alt mr-1"></i> Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-gray-500 py-4">No se encontraron animales registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="mt-4">
                {{ $animales->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Eliminar Animal -->
@if($showDeleteModal)
    <div class="fixed inset-0 bg-black/20 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg max-w-sm w-full">
            <h2 class="text-xl font-semibold mb-4">Confirmar eliminación</h2>
            <p class="mb-4 text-sm text-gray-600">
                ¿Está seguro que desea eliminar este animal? Esta acción no se puede deshacer.
            </p>
            
            <div class="mb-4">
                <p><strong>Animal:</strong> {{ Animal::find($animalToDelete)->nomAni ?? 'Sin nombre' }}</p>
                <p><strong>Especie:</strong> {{ Animal::find($animalToDelete)->espAni ?? '' }}</p>
            </div>
            
            <div class="flex justify-end gap-3">
                <button wire:click="$set('showDeleteModal', false)"
                        class="cursor-pointer px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">Cancelar</button>
                <button wire:click="deleteAnimal"
                        class="cursor-pointer px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">Confirmar Eliminación</button>
            </div>
        </div>
    </div>
@endif