<?php
use App\Models\Herramienta;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;

    public Herramienta $herramienta;
    public $alertaStock = false;
    public $tipoAlerta = '';
    public $mensajeAlerta = '';
    public $showDeleteModal = false;

    public function mount(Herramienta $herramienta): void
    {
        $this->herramienta = $herramienta->load(['proveedor']);
        
        // Configurar alerta de stock
        $stockActual = $herramienta->stockActual ?? 0;
        $stockMin = $herramienta->stockMinHer ?? 0;
        
        if ($stockMin > 0) {
            if ($stockActual <= $stockMin) {
                $this->alertaStock = true;
                $this->tipoAlerta = 'danger';
                $this->mensajeAlerta = '¡Stock crítico! La cantidad actual está por debajo del mínimo establecido.';
            } elseif ($stockActual <= ($stockMin * 1.5)) {
                $this->alertaStock = true;
                $this->tipoAlerta = 'warning';
                $this->mensajeAlerta = 'Stock bajo. Considera reabastecer pronto.';
            }
        }
    }

    public function movimientos()
    {
        return $this->herramienta->movimientos()
            ->with('usuario')
            ->orderBy('fecMovInv', 'desc')
            ->paginate(10);
    }

    public function confirmDelete(): void
    {
        if (!$this->herramienta->puedeEliminar()) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No se puede eliminar: ' . $this->herramienta->razonNoEliminar()
            ]);
            return;
        }
        $this->showDeleteModal = true;
    }

    public function deleteHerramienta(): void
    {
        try {
            $this->herramienta->delete();
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Herramienta eliminada exitosamente'
            ]);
            $this->redirect(route('inventario.herramientas.index'), navigate: true);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ]);
        }
    }
}; ?>

@section('title', 'Detalles de herramienta')

<div class="flex items-center justify-center min-h-screen py-3">
    <div class="w-full max-w-7xl mx-auto bg-white/80 backdrop-blur-xl shadow rounded-3xl p-3 relative border border-white/20">
        <!-- Encabezado -->
        <div class="text-center mb-3">
            <div class="flex justify-center mb-1">
                <div class="p-2 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <div class="w-4 h-4 text-white flex items-center justify-center">
                            <i class="fas fa-tools text-sm"></i>
                    </div>
                </div>
            </div>
            <h1 class="text-base font-black bg-gradient-to-r from-gray-900 via-gray-800 to-blue-800 bg-clip-text text-transparent mb-1">
                Detalles de Herramienta
            </h1>
            <p class="text-gray-600 text-xs">Información completa de la herramienta</p>
        </div>

        <!-- Botones -->
        <div class="absolute top-2 right-2 flex space-x-2">
            <a href="{{ route('inventario.herramientas.edit', $herramienta->idHer) }}" wire:navigate
               class="group relative inline-flex items-center px-2 py-1 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                <svg class="w-3 h-3 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span class="relative z-10 text-xs">Editar</span>
            </a>
            <a href="{{ route('inventario.herramientas.index') }}" wire:navigate
               class="group relative inline-flex items-center px-2 py-1 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                <svg class="w-3 h-3 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span class="relative z-10 text-xs">Volver</span>
            </a>
        </div>

        <!-- Alerta de Stock -->
        @if($alertaStock)
            <div class="mb-2 {{ $tipoAlerta == 'danger' ? 'bg-red-50 border-red-200 text-red-600' : 'bg-yellow-50 border-yellow-200 text-yellow-600' }} border px-3 py-2 rounded-2xl flex items-center text-xs">
                <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <strong>Alerta de Stock:</strong> {{ $mensajeAlerta }}
            </div>
        @endif

        <!-- Tarjeta de información principal -->
        <div class="border border-gray-300 rounded-3xl overflow-hidden bg-gradient-to-br from-white to-gray-50">
            <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#0066CC]"></div>
            
            <div class="p-2">
                <!-- Grid de información -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                    <!-- Información General -->
                    <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Información General</h3>
                        </div>
                        <div class="bg-blue-50/50 border border-blue-200 rounded-xl p-2">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">ID</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">{{ $herramienta->idHer }}</p>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Nombre</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">{{ $herramienta->nomHer }}</p>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Categoría</span>
                                    </div>
                                    <span class="inline-flex px-1.5 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ ucfirst($herramienta->catHer) }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Estado</span>
                                    </div>
                                    @php
                                        $estadoClasses = match($herramienta->estHer) {
                                            'bueno' => 'bg-gradient-to-r from-green-500 to-emerald-600 text-white',
                                            'regular' => 'bg-gradient-to-r from-yellow-500 to-yellow-600 text-white', 
                                            'malo' => 'bg-gradient-to-r from-red-500 to-red-600 text-white',
                                            default => 'bg-gradient-to-r from-gray-500 to-gray-600 text-white'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center text-xs font-semibold px-1.5 py-0.5 rounded-full {{ $estadoClasses }} shadow-sm">
                                        {{ ucfirst($herramienta->estHer) }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Ubicación</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">{{ $herramienta->ubiHer ?? 'No especificada' }}</p>
                                </div>
                                @if($herramienta->proveedor)
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                        </svg>
                                        <span class="text-xs text-black font-medium">Proveedor</span>
                                    </div>
                                    <p class="text-xs text-blue-600 font-semibold hover:text-blue-800 cursor-pointer">
                                        {{ $herramienta->proveedor->nomProve }}
                                    </p>
                                </div>
                                @endif
                                @if($herramienta->obsHer)
                                <div class="py-1">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Observaciones</span>
                                    </div>
                                    <p class="text-xs text-black">{{ $herramienta->obsHer }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Control de Stock -->
                    <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Control de Stock</h3>
                        </div>
                        <div class="bg-green-50/50 border border-green-200 rounded-xl p-2">
                            <!-- Stock Principal -->
                            <div class="text-center mb-3 p-2 bg-white rounded-xl border border-green-100 shadow-sm">
                                <div class="text-2xl font-black {{ $alertaStock && $tipoAlerta == 'danger' ? 'text-red-600' : ($alertaStock ? 'text-yellow-600' : 'text-green-600') }} mb-1">
                                    {{ $herramienta->stockActual ?? 0 }}
                                </div>
                                <p class="text-xs text-gray-600 mb-1">unidades disponibles</p>
                                @if($alertaStock)
                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full {{ $tipoAlerta == 'danger' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        @if($tipoAlerta == 'danger')
                                            Stock Crítico
                                        @else
                                            Stock Bajo
                                        @endif
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                        Stock Normal
                                    </span>
                                @endif
                            </div>

                            <!-- Límites de Stock -->
                            @if($herramienta->stockMinHer || $herramienta->stockMaxHer)
                            <div class="grid grid-cols-2 gap-2 mb-3">
                                @if($herramienta->stockMinHer)
                                <div class="text-center p-2 bg-red-50 rounded-lg border border-red-100">
                                    <div class="text-xs text-gray-600 mb-0.5">Stock Mínimo</div>
                                    <div class="text-sm font-bold text-red-600">{{ $herramienta->stockMinHer }}</div>
                                </div>
                                @endif
                                @if($herramienta->stockMaxHer)
                                <div class="text-center p-2 bg-green-50 rounded-lg border border-green-100">
                                    <div class="text-xs text-gray-600 mb-0.5">Stock Máximo</div>
                                    <div class="text-sm font-bold text-green-600">{{ $herramienta->stockMaxHer }}</div>
                                </div>
                                @endif
                            </div>
                            @endif

                            <!-- Progreso de Stock -->
                            @if($herramienta->stockMinHer && $herramienta->stockMaxHer)
                            <div class="mb-3">
                                @php
                                    $porcentaje = min(100, (($herramienta->stockActual ?? 0) / $herramienta->stockMaxHer) * 100);
                                    $colorProgreso = $porcentaje <= 30 ? 'bg-red-500' : ($porcentaje <= 50 ? 'bg-yellow-500' : 'bg-green-500');
                                @endphp
                                <div class="flex justify-between text-xs text-gray-600 mb-1">
                                    <span>Nivel de Stock</span>
                                    <span>{{ round($porcentaje) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                    <div class="{{ $colorProgreso }} h-1.5 rounded-full transition-all duration-300" style="width: {{ $porcentaje }}%"></div>
                                </div>
                            </div>
                            @endif

                            <!-- Información Adicional -->
                            <div class="grid grid-cols-2 gap-2 text-center">
                                @if($herramienta->cantidadPrestada && $herramienta->cantidadPrestada > 0)
                                <div class="p-1.5 bg-yellow-50 rounded border border-yellow-100">
                                    <div class="text-xs text-gray-600">Prestadas</div>
                                    <div class="text-sm font-bold text-yellow-600">{{ $herramienta->cantidadPrestada }}</div>
                                </div>
                                @endif
                                <div class="p-1.5 bg-blue-50 rounded border border-blue-100">
                                    <div class="text-xs text-gray-600">Valor Inventario</div>
                                    <div class="text-sm font-bold text-blue-600">${{ number_format($herramienta->valorInventario ?? 0, 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones Rápidas -->
                    <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Acciones Rápidas</h3>
                        </div>
                        <div class="bg-yellow-50/50 border border-yellow-200 rounded-xl p-2">
                            <div class="grid grid-cols-2 gap-2">
                                <a href="#" class="flex flex-col items-center p-2 bg-green-50 hover:bg-green-100 rounded-lg border border-green-100 transition duration-150 ease-in-out">
                                    <svg class="w-4 h-4 text-green-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    <span class="text-xs font-medium text-green-800">Entrada</span>
                                </a>
                                <a href="#" class="flex flex-col items-center p-2 bg-red-50 hover:bg-red-100 rounded-lg border border-red-100 transition duration-150 ease-in-out">
                                    <svg class="w-4 h-4 text-red-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                    </svg>
                                    <span class="text-xs font-medium text-red-800">Salida</span>
                                </a>
                                <a href="#" class="flex flex-col items-center p-2 bg-yellow-50 hover:bg-yellow-100 rounded-lg border border-yellow-100 transition duration-150 ease-in-out">
                                    <svg class="w-4 h-4 text-yellow-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h4a1 1 0 011 1v2h4a1 1 0 110 2h-1v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6H3a1 1 0 110-2h4z"></path>
                                    </svg>
                                    <span class="text-xs font-medium text-yellow-800">Préstamo</span>
                                </a>
                                <a href="#" class="flex flex-col items-center p-2 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-100 transition duration-150 ease-in-out">
                                    <svg class="w-4 h-4 text-blue-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    </svg>
                                    <span class="text-xs font-medium text-blue-800">Mantenimiento</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Sistema -->
                    <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-gray-500 to-gray-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Información del Sistema</h3>
                        </div>
                        <div class="bg-gray-50/50 border border-gray-200 rounded-xl p-2">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between py-1 border-b border-gray-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Fecha de Creación</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">{{ $herramienta->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-gray-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Última Actualización</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">{{ $herramienta->updated_at->format('d/m/Y H:i') }}</p>
                                </div>
                                @if($herramienta->ultimoMovimiento)
                                <div class="flex items-center justify-between py-1 border-b border-gray-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Último Movimiento</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">{{ $herramienta->ultimoMovimiento->format('d/m/Y H:i') }}</p>
                                </div>
                                @endif
                                <div class="flex items-center justify-between py-1">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Total de Movimientos</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">{{ $herramienta->totalMovimientos ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historial de Movimientos -->
        <div class="mt-2 border border-gray-300 rounded-3xl overflow-hidden bg-gradient-to-br from-white to-gray-50">
            <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#9333EA]"></div>
            
            <div class="p-2">
                <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <div class="p-1.5 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Historial de Movimientos</h3>
                        </div>
                        <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2 py-0.5 rounded-full">
                            {{ $this->movimientos()->total() }} movimientos
                        </span>
                    </div>

                    <div class="bg-purple-50/50 border border-purple-200 rounded-xl p-2">
                        @if($this->movimientos()->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-xs">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Costo</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($this->movimientos() as $movimiento)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <div class="text-gray-900">{{ $movimiento->fecMovInv->format('d/m/Y') }}</div>
                                            <div class="text-gray-500 text-2xs">{{ $movimiento->fecMovInv->format('H:i') }}</div>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            @php
                                                $tipoClasses = match($movimiento->tipMovInv) {
                                                    'entrada', 'apertura' => 'bg-green-100 text-green-800',
                                                    'salida', 'consumo' => 'bg-red-100 text-red-800',
                                                    'prestamo_salida' => 'bg-yellow-100 text-yellow-800',
                                                    'prestamo_retorno' => 'bg-blue-100 text-blue-800',
                                                    'mantenimiento' => 'bg-purple-100 text-purple-800',
                                                    default => 'bg-gray-100 text-gray-800'
                                                };
                                                $tipoTexto = match($movimiento->tipMovInv) {
                                                    'entrada' => 'Entrada',
                                                    'salida' => 'Salida',
                                                    'consumo' => 'Consumo',
                                                    'prestamo_salida' => 'Préstamo',
                                                    'prestamo_retorno' => 'Retorno',
                                                    'mantenimiento' => 'Mantenimiento',
                                                    'apertura' => 'Apertura',
                                                    default => ucfirst($movimiento->tipMovInv)
                                                };
                                            @endphp
                                            <span class="inline-flex px-1.5 py-0.5 text-2xs font-semibold rounded-full {{ $tipoClasses }}">
                                                {{ $tipoTexto }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-gray-900">
                                            <span class="font-medium">{{ $movimiento->cantMovInv }}</span> {{ $movimiento->uniMovInv }}
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            @if($movimiento->costoTotInv)
                                                <span class="font-medium text-green-600 text-xs">
                                                    ${{ number_format($movimiento->costoTotInv, 0, ',', '.') }}
                                                </span>
                                            @else
                                                <span class="text-gray-400 text-xs">N/A</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-gray-900 text-xs">
                                            @if($movimiento->usuario)
                                                {{ $movimiento->usuario->nomUsu }} {{ $movimiento->usuario->apeUsu }}
                                            @else
                                                <span class="text-gray-400">Sistema</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="mt-2 px-3 py-2 bg-gray-50 rounded-lg">
                            {{ $this->movimientos()->links() }}
                        </div>
                        @else
                        <div class="text-center py-4">
                            <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h3 class="mt-1 text-sm font-medium text-gray-900">Sin Movimientos</h3>
                            <p class="mt-0.5 text-xs text-gray-500">Esta herramienta no tiene movimientos registrados aún.</p>
                            <div class="mt-3">
                                <a href="{{ route('inventario.movimientos.create') }}" wire:navigate class="inline-flex items-center px-3 py-1 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg text-xs">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Registrar movimiento
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
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