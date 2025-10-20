<?php
// resources/views/livewire/inventario/insumos/show.php

use App\Models\Insumo;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public Insumo $insumo;
    public bool $showDeleteModal = false;

    public function mount(Insumo $insumo): void
    {
        $this->insumo = $insumo->load('proveedor');
    }

    public function with(): array
    {
        // Calcular stock actual
        $stockActual = $this->insumo->movimientos()
            ->selectRaw('
                SUM(CASE 
                    WHEN tipMovInv IN ("entrada", "apertura", "ajuste_pos") THEN cantMovInv
                    ELSE -cantMovInv
                END) as stock_total
            ')
            ->value('stock_total') ?? 0;

        // Últimos movimientos
        $ultimosMovimientos = $this->insumo->movimientos()
            ->orderBy('fecMovInv', 'desc')
            ->limit(5)
            ->get();

        // Estado del stock
        $estadoStock = 'normal';
        if ($this->insumo->stockMinIns && $stockActual <= $this->insumo->stockMinIns) {
            $estadoStock = 'critico';
        }

        // Estado de vencimiento
        $diasParaVencer = null;
        if ($this->insumo->fecVenIns) {
            $diasParaVencer = now()->diffInDays($this->insumo->fecVenIns, false);
        }

        return [
            'stockActual' => $stockActual,
            'ultimosMovimientos' => $ultimosMovimientos,
            'estadoStock' => $estadoStock,
            'diasParaVencer' => $diasParaVencer
        ];
    }

    public function eliminarInsumo(): void
    {
        try {
            $this->insumo->delete();
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Insumo eliminado correctamente'
            ]);
            $this->redirect(route('inventario.insumos.index'), navigate: true);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        $this->showDeleteModal = false;
    }
};
?>

@section('title', 'Detalles del Insumo')

<div class="flex items-center justify-center min-h-screen py-3">
    <div class="w-full max-w-7xl mx-auto bg-white/80 backdrop-blur-xl shadow rounded-3xl p-3 relative border border-white/20">
        <!-- Encabezado -->
        <div class="text-center mb-3">
            <div class="flex justify-center mb-1">
                <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <div class="w-4 h-4 text-white flex items-center justify-center">
                        <i class="fas fa-boxes text-sm"></i>
                    </div>
                </div>
            </div>
            <h1 class="text-base font-black bg-gradient-to-r from-gray-900 via-gray-800 to-green-800 bg-clip-text text-transparent mb-1">
                Detalles del Insumo
            </h1>
            <p class="text-gray-600 text-xs">Información completa del insumo</p>
        </div>

        <!-- Botones -->
        <div class="absolute top-2 right-2 flex space-x-2">
            <a href="{{ route('inventario.insumos.edit', $insumo->idIns) }}" wire:navigate
               class="group relative inline-flex items-center px-2 py-1 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                <svg class="w-3 h-3 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span class="relative z-10 text-xs">Editar</span>
            </a>
            <a href="{{ route('inventario.insumos.index') }}" wire:navigate
               class="group relative inline-flex items-center px-2 py-1 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                <svg class="w-3 h-3 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span class="relative z-10 text-xs">Volver</span>
            </a>
        </div>

        <!-- Alerta de Stock -->
        @if($estadoStock === 'critico')
            <div class="mb-2 bg-red-50 border border-red-200 text-red-600 px-3 py-2 rounded-2xl flex items-center text-xs">
                <svg class="w-3 h-3 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <strong>Stock Crítico:</strong> El stock actual ({{ number_format($stockActual, 2) }} {{ $insumo->uniIns }}) está por debajo del mínimo.
            </div>
        @endif

        <!-- Tarjeta de información principal -->
        <div class="border border-gray-300 rounded-3xl overflow-hidden bg-gradient-to-br from-white to-gray-50">
            <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#39A900]"></div>
            
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
                                    <p class="text-xs text-black font-semibold">{{ $insumo->idIns }}</p>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Nombre</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">{{ $insumo->nomIns }}</p>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Tipo</span>
                                    </div>
                                    <span class="inline-flex px-1.5 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ ucfirst($insumo->tipIns) }}
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
                                        $estadoClasses = match($insumo->estIns) {
                                            'disponible' => 'bg-gradient-to-r from-green-500 to-emerald-600 text-white',
                                            'agotado' => 'bg-gradient-to-r from-red-500 to-red-600 text-white',
                                            'vencido' => 'bg-gradient-to-r from-gray-500 to-gray-600 text-white',
                                            default => 'bg-gradient-to-r from-gray-500 to-gray-600 text-white'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center text-xs font-semibold px-1.5 py-0.5 rounded-full {{ $estadoClasses }} shadow-sm">
                                        {{ ucfirst($insumo->estIns) }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Unidad</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">{{ $insumo->uniIns }}</p>
                                </div>
                                @if($insumo->marIns)
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Marca</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">{{ $insumo->marIns }}</p>
                                </div>
                                @endif
                                @if($insumo->fecVenIns)
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Vencimiento</span>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-black font-semibold">{{ $insumo->fecVenIns->format('d/m/Y') }}</p>
                                        @if($diasParaVencer !== null)
                                            <p class="text-xs {{ $diasParaVencer < 0 ? 'text-red-600' : ($diasParaVencer <= 30 ? 'text-yellow-600' : 'text-green-600') }}">
                                                {{ $diasParaVencer < 0 ? 'Vencido' : $diasParaVencer . ' días' }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                @endif
                                @if($insumo->obsIns)
                                <div class="py-1">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Observaciones</span>
                                    </div>
                                    <p class="text-xs text-black">{{ $insumo->obsIns }}</p>
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
                            <!-- Stock Actual -->
                            <div class="text-center mb-3 p-2 bg-white rounded-xl border border-green-100 shadow-sm">
                                <div class="text-2xl font-black {{ $estadoStock === 'critico' ? 'text-red-600' : 'text-green-600' }} mb-1">
                                    {{ number_format($stockActual, 2) }}
                                </div>
                                <p class="text-xs text-gray-600 mb-1">{{ $insumo->uniIns }} disponibles</p>
                                @if($estadoStock === 'critico')
                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                        Stock Crítico
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                        Stock Normal
                                    </span>
                                @endif
                            </div>

                            <!-- Límites de Stock -->
                            <div class="grid grid-cols-2 gap-2 mb-3">
                                <div class="text-center p-2 bg-red-50 rounded-lg border border-red-100">
                                    <div class="text-xs text-gray-600 mb-0.5">Stock Mínimo</div>
                                    <div class="text-sm font-bold text-red-600">
                                        {{ $insumo->stockMinIns ? number_format($insumo->stockMinIns, 0) : 'N/A' }}
                                    </div>
                                </div>
                                <div class="text-center p-2 bg-green-50 rounded-lg border border-green-100">
                                    <div class="text-xs text-gray-600 mb-0.5">Stock Máximo</div>
                                    <div class="text-sm font-bold text-green-600">
                                        {{ $insumo->stockMaxIns ? number_format($insumo->stockMaxIns, 0) : 'N/A' }}
                                    </div>
                                </div>
                            </div>

                            <!-- Progreso de Stock -->
                            @if($insumo->stockMinIns && $insumo->stockMaxIns)
                            <div class="mb-3">
                                @php
                                    $porcentaje = min(100, (($stockActual ?? 0) / $insumo->stockMaxIns) * 100);
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
                                <div class="p-1.5 bg-blue-50 rounded border border-blue-100">
                                    <div class="text-xs text-gray-600">Total Movimientos</div>
                                    <div class="text-sm font-bold text-blue-600">{{ $insumo->movimientos()->count() }}</div>
                                </div>
                                <div class="p-1.5 bg-purple-50 rounded border border-purple-100">
                                    <div class="text-xs text-gray-600">Último Movimiento</div>
                                    <div class="text-sm font-bold text-purple-600">
                                        @if($ultimosMovimientos->first())
                                            {{ $ultimosMovimientos->first()->fecMovInv->diffForHumans() }}
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Proveedor -->
                    @if($insumo->proveedor)
                    <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Proveedor</h3>
                        </div>
                        <div class="bg-orange-50/50 border border-orange-200 rounded-xl p-2">
                            <div class="flex items-center mb-3">
                                <div class="flex-shrink-0 h-8 w-8">
                                    <div class="h-8 w-8 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-bold text-gray-900">{{ $insumo->proveedor->nomProve }}</h4>
                                    <p class="text-xs text-gray-500">{{ $insumo->proveedor->nitProve }}</p>
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                @if($insumo->proveedor->telProve)
                                <div class="flex items-center justify-between py-1 border-b border-orange-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Teléfono</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">{{ $insumo->proveedor->telProve }}</p>
                                </div>
                                @endif
                                
                                @if($insumo->proveedor->emailProve)
                                <div class="flex items-center justify-between py-1 border-b border-orange-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Email</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">{{ $insumo->proveedor->emailProve }}</p>
                                </div>
                                @endif
                                
                                @if($insumo->proveedor->dirProve)
                                <div class="py-1">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Dirección</span>
                                    </div>
                                    <p class="text-xs text-black">{{ $insumo->proveedor->dirProve }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-gray-500 to-gray-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Proveedor</h3>
                        </div>
                        <div class="bg-gray-50/50 border border-gray-200 rounded-xl p-2 text-center">
                            <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <h3 class="mt-1 text-sm font-medium text-gray-900">Sin Proveedor</h3>
                            <p class="mt-0.5 text-xs text-gray-500">Este insumo no tiene proveedor asignado.</p>
                            <div class="mt-3">
                                <a href="{{ route('inventario.insumos.edit', $insumo->idIns) }}" wire:navigate
                                   class="inline-flex items-center px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg text-xs">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Asignar Proveedor
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Información del Sistema -->
                    <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Información del Sistema</h3>
                        </div>
                        <div class="bg-purple-50/50 border border-purple-200 rounded-xl p-2">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between py-1 border-b border-purple-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Fecha de Creación</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">{{ $insumo->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-purple-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Última Actualización</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">{{ $insumo->updated_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <div class="flex items-center justify-between py-1">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Total de Movimientos</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">{{ $insumo->movimientos()->count() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Últimos Movimientos -->
        @if($ultimosMovimientos->count() > 0)
        <div class="mt-2 border border-gray-300 rounded-3xl overflow-hidden bg-gradient-to-br from-white to-gray-50">
            <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#9333EA]"></div>
            
            <div class="p-2">
                <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <div class="p-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Últimos Movimientos</h3>
                        </div>
                        <span class="bg-indigo-100 text-indigo-800 text-xs font-medium px-2 py-0.5 rounded-full">
                            {{ $ultimosMovimientos->count() }} movimientos
                        </span>
                    </div>

                    <div class="bg-indigo-50/50 border border-indigo-200 rounded-xl p-2">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-xs">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($ultimosMovimientos as $movimiento)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2 whitespace-nowrap">
                                                <div class="text-gray-900">{{ $movimiento->fecMovInv->format('d/m/Y') }}</div>
                                                <div class="text-gray-500 text-2xs">{{ $movimiento->fecMovInv->format('H:i') }}</div>
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap">
                                                @php
                                                    $tipoClasses = match($movimiento->tipMovInv) {
                                                        'entrada', 'apertura', 'ajuste_pos' => 'bg-green-100 text-green-800',
                                                        'salida', 'consumo', 'ajuste_neg' => 'bg-red-100 text-red-800',
                                                        default => 'bg-gray-100 text-gray-800'
                                                    };
                                                @endphp
                                                <span class="inline-flex px-1.5 py-0.5 text-2xs font-semibold rounded-full {{ $tipoClasses }}">
                                                    {{ ucfirst(str_replace('_', ' ', $movimiento->tipMovInv)) }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-gray-900 text-right">
                                                <span class="font-medium">{{ number_format($movimiento->cantMovInv, 2) }}</span> {{ $movimiento->uniMovInv }}
                                            </td>
                                            <td class="px-3 py-2 text-sm text-gray-500 text-xs">
                                                {{ $movimiento->obsInv ?: 'Sin observaciones' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
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