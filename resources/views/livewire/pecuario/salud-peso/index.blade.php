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
    public int $perPage = 10; // Valor predeterminado
    public $showDeleteModal = false;
    public $historialToDelete = null;

    public function with(): array
    {
        return [
            'historiales' => HistorialMedico::with('animal')
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('desHisMed', 'like', '%' . $this->search . '%')
                            ->orWhere('responHisMed', 'like', '%' . $this->search . '%')
                            ->orWhereHas('animal', function ($animalQuery) {
                                $animalQuery->where('ideAni', 'like', '%' . $this->search . '%')
                                    ->orWhere('espAni', 'like', '%' . $this->search . '%');
                            });
                    });
                })
                ->when($this->tipo, fn($q) => $q->where('tipHisMed', $this->tipo))
                ->when($this->fecha, fn($q) => $q->whereDate('fecHisMed', $this->fecha))
                ->latest()
                ->paginate($this->perPage)
        ];
    }

    // Reinicia la paginación cuando cambian los filtros
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTipo(): void
    {
        $this->resetPage();
    }

    public function updatedFecha(): void
    {
        $this->resetPage();
    }

    // Reinicia la paginación cuando cambia perPage
    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->tipo = '';
        $this->fecha = '';
        $this->perPage = 10; // Vuelve al valor predeterminado al limpiar filtros
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
            // Asegúrate de que la paginación se actualice si la última página queda vacía
            $this->resetPage();
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

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clipboard-list text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Historial Médico</h1>
                        <p class="text-gray-600">Gestión de registros médicos de los animales</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('pecuario.dashboard') }}" wire:navigate
                       class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-arrow-left text-sm"></i>
                        Volver
                    </a>
                    <a href="{{ route('pecuario.salud-peso.create') }}" wire:navigate
                       class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-plus text-sm"></i>
                        Nuevo Registro
                    </a>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-filter text-gray-600 text-sm"></i>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900">Filtros de Búsqueda</h2>
                </div>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" wire:model.live.debounce.500ms="search"
                                   placeholder="Buscar..."
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-tag text-gray-400"></i>
                            </div>
                            <select wire:model.live="tipo" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                                <option value="">Todos</option>
                                <option value="vacuna">Vacuna</option>
                                <option value="tratamiento">Tratamiento</option>
                                <option value="control">Control</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-calendar text-gray-400"></i>
                            </div>
                            <input type="date" wire:model.live="fecha" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                        </div>
                    </div>
                    
                    <div class="flex items-end gap-2">
                        <button wire:click="clearFilters"
                                class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 px-4 rounded-lg transition-colors flex items-center justify-center gap-2">
                            <i class="fas fa-times text-sm"></i>
                            Limpiar Filtros
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de resultados -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-list text-gray-600 text-sm"></i>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900">Registros Médicos</h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-600">Mostrar</span>
                        <select wire:model.live="perPage" class="border border-gray-300 rounded px-2 py-1 text-sm">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-sm text-gray-600">registros</span>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                @if($historiales->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="bg-gray-100 text-gray-900">
                                <th class="px-4 py-3 text-left text-sm font-semibold">ID</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Animal</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Tipo</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Descripción</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Fecha</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Responsable</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($historiales as $historial)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-sm">{{ $historial->idHisMed }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-medium">{{ $historial->animal->ideAni ?? 'Animal #' . $historial->idAniHis }}</div>
                                    <div class="text-gray-500 text-xs">{{ $historial->animal->espAni ?? 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($historial->tipHisMed == 'vacuna')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-syringe mr-1"></i>Vacuna
                                        </span>
                                    @elseif($historial->tipHisMed == 'tratamiento')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-pills mr-1"></i>Tratamiento
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-weight mr-1"></i>Control
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="max-w-xs truncate" title="{{ $historial->desHisMed }}">
                                        {{ $historial->desHisMed }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ \Carbon\Carbon::parse($historial->fecHisMed)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ $historial->responHisMed }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex justify-center gap-2">
                                        <!-- Botón Ver -->
                                        <a href="{{ route('pecuario.salud-peso.show', $historial->idHisMed) }}" wire:navigate
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-xs"
                                        title="Ver">
                                            <i class="fas fa-eye text-xs"></i>
                                            Ver
                                        </a>
                                        
                                        <!-- Botón Editar -->
                                        <a href="{{ route('pecuario.salud-peso.edit', $historial->idHisMed) }}" wire:navigate
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors text-xs"
                                        title="Editar">
                                            <i class="fas fa-edit text-xs"></i>
                                            Editar
                                        </a>
                                        
                                        <!-- Botón Eliminar -->
                                        <button wire:click="confirmDelete({{ $historial->idHisMed }})"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-xs"
                                                title="Eliminar">
                                            <i class="fas fa-trash text-xs"></i>
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $historiales->links() }}
                </div>
                @else
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-clipboard-list text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay registros médicos</h3>
                    <p class="text-gray-500 mb-6">No se encontraron registros con los filtros aplicados.</p>
                    <div class="flex justify-center gap-4">
                        <a href="{{ route('pecuario.dashboard') }}" wire:navigate
                           class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-arrow-left text-sm"></i>
                            Volver al Dashboard
                        </a>
                        <a href="{{ route('pecuario.salud-peso.create') }}" wire:navigate
                           class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-plus text-sm"></i>
                            Agregar Registro
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    @if($showDeleteModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <h2 class="text-xl font-semibold text-gray-900">Confirmar eliminación</h2>
            </div>
            
            <p class="mb-4 text-gray-600">
                ¿Está seguro que desea eliminar este registro médico? Esta acción no se puede deshacer.
            </p>

            <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                @php $historial = HistorialMedico::find($historialToDelete); @endphp
                @if($historial)
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="font-medium">Tipo:</div>
                    <div>{{ ucfirst($historial->tipHisMed) }}</div>
                    
                    <div class="font-medium">Animal:</div>
                    <div>{{ $historial->animal->ideAni ?? 'Animal #'.$historial->idAniHis }}</div>
                    
                    <div class="font-medium">Fecha:</div>
                    <div>{{ \Carbon\Carbon::parse($historial->fecHisMed)->format('d/m/Y') }}</div>
                    
                    <div class="font-medium">Responsable:</div>
                    <div>{{ $historial->responHisMed }}</div>
                </div>
                @endif
            </div>

            <div class="flex justify-end gap-3">
                <button wire:click="$set('showDeleteModal', false)"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button wire:click="deleteHistorial"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    Confirmar Eliminación
                </button>
            </div>
        </div>
    </div>
    @endif
</div>