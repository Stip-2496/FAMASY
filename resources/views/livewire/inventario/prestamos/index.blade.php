<?php
use App\Models\PrestamoHerramienta;
use App\Models\Herramienta;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;

    public string $search = '';
    public string $filtroEstado = '';
    public string $filtroUsuario = '';
    public string $filtroHerramienta = '';
    public int $perPage = 10;
    public $showDeleteModal = false;
    public $prestamoToDelete = null;

    public function with(): array
    {
        return [
            'prestamos' => PrestamoHerramienta::query()
                ->with(['herramienta', 'usuario'])
                ->when($this->filtroEstado, function ($query) {
                    $query->where('estPre', $this->filtroEstado);
                })
                ->when($this->filtroUsuario, function ($query) {
                    $query->whereHas('usuario', function($q) {
                        $q->where('nomUsu', 'like', '%'.$this->filtroUsuario.'%')
                          ->orWhere('apeUsu', 'like', '%'.$this->filtroUsuario.'%');
                    });
                })
                ->when($this->filtroHerramienta, function ($query) {
                    $query->whereHas('herramienta', function($q) {
                        $q->where('nomHer', 'like', '%'.$this->filtroHerramienta.'%');
                    });
                })
                ->orderBy('fecPre', 'desc')
                ->paginate($this->perPage),
            'estadisticas' => [
                'prestados' => PrestamoHerramienta::where('estPre', 'prestado')->count(),
                'devueltos' => PrestamoHerramienta::where('estPre', 'devuelto')->count(),
                'vencidos' => PrestamoHerramienta::where('estPre', 'vencido')->count(),
                'total' => PrestamoHerramienta::count()
            ]
        ];
    }

    public function clearFilters(): void
    {
        $this->reset(['filtroEstado', 'filtroUsuario', 'filtroHerramienta']);
        $this->resetPage();
    }

    public function confirmDelete($id): void
    {
        $this->prestamoToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deletePrestamo(): void
    {
        try {
            $prestamo = PrestamoHerramienta::findOrFail($this->prestamoToDelete);
            
            if ($prestamo->estPre !== 'devuelto') {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'No se puede eliminar un préstamo que no ha sido devuelto'
                ]);
                return;
            }
            
            $prestamo->delete();
            
            $this->showDeleteModal = false;
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Préstamo eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar el préstamo: ' . $e->getMessage()
            ]);
        } finally {
            $this->prestamoToDelete = null;
        }
    }

    public function devolverPrestamo($prestamoId): void
    {
        $this->dispatch('open-modal', name: 'devolver-prestamo', data: ['prestamoId' => $prestamoId]);
    }
}; ?>

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        Préstamos de Herramientas
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">Gestión y seguimiento de préstamos de herramientas</p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <a href="{{ route('inventario.prestamos.create') }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Nuevo Préstamo
                    </a>
                </div>
            </div>
        </div>

        <!-- Filtros y búsqueda -->
        <div class="mb-6 bg-white p-6 rounded-lg shadow">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Filtro por Estado -->
                <div>
                    <label for="filtro_estado" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                    <select wire:model.live="filtroEstado" id="filtro_estado" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos los estados</option>
                        <option value="prestado">Prestado</option>
                        <option value="devuelto">Devuelto</option>
                        <option value="vencido">Vencido</option>
                    </select>
                </div>

                <!-- Filtro por Usuario -->
                <div>
                    <label for="filtro_usuario" class="block text-sm font-medium text-gray-700 mb-2">Usuario</label>
                    <input type="text" wire:model.live.debounce.300ms="filtroUsuario" id="filtro_usuario" placeholder="Buscar por usuario..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Búsqueda por herramienta -->
                <div>
                    <label for="filtro_herramienta" class="block text-sm font-medium text-gray-700 mb-2">Buscar herramienta</label>
                    <input type="text" wire:model.live.debounce.300ms="filtroHerramienta" id="filtro_herramienta" placeholder="Nombre de herramienta..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Botón limpiar filtros -->
                <div class="flex items-end">
                    <button wire:click="clearFilters" 
                            class="w-full px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out">
                        Limpiar Filtros
                    </button>
                </div>
            </div>
        </div>

        <!-- Estadísticas rápidas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Prestados</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $estadisticas['prestados'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Devueltos</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $estadisticas['devueltos'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Vencidos</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $estadisticas['vencidos'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $estadisticas['total'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de préstamos -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-6 py-4 bg-blue-600">
                <h3 class="text-lg font-medium text-white">Lista de Préstamos</h3>
            </div>
            
            @if($prestamos->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Herramienta
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Usuario
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fecha Préstamo
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fecha Devolución
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($prestamos as $prestamo)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $prestamo->herramienta->nomHer ?? 'Sin herramienta' }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $prestamo->herramienta->catHer ?? '' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $prestamo->usuario->nomUsu ?? '' }} {{ $prestamo->usuario->apeUsu ?? '' }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $prestamo->usuario->email ?? 'Sin usuario' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $prestamo->fecPre->format('d/m/Y') }}</div>
                                <div class="text-sm text-gray-500">{{ $prestamo->fecPre->diffForHumans() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($prestamo->fecDev)
                                    <div class="text-sm text-gray-900">{{ $prestamo->fecDev->format('d/m/Y') }}</div>
                                    <div class="text-sm text-gray-500">{{ $prestamo->fecDev->diffForHumans() }}</div>
                                @else
                                    <span class="text-sm text-gray-400">Sin programar</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $estadoColors = [
                                        'prestado' => 'bg-blue-100 text-blue-800',
                                        'devuelto' => 'bg-green-100 text-green-800',
                                        'vencido' => 'bg-red-100 text-red-800'
                                    ];
                                    $colorClass = $estadoColors[$prestamo->estPre] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                    {{ ucfirst($prestamo->estPre) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('inventario.prestamos.show', $prestamo) }}" wire:navigate
                                       class="text-blue-600 hover:text-blue-900" title="Ver detalles">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    
                                    @if($prestamo->estPre !== 'devuelto')
                                    <a href="{{ route('inventario.prestamos.edit', $prestamo) }}" wire:navigate
                                       class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    @endif

                                    @if($prestamo->estPre === 'prestado')
                                    <button wire:click="devolverPrestamo({{ $prestamo->idPreHer }})" 
                                            class="text-green-600 hover:text-green-900" title="Devolver">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                    @endif

                                    @if($prestamo->estPre === 'prestado')
                                    <button wire:click="confirmDelete({{ $prestamo->idPreHer }})"
                                            class="text-red-600 hover:text-red-900" title="Eliminar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $prestamos->links() }}
            </div>
            @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No hay préstamos</h3>
                <p class="mt-1 text-sm text-gray-500">Comienza registrando tu primer préstamo de herramienta.</p>
                <div class="mt-6">
                    <a href="{{ route('inventario.prestamos.create') }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Nuevo Préstamo
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Eliminar Préstamo -->
@if($showDeleteModal)
    <x-modal wire:model="showDeleteModal" maxWidth="sm">
        <div class="bg-white p-6 rounded-lg">
            <h2 class="text-xl font-semibold mb-4">Confirmar eliminación</h2>
            <p class="mb-4 text-sm text-gray-600">
                ¿Está seguro que desea eliminar este préstamo? Esta acción no se puede deshacer.
            </p>
            
            <div class="flex justify-end gap-3">
                <button wire:click="$set('showDeleteModal', false)"
                        class="cursor-pointer px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">Cancelar</button>
                <button wire:click="deletePrestamo"
                        class="cursor-pointer px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">Confirmar Eliminación</button>
            </div>
        </div>
    </x-modal>
@endif

<!-- Modal Devolver Préstamo -->
@script
<script>
    Livewire.on('open-modal', (event) => {
        const { name, data } = event;
        
        if (name === 'devolver-prestamo') {
            const fechaDevolucion = prompt('Ingrese la fecha de devolución (YYYY-MM-DD):');
            if (fechaDevolucion) {
                const observaciones = prompt('Observaciones adicionales (opcional):');
                
                Livewire.dispatch('devolver', {
                    prestamoId: data.prestamoId,
                    fechaDevolucion,
                    observaciones
                });
            }
        }
    });
</script>
@endscript