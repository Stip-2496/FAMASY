<?php
use App\Models\HistorialMedico;
use App\Models\Animal;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;

    public string $search = '';
    public string $tipo = '';
    public string $fecha = '';
    public int $perPage = 10;
    public $showDeleteModal = false;
    public $historialToDelete = null;

    public function with(): array
    {
        return [
            'historiales' => HistorialMedico::with('animal')
                ->when($this->search, function ($query) {
                    $query->where(function($q) {
                        $q->where('desHisMed', 'like', '%'.$this->search.'%')
                          ->orWhere('responHisMed', 'like', '%'.$this->search.'%')
                          ->orWhereHas('animal', function($animalQuery) {
                              $animalQuery->where('nomAni', 'like', '%'.$this->search.'%')
                                         ->orWhere('espAni', 'like', '%'.$this->search.'%');
                          });
                    });
                })
                ->when($this->tipo, fn($q) => $q->where('tipHisMed', $this->tipo))
                ->when($this->fecha, fn($q) => $q->whereDate('fecHisMed', $this->fecha))
                ->latest()
                ->paginate($this->perPage)
        ];
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->tipo = '';
        $this->fecha = '';
        $this->resetPage();
    }

    public function confirmDelete($id): void
    {
        $this->historialToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteHistorial(): void
    {
        try {
            $historial = HistorialMedico::findOrFail($this->historialToDelete);
            $historial->delete();
            
            $this->showDeleteModal = false;
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Registro médico eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar el registro: ' . $e->getMessage()
            ]);
        } finally {
            $this->historialToDelete = null;
        }
    }
}; ?>

@section('title', 'Historial Médico')

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="bg-green-700 text-white px-6 py-4 flex justify-between items-center rounded-t-lg">
            <h5 class="text-lg font-semibold flex items-center gap-2">
                <i class="fas fa-clipboard-list"></i> Historial Médico
            </h5>
            <a href="{{ route('pecuario.salud-peso.create') }}" wire:navigate
               class="bg-green-100 text-green-800 hover:bg-green-200 px-3 py-1 rounded text-sm flex items-center gap-1">
                <i class="fas fa-plus"></i> Nuevo Registro
            </a>
        </div>

        <!-- Filtros -->
        <div class="p-4 border-b">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                    <input type="text" wire:model.live.debounce.500ms="search" 
                           placeholder="Buscar..." 
                           class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select wire:model.live="tipo" class="w-full border border-gray-300 rounded px-3 py-2">
                        <option value="">Todos</option>
                        <option value="vacuna">Vacuna</option>
                        <option value="tratamiento">Tratamiento</option>
                        <option value="control">Control</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                    <input type="date" wire:model.live="fecha" class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div class="flex items-end">
                    <button wire:click="clearFilters" 
                            class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded">
                        Limpiar Filtros
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="p-6">
            @if($historiales->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto border-collapse border border-gray-200">
                    <thead>
                        <tr class="bg-green-100 text-green-900">
                            <th class="border border-gray-300 px-3 py-2 text-left text-sm font-semibold">ID</th>
                            <th class="border border-gray-300 px-3 py-2 text-left text-sm font-semibold">Animal</th>
                            <th class="border border-gray-300 px-3 py-2 text-left text-sm font-semibold">Tipo</th>
                            <th class="border border-gray-300 px-3 py-2 text-left text-sm font-semibold">Descripción</th>
                            <th class="border border-gray-300 px-3 py-2 text-left text-sm font-semibold">Fecha</th>
                            <th class="border border-gray-300 px-3 py-2 text-left text-sm font-semibold">Responsable</th>
                            <th class="border border-gray-300 px-3 py-2 text-center text-sm font-semibold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($historiales as $historial)
                        <tr class="hover:bg-green-50">
                            <td class="border border-gray-300 px-3 py-2 text-sm">{{ $historial->idHisMed }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-sm">
                                {{ $historial->animal->nomAni ?? 'Animal #' . $historial->idAniHis }}
                                ({{ $historial->animal->espAni ?? 'N/A' }})
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-sm">
                                @if($historial->tipHisMed == 'vacuna')
                                    <span class="inline-block bg-green-200 text-green-800 px-2 py-0.5 rounded text-xs font-semibold">Vacuna</span>
                                @elseif($historial->tipHisMed == 'tratamiento')
                                    <span class="inline-block bg-yellow-200 text-yellow-800 px-2 py-0.5 rounded text-xs font-semibold">Tratamiento</span>
                                @else
                                    <span class="inline-block bg-blue-200 text-blue-800 px-2 py-0.5 rounded text-xs font-semibold">Control</span>
                                @endif
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-sm">{{ \Illuminate\Support\Str::limit($historial->desHisMed, 50) }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-sm">{{ \Carbon\Carbon::parse($historial->fecHisMed)->format('d/m/Y') }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-sm">{{ $historial->responHisMed }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center text-sm">
                                <div class="inline-flex space-x-1">
                                    <a href="{{ route('pecuario.salud-peso.show', $historial->idHisMed) }}" wire:navigate
                                       class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs"
                                       title="Ver">
                                        Ver
                                    </a>
                                    <a href="{{ route('pecuario.salud-peso.edit', $historial->idHisMed) }}" wire:navigate
                                       class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded text-xs"
                                       title="Editar">
                                        Editar
                                    </a>
                                    <button wire:click="confirmDelete({{ $historial->idHisMed }})"
                                            class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-xs"
                                            title="Eliminar">
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex justify-center">
                {{ $historiales->links() }}
            </div>
            @else
            <div class="text-center py-8">
                <i class="fas fa-clipboard-list text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No hay registros médicos</h3>
                <p class="text-gray-500 mb-4">No se encontraron registros con los filtros aplicados.</p>
                <a href="{{ route('pecuario.salud-peso.create') }}" wire:navigate
                   class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-plus mr-2"></i>Agregar Registro
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Modal Eliminar -->
    @if($showDeleteModal)
    <div class="fixed inset-0 bg-black/20 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg max-w-sm w-full">
            <h2 class="text-xl font-semibold mb-4">Confirmar eliminación</h2>
            <p class="mb-4 text-sm text-gray-600">
                ¿Está seguro que desea eliminar este registro médico? Esta acción no se puede deshacer.
            </p>
            
            <div class="mb-4">
                @php $historial = HistorialMedico::find($historialToDelete); @endphp
                @if($historial)
                <p><strong>Tipo:</strong> {{ ucfirst($historial->tipHisMed) }}</p>
                <p><strong>Animal:</strong> {{ $historial->animal->nomAni ?? 'Animal #'.$historial->idAniHis }}</p>
                <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($historial->fecHisMed)->format('d/m/Y') }}</p>
                @endif
            </div>
            
            <div class="flex justify-end gap-3">
                <button wire:click="$set('showDeleteModal', false)"
                        class="cursor-pointer px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">Cancelar</button>
                <button wire:click="deleteHistorial"
                        class="cursor-pointer px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">Confirmar Eliminación</button>
            </div>
        </div>
    </div>
    @endif
</div>