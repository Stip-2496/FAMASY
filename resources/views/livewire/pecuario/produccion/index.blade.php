<?php
use App\Models\ProduccionAnimal;
use App\Models\Animal;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;

    public string $tipo = '';
    public string $fecha = '';
    public $showDeleteModal = false;
    public $produccionToDelete = null;

    public function with(): array
    {
        return [
            'producciones' => ProduccionAnimal::with('animal')
                ->when($this->tipo, fn($q) => $q->where('tipProAni', $this->tipo))
                ->when($this->fecha, fn($q) => $q->whereDate('fecProAni', $this->fecha))
                ->orderBy('fecProAni', 'desc')
                ->paginate(10)
        ];
    }

    public function updatedTipo(): void
    {
        $this->resetPage();
    }

    public function updatedFecha(): void
    {
        $this->resetPage();
    }

    public function confirmDelete($id): void
    {
        $this->produccionToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteProduccion(): void
    {
        try {
            $produccion = ProduccionAnimal::findOrFail($this->produccionToDelete);
            $produccion->delete();
            
            $this->showDeleteModal = false;
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Registro de producción eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar el registro: ' . $e->getMessage()
            ]);
        } finally {
            $this->produccionToDelete = null;
        }
    }
}; ?>

<div class="px-6 py-4">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-green-800">
            Registros de Producción
        </h2>
        <a href="{{ route('pecuario.produccion.create') }}" wire:navigate
           class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow">
            Nuevo Registro
        </a>
    </div>

    <div class="bg-white shadow rounded p-4">
        <!-- Filtros -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                <input type="date" wire:model.live="fecha"
                       class="border border-green-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-green-400">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                <select wire:model.live="tipo"
                        class="border border-green-300 rounded px-3 py-2 w-full cursor-pointer focus:outline-none focus:ring-2 focus:ring-green-400">
                    <option value="">Todos los tipos</option>
                    @foreach(['leche', 'huevos', 'carne', 'lana'] as $tipoOpt)
                        <option value="{{ $tipoOpt }}">{{ ucfirst($tipoOpt) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Tabla de registros -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-green-200 rounded">
                <thead class="bg-green-700 text-white">
                    <tr>
                        <th class="px-4 py-2 text-left">ID</th>
                        <th class="px-4 py-2 text-left">Animal</th>
                        <th class="px-4 py-2 text-left">Tipo</th>
                        <th class="px-4 py-2 text-left">Cantidad</th>
                        <th class="px-4 py-2 text-left">Fecha</th>
                        <th class="px-4 py-2 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($producciones as $prod)
                    <tr class="border-t border-green-200 hover:bg-green-50">
                        <td class="px-4 py-2">{{ $prod->idProAni }}</td>
                        <td class="px-4 py-2">
                            {{ $prod->animal->nomAni ?? 'Animal #' . $prod->idAniPro }}
                        </td>
                        <td class="px-4 py-2">
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full
                                {{ [
                                    'leche' => 'bg-green-100 text-green-800',
                                    'huevos' => 'bg-yellow-100 text-yellow-800',
                                    'carne' => 'bg-red-100 text-red-800',
                                    'lana' => 'bg-gray-200 text-gray-800'
                                ][$prod->tipProAni] ?? 'bg-purple-100 text-purple-800' }}">
                                {{ ucfirst($prod->tipProAni) }}
                            </span>
                        </td>
                        <td class="px-4 py-2">
                            {{ $prod->canProAni }} {{ $prod->uniProAni ?? ($prod->tipProAni == 'leche' ? 'L' : 'kg') }}
                        </td>
                        <td class="px-4 py-2">{{ $prod->fecProAni->format('d/m/Y') }}</td>
                        <td class="px-4 py-2 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('pecuario.produccion.show', $prod->idProAni) }}" wire:navigate
                                   class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs font-semibold"
                                   title="Ver">
                                    Ver
                                </a>
                                <a href="{{ route('pecuario.produccion.edit', $prod->idProAni) }}" wire:navigate
                                   class="bg-green-400 hover:bg-green-500 text-white px-3 py-1 rounded text-xs font-semibold"
                                   title="Editar">
                                    Editar
                                </a>
                                <button wire:click="confirmDelete({{ $prod->idProAni }})"
                                   class="cursor-pointer bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-semibold"
                                   title="Eliminar">
                                    Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-3 text-center text-green-600 font-semibold">
                            No hay registros de producción
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="mt-4">
            {{ $producciones->links() }}
        </div>
    </div>

    <!-- Modal Eliminar Producción -->
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-black/20 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg max-w-sm w-full">
                <h2 class="text-xl font-semibold mb-4">Confirmar eliminación</h2>
                <p class="mb-4 text-sm text-gray-600">
                    ¿Está seguro que desea eliminar este registro de producción? Esta acción no se puede deshacer.
                </p>
                
                <div class="mb-4">
                    @php
                        $produccion = ProduccionAnimal::find($produccionToDelete);
                    @endphp
                    @if($produccion)
                        <p><strong>ID:</strong> {{ $produccion->idProAni }}</p>
                        <p><strong>Animal:</strong> {{ $produccion->animal->nomAni ?? 'Animal #'.$produccion->idAniPro }}</p>
                        <p><strong>Tipo:</strong> {{ ucfirst($produccion->tipProAni) }}</p>
                        <p><strong>Cantidad:</strong> {{ $produccion->canProAni }} {{ $produccion->uniProAni ?? ($produccion->tipProAni == 'leche' ? 'L' : 'kg') }}</p>
                    @endif
                </div>
                
                <div class="flex justify-end gap-3">
                    <button wire:click="$set('showDeleteModal', false)"
                            class="cursor-pointer px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">Cancelar</button>
                    <button wire:click="deleteProduccion"
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