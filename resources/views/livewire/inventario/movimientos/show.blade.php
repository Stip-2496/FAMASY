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

@section('title', 'Detalles de movimiento')

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        @php
                            $iconoColor = match($movimiento->tipMovInv) {
                                'entrada', 'compra', 'donacion', 'apertura' => 'text-green-600',
                                'salida', 'consumo', 'aplicacion', 'venta' => 'text-red-600',
                                'perdida', 'vencimiento' => 'text-gray-600',
                                default => 'text-blue-600'
                            };
                        @endphp
                        <svg class="w-8 h-8 {{ $iconoColor }} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Movimiento #{{ $movimiento->idInv }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">Detalles completos del movimiento de inventario</p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <a href="{{ route('inventario.movimientos.index') }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver
                    </a>
                    <a href="{{ route('inventario.movimientos.edit', $movimiento->idInv) }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Información Principal -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Datos del Movimiento -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-blue-600 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Información del Movimiento
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Tipo de Movimiento -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Movimiento</label>
                                <div class="flex items-center">
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
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $colorClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $movimiento->tipMovInv)) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Fecha del Movimiento -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha del Movimiento</label>
                                <div class="flex items-center text-gray-900">
                                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    {{ $movimiento->fecMovInv ? $movimiento->fecMovInv->format('d/m/Y H:i') : 'No especificada' }}
                                </div>
                            </div>

                            <!-- Item -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Item</label>
                                <div class="flex items-center">
                                    @if($movimiento->insumo)
                                        <div class="flex items-center">
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 mr-3">
                                                Insumo
                                            </span>
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $movimiento->insumo->nomIns }}</p>
                                                <p class="text-sm text-gray-500">{{ $movimiento->insumo->tipIns ?? '' }} - {{ $movimiento->insumo->marIns ?? '' }}</p>
                                            </div>
                                        </div>
                                    @elseif($movimiento->herramienta)
                                        <div class="flex items-center">
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800 mr-3">
                                                Herramienta
                                            </span>
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $movimiento->herramienta->nomHer }}</p>
                                                <p class="text-sm text-gray-500">{{ $movimiento->herramienta->catHer ?? '' }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-gray-500">Item no especificado</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Cantidad y Unidad -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cantidad</label>
                                <div class="flex items-center text-gray-900">
                                    <span class="text-2xl font-bold text-blue-600">{{ number_format($movimiento->cantMovInv, 2) }}</span>
                                    <span class="ml-2 text-gray-500">{{ $movimiento->uniMovInv }}</span>
                                </div>
                            </div>

                            <!-- Costo Total -->
                            @if($movimiento->costoTotInv)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Costo Total</label>
                                <div class="flex items-center text-gray-900">
                                    <span class="text-2xl font-bold text-green-600">${{ number_format($movimiento->costoTotInv, 2) }}</span>
                                    @if($movimiento->costoUnitInv)
                                        <span class="ml-2 text-sm text-gray-500">(${{ number_format($movimiento->costoUnitInv, 2) }}/{{ $movimiento->uniMovInv }})</span>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Información Adicional -->
                @if($movimiento->loteInv || $movimiento->fecVenceInv || $movimiento->obsInv)
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-gray-600 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Información Adicional
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @if($movimiento->loteInv)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Lote/Serie</label>
                                <p class="text-gray-900">{{ $movimiento->loteInv }}</p>
                            </div>
                            @endif

                            @if($movimiento->fecVenceInv)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Vencimiento</label>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-gray-900">{{ $movimiento->fecVenceInv->format('d/m/Y') }}</span>
                                    @php
                                        $diasParaVencer = $movimiento->fecVenceInv->diffInDays(now(), false);
                                    @endphp
                                    @if($diasParaVencer > 0)
                                        <span class="ml-2 inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">
                                            Vencido hace {{ $diasParaVencer }} días
                                        </span>
                                    @elseif($diasParaVencer > -30)
                                        <span class="ml-2 inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Vence en {{ abs($diasParaVencer) }} días
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>

                        @if($movimiento->obsInv)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-gray-900">{{ $movimiento->obsInv }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Panel Lateral -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Registro del Movimiento -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-yellow-500 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Registro
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Registrado por</label>
                            @if($movimiento->usuario)
                                <p class="text-gray-900">{{ $movimiento->usuario->nomUsu ?? '' }} {{ $movimiento->usuario->apeUsu ?? '' }}</p>
                                <p class="text-sm text-gray-500">{{ $movimiento->usuario->email ?? '' }}</p>
                            @else
                                <p class="text-gray-500">Usuario no disponible</p>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Creación</label>
                            <p class="text-gray-900">{{ $movimiento->created_at ? $movimiento->created_at->format('d/m/Y H:i:s') : 'No disponible' }}</p>
                        </div>

                        @if($movimiento->updated_at && $movimiento->updated_at != $movimiento->created_at)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Última Modificación</label>
                            <p class="text-gray-900">{{ $movimiento->updated_at->format('d/m/Y H:i:s') }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Proveedor -->
                @if($movimiento->proveedor)
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-purple-600 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            Proveedor
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Empresa</label>
                                <p class="text-gray-900 font-medium">{{ $movimiento->proveedor->nomProve ?? '' }}</p>
                            </div>
                            
                            @if($movimiento->proveedor->conProve)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Contacto</label>
                                <p class="text-gray-900">{{ $movimiento->proveedor->conProve }}</p>
                            </div>
                            @endif

                            @if($movimiento->proveedor->telProve)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                                <p class="text-gray-900">{{ $movimiento->proveedor->telProve }}</p>
                            </div>
                            @endif

                            @if($movimiento->proveedor->tipSumProve)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Suministro</label>
                                <p class="text-gray-900">{{ $movimiento->proveedor->tipSumProve }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Acciones Rápidas -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-indigo-600 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Acciones
                        </h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="{{ route('inventario.movimientos.edit', $movimiento->idInv) }}" wire:navigate
                           class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Editar Movimiento
                        </a>

                        <button wire:click="confirmDelete"
                              class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Eliminar Movimiento
                        </button>

                        <a href="{{ route('inventario.movimientos.create') }}" wire:navigate
                           class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Nuevo Movimiento
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Eliminar Movimiento -->
@if($showDeleteModal)
    <div class="fixed inset-0 bg-black/20 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg max-w-sm w-full">
            <h2 class="text-xl font-semibold mb-4">Confirmar eliminación</h2>
            <p class="mb-4 text-sm text-gray-600">
                ¿Está seguro que desea eliminar este movimiento? Esta acción no se puede deshacer.
            </p>
            
            <div class="mb-4">
                <p><strong>Tipo:</strong> {{ ucfirst(str_replace('_', ' ', $movimiento->tipMovInv)) }}</p>
                <p><strong>Item:</strong> {{ $movimiento->insumo ? $movimiento->insumo->nomIns : $movimiento->herramienta->nomHer }}</p>
                <p><strong>Cantidad:</strong> {{ $movimiento->cantMovInv }} {{ $movimiento->uniMovInv }}</p>
                <p><strong>ID:</strong> {{ $movimiento->idInv }}</p>
            </div>
            
            <div class="flex justify-end gap-3">
                <button wire:click="$set('showDeleteModal', false)"
                        class="cursor-pointer px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">Cancelar</button>
                <button wire:click="deleteMovimiento"
                        class="cursor-pointer px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">Confirmar Eliminación</button>
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