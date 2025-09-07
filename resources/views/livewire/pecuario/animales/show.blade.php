<?php
use App\Models\Animal;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public Animal $animal;
    public $showDeleteModal = false;

    public function mount(Animal $animal)
    {
        $this->animal = $animal;
    }

    public function confirmDelete(): void
    {
        $this->showDeleteModal = true;
    }

    public function deleteAnimal(): void
    {
        try {
            $this->animal->delete();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Animal eliminado correctamente'
            ]);

            $this->redirect(route('pecuario.animales.index'), navigate: true);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar el animal: ' . $e->getMessage()
            ]);
        }
    }
}; ?>

@section('title', 'Detalles del Animal')

<div class="container mx-auto px-4 py-6">
    <!-- Header simplificado -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-paw text-green-600"></i>
                {{ $animal->ideAni ?? 'Animal #' . $animal->idAni }}
            </h1>
            <p class="text-sm text-gray-600 mt-1">ID #{{ $animal->idAni }}</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('pecuario.animales.edit', $animal->idAni) }}" wire:navigate
                class="bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-2 px-4 rounded transition duration-200">
                <i class="fas fa-edit mr-2"></i>Editar
            </a>
            <a href="{{ route('pecuario.animales.index') }}" wire:navigate
                class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </div>

    <!-- Información Actual -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-medium text-gray-900">
                <i class="fas fa-info-circle text-blue-500 mr-2"></i>Información Actual
            </h2>
            <div class="text-sm text-gray-500">
                Última actualización: {{ $animal->updated_at->format('d/m/Y H:i') }}
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-sm text-gray-500">ID</p>
                <p class="font-medium">#{{ $animal->idAni }}</p>
            </div>
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-sm text-gray-500">Estado General</p>
                <p class="font-medium">
                    @switch($animal->estAni)
                        @case('vivo')
                            <span class="text-green-600">
                                <i class="fas fa-heart mr-1"></i>Vivo
                            </span>
                            @break
                        @case('muerto')
                            <span class="text-red-600">
                                <i class="fas fa-skull mr-1"></i>Muerto
                            </span>
                            @break
                        @default
                            <span class="text-yellow-600">
                                <i class="fas fa-hand-holding-usd mr-1"></i>Vendido
                            </span>
                    @endswitch
                </p>
            </div>
            <div class="bg-gray-50 p-3 rounded">
                <p class="text-sm text-gray-500">Especie</p>
                <p class="font-medium">{{ $animal->espAni }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Información Básica -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4 border-b border-gray-200 pb-2">
                <i class="fas fa-info-circle text-blue-500 mr-2"></i>Información Básica
            </h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">NIT del Animal</label>
                    <p class="text-gray-900">{{ $animal->nitAni ?? 'No registrado' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Identificación</label>
                    <p class="text-gray-900">{{ $animal->ideAni ?? 'No registrado' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Ubicación</label>
                    <p class="text-gray-900">{{ $animal->ubicacionAni ?? 'No registrada' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Raza</label>
                    <p class="text-gray-900">{{ $animal->razAni ?? 'No especificada' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Sexo</label>
                    <p class="text-gray-900">
                        @if($animal->sexAni === 'Hembra')
                            <span class="text-pink-600">
                                <i class="fas fa-venus mr-1"></i>Hembra
                            </span>
                        @else
                            <span class="text-blue-600">
                                <i class="fas fa-mars mr-1"></i>Macho
                            </span>
                        @endif
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Peso</label>
                    <p class="text-gray-900">{{ $animal->pesAni ? $animal->pesAni.' kg' : 'No registrado' }}</p>
                </div>

                @if($animal->fotoAni)
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Foto</label>
                    <div class="mt-1">
                        <img src="{{ asset('storage/' . $animal->fotoAni) }}" alt="Foto del animal" 
                             class="w-32 h-32 object-cover rounded-lg border border-gray-200 shadow-sm">
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Fechas y Estados -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4 border-b border-gray-200 pb-2">
                <i class="fas fa-calendar-alt text-orange-500 mr-2"></i>Fechas y Estados
            </h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Fecha de Nacimiento</label>
                    <p class="text-gray-900">
                        @if($animal->fecNacAni)
                            {{ \Carbon\Carbon::parse($animal->fecNacAni)->format('d/m/Y') }}
                            <span class="text-xs text-gray-500">({{ \Carbon\Carbon::parse($animal->fecNacAni)->age }} años)</span>
                        @else
                            No registrada
                        @endif
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Fecha de Compra/Ingreso</label>
                    <p class="text-gray-900">
                        @if($animal->fecComAni)
                            {{ \Carbon\Carbon::parse($animal->fecComAni)->format('d/m/Y') }}
                        @else
                            No registrada
                        @endif
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Estado de Salud</label>
                    <p class="text-gray-900">
                        @switch($animal->estSaludAni)
                            @case('saludable')
                                <span class="text-green-600">
                                    <i class="fas fa-check-circle mr-1"></i>Saludable
                                </span>
                                @break
                            @case('enfermo')
                                <span class="text-red-600">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>Enfermo
                                </span>
                                @break
                            @default
                                <span class="text-yellow-600">
                                    <i class="fas fa-medkit mr-1"></i>En tratamiento
                                </span>
                        @endswitch
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Estado Reproductivo</label>
                    <p class="text-gray-900">
                        @php
                            $estadosRepro = [
                                'no_aplica' => ['label' => 'No aplica', 'color' => 'gray', 'icon' => 'fas fa-ban'],
                                'ciclo' => ['label' => 'En ciclo', 'color' => 'blue', 'icon' => 'fas fa-sync-alt'],
                                'cubierta' => ['label' => 'Cubierta', 'color' => 'purple', 'icon' => 'fas fa-heart'],
                                'gestacion' => ['label' => 'Gestación', 'color' => 'pink', 'icon' => 'fas fa-baby'],
                                'parida' => ['label' => 'Parida', 'color' => 'green', 'icon' => 'fas fa-baby-carriage']
                            ];
                            $estadoActual = $estadosRepro[$animal->estReproAni] ?? $estadosRepro['no_aplica'];
                        @endphp
                        <span class="text-{{ $estadoActual['color'] }}-600">
                            <i class="{{ $estadoActual['icon'] }} mr-1"></i>{{ $estadoActual['label'] }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Observaciones -->
    @if($animal->obsAni)
    <div class="bg-white rounded-lg shadow-md p-6 mt-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4 border-b border-gray-200 pb-2">
            <i class="fas fa-comment-alt text-yellow-500 mr-2"></i>Observaciones
        </h2>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
            <p class="text-gray-700 whitespace-pre-line">{{ $animal->obsAni }}</p>
        </div>
    </div>
    @endif

    <!-- Footer con acciones -->
    <div class="mt-6 flex justify-between items-center">
        <div class="text-sm text-gray-500">
            Registrado el: {{ $animal->created_at->format('d/m/Y H:i') }}
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('pecuario.animales.edit', $animal->idAni) }}" wire:navigate
                class="text-yellow-600 hover:text-yellow-800 font-medium">
                <i class="fas fa-edit mr-1"></i>Editar información
            </a>
            <span class="text-gray-300">|</span>
            <button wire:click="confirmDelete"
                class="text-red-600 hover:text-red-800 font-medium">
                <i class="fas fa-trash mr-1"></i>Eliminar animal
            </button>
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
                    <p><strong>Animal:</strong> {{ $animal->ideAni ?? 'Sin nombre' }}</p>
                    <p><strong>Especie:</strong> {{ $animal->espAni }}</p>
                    <p><strong>ID:</strong> {{ $animal->idAni }}</p>
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