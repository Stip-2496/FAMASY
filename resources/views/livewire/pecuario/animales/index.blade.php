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
                        $q->where('ideAni', 'like', '%'.$this->search.'%')
                          ->orWhere('espAni', 'like', '%'.$this->search.'%')
                          ->orWhere('razAni', 'like', '%'.$this->search.'%')
                          ->orWhere('idAni', 'like', '%'.$this->search.'%')
                          ->orWhere('nitAni', 'like', '%'.$this->search.'%') // ✅ Busqueda por NIT
                          ->orWhere('ubicacionAni', 'like', '%'.$this->search.'%'); // ✅ Busqueda por ubicación
                    });
                })
                ->orderBy('ideAni', 'asc')
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

@section('title', 'Gestión de Animales')

<div class="container mx-auto px-4 py-6">
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-2 flex items-center justify-center">
            <i class="fas fa-paw mr-3 text-green-600"></i>Gestión de Animales
        </h1>
        <p class="text-gray-600 flex items-center justify-center">
            <i class="fas fa-tasks mr-2 text-green-500"></i>Administra y controla todos tus animales
        </p>
    </div>

    <div class="bg-white rounded-lg shadow-md border-2 border-gray-200 p-4 mb-6">
        <div class="flex flex-col md:flex-row gap-4 items-end justify-between">
            <div class="flex-1 w-full md:w-auto">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                    <i class="fas fa-search mr-2 text-gray-500"></i>Buscar animales
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-filter text-gray-400"></i>
                    </div>
                    <input type="text"
                    wire:model.live.debounce.500ms="search"
                    wire:keydown.enter="$set('search', $event.target.value)"
                    wire:change="$set('search', $event.target.value)"
                    placeholder="Buscar por nombre, especie, raza, ID, NIT o ubicación..."
                    class="w-full pl-10 pr-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                </div>
            </div>
            <div class="flex flex-col md:flex-row gap-2 w-full md:w-auto mt-4 md:mt-0">
                <a href="{{ route('pecuario.animales.create') }}" wire:navigate
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 text-center flex items-center justify-center">
                    <i class="fas fa-plus-circle mr-2"></i>Registrar
                </a>
                @if($search)
                <button wire:click="clearSearch"
                    class="cursor-pointer bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200 text-center flex items-center justify-center">
                    <i class="fas fa-times-circle mr-2"></i>Limpiar
                </button>
                @endif
                <a href="{{ route('pecuario.dashboard') }}" wire:navigate
                   class="bg-green-700 hover:bg-green-800 text-white font-bold py-2 px-4 rounded-lg transition duration-200 text-center flex items-center justify-center">
                    <i class="fas fa-arrow-left mr-2"></i>Volver
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        @if($animales->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-green-600">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                            <i class="fas fa-hashtag mr-1"></i>#
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                            <i class="fas fa-id-card mr-1"></i>ID
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                            <i class="fas fa-address-card mr-1"></i>NIT
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                            <i class="fas fa-signature mr-1"></i>Nombre
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                            <i class="fas fa-dna mr-1"></i>Especie/Raza
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                            <i class="fas fa-venus-mars mr-1"></i>Sexo
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                            <i class="fas fa-birthday-cake mr-1"></i>Edad
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                            <i class="fas fa-map-marker-alt mr-1"></i>Ubicación
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">
                            <i class="fas fa-heartbeat mr-1"></i>Estado
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">
                            <i class="fas fa-cogs mr-1"></i>Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($animales as $index => $animal)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 flex items-center">
                                <i class="fas fa-list-ol mr-2 text-gray-400"></i>{{ ($animales->currentPage() - 1) * $animales->perPage() + $index + 1 }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 flex items-center">
                                <i class="fas fa-tag mr-2 text-blue-400"></i>{{ $animal->idAni }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 flex items-center">
                                <i class="fas fa-id-badge mr-2 text-purple-400"></i>{{ $animal->nitAni ?? 'N/A' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 flex items-center">
                                <i class="fas fa-file-signature mr-2 text-green-400"></i>{{ $animal->ideAni ?? 'Sin nombre' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 flex items-center">
                                <i class="fas fa-dove mr-2 text-yellow-500"></i>{{ $animal->espAni }}
                            </div>
                            <div class="text-sm text-gray-500 flex items-center pl-5 mt-1">
                                <i class="fas fa-arrow-right mr-2 text-gray-400 text-xs"></i>{{ $animal->razAni ?? 'Sin raza' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($animal->sexAni === 'Hembra')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
                                    <i class="fas fa-venus mr-1"></i>Hembra
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-mars mr-1"></i>Macho
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 flex items-center">
                                <i class="fas fa-clock mr-2 text-gray-400"></i>
                                @if($animal->fecNacAni)
                                    {{ \Carbon\Carbon::parse($animal->fecNacAni)->age }} años
                                @else
                                    N/A
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 flex items-center">
                                <i class="fas fa-map-pin mr-2 text-red-400"></i>{{ $animal->ubicacionAni ?? 'No especificada' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @switch($animal->estAni)
                                @case('vivo')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-heart mr-1"></i>Vivo
                                    </span>
                                    @break
                                @case('muerto')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-skull-crossbones mr-1"></i>Muerto
                                    </span>
                                    @break
                                @default
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-money-bill-wave mr-1"></i>Vendido
                                    </span>
                            @endswitch
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex justify-center space-x-2">
                                <a href="{{ route('pecuario.animales.show', $animal->idAni) }}" wire:navigate
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-sm transition duration-200 flex items-center">
                                    <i class="fas fa-eye mr-1"></i>Detalles
                                </a>
                                <a href="{{ route('pecuario.animales.edit', $animal->idAni) }}" wire:navigate
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1 px-3 rounded text-sm transition duration-200 flex items-center">
                                    <i class="fas fa-edit mr-1"></i>Editar
                                </a>
                                <button wire:click="confirmDelete({{ $animal->idAni }})"
                                    class="cursor-pointer bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm transition duration-200 flex items-center">
                                    <i class="fas fa-trash-alt mr-1"></i>Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 bg-gray-50 flex items-center justify-between">
            <div class="flex items-center text-sm text-gray-700">
                <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                Mostrando {{ $animales->firstItem() }} a {{ $animales->lastItem() }} de {{ $animales->total() }} resultados
            </div>
            {{ $animales->links() }}
        </div>
        @else
        <div class="px-6 py-12 text-center">
            <i class="fas fa-paw text-gray-400 text-6xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No hay animales registrados</h3>
            <p class="text-gray-500 mb-4">Comience agregando su primer animal.</p>
            <a href="{{ route('pecuario.animales.create') }}" wire:navigate
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 inline-flex items-center">
                <i class="fas fa-plus-circle mr-2"></i>Agregar Animal
            </a>
        </div>
        @endif
    </div>

    @if($showDeleteModal)
        <div class="fixed inset-0 bg-black/20 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg max-w-sm w-full">
                <div class="flex items-center mb-4">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl mr-2"></i>
                    <h2 class="text-xl font-semibold">Confirmar eliminación</h2>
                </div>
                <p class="mb-4 text-sm text-gray-600">
                    ¿Está seguro que desea eliminar este animal? Esta acción no se puede deshacer.
                </p>
                
                <div class="mb-4 p-3 bg-gray-100 rounded-md">
                    <p class="flex items-center"><i class="fas fa-signature text-blue-500 mr-2"></i><strong>Animal:</strong> {{ Animal::find($animalToDelete)->ideAni ?? 'Sin nombre' }}</p>
                    <p class="flex items-center mt-1"><i class="fas fa-dove text-yellow-500 mr-2"></i><strong>Especie:</strong> {{ Animal::find($animalToDelete)->espAni ?? '' }}</p>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button wire:click="$set('showDeleteModal', false)"
                            class="cursor-pointer px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition flex items-center">
                        <i class="fas fa-times mr-2"></i>Cancelar
                    </button>
                    <button wire:click="deleteAnimal"
                            class="cursor-pointer px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition flex items-center">
                        <i class="fas fa-trash mr-2"></i>Eliminar
                    </button>
                </div>
            </div>
        </div>
    @endif

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