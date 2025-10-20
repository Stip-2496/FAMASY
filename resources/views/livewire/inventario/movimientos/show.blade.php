<?php
use App\Models\Inventario;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public Inventario $movimiento;
    public $showDeleteModal = false;

    public function mount(Inventario $movimiento)
    {
        $this->movimiento = $movimiento->load(['insumo', 'herramienta', 'proveedor', 'usuario']);
    }

    public function confirmDelete(): void
    {
        $this->showDeleteModal = true;
    }

    public function deleteMovimiento(): void
    {
        try {
            $this->movimiento->delete();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Movimiento eliminado correctamente'
            ]);

            $this->redirect(route('inventario.movimientos.index'), navigate: true);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar el movimiento: ' . $e->getMessage()
            ]);
        }
    }
}; ?>

@section('title', 'Detalles de Movimiento')

<div class="flex items-center justify-center min-h-screen py-3">
    <div class="w-full max-w-7xl mx-auto bg-white/80 backdrop-blur-xl shadow rounded-3xl p-3 relative border border-white/20">
        <!-- Encabezado -->
        <div class="text-center mb-3">
            <div class="flex justify-center mb-1">
                <div class="p-2 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <div class="w-4 h-4 text-white flex items-center justify-center">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <h1 class="text-base font-black bg-gradient-to-r from-gray-900 via-gray-800 to-blue-800 bg-clip-text text-transparent mb-1">
                Detalles de Movimiento
            </h1>
            <p class="text-gray-600 text-xs">Información completa del movimiento de inventario</p>
        </div>

        <!-- Botones -->
        <div class="absolute top-2 right-2 flex space-x-2">
            <a href="{{ route('inventario.movimientos.edit', $movimiento->idInv) }}" wire:navigate
               class="group relative inline-flex items-center px-2 py-1 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                <svg class="w-3 h-3 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span class="relative z-10 text-xs">Editar</span>
            </a>
            <a href="{{ route('inventario.movimientos.index') }}" wire:navigate
               class="group relative inline-flex items-center px-2 py-1 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                <svg class="w-3 h-3 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span class="relative z-10 text-xs">Volver</span>
            </a>
        </div>

        <!-- Tarjeta de información principal -->
        <div class="border border-gray-300 rounded-3xl overflow-hidden bg-gradient-to-br from-white to-gray-50">
            <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#0066CC]"></div>
            
            <div class="p-2">
                <!-- Grid de información -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                    <!-- Información del Movimiento -->
                    <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Información del Movimiento</h3>
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
                                    <p class="text-xs text-black font-semibold">{{ $movimiento->idInv }}</p>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Tipo</span>
                                    </div>
                                    @php
                                        $tipoColors = [
                                            'apertura' => 'bg-green-100 text-green-800',
                                            'entrada' => 'bg-green-100 text-green-800',
                                            'salida' => 'bg-red-100 text-red-800',
                                            'consumo' => 'bg-orange-100 text-orange-800',
                                            'prestamo_salida' => 'bg-blue-100 text-blue-800',
                                            'prestamo_retorno' => 'bg-indigo-100 text-indigo-800',
                                            'perdida' => 'bg-gray-100 text-gray-800',
                                            'ajuste_pos' => 'bg-green-100 text-green-800',
                                            'ajuste_neg' => 'bg-red-100 text-red-800',
                                            'mantenimiento' => 'bg-purple-100 text-purple-800',
                                            'venta' => 'bg-indigo-100 text-indigo-800'
                                        ];
                                        $colorClass = $tipoColors[$movimiento->tipMovInv] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex px-1.5 py-0.5 text-xs font-semibold rounded-full {{ $colorClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $movimiento->tipMovInv)) }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Fecha</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">{{ $movimiento->fecMovInv ? $movimiento->fecMovInv->format('d/m/Y H:i') : 'No especificada' }}</p>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Item</span>
                                    </div>
                                    @if($movimiento->insumo)
                                        <div class="flex items-center">
                                            <span class="inline-flex px-1.5 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800 mr-2">
                                                Insumo
                                            </span>
                                            <div>
                                                <p class="text-xs text-black font-semibold">{{ $movimiento->insumo->nomIns }}</p>
                                                <p class="text-[10px] text-gray-500">{{ $movimiento->insumo->tipIns ?? '' }} - {{ $movimiento->insumo->marIns ?? '' }}</p>
                                            </div>
                                        </div>
                                    @elseif($movimiento->herramienta)
                                        <div class="flex items-center">
                                            <span class="inline-flex px-1.5 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 mr-2">
                                                Herramienta
                                            </span>
                                            <div>
                                                <p class="text-xs text-black font-semibold">{{ $movimiento->herramienta->nomHer }}</p>
                                                <p class="text-[10px] text-gray-500">{{ $movimiento->herramienta->catHer ?? '' }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-xs text-gray-500">Item no especificado</p>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Cantidad</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">{{ number_format($movimiento->cantMovInv, 2) }} {{ $movimiento->uniMovInv }}</p>
                                </div>
                                @if($movimiento->costoTotInv)
                                    <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span class="text-xs text-black font-medium">Costo Total</span>
                                        </div>
                                        <p class="text-xs text-green-600 font-semibold">${{ number_format($movimiento->costoTotInv, 2) }}
                                            @if($movimiento->costoUnitInv)
                                                <span class="text-[10px] text-gray-500">(${{ number_format($movimiento->costoUnitInv, 2) }}/{{ $movimiento->uniMovInv }})</span>
                                            @endif
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Información Adicional -->
                    @if($movimiento->loteInv || $movimiento->fecVenceInv || $movimiento->obsInv)
                        <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                            <div class="flex items-center space-x-2 mb-2">
                                <div class="p-1.5 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl shadow-md">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xs font-bold text-gray-900">Información Adicional</h3>
                            </div>
                            <div class="bg-purple-50/50 border border-purple-200 rounded-xl p-2">
                                <div class="space-y-2">
                                    @if($movimiento->loteInv)
                                        <div class="flex items-center justify-between py-1 border-b border-purple-100">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                <span class="text-xs text-black font-medium">Lote/Serie</span>
                                            </div>
                                            <p class="text-xs text-black font-semibold">{{ $movimiento->loteInv }}</p>
                                        </div>
                                    @endif
                                    @if($movimiento->fecVenceInv)
                                        <div class="flex items-center justify-between py-1 border-b border-purple-100">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span class="text-xs text-black font-medium">Fecha de Vencimiento</span>
                                            </div>
                                            <div class="flex items-center">
                                                <p class="text-xs text-black font-semibold">{{ $movimiento->fecVenceInv->format('d/m/Y') }}</p>
                                                @php
                                                    $diasParaVencer = $movimiento->fecVenceInv->diffInDays(now(), false);
                                                @endphp
                                                @if($diasParaVencer > 0)
                                                    <span class="ml-2 inline-flex px-1.5 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                        Vencido hace {{ $diasParaVencer }} días
                                                    </span>
                                                @elseif($diasParaVencer > -30)
                                                    <span class="ml-2 inline-flex px-1.5 py-0.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        Vence en {{ abs($diasParaVencer) }} días
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                    @if($movimiento->obsInv)
                                        <div class="py-1">
                                            <div class="flex items-center space-x-2 mb-1">
                                                <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                                </svg>
                                                <span class="text-xs text-black font-medium">Observaciones</span>
                                            </div>
                                            <p class="text-xs text-black">{{ $movimiento->obsInv }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Proveedor y Registro -->
                <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <!-- Proveedor -->
                        @if($movimiento->proveedor)
                            <div class="bg-white rounded-xl p-2 border border-gray-200">
                                <div class="flex items-center space-x-2 mb-2">
                                    <div class="p-1.5 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl shadow-md">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-xs font-bold text-gray-900">Proveedor</h3>
                                </div>
                                <div class="bg-orange-50/50 border border-orange-200 rounded-xl p-2">
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between py-1 border-b border-orange-100">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                </svg>
                                                <span class="text-xs text-black font-medium">Empresa</span>
                                            </div>
                                            <p class="text-xs text-black font-semibold">{{ $movimiento->proveedor->nomProve ?? '' }}</p>
                                        </div>
                                        @if($movimiento->proveedor->conProve)
                                            <div class="flex items-center justify-between py-1 border-b border-orange-100">
                                                <div class="flex items-center space-x-2">
                                                    <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                    <span class="text-xs text-black font-medium">Contacto</span>
                                                </div>
                                                <p class="text-xs text-black font-semibold">{{ $movimiento->proveedor->conProve }}</p>
                                            </div>
                                        @endif
                                        @if($movimiento->proveedor->telProve)
                                            <div class="flex items-center justify-between py-1 border-b border-orange-100">
                                                <div class="flex items-center space-x-2">
                                                    <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                    </svg>
                                                    <span class="text-xs text-black font-medium">Teléfono</span>
                                                </div>
                                                <p class="text-xs text-black font-semibold">{{ $movimiento->proveedor->telProve }}</p>
                                            </div>
                                        @endif
                                        @if($movimiento->proveedor->tipSumProve)
                                            <div class="flex items-center justify-between py-1">
                                                <div class="flex items-center space-x-2">
                                                    <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                                    </svg>
                                                    <span class="text-xs text-black font-medium">Tipo de Suministro</span>
                                                </div>
                                                <p class="text-xs text-black font-semibold">{{ $movimiento->proveedor->tipSumProve }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Registro -->
                        <div class="bg-white rounded-xl p-2 border border-gray-200">
                            <div class="flex items-center space-x-2 mb-2">
                                <div class="p-1.5 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl shadow-md">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xs font-bold text-gray-900">Registro</h3>
                            </div>
                            <div class="bg-yellow-50/50 border border-yellow-200 rounded-xl p-2">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between py-1 border-b border-yellow-100">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            <span class="text-xs text-black font-medium">Registrado por</span>
                                        </div>
                                        @if($movimiento->usuario)
                                            <div>
                                                <p class="text-xs text-black font-semibold">{{ $movimiento->usuario->nomUsu ?? '' }} {{ $movimiento->usuario->apeUsu ?? '' }}</p>
                                                <p class="text-[10px] text-gray-500">{{ $movimiento->usuario->email ?? '' }}</p>
                                            </div>
                                        @else
                                            <p class="text-xs text-gray-500">Usuario no disponible</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center justify-between py-1 border-b border-yellow-100">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <span class="text-xs text-black font-medium">Fecha de Creación</span>
                                        </div>
                                        <p class="text-xs text-black font-semibold">{{ $movimiento->created_at ? $movimiento->created_at->format('d/m/Y H:i') : 'No disponible' }}</p>
                                    </div>
                                    @if($movimiento->updated_at && $movimiento->updated_at != $movimiento->created_at)
                                        <div class="flex items-center justify-between py-1">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                </svg>
                                                <span class="text-xs text-black font-medium">Última Modificación</span>
                                            </div>
                                            <p class="text-xs text-black font-semibold">{{ $movimiento->updated_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Eliminar Movimiento -->
@if($showDeleteModal)
    <div class="fixed inset-0 bg-black/20 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-3 rounded-2xl max-w-sm w-full border border-gray-200 shadow-xl">
            <h2 class="text-base font-semibold mb-2">Confirmar eliminación</h2>
            <p class="mb-2 text-xs text-gray-600">
                ¿Está seguro que desea eliminar este movimiento? Esta acción no se puede deshacer.
            </p>
            
            <div class="mb-2 bg-gray-50 rounded-xl p-2 text-xs">
                <p><strong>Tipo:</strong> {{ ucfirst(str_replace('_', ' ', $movimiento->tipMovInv)) }}</p>
                <p><strong>Item:</strong> {{ $movimiento->insumo ? $movimiento->insumo->nomIns : ($movimiento->herramienta ? $movimiento->herramienta->nomHer : 'No especificado') }}</p>
                <p><strong>Cantidad:</strong> {{ $movimiento->cantMovInv }} {{ $movimiento->uniMovInv }}</p>
                <p><strong>ID:</strong> {{ $movimiento->idInv }}</p>
            </div>
            
            <div class="flex justify-end gap-2">
                <button wire:click="$set('showDeleteModal', false)"
                        class="cursor-pointer px-2 py-1 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 text-xs">
                    Cancelar
                </button>
                <button wire:click="deleteMovimiento"
                        class="cursor-pointer px-2 py-1 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 text-xs">
                    Confirmar Eliminación
                </button>
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