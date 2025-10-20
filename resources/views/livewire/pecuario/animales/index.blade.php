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
                        $q->where('espAni', 'like', '%'.$this->search.'%')
                          ->orWhere('nitAni', 'like', '%'.$this->search.'%')
                          ->orWhere('razAni', 'like', '%'.$this->search.'%')
                          ->orWhere('sexAni', 'like', '%'.$this->search.'%')
                          ->orWhere('fecNacAni', 'like', '%'.$this->search.'%')
                          ->orWhere('fecComAni', 'like', '%'.$this->search.'%')
                          ->orWhere('estAni', 'like', '%'.$this->search.'%')
                          ->orWhere('estReproAni', 'like', '%'.$this->search.'%')
                          ->orWhere('estSaludAni', 'like', '%'.$this->search.'%')
                          ->orWhere('ubicacionAni', 'like', '%'.$this->search.'%'); 
                    });
                })
                ->orderBy('espAni', 'asc')
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

<div class="min-h-screen py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @if(session('success'))
            <div class="mb-2 p-2 bg-emerald-50 border border-emerald-200 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="h-3 w-3 text-emerald-500 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-xs text-emerald-800 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-2 p-2 bg-red-50 border border-red-200 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="h-3 w-3 text-red-500 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-xs text-red-800 font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- Header -->
        <div class="mb-4">
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl border border-white/20 p-4 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-blue-600/5"></div>
                
                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center space-x-2 mb-3 lg:mb-0">
                        <div class="p-2 bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg transform rotate-3 hover:rotate-0 transition-transform duration-300">
                            <div class="w-5 h-5 text-white flex items-center justify-center">
                                <i class="fas fa-paw text-base"></i>
                            </div>
                        </div>
                        <div>
                            <h1 class="text-xl font-black bg-gradient-to-r from-gray-900 via-gray-800 to-blue-800 bg-clip-text text-transparent leading-tight">
                                Gestión de Animales
                            </h1>
                            <p class="text-gray-600 text-xs">Administra y controla todos tus animales</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('pecuario.animales.create') }}" wire:navigate
                           class="group relative inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-600 hover:from-blue-700 hover:to-blue-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                            <svg class="w-4 h-4 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span class="relative z-10 text-xs">Nuevo Animal</span>
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
                           placeholder="Buscar por nombre, especie, raza, ID, NIT o ubicación..."
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

        <!-- Tabla -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 overflow-hidden">
            @if($animales->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-black">
                        <tr>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">#</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">NIT</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Especie</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Raza</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Sexo</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Edad</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Ubicación</th>
                            <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Estado</th>
                            <th class="px-2 py-1.5 text-center text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($animales as $index => $animal)
                        <tr class="hover:bg-gray-50/50 transition-colors duration-200">
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs font-medium text-gray-900">
                                {{ ($animales->currentPage() - 1) * $animales->perPage() + $index + 1 }}
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                                <div class="flex items-center gap-1">
                                    <span class="truncate max-w-[100px]">{{ $animal->nitAni ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                                <div class="flex items-center gap-1">
                                        <span class="text-xs text-gray-600">{{ $animal->espAni }}</span>
                                </div>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                                <div class="flex items-center gap-1">
                                    <p class="text-xs text-gray-600">{{ $animal->razAni ?? 'Sin raza' }}</p>
                                </div>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap">
                                @if($animal->sexAni === 'Hembra')
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-medium bg-pink-100 text-pink-600">
                                        Hembra
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-600">
                                        Macho
                                    </span>
                                @endif
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                                <div class="flex items-center gap-1">
                                    <span class="truncate max-w-[80px]">
                                        @if($animal->fecNacAni)
                                            {{ \Carbon\Carbon::parse($animal->fecNacAni)->age }} años
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                                <div class="flex items-center gap-1">
                                    <span class="max-w-[80px]">{{ $animal->ubicacionAni ?? 'No especificada' }}</span>
                                </div>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap">
                                @switch($animal->estAni)
                                    @case('vivo')
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">
                                            Vivo
                                        </span>
                                        @break
                                    @case('muerto')
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">
                                            Muerto
                                        </span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-700">
                                            Vendido
                                        </span>
                                @endswitch
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('pecuario.animales.show', $animal->idAni) }}" wire:navigate
                                       class="bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs font-medium py-1 px-1.5 rounded transition duration-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('pecuario.animales.edit', $animal->idAni) }}" wire:navigate
                                       class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 text-xs font-medium py-1 px-1.5 rounded transition duration-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <button wire:click="confirmDelete({{ $animal->idAni }})"
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
            @if($animales->hasPages())
            <div class="bg-white px-2 py-1.5 border-t border-gray-200 sm:px-3">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        {{ $animales->links() }}
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs text-gray-700">
                                Mostrando <span class="font-medium">{{ $animales->firstItem() }}</span> a 
                                <span class="font-medium">{{ $animales->lastItem() }}</span> de 
                                <span class="font-medium">{{ $animales->total() }}</span> resultados
                            </p>
                        </div>
                        <div>
                            {{ $animales->links() }}
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @else
            <!-- Estado Vacío -->
            <div class="bg-white border border-gray-200 rounded-b-lg shadow-sm">
                <div class="text-center py-4 px-4">
                    <div class="w-8 h-8 bg-gray-100 rounded-full mx-auto mb-2 flex items-center justify-center">
                        <div class="w-4 h-4 text-gray-400 flex items-center justify-center">
                            <i class="fas fa-paw text-base"></i>
                        </div>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 mb-1">No se encontraron animales</h3>
                    <p class="text-xs text-gray-600 mb-2">
                        @if($search)
                            No hay resultados para "{{ $search }}". Intenta con otros términos de búsqueda.
                        @else
                            No hay animales registrados en el sistema.
                        @endif
                    </p>
                    <a href="{{ route('pecuario.animales.create') }}" wire:navigate
                       class="cursor-pointer inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-700 hover:to-blue-900 text-white font-medium rounded-lg shadow hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span class="text-xs">Agregar Animal</span>
                    </a>
                </div>
            </div>
            @endif
        </div>

        <!-- Modal Confirmar Eliminación -->
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
                    <p class="text-xs text-gray-600 mb-3">¿Estás seguro de que deseas eliminar este animal? Esta acción no se puede deshacer.</p>
                    
                    @php
                        $animalToDeleteData = $animales->where('idAni', $animalToDelete)->first();
                    @endphp
                    
                    @if($animalToDeleteData)
                    <div class="bg-gray-50 rounded-lg p-2.5 mb-3 text-left">
                        <div class="space-y-1 text-xs">
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">NIT del animal:</span>
                                <span class="text-gray-900">{{ $animalToDeleteData->nitAni ?? 'Sin nombre' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Especie:</span>
                                <span class="text-gray-900">{{ $animalToDeleteData->espAni ?? '' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Raza:</span>
                                <span class="text-gray-900">{{ $animalToDeleteData->razAni ?? '' }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="flex space-x-2">
                        <button wire:click="$set('showDeleteModal', false)" 
                                class="cursor-pointer flex-1 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg shadow hover:shadow transition-all duration-200 text-xs">
                            Cancelar
                        </button>
                        <button wire:click="deleteAnimal" 
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