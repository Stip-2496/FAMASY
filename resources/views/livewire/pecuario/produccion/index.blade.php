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
    public string $search = '';
    public $showDeleteModal = false;
    public $produccionToDelete = null;

    public function with(): array
    {
        return [
            'producciones' => ProduccionAnimal::with(['animal'])
                ->when($this->tipo, fn($q) => $q->where('tipProAni', $this->tipo))
                ->when($this->fecha, fn($q) => $q->whereDate('fecProAni', $this->fecha))
                ->when($this->search, function($q) {
                    $q->where(function($query) {
                        $query->whereHas('animal', function($subQuery) {
                            $subQuery->where('ideAni', 'like', '%' . $this->search . '%')
                                   ->orWhere('espAni', 'like', '%' . $this->search . '%');
                        })
                        ->orWhere('tipProAni', 'like', '%' . $this->search . '%')
                        ->orWhere('obsProAni', 'like', '%' . $this->search . '%')
                        ->orWhere('uniProAni', 'like', '%' . $this->search . '%');
                    });
                })
                ->orderBy('fecProAni', 'desc')
                ->paginate(15),
            'animales' => Animal::where('estAni', 'vivo')
                ->orderBy('ideAni')
                ->get(['idAni', 'ideAni', 'espAni']),
            'tipos' => [
                'leche bovina' => 'Leche Bovina',
                'venta en pie bovino' => 'Venta en Pie Bovino',
                'lana ovina' => 'Lana Ovina',
                'venta en pie ovino' => 'Venta en Pie Ovino',
                'leche ovina' => 'Leche Ovina',
                'venta gallinas en pie' => 'Venta Gallinas en Pie',
                'huevo A' => 'Huevo A',
                'huevo AA' => 'Huevo AA',
                'huevo AAA' => 'Huevo AAA',
                'huevo Jumbo' => 'Huevo Jumbo',
                'huevo B' => 'Huevo B',
                'huevo C' => 'Huevo C',
                'venta pollo engorde' => 'Venta Pollo Engorde',
                'otros' => 'Otros'
            ]
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

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['tipo', 'fecha', 'search']);
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
        } catch (\Exception $e) {
            // Manejo básico de errores sin notificaciones
        } finally {
            $this->produccionToDelete = null;
        }
    }

    public function getTipoProduccionFormateado($tipo)
    {
        $tipos = [
            'leche bovina' => 'Leche Bovina',
            'venta en pie bovino' => 'Venta en Pie Bovino',
            'lana ovina' => 'Lana Ovina',
            'venta en pie ovino' => 'Venta en Pie Ovino',
            'leche ovina' => 'Leche Ovina',
            'venta gallinas en pie' => 'Venta Gallinas en Pie',
            'huevo A' => 'Huevo A',
            'huevo AA' => 'Huevo AA',
            'huevo AAA' => 'Huevo AAA',
            'huevo Jumbo' => 'Huevo Jumbo',
            'huevo B' => 'Huevo B',
            'huevo C' => 'Huevo C',
            'venta pollo engorde' => 'Venta Pollo Engorde',
            'otros' => 'Otros'
        ];
        
        return $tipos[$tipo] ?? ucfirst($tipo);
    }

    public function getColorTipo($tipo)
    {
        $colores = [
            'leche bovina' => 'bg-blue-100 text-blue-800',
            'leche ovina' => 'bg-blue-100 text-blue-800',
            'venta en pie bovino' => 'bg-red-100 text-red-800',
            'venta en pie ovino' => 'bg-red-100 text-red-800',
            'venta gallinas en pie' => 'bg-red-100 text-red-800',
            'venta pollo engorde' => 'bg-red-100 text-red-800',
            'lana ovina' => 'bg-purple-100 text-purple-800',
            'huevo A' => 'bg-yellow-100 text-yellow-800',
            'huevo AA' => 'bg-yellow-100 text-yellow-800',
            'huevo AAA' => 'bg-yellow-100 text-yellow-800',
            'huevo Jumbo' => 'bg-yellow-100 text-yellow-800',
            'huevo B' => 'bg-yellow-100 text-yellow-800',
            'huevo C' => 'bg-yellow-100 text-yellow-800',
            'otros' => 'bg-gray-100 text-gray-800'
        ];
        
        return $colores[$tipo] ?? 'bg-green-100 text-green-800';
    }
}; ?>

<div class="px-6 py-4">
    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('pecuario.dashboard') }}"
               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded shadow transition-colors">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
            <div>
                <h2 class="text-2xl font-bold text-green-800">
                    <i class="fas fa-chart-line mr-2"></i>Registros de Producción Animal
                </h2>
                <p class="text-sm text-gray-600 mt-1">Gestión y seguimiento de la producción pecuaria</p>
            </div>
        </div>

        <a href="{{ route('pecuario.produccion.create') }}" wire:navigate
           class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow transition-colors">
            <i class="fas fa-plus mr-1"></i> Nuevo Registro
        </a>
    </div>

    <div class="bg-white shadow rounded-lg">
        <!-- Filtros -->
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-filter mr-2"></i>Filtros de Búsqueda
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Búsqueda general más interactiva -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-search mr-1"></i>Búsqueda Inteligente
                    </label>
                    <div class="relative">
                        <input type="text" 
                               wire:model.live.debounce.500ms="search"
                               placeholder="Buscar animal, tipo de producción, observaciones..."
                               class="border border-gray-300 rounded-lg px-4 py-2 w-full pr-10 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent shadow-sm hover:shadow-md transition-shadow">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        @if($search)
                            <button wire:click="$set('search', '')" 
                                    class="absolute inset-y-0 right-8 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        @endif
                    </div>

                </div>

                <!-- Filtro por fecha -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-calendar mr-1"></i>Fecha
                    </label>
                    <div class="relative">
                        <input type="date" 
                               wire:model.live="fecha"
                               class="border border-gray-300 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent shadow-sm hover:shadow-md transition-shadow">
                        @if($fecha)
                            <button wire:click="$set('fecha', '')" 
                                    class="absolute inset-y-0 right-2 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Filtro por tipo -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-tags mr-1"></i>Tipo de Producción
                    </label>
                    <div class="relative">
                        <select wire:model.live="tipo"
                                class="border border-gray-300 rounded-lg px-4 py-2 w-full cursor-pointer focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent shadow-sm hover:shadow-md transition-shadow appearance-none">
                            <option value="">Todos los tipos</option>
                            @foreach($tipos as $valor => $texto)
                                <option value="{{ $valor }}">{{ $texto }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400"></i>
                        </div>
                        @if($tipo)
                            <button wire:click="$set('tipo', '')" 
                                    class="absolute inset-y-0 right-8 pr-3 flex items-center text-gray-400 hover:text-gray-600 pointer-events-auto">
                                <i class="fas fa-times"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Botón limpiar filtros -->
            @if($tipo || $fecha)
            <div class="mt-4 flex justify-between items-center">
                <div class="flex flex-wrap gap-2">
                    @if($tipo)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-tag mr-1"></i>
                            {{ $tipos[$tipo] }}
                            <button wire:click="$set('tipo', '')" class="ml-2 text-green-600 hover:text-green-800">
                                <i class="fas fa-times"></i>
                            </button>
                        </span>
                    @endif
                    @if($fecha)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            <i class="fas fa-calendar mr-1"></i>
                            {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
                            <button wire:click="$set('fecha', '')" class="ml-2 text-purple-600 hover:text-purple-800">
                                <i class="fas fa-times"></i>
                            </button>
                        </span>
                    @endif
                </div>
                <button wire:click="limpiarFiltros"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    <i class="fas fa-eraser mr-1"></i> Limpiar Todo
                </button>
            </div>
            @endif
        </div>

        <!-- Tabla -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-green-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Animal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Cantidad</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Cant. Total</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Fecha</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($producciones as $prod)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                            #{{ $prod->idProAni }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($prod->animal)
                                <div class="text-sm font-medium text-gray-900">{{ $prod->animal->ideAni }}</div>
                                <div class="text-sm text-gray-500">{{ ucfirst($prod->animal->espAni) }}</div>
                            @else
                                <span class="text-sm text-gray-400 italic">No asignado</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $this->getColorTipo($prod->tipProAni) }}">
                                {{ $this->getTipoProduccionFormateado($prod->tipProAni) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            @if($prod->canProAni)
                                <span class="font-medium">{{ number_format($prod->canProAni, 2) }}</span>
                                <span class="text-gray-500 ml-1">{{ $prod->uniProAni ?: 'un.' }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            @if($prod->canTotProAni)
                                <span class="font-medium text-blue-600">{{ number_format($prod->canTotProAni, 2) }}</span>
                                <span class="text-gray-500 ml-1">{{ $prod->uniProAni ?: 'un.' }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($prod->fecProAni)
                                <div class="text-sm text-gray-900">{{ $prod->fecProAni->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $prod->fecProAni->diffForHumans() }}</div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('pecuario.produccion.show', $prod->idProAni) }}" wire:navigate
                                   class="inline-flex items-center px-3 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-sm font-medium transition-colors"
                                   title="Ver detalles">
                                    <i class="fas fa-eye mr-1"></i>Ver
                                </a>
                                <a href="{{ route('pecuario.produccion.edit', $prod->idProAni) }}" wire:navigate
                                   class="inline-flex items-center px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white rounded text-sm font-medium transition-colors"
                                   title="Editar registro">
                                    <i class="fas fa-edit mr-1"></i>Editar
                                </a>
                                <button wire:click="confirmDelete({{ $prod->idProAni }})"
                                   class="inline-flex items-center px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-sm font-medium transition-colors"
                                   title="Eliminar registro">
                                    <i class="fas fa-trash mr-1"></i>Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2"></i>
                                <p class="text-lg font-medium">No hay registros de producción</p>
                                <p class="text-sm">{{ ($tipo || $fecha) ? 'Intenta cambiar los filtros de búsqueda' : 'Comienza creando tu primer registro' }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($producciones->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $producciones->links() }}
        </div>
        @endif
    </div>

    <!-- Modal de eliminación -->
    @if($showDeleteModal)
        <div class="fixed inset-0 backdrop-blur-sm flex items-center justify-center z-50">
            <div class="bg-white border border-red-300 rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <div class="flex items-center mb-4">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl mr-3"></i>
                    <h2 class="text-lg font-semibold text-gray-900">Confirmar eliminación</h2>
                </div>
                
                <p class="mb-4 text-gray-600 text-sm">
                    ¿Está seguro que desea eliminar este registro de producción? Esta acción no se puede deshacer.
                </p>
                
                <div class="bg-gray-50 p-3 rounded-lg mb-4 border-l-4 border-red-400">
                    @php
                        $produccion = \App\Models\ProduccionAnimal::with('animal')->find($produccionToDelete);
                    @endphp
                    @if($produccion)
                        <div class="space-y-1 text-sm">
                            <p><strong>ID:</strong> {{ $produccion->idProAni }}</p>
                            <p><strong>Animal:</strong> {{ $produccion->animal->ideAni ?? 'No asignado' }}</p>
                            <p><strong>Tipo:</strong> {{ $this->getTipoProduccionFormateado($produccion->tipProAni) }}</p>
                            <p><strong>Cantidad:</strong> 
                                {{ $produccion->canProAni ? number_format($produccion->canProAni, 2) : '0' }} 
                                {{ $produccion->uniProAni ?: 'unidades' }}
                            </p>
                            @if($produccion->fecProAni)
                            <p><strong>Fecha:</strong> {{ $produccion->fecProAni->format('d/m/Y') }}</p>
                            @endif
                        </div>
                    @endif
                </div>
                
                <div class="flex justify-end gap-2">
                    <button wire:click="$set('showDeleteModal', false)"
                            class="px-3 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded text-sm font-medium transition-colors">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                    <button wire:click="deleteProduccion"
                            class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded text-sm font-medium transition-colors">
                        <i class="fas fa-trash mr-1"></i> Eliminar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>