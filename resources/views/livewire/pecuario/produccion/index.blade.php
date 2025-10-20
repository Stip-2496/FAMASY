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
    public int $perPage = 10;
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
                            $subQuery->where('nitAni', 'like', '%' . $this->search . '%')
                                    ->orWhere('espAni', 'like', '%' . $this->search . '%')
                                    ->orWhere('razAni', 'like', '%' . $this->search . '%');
                        })
                        ->orWhere('tipProAni', 'like', '%' . $this->search . '%')
                        ->orWhere('obsProAni', 'like', '%' . $this->search . '%')
                        ->orWhere('uniProAni', 'like', '%' . $this->search . '%')
                        ->orWhere('canProAni', 'like', '%' . $this->search . '%')
                        ->orWhere('canTotProAni', 'like', '%' . $this->search . '%');
                    });
                })
                ->orderBy('fecProAni', 'desc')
                ->paginate($this->perPage),
            'animales' => Animal::where('estAni', 'vivo')
                ->orderBy('espAni')
                ->get(['idAni', 'espAni', 'nitAni']),
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
            'leche bovina' => 'bg-blue-100 text-blue-600',
            'leche ovina' => 'bg-blue-100 text-blue-600',
            'venta en pie bovino' => 'bg-red-100 text-red-600',
            'venta en pie ovino' => 'bg-red-100 text-red-600',
            'venta gallinas en pie' => 'bg-red-100 text-red-600',
            'venta pollo engorde' => 'bg-red-100 text-red-600',
            'lana ovina' => 'bg-purple-100 text-purple-600',
            'huevo A' => 'bg-yellow-100 text-yellow-600',
            'huevo AA' => 'bg-yellow-100 text-yellow-600',
            'huevo AAA' => 'bg-yellow-100 text-yellow-600',
            'huevo Jumbo' => 'bg-yellow-100 text-yellow-600',
            'huevo B' => 'bg-yellow-100 text-yellow-600',
            'huevo C' => 'bg-yellow-100 text-yellow-600',
            'otros' => 'bg-gray-100 text-gray-600'
        ];
        
        return $colores[$tipo] ?? 'bg-green-100 text-green-600';
    }
}; ?>

@section('title', 'Gestión de Producción Animal')

<div class="min-h-screen py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Notificaciones -->
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

        <!-- Encabezado -->
        <div class="mb-4">
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl border border-white/20 p-4 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-blue-600/5"></div>
                
                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center space-x-2 mb-3 lg:mb-0">
                        <div class="p-2 bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg transform rotate-3 hover:rotate-0 transition-transform duration-300">
                            <div class="w-5 h-5 text-white flex items-center justify-center">
                                <i class="fas fa-chart-line text-base"></i>
                            </div>
                        </div>
                        <div>
                            <h1 class="text-xl font-black bg-gradient-to-r from-gray-900 via-gray-800 to-blue-800 bg-clip-text text-transparent leading-tight">
                                Gestión de Producción Animal
                            </h1>
                            <p class="text-gray-600 text-xs">Seguimiento de la producción pecuaria</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('pecuario.produccion.create') }}" wire:navigate
                           class="group relative inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-600 hover:from-blue-700 hover:to-blue-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                            <svg class="w-4 h-4 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span class="relative z-10 text-xs">Nuevo Registro</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-3 mb-3">
            <div class="flex flex-col sm:flex-row gap-2">
                <!-- Búsqueda general -->
                <div class="flex-1 relative">
                    <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input type="text" 
                           wire:model.live.debounce.500ms="search"
                           placeholder="Buscar animal, tipo de producción, observaciones..."
                           class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @if($search)
                        <button wire:click="$set('search', '')" 
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    @endif
                </div>

                <!-- Filtro por fecha -->
                <div class="relative">
                    <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <input type="date" 
                           wire:model.live="fecha"
                           class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @if($fecha)
                        <button wire:click="$set('fecha', '')" 
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    @endif
                </div>

                <!-- Filtro por tipo -->
                <div class="relative">
                    <select wire:model.live="tipo"
                            class="w-full pl-2 pr-8 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos los tipos</option>
                        @foreach($tipos as $valor => $texto)
                            <option value="{{ $valor }}">{{ $texto }}</option>
                        @endforeach
                    </select>
                    @if($tipo)
                        <button wire:click="$set('tipo', '')" 
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    @endif
                </div>

                <!-- Botón limpiar filtros -->
                @if($tipo || $fecha || $search)
                    <button wire:click="limpiarFiltros"
                            class="cursor-pointer inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-medium rounded-lg shadow hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span class="text-xs">Limpiar</span>
                    </button>
                @endif
            </div>

            <!-- Filtros activos -->
            @if($tipo || $fecha)
                <div class="mt-2 flex flex-wrap gap-2">
                    @if($tipo)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-600">
                            <svg class="w-2.5 h-2.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10m0 0v10m0-10l-5 5m5-5H7"></path>
                            </svg>
                            {{ $tipos[$tipo] }}
                            <button wire:click="$set('tipo', '')" class="ml-1 text-green-600 hover:text-green-800">
                                <svg class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </span>
                    @endif
                    @if($fecha)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-600">
                            <svg class="w-2.5 h-2.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
                            <button wire:click="$set('fecha', '')" class="ml-1 text-purple-600 hover:text-purple-800">
                                <svg class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </span>
                    @endif
                </div>
            @endif
        </div>

        <!-- Tabla -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 overflow-hidden">
            @if($producciones->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead class="bg-black">
                            <tr>
                                <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">#</th>
                                <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">NIT</th>
                                <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Especie</th>
                                <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Raza</th>
                                <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Tipo</th>
                                <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Cantidad</th>
                                <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Cant. Total</th>
                                <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Fecha</th>
                                <th class="px-2 py-1.5 text-center text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($producciones as $index => $prod)
                                <tr class="hover:bg-gray-50/50 transition-colors duration-200">
                                    <td class="px-2 py-1.5 whitespace-nowrap text-xs font-medium text-gray-900">
                                        {{ ($producciones->currentPage() - 1) * $producciones->perPage() + $index + 1 }}
                                    </td>
                                    <td class="px-2 py-1.5 whitespace-nowrap">
                                        @if($prod->animal)
                                            <div class="flex items-center gap-1">
                                                <div class="min-w-0">
                                                    <p class="text-xs font-medium text-gray-900">{{ $prod->animal->nitAni }}</p>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-600 italic">No asignado</span>
                                        @endif
                                    </td>
                                    <td class="px-2 py-1.5 whitespace-nowrap">
                                        @if($prod->animal)
                                            <div class="flex items-center gap-1">
                                                <div class="min-w-0">
                                                    <p class="text-xs text-gray-600">{{ ucfirst($prod->animal->espAni) }}</p>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-600 italic">No asignado</span>
                                        @endif
                                    </td>
                                    <td class="px-2 py-1.5 whitespace-nowrap">
                                        @if($prod->animal)
                                            <div class="flex items-center gap-1">
                                                <div class="min-w-0">
                                                    <p class="text-xs text-gray-600">{{ ucfirst($prod->animal->razAni) }}</p>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-600 italic">No asignado</span>
                                        @endif
                                    </td>
                                    <td class="px-2 py-1.5 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-medium {{ $this->getColorTipo($prod->tipProAni) }}">
                                            {{ $this->getTipoProduccionFormateado($prod->tipProAni) }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                                        @if($prod->canProAni)
                                            <span class="font-medium">{{ number_format($prod->canProAni, 2) }}</span>
                                            <span class="text-gray-600 ml-1">{{ $prod->uniProAni ?: 'un.' }}</span>
                                        @else
                                            <span class="text-gray-600">-</span>
                                        @endif
                                    </td>
                                    <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                                        @if($prod->canTotProAni)
                                            <span class="font-medium text-blue-600">{{ number_format($prod->canTotProAni, 2) }}</span>
                                            <span class="text-gray-600 ml-1">{{ $prod->uniProAni ?: 'un.' }}</span>
                                        @else
                                            <span class="text-gray-600">-</span>
                                        @endif
                                    </td>
                                    <td class="px-2 py-1.5 whitespace-nowrap">
                                        @if($prod->fecProAni)
                                            <div class="flex items-center gap-1">
                                                <svg class="w-2.5 h-2.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                <div class="min-w-0">
                                                    <p class="text-xs text-gray-900">{{ $prod->fecProAni->format('d/m/Y') }}</p>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-gray-600">-</span>
                                        @endif
                                    </td>
                                    <td class="px-2 py-1.5 whitespace-nowrap text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <a href="{{ route('pecuario.produccion.show', $prod->idProAni) }}" wire:navigate
                                               class="bg-blue-100 hover:bg-blue-200 text-blue-600 text-xs font-medium py-1 px-1.5 rounded transition duration-200">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </a>
                                            <a href="{{ route('pecuario.produccion.edit', $prod->idProAni) }}" wire:navigate
                                               class="bg-yellow-100 hover:bg-yellow-200 text-yellow-600 text-xs font-medium py-1 px-1.5 rounded transition duration-200">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                            <button wire:click="confirmDelete({{ $prod->idProAni }})"
                                                    class="cursor-pointer bg-red-100 hover:bg-red-200 text-red-600 text-xs font-medium py-1 px-1.5 rounded transition duration-200">
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
                @if($producciones->hasPages())
                    <div class="bg-white px-2 py-1.5 border-t border-gray-200 sm:px-3">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 flex justify-between sm:hidden">
                                {{ $producciones->links() }}
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-xs text-gray-700">
                                        Mostrando <span class="font-medium">{{ $producciones->firstItem() }}</span> a 
                                        <span class="font-medium">{{ $producciones->lastItem() }}</span> de 
                                        <span class="font-medium">{{ $producciones->total() }}</span> resultados
                                    </p>
                                </div>
                                <div>
                                    {{ $producciones->links() }}
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
                            <div class="w-8 h-8 text-gray-400 flex items-center justify-center">
                                <i class="fas fa-chart-line text-base"></i>
                            </div>
                        </div>
                        <h3 class="text-sm font-medium text-gray-900 mb-1">No hay registros de producción</h3>
                        <p class="text-xs text-gray-600 mb-2">
                            @if($tipo || $fecha || $search)
                                No hay resultados para los filtros aplicados. Intenta con otros términos.
                            @else
                                No hay registros de producción en el sistema.
                            @endif
                        </p>
                        <a href="{{ route('pecuario.produccion.create') }}" wire:navigate
                           class="cursor-pointer inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-700 hover:to-blue-900 text-white font-medium rounded-lg shadow hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span class="text-xs">Agregar Registro</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Modal de eliminación -->
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
                        <p class="text-xs text-gray-600 mb-3">¿Estás seguro de que deseas eliminar este registro de producción? Esta acción no se puede deshacer.</p>
                        
                        @php
                            $produccion = \App\Models\ProduccionAnimal::with('animal')->find($produccionToDelete);
                        @endphp
                        
                        @if($produccion)
                            <div class="bg-gray-50 rounded-lg p-2.5 mb-3 text-left">
                                <div class="space-y-1 text-xs">
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-700">NIT:</span>
                                        <span class="text-gray-900">{{ $produccion->idAniPro }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-700">Animal:</span>
                                        <span class="text-gray-900">{{ $produccion->animal->espAni ?? 'No asignado' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-700">Tipo:</span>
                                        <span class="text-gray-900">{{ $this->getTipoProduccionFormateado($produccion->tipProAni) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-700">Cantidad:</span>
                                        <span class="text-gray-900">
                                            {{ $produccion->canProAni ? number_format($produccion->canProAni, 2) : '0' }} 
                                            {{ $produccion->uniProAni ?: 'unidades' }}
                                        </span>
                                    </div>
                                    @if($produccion->fecProAni)
                                        <div class="flex justify-between">
                                            <span class="font-medium text-gray-700">Fecha:</span>
                                            <span class="text-gray-900">{{ $produccion->fecProAni->format('d/m/Y') }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                        
                        <div class="flex space-x-2">
                            <button wire:click="$set('showDeleteModal', false)" 
                                    class="flex-1 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg shadow hover:shadow transition-all duration-200 text-xs">
                                Cancelar
                            </button>
                            <button wire:click="deleteProduccion" 
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