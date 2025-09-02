<?php
use App\Models\PrestamoHerramienta;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;

    public string $search = '';
    public string $filtroEstado = '';
    public string $filtroUsuario = '';
    public string $filtroSolicitante = '';
    public string $filtroHerramienta = '';
    public int $perPage = 10;
    public $prestamoParaDevolver = null;
    public $fechaDevolucion;
    public $observaciones;

    public function with(): array
    {
        // Actualizar primero los préstamos vencidos
        $this->actualizarPrestamosVencidos();

        // Obtener el usuario autenticado
        $user = Auth::user();
        $userId = $user->id;
        $userRol = $user->idRolUsu;

        return [
            'prestamos' => PrestamoHerramienta::query()
                ->with(['herramienta', 'usuario', 'solicitante'])
                // Filtrar según el rol del usuario autenticado
                ->when($userRol == 1, function ($query) use ($userId) {
                    // Administrador: solo préstamos donde él sea el encargado (idUsuPre)
                    $query->where('idUsuPre', $userId);
                })
                ->when($userRol == 3, function ($query) use ($userId) {
                    // Aprendiz: solo préstamos donde él sea el solicitante (idUsuSol)
                    $query->where('idUsuSol', $userId);
                })
                // Resto de filtros
                ->when($this->filtroEstado, function ($query) {
                    $query->where('estPre', $this->filtroEstado);
                })
                ->when($this->filtroUsuario, function ($query) {
                    $query->whereHas('usuario', function($q) {
                        $q->where('nomUsu', 'like', '%'.$this->filtroUsuario.'%')
                          ->orWhere('apeUsu', 'like', '%'.$this->filtroUsuario.'%');
                    });
                })
                ->when($this->filtroSolicitante, function ($query) {
                    $query->whereHas('solicitante', function($q) {
                        $q->where('nomUsu', 'like', '%'.$this->filtroSolicitante.'%')
                          ->orWhere('apeUsu', 'like', '%'.$this->filtroSolicitante.'%')
                          ->orWhere('numDocUsu', 'like', '%'.$this->filtroSolicitante.'%');
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
                'prestados' => PrestamoHerramienta::query()
                    ->when($userRol == 1, function ($query) use ($userId) {
                        $query->where('idUsuPre', $userId);
                    })
                    ->when($userRol == 3, function ($query) use ($userId) {
                        $query->where('idUsuSol', $userId);
                    })
                    ->where('estPre', 'prestado')
                    ->count(),
                    
                'devueltos' => PrestamoHerramienta::query()
                    ->when($userRol == 1, function ($query) use ($userId) {
                        $query->where('idUsuPre', $userId);
                    })
                    ->when($userRol == 3, function ($query) use ($userId) {
                        $query->where('idUsuSol', $userId);
                    })
                    ->where('estPre', 'devuelto')
                    ->count(),
                    
                'vencidos' => PrestamoHerramienta::query()
                    ->when($userRol == 1, function ($query) use ($userId) {
                        $query->where('idUsuPre', $userId);
                    })
                    ->when($userRol == 3, function ($query) use ($userId) {
                        $query->where('idUsuSol', $userId);
                    })
                    ->where('estPre', 'vencido')
                    ->count(),
                    
                'total' => PrestamoHerramienta::query()
                    ->when($userRol == 1, function ($query) use ($userId) {
                        $query->where('idUsuPre', $userId);
                    })
                    ->when($userRol == 3, function ($query) use ($userId) {
                        $query->where('idUsuSol', $userId);
                    })
                    ->count()
            ]
        ];
    }

    // Resto del código permanece igual...
    protected function actualizarPrestamosVencidos(): void
    {
        // Actualizar préstamos con fecha de devolución pasada y estado 'prestado'
        PrestamoHerramienta::where('estPre', 'prestado')
            ->whereNotNull('fecDev')
            ->where('fecDev', '<', now())
            ->update(['estPre' => 'vencido']);
    }

    public function puedeEditar($prestamo): bool
    {
        return $prestamo->fecPre->isToday() && $prestamo->estPre !== 'devuelto';
    }

    public function clearFilters(): void
    {
        $this->reset(['filtroEstado', 'filtroUsuario', 'filtroHerramienta', 'filtroSolicitante']);
        $this->resetPage();
    }

    public function devolver($prestamoId, $fechaDevolucion, $observaciones = null): void
    {
        try {
            $prestamo = PrestamoHerramienta::findOrFail($prestamoId);
            
            if ($prestamo->estPre === 'devuelto') {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Este préstamo ya ha sido devuelto'
                ]);
                return;
            }
            
            $prestamo->update([
                'fecDev' => $fechaDevolucion,
                'estPre' => 'devuelto',
                'obsPre' => $observaciones
            ]);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Préstamo devuelto correctamente'
            ]);
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al devolver el préstamo: ' . $e->getMessage()
            ]);
        }
    }
    
    public function devolverPrestamo($prestamoId): void
    {
        $this->prestamoParaDevolver = $prestamoId;
        $this->fechaDevolucion = now()->format('Y-m-d\TH:i');
        $this->observaciones = null;
    }

    public function confirmarDevolucion(): void
    {
        $this->validate([
            'fechaDevolucion' => 'required|date_format:Y-m-d\TH:i',
        ]);
        
        $this->devolver($this->prestamoParaDevolver, $this->fechaDevolucion, $this->observaciones);
        $this->reset(['prestamoParaDevolver', 'fechaDevolucion', 'observaciones']);
    }
}; ?>

@section('title', 'Prestamos')

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
                
                @can('admin')
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <a href="{{ route('inventario.prestamos.create') }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Nuevo Préstamo
                    </a>
                </div>
                @endcan
            </div>
        </div>

       <!-- Filtros y búsqueda -->
<div class="mb-6 bg-white p-4 rounded-lg shadow"> 
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3"> 
        <!-- Filtro por Estado -->
        <div class="flex flex-col">
            <label for="filtro_estado" class="block text-xs font-medium text-gray-700 mb-1">Estado</label>
            <select wire:model.live="filtroEstado" id="filtro_estado" 
                   class="text-xs px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Todos</option>
                <option value="prestado">Prestado</option>
                <option value="devuelto">Devuelto</option>
                <option value="vencido">Vencido</option>
            </select>
        </div>

        <!-- Filtro por Encargado -->
        <div class="flex flex-col">
            <label for="filtro_usuario" class="block text-xs font-medium text-gray-700 mb-1">Encargado</label>
            <input type="text" wire:model.live.debounce.300ms="filtroUsuario" id="filtro_usuario" 
                   placeholder="Buscar encargado..."
                   class="text-xs px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Filtro por Solicitante -->
        <div class="flex flex-col">
            <label for="filtro_solicitante" class="block text-xs font-medium text-gray-700 mb-1">Solicitante</label>
            <input type="text" wire:model.live.debounce.300ms="filtroSolicitante" id="filtro_solicitante" 
                   placeholder="Nombre o documento" 
                   class="text-xs px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
        </div>
        
        <!-- Búsqueda por herramienta -->
        <div class="flex flex-col">
            <label for="filtro_herramienta" class="block text-xs font-medium text-gray-700 mb-1">Herramienta</label>
            <input type="text" wire:model.live.debounce.300ms="filtroHerramienta" id="filtro_herramienta" 
                   placeholder="Nombre herramienta"
                   class="text-xs px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Botón limpiar filtros -->
        <div class="flex items-end">
            <button wire:click="clearFilters" 
                   class="w-full px-2 py-1.5 text-xs bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-md transition duration-150 ease-in-out">
                Limpiar
            </button>
        </div>
    </div>
</div>

        @can('admin')
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
        @endcan

<!-- Tabla de préstamos compacta -->
<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-3 bg-blue-600"> <!-- Reducido de px-6 py-4 a px-4 py-3 -->
        <h3 class="text-md font-medium text-white">Lista de Préstamos</h3> <!-- Texto más pequeño -->
    </div>
    
    @if($prestamos->count() > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-xs"> <!-- Texto más pequeño -->
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider"> <!-- Padding reducido -->
                        Herramienta
                    </th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">
                        Encargado
                    </th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">
                        Solicitante
                    </th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">
                        F. Préstamo
                    </th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">
                        F. Devolución
                    </th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">
                        Estado
                    </th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($prestamos as $prestamo)
                <tr class="hover:bg-gray-50">
                    <!-- Columna Herramienta -->
                    <td class="px-3 py-2 whitespace-nowrap"> <!-- Padding reducido -->
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center"> <!-- Icono más pequeño -->
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"> <!-- SVG más pequeño -->
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z"></path>
                                </svg>
                            </div>
                            <div class="ml-2"> <!-- Margen reducido -->
                                <div class="font-medium text-gray-900"> <!-- text-sm removido -->
                                    {{ $prestamo->herramienta->nomHer ?? 'Sin herramienta' }}
                                </div>
                                <div class="text-gray-500">
                                    {{ $prestamo->herramienta->catHer ?? '' }}
                                </div>
                            </div>
                        </div>
                    </td>
                    
                    <!-- Columna Encargado -->
                    <td class="px-3 py-2 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center"> <!-- Icono más pequeño -->
                                <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"> <!-- SVG más pequeño -->
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div class="ml-2">
                                <div class="font-medium text-gray-900">
                                    {{ $prestamo->usuario->nomUsu ?? '' }} {{ $prestamo->usuario->apeUsu ?? '' }}
                                </div>
                                <div class="text-gray-500">
                                    {{ $prestamo->usuario->email ?? 'Sin usuario' }}
                                </div>
                            </div>
                        </div>
                    </td>
                    
                    <!-- Solicitante -->
                    <td class="px-3 py-2 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center">
                                <svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-2">
                                <div class="font-medium text-gray-900">
                                    {{ $prestamo->solicitante->nomUsu ?? '' }} {{ $prestamo->solicitante->apeUsu ?? '' }}
                                </div>
                                <div class="text-gray-500">
                                    {{ $prestamo->solicitante->numDocUsu ?? 'Sin documento' }}
                                </div>
                            </div>
                        </div>
                    </td>
                    
                    <!-- Fechas -->
                    <td class="px-3 py-2 whitespace-nowrap">
                        <div class="text-gray-900">{{ $prestamo->fecPre->format('d/m/Y H:i') }}</div>
                        <div class="text-gray-500">{{ $prestamo->fecPre->diffForHumans() }}</div>
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        @if($prestamo->fecDev)
                            <div class="text-gray-900">{{ $prestamo->fecDev->format('d/m/Y H:i') }}</div>
                            <div class="text-gray-500">{{ $prestamo->fecDev->diffForHumans() }}</div>
                        @else
                            <span class="text-gray-400">Sin programar</span>
                        @endif
                    </td>
                    
                    <!-- Estado -->
                    <td class="px-3 py-2 whitespace-nowrap">
                        @php
                            $estadoColors = [
                                'prestado' => 'bg-blue-100 text-blue-800',
                                'devuelto' => 'bg-green-100 text-green-800',
                                'vencido' => 'bg-red-100 text-red-800'
                            ];
                            $colorClass = $estadoColors[$prestamo->estPre] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xxs font-medium {{ $colorClass }}"> <!-- Texto más pequeño -->
                            {{ ucfirst($prestamo->estPre) }}
                        </span>
                    </td>
                    
                    <!-- Acciones -->
                    <td class="px-3 py-2 whitespace-nowrap font-medium">
                        <div class="flex space-x-1"> <!-- Espacio reducido entre iconos -->
                            <a href="{{ route('inventario.prestamos.show', $prestamo) }}" wire:navigate
                               class="text-blue-600 hover:text-blue-900" title="Ver detalles">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"> <!-- Icono más pequeño -->
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </a>

                            @if($this->puedeEditar($prestamo))
                            <a href="{{ route('inventario.prestamos.edit', $prestamo) }}" wire:navigate
                               class="text-indigo-600 hover:text-indigo-900" title="Editar (solo hoy)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            @endif

                            @if($prestamo->estPre === 'prestado')
                            <button wire:click="devolverPrestamo({{ $prestamo->idPreHer }})" 
                                    class="text-green-600 hover:text-green-900" title="Devolver">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
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
                @can('admin')
                <div class="mt-6">
                    <a href="{{ route('inventario.prestamos.create') }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Nuevo Préstamo
                    </a>
                </div>
                @endcan
            </div>
            @endif
        </div>
    </div>
<!-- Modal Devolver Préstamo -->
@if($prestamoParaDevolver)
<div class="fixed inset-0 bg-black/20 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg max-w-sm w-full">
        <h2 class="text-xl font-semibold mb-4">Devolver Préstamo</h2>
        
        <div class="mb-4">
            <label for="fechaDevolucion" class="block text-sm font-medium text-gray-700 mb-2">Fecha y Hora de Devolución</label>
            <input type="datetime-local" wire:model="fechaDevolucion" id="fechaDevolucion" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   value="{{ now()->format('Y-m-d\TH:i') }}">
        </div>
        
        <div class="mb-4">
            <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
            <textarea wire:model="observaciones" id="observaciones" rows="3"
                     class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
        </div>
        
        <div class="flex justify-end gap-3">
            <button wire:click="$set('prestamoParaDevolver', null)"
                    class="cursor-pointer px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">Cancelar</button>
            <button wire:click="confirmarDevolucion"
                    class="cursor-pointer px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                Confirmar Devolución
            </button>
        </div>
    </div>
</div>
@endif
</div>