<?php
use App\Models\PrestamoHerramienta;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public PrestamoHerramienta $prestamo;
    public $showReturnModal = false;
    public $fecDev;
    public $obsPre;

    public function mount(PrestamoHerramienta $prestamo): void
    {
        $this->prestamo = $prestamo;
        $this->fecDev = now()->format('Y-m-d\TH:i'); // Formato datetime-local
    }

    public function confirmReturn(): void
    {
        $this->validate([
            'fecDev' => 'required|date|after_or_equal:' . $this->prestamo->fecPre->format('Y-m-d'),
            'obsPre' => 'nullable|string'
        ]);

        $this->showReturnModal = true;
    }

    public function returnTool(): void
    {
        $this->prestamo->update([
            'fecDev' => $this->fecDev,
            'estPre' => 'devuelto',
            'obsPre' => $this->obsPre
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Herramienta devuelta exitosamente'
        ]);

        $this->redirect(route('inventario.prestamos.index'), navigate: true);
    }

    public function deletePrestamo(): void
    {
        try {
            $this->prestamo->delete();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Préstamo eliminado exitosamente'
            ]);

            $this->redirect(route('inventario.prestamos.index'), navigate: true);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar el préstamo: ' . $e->getMessage()
            ]);
        }
    }
}; ?>

@section('title', 'Detalles del Préstamo #' . $prestamo->idPreHer)

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        @php
                            $iconoColor = match($prestamo->estPre) {
                                'prestado' => 'text-blue-600',
                                'devuelto' => 'text-green-600',
                                'vencido' => 'text-red-600',
                                default => 'text-blue-600'
                            };
                        @endphp
                        <svg class="w-8 h-8 {{ $iconoColor }} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        Préstamo #{{ $prestamo->idPreHer }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">Detalles completos del préstamo de herramienta</p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <a href="{{ route('inventario.prestamos.index') }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver
                    </a>
                    @can('admin')
                    @if($prestamo->estPre !== 'devuelto')
                    <a href="{{ route('inventario.prestamos.edit', $prestamo) }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar
                    </a>
                    @endif
                    @endcan
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Información Principal -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Datos del Préstamo -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-blue-600 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Información del Préstamo
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Solicitante -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Solicitante</label>
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $prestamo->solicitante->nomUsu ?? '' }} {{ $prestamo->solicitante->apeUsu ?? '' }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $prestamo->solicitante->numDocUsu ?? 'Sin documento' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Estado -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                                <div class="flex items-center">
                                    @php
                                        $estadoColors = [
                                            'prestado' => 'bg-blue-100 text-blue-800',
                                            'devuelto' => 'bg-green-100 text-green-800',
                                            'vencido' => 'bg-red-100 text-red-800'
                                        ];
                                        $colorClass = $estadoColors[$prestamo->estPre] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $colorClass }}">
                                        {{ ucfirst($prestamo->estPre) }}
                                    </span>
                                    
                                    @if($prestamo->estPre === 'vencido')
                                    <div class="ml-3 flex items-center text-red-600">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-xs font-medium">Requiere atención</span>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Fecha de Préstamo -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Préstamo</label>
                                <div class="flex items-center text-gray-900">
                                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <div>
                                        <p class="font-medium">{{ $prestamo->fecPre->format('d/m/Y H:i') }}</p>
                                        <p class="text-sm text-gray-500">{{ $prestamo->fecPre->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Fecha de Devolución -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    @if($prestamo->estPre === 'devuelto')
                                        Fecha de Devolución Real
                                    @else
                                        Fecha de Devolución Programada
                                    @endif
                                </label>
                                @if($prestamo->fecDev)
                                <div class="flex items-center text-gray-900">
                                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($prestamo->estPre === 'devuelto')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        @endif
                                    </svg>
                                    <div>
                                        <p class="font-medium">{{ $prestamo->fecDev->format('d/m/Y H:i') }}</p>
                                        <p class="text-sm text-gray-500">{{ $prestamo->fecDev->diffForHumans() }}</p>
                                    </div>
                                </div>
                                @else
                                <div class="flex items-center text-gray-500">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-sm">No programada</span>
                                </div>
                                @endif
                            </div>

                            <!-- Tiempo transcurrido -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tiempo transcurrido</label>
                                <div class="flex items-center text-gray-900">
                                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        @php
                                            $dias = $prestamo->fecPre->diffInDays(now());
                                            $horas = $prestamo->fecPre->diffInHours(now()) % 24;
                                            $minutos = $prestamo->fecPre->diffInMinutes(now()) % 60;
                                        @endphp
                                        <p class="font-medium">
                                            @if($dias > 0)
                                                {{ $dias }} día{{ $dias > 1 ? 's' : '' }}, 
                                            @endif
                                            @if($horas > 0)
                                                {{ $horas }} hora{{ $horas > 1 ? 's' : '' }}, 
                                            @endif
                                            {{ $minutos }} minuto{{ $minutos != 1 ? 's' : '' }}
                                        </p>
                                        <p class="text-sm text-gray-500">{{ $prestamo->fecPre->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Observaciones -->
                        @if($prestamo->obsPre)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-gray-900">{{ $prestamo->obsPre }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Historial y Movimientos -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-gray-600 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Historial y Registro
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <!-- Registro inicial -->
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">Préstamo creado</div>
                                    <div class="text-sm text-gray-500">{{ $prestamo->created_at->format('d/m/Y H:i') }} - {{ $prestamo->created_at->diffForHumans() }}</div>
                                </div>
                            </div>

                            @if($prestamo->updated_at != $prestamo->created_at)
                            <!-- Última modificación -->
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">Última modificación</div>
                                    <div class="text-sm text-gray-500">{{ $prestamo->updated_at->format('d/m/Y H:i') }} - {{ $prestamo->updated_at->diffForHumans() }}</div>
                                </div>
                            </div>
                            @endif

                            @if($prestamo->estPre === 'devuelto' && $prestamo->fecDev)
                            <!-- Devolución -->
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">Herramienta devuelta</div>
                                    <div class="text-sm text-gray-500">{{ $prestamo->fecDev->format('d/m/Y') }} - {{ $prestamo->fecDev->diffForHumans() }}</div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Movimientos relacionados -->
                        @if($prestamo->movimientos && $prestamo->movimientos->count() > 0)
                        <div class="mt-6">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Movimientos de Inventario Relacionados</h4>
                            <div class="space-y-3">
                                @foreach($prestamo->movimientos as $movimiento)
                                <div class="border border-gray-200 rounded-lg p-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ ucfirst($movimiento->tipMovInv) }}</p>
                                            <p class="text-xs text-gray-500">{{ $movimiento->fecMovInv->format('d/m/Y H:i') }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-medium text-gray-900">{{ number_format($movimiento->cantMovInv, 2) }} {{ $movimiento->uniMovInv }}</p>
                                            @if($movimiento->costoTotInv)
                                            <p class="text-xs text-gray-500">${{ number_format($movimiento->costoTotInv, 2) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Panel Lateral -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Herramienta -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-purple-600 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z"></path>
                            </svg>
                            Herramienta Prestada
                        </h3>
                    </div>
                    <div class="p-6">
                        @if($prestamo->herramienta)
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="font-medium text-gray-900">{{ $prestamo->herramienta->nomHer }}</p>
                                <p class="text-sm text-gray-500">{{ $prestamo->herramienta->catHer ?? 'Sin categoría' }}</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @if($prestamo->herramienta->marHer)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                                <p class="text-gray-900">{{ $prestamo->herramienta->marHer }}</p>
                            </div>
                            @endif

                            @if($prestamo->herramienta->modHer)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
                                <p class="text-gray-900">{{ $prestamo->herramienta->modHer }}</p>
                            </div>
                            @endif

                            @if($prestamo->herramienta->estHer)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <p class="text-gray-900">{{ ucfirst($prestamo->herramienta->estHer) }}</p>
                            </div>
                            @endif
                        </div>
                        @can('admin')
                        <div class="mt-4">
                            <a href="{{ route('inventario.herramientas.show', $prestamo->herramienta) }}" wire:navigate
                               class="w-full inline-flex items-center justify-center px-3 py-2 border border-purple-300 rounded-md shadow-sm text-sm font-medium text-purple-700 bg-white hover:bg-purple-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                Ver Herramienta Completa
                            </a>
                        </div>
                        @endcan
                        @else
                        <p class="text-gray-500">Herramienta no encontrada</p>
                        @endif
                    </div>
                </div>

                <!-- Usuario -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-orange-600 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Encargado
                        </h3>
                    </div>
                    <div class="p-6">
                        @if($prestamo->usuario)
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="font-medium text-gray-900">{{ $prestamo->usuario->nomUsu ?? '' }} {{ $prestamo->usuario->apeUsu ?? '' }}</p>
                                <p class="text-sm text-gray-500">{{ $prestamo->usuario->email ?? '' }}</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @if($prestamo->usuario->telefono)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                                <p class="text-gray-900">{{ $prestamo->usuario->telefono }}</p>
                            </div>
                            @endif

                            @if($prestamo->usuario->departamento)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
                                <p class="text-gray-900">{{ $prestamo->usuario->departamento }}</p>
                            </div>
                            @endif
                        </div>
                        @else
                        <p class="text-gray-500">Usuario no encontrado</p>
                        @endif
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                @can('admin')
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-blue-600 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Acciones
                        </h3>
                    </div>
                    <div class="p-6 space-y-3">
                        @if($prestamo->estPre !== 'devuelto')
                        <a href="{{ route('inventario.prestamos.edit', $prestamo) }}" wire:navigate
                           class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Editar Préstamo
                        </a>
                        @endif

                        @if($prestamo->estPre === 'prestado')
                        <button wire:click="confirmReturn" 
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Devolver Herramienta
                        </button>
                        @endif

                        @if($prestamo->estPre === 'prestado')
                        <button wire:click="deletePrestamo" 
                                wire:confirm="¿Está seguro de eliminar este préstamo? Esta acción no se puede deshacer."
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Eliminar Préstamo
                        </button>
                        @endif
                        
                        <a href="{{ route('inventario.prestamos.create') }}" wire:navigate
                           class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Nuevo Préstamo
                        </a>
                    </div>
                </div>
                @endcan
            </div>
        </div>
    </div>
</div>

<!-- Modal para devolver préstamo -->
@if($showReturnModal)
<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Devolver Herramienta</h3>
            <div class="mt-4">
                <div class="mb-4">
                    <label for="fecDev" class="block text-sm font-medium text-gray-700 mb-2 text-left">
                        Fecha y Hora de Devolución <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" wire:model="fecDev" id="fecDev" required
                           min="{{ $prestamo->fecPre->format('Y-m-d\TH:i') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    @error('fecDev') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label for="obsPre" class="block text-sm font-medium text-gray-700 mb-2 text-left">
                        Observaciones de Devolución
                    </label>
                    <textarea wire:model="obsPre" id="obsPre" rows="3"
                              placeholder="Estado de la herramienta al momento de la devolución..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                </div>

                <div class="flex space-x-3">
                    <button wire:click="$set('showReturnModal', false)" 
                            class="flex-1 px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out">
                        Cancelar
                    </button>
                    <button wire:click="returnTool" 
                            class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                        Devolver
                    </button>
                </div>
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