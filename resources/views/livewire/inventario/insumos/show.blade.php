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
        $stockActual = $this->insumo->movimientosInventario()
            ->selectRaw('
                SUM(CASE 
                    WHEN tipMovInv IN ("entrada", "apertura", "ajuste_pos") THEN cantMovInv
                    ELSE -cantMovInv
                END) as stock_total
            ')
            ->value('stock_total') ?? 0;

        // Últimos movimientos
        $ultimosMovimientos = $this->insumo->movimientosInventario()
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

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center">
                        <a href="{{ route('inventario.insumos.index') }}" wire:navigate
                           class="mr-4 text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                        </a>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">{{ $insumo->nomIns }}</h1>
                            <p class="mt-1 text-lg text-gray-600">{{ ucfirst($insumo->tipIns) }}</p>
                            <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500">
                                <span>ID: {{ $insumo->idIns }}</span>
                                <span>•</span>
                                <span>Creado: {{ $insumo->created_at->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('inventario.insumos.edit', $insumo->idIns) }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar
                    </a>
                    <button wire:click="$set('showDeleteModal', true)"
                            class="cursor-pointer inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Eliminar
                    </button>
                </div>
            </div>
        </div>

        <!-- Alertas de Stock -->
        @if($estadoStock === 'critico')
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <strong>Stock Crítico:</strong> El stock actual ({{ number_format($stockActual, 2) }} {{ $insumo->uniIns }}) está por debajo del mínimo.
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Columna Principal -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Información Básica -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Información Básica</h3>
                    </div>
                    <div class="px-6 py-6">
                        <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $insumo->nomIns }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Tipo</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($insumo->tipIns) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Marca</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $insumo->marIns ?: 'Sin marca' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Unidad</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $insumo->uniIns }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Estado</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($insumo->estIns) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Vencimiento</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($insumo->fecVenIns)
                                        {{ $insumo->fecVenIns->format('d/m/Y') }}
                                        @if($diasParaVencer !== null)
                                            <span class="text-xs text-gray-500">
                                                ({{ $diasParaVencer < 0 ? 'Vencido' : $diasParaVencer . ' días' }})
                                            </span>
                                        @endif
                                    @else
                                        Sin vencimiento
                                    @endif
                                </dd>
                            </div>
                        </dl>
                        
                        @if($insumo->obsIns)
                            <div class="mt-6">
                                <dt class="text-sm font-medium text-gray-500">Observaciones</dt>
                                <dd class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded-lg">{{ $insumo->obsIns }}</dd>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Stock -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Stock</h3>
                    </div>
                    <div class="px-6 py-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                            <div class="text-center p-4 bg-blue-50 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">{{ number_format($stockActual, 2) }}</div>
                                <div class="text-sm text-blue-600">Stock Actual</div>
                                <div class="text-xs text-gray-500">{{ $insumo->uniIns }}</div>
                            </div>
                            <div class="text-center p-4 bg-yellow-50 rounded-lg">
                                <div class="text-2xl font-bold text-yellow-600">{{ $insumo->stockMinIns ? number_format($insumo->stockMinIns, 0) : 'N/A' }}</div>
                                <div class="text-sm text-yellow-600">Stock Mínimo</div>
                            </div>
                            <div class="text-center p-4 bg-green-50 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">{{ $insumo->stockMaxIns ? number_format($insumo->stockMaxIns, 0) : 'N/A' }}</div>
                                <div class="text-sm text-green-600">Stock Máximo</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Movimientos -->
                @if($ultimosMovimientos->count() > 0)
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Últimos Movimientos</h3>
                        </div>
                        <div class="overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($ultimosMovimientos as $movimiento)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $movimiento->fecMovInv->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ ucfirst(str_replace('_', ' ', $movimiento->tipMovInv)) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                                {{ number_format($movimiento->cantMovInv, 2) }} {{ $movimiento->uniMovInv }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                {{ $movimiento->obsInv ?: 'Sin observaciones' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Columna Lateral -->
            <div class="space-y-8">
                <!-- Proveedor -->
                @if($insumo->proveedor)
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Proveedor</h3>
                        </div>
                        <div class="px-6 py-6">
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0 h-12 w-12">
                                    <div class="h-12 w-12 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-medium text-gray-900">{{ $insumo->proveedor->nomProve }}</h4>
                                    <p class="text-sm text-gray-500">{{ $insumo->proveedor->nitProve }}</p>
                                </div>
                            </div>
                            
                            <dl class="space-y-3">
                                @if($insumo->proveedor->telProve)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Teléfono</dt>
                                        <dd class="text-sm text-gray-900">{{ $insumo->proveedor->telProve }}</dd>
                                    </div>
                                @endif
                                
                                @if($insumo->proveedor->emailProve)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                                        <dd class="text-sm text-gray-900">{{ $insumo->proveedor->emailProve }}</dd>
                                    </div>
                                @endif
                                
                                @if($insumo->proveedor->dirProve)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Dirección</dt>
                                        <dd class="text-sm text-gray-900">{{ $insumo->proveedor->dirProve }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                @else
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-6 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Sin Proveedor</h3>
                            <p class="mt-1 text-sm text-gray-500">Este insumo no tiene proveedor asignado.</p>
                            <div class="mt-6">
                                <a href="{{ route('inventario.insumos.edit', $insumo->idIns) }}" wire:navigate
                                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    Asignar Proveedor
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Estadísticas -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Estadísticas</h3>
                    </div>
                    <div class="px-6 py-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Total movimientos</span>
                            <span class="text-sm font-medium text-gray-900">{{ $insumo->movimientosInventario()->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Último movimiento</span>
                            <span class="text-sm font-medium text-gray-900">
                                @if($ultimosMovimientos->first())
                                    {{ $ultimosMovimientos->first()->fecMovInv->diffForHumans() }}
                                @else
                                    Sin movimientos
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Fecha registro</span>
                            <span class="text-sm font-medium text-gray-900">{{ $insumo->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar -->
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg max-w-md w-full mx-4">
                <div class="flex items-center mb-4">
                    <svg class="h-6 w-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900">Confirmar eliminación</h3>
                </div>
                <p class="mb-4 text-sm text-gray-600">
                    ¿Estás seguro de eliminar "<strong>{{ $insumo->nomIns }}</strong>"?
                </p>
                
                <div class="flex justify-end space-x-3">
                    <button wire:click="$set('showDeleteModal', false)"
                            class="cursor-pointer px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg">
                        Cancelar
                    </button>
                    <button wire:click="eliminarInsumo"
                            class="cursor-pointer px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('livewire:initialized', () => {
    Livewire.on('notify', (event) => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        
        Toast.fire({
            icon: event.type,
            title: event.message
        });
    });
});
</script>