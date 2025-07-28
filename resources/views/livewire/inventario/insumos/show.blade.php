<?php
use App\Models\Insumo;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public Insumo $insumo;
    public $stockActual = 0; // Temporal hasta implementar movimientos
    public $showDeleteModal = false;

    public function mount(Insumo $insumo)
    {
        $this->insumo = $insumo;
    }

    public function confirmDelete(): void
    {
        $this->showDeleteModal = true;
    }

    public function deleteInsumo(): void
    {
        try {
            if (!$this->insumo->puedeEliminar()) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'No se puede eliminar el insumo porque ' . $this->insumo->razonNoEliminar()
                ]);
                return;
            }

            $this->insumo->delete();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Insumo eliminado correctamente'
            ]);

            $this->redirect(route('inventario.insumos.index'), navigate: true);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar el insumo: ' . $e->getMessage()
            ]);
        }
    }
}; ?>

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        @php
                            $iconoColor = match(strtolower($insumo->tipIns)) {
                                'medicamento veterinario', 'medicamento' => 'text-blue-600',
                                'concentrado' => 'text-green-600',
                                'vacuna' => 'text-red-600',
                                'vitamina' => 'text-yellow-600',
                                'suplemento' => 'text-purple-600',
                                default => 'text-gray-600'
                            };
                        @endphp
                        <svg class="w-8 h-8 {{ $iconoColor }} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                        </svg>
                        {{ $insumo->nomIns }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        ID: {{ $insumo->idIns }} - {{ ucfirst($insumo->tipIns) }}
                        @if($insumo->marIns)
                            - {{ $insumo->marIns }}
                        @endif
                    </p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <a href="{{ route('inventario.insumos.edit', $insumo->idIns) }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar
                    </a>
                    <button wire:click="confirmDelete"
                       class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Eliminar
                    </button>
                    <a href="{{ route('inventario.insumos.index') }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Nota Temporal -->
        <div class="mb-6 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
            </svg>
            <div>
                <strong>Información:</strong> El stock actual aparece en 0 temporalmente. Se calculará automáticamente cuando implementemos el módulo de Movimientos de Inventario.
            </div>
        </div>

        <!-- Alertas de Vencimiento -->
        @if($insumo->fecVenIns)
            @php
                $diasParaVencer = now()->diffInDays($insumo->fecVenIns, false);
                $alertaVencimiento = false;
                $tipoAlertaVencimiento = '';
                $mensajeVencimiento = '';
                
                if ($diasParaVencer < 0) {
                    $alertaVencimiento = true;
                    $tipoAlertaVencimiento = 'danger';
                    $mensajeVencimiento = '¡Este insumo está vencido! No debe ser utilizado.';
                } elseif ($diasParaVencer <= 7) {
                    $alertaVencimiento = true;
                    $tipoAlertaVencimiento = 'danger';
                    $mensajeVencimiento = "¡Atención! Este insumo vence en {$diasParaVencer} días. Úsalo prioritariamente.";
                } elseif ($diasParaVencer <= 30) {
                    $alertaVencimiento = true;
                    $tipoAlertaVencimiento = 'warning';
                    $mensajeVencimiento = "Este insumo vence en {$diasParaVencer} días. Planifica su uso.";
                }
            @endphp

            @if($alertaVencimiento)
                <div class="mb-6 {{ $tipoAlertaVencimiento == 'danger' ? 'bg-red-50 border-red-200 text-red-600' : 'bg-yellow-50 border-yellow-200 text-yellow-600' }} border px-4 py-3 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <strong>Alerta de Vencimiento:</strong> {{ $mensajeVencimiento }}
                </div>
            @endif
        @endif

        <!-- Información Principal -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Información General -->
            <div class="lg:col-span-2 bg-white shadow rounded-lg">
                <div class="px-6 py-4 bg-green-600 rounded-t-lg">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Información General
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                                <span class="font-medium text-gray-500">ID:</span>
                                <span class="text-gray-900">{{ $insumo->idIns }}</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                                <span class="font-medium text-gray-500">Nombre:</span>
                                <span class="text-gray-900">{{ $insumo->nomIns }}</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                                <span class="font-medium text-gray-500">Tipo:</span>
                                @php
                                    $tipoColor = match(strtolower($insumo->tipIns)) {
                                        'medicamento veterinario', 'medicamento' => 'bg-blue-100 text-blue-800',
                                        'concentrado' => 'bg-green-100 text-green-800',
                                        'vacuna' => 'bg-red-100 text-red-800',
                                        'vitamina' => 'bg-yellow-100 text-yellow-800',
                                        'suplemento' => 'bg-purple-100 text-purple-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $tipoColor }}">
                                    {{ ucfirst($insumo->tipIns) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                                <span class="font-medium text-gray-500">Marca:</span>
                                <span class="text-gray-900">{{ $insumo->marIns ?? 'Sin marca' }}</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                                <span class="font-medium text-gray-500">Unidad:</span>
                                <span class="text-gray-900">{{ ucfirst($insumo->uniIns) }}</span>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                                <span class="font-medium text-gray-500">Estado:</span>
                                @php
                                    $estadoClasses = match($insumo->estIns) {
                                        'disponible' => 'bg-green-100 text-green-800',
                                        'agotado' => 'bg-red-100 text-red-800',
                                        'vencido' => 'bg-gray-100 text-gray-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $estadoClasses }}">
                                    {{ ucfirst($insumo->estIns) }}
                                </span>
                            </div>
                            @if($insumo->fecVenIns)
                            <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                                <span class="font-medium text-gray-500">Vencimiento:</span>
                                <div class="text-right">
                                    <div class="text-gray-900">{{ $insumo->fecVenIns->format('d/m/Y') }}</div>
                                    @php
                                        $diasParaVencer = now()->diffInDays($insumo->fecVenIns, false);
                                        $colorVencimiento = 'text-green-600';
                                        $textoVencimiento = '';
                                        
                                        if ($diasParaVencer < 0) {
                                            $colorVencimiento = 'text-red-600';
                                            $textoVencimiento = 'Vencido';
                                        } elseif ($diasParaVencer <= 7) {
                                            $colorVencimiento = 'text-red-600';
                                            $textoVencimiento = $diasParaVencer . ' días';
                                        } elseif ($diasParaVencer <= 30) {
                                            $colorVencimiento = 'text-yellow-600';
                                            $textoVencimiento = $diasParaVencer . ' días';
                                        } else {
                                            $textoVencimiento = $diasParaVencer . ' días';
                                        }
                                    @endphp
                                    <div class="{{ $colorVencimiento }} text-xs">{{ $textoVencimiento }}</div>
                                </div>
                            </div>
                            @endif
                            @if($insumo->proveedor)
                            <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                                <span class="font-medium text-gray-500">Proveedor:</span>
                                <span class="text-green-600 hover:text-green-800 cursor-pointer">
                                    {{ $insumo->proveedor->nomProve }}
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    @if($insumo->obsIns)
                    <div class="mt-6 border-t border-gray-200 pt-4">
                        <span class="font-medium text-gray-500 block mb-2">Observaciones:</span>
                        <span class="text-gray-900 text-sm">{{ $insumo->obsIns }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Control de Stock -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 bg-blue-600 rounded-t-lg">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        Control de Stock
                    </h3>
                </div>
                <div class="p-6">
                    <div class="text-center mb-6">
                        <div class="text-4xl font-bold text-gray-600 mb-2">{{ $stockActual }}</div>
                        <p class="text-gray-600 mb-2">{{ $insumo->uniIns }} disponibles</p>
                        <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full bg-gray-100 text-gray-800">
                            Stock Temporal
                        </span>
                        <div class="mt-2 text-xs text-gray-500">
                            Se calculará con movimientos de inventario
                        </div>
                    </div>

                    @if($insumo->stockMinIns || $insumo->stockMaxIns)
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        @if($insumo->stockMinIns)
                        <div class="text-center p-3 bg-red-50 rounded-lg">
                            <div class="text-sm text-gray-600 mb-1">Stock Mínimo</div>
                            <div class="text-xl font-bold text-red-600">{{ $insumo->stockMinIns }}</div>
                        </div>
                        @endif
                        @if($insumo->stockMaxIns)
                        <div class="text-center p-3 bg-green-50 rounded-lg">
                            <div class="text-sm text-gray-600 mb-1">Stock Máximo</div>
                            <div class="text-xl font-bold text-green-600">{{ $insumo->stockMaxIns }}</div>
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Información de Valor -->
                    <div class="grid grid-cols-1 gap-4 text-center">
                        <div class="p-2 bg-blue-50 rounded">
                            <div class="text-sm text-gray-600">Valor en Stock</div>
                            <div class="font-bold text-blue-600">$0</div>
                            <div class="text-xs text-gray-500">Se calculará con movimientos</div>
                        </div>
                        <div class="p-2 bg-gray-50 rounded">
                            <div class="text-sm text-gray-600">Consumo Promedio</div>
                            <div class="font-bold text-gray-600">Pendiente</div>
                            <div class="text-xs text-gray-500">Se calculará con histórico</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="bg-white shadow rounded-lg mb-8">
            <div class="px-6 py-4 bg-yellow-500 rounded-t-lg">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Acciones Rápidas
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="#" class="flex flex-col items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition duration-150 ease-in-out">
                        <svg class="w-8 h-8 text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span class="text-sm font-medium text-green-800">Registrar Entrada</span>
                    </a>
                    <a href="#" class="flex flex-col items-center p-4 bg-red-50 hover:bg-red-100 rounded-lg transition duration-150 ease-in-out">
                        <svg class="w-8 h-8 text-red-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                        <span class="text-sm font-medium text-red-800">Registrar Salida</span>
                    </a>
                    <a href="#" class="flex flex-col items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition duration-150 ease-in-out">
                        <svg class="w-8 h-8 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                        </svg>
                        <span class="text-sm font-medium text-blue-800">Registrar Consumo</span>
                    </a>
                    <a href="#" class="flex flex-col items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition duration-150 ease-in-out">
                        <svg class="w-8 h-8 text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="text-sm font-medium text-purple-800">Generar Reporte</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Historial de Movimientos -->
        <div class="bg-white shadow rounded-lg mb-8">
            <div class="px-6 py-4 bg-gray-600 rounded-t-lg">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Historial de Movimientos
                    </h3>
                    <span class="bg-gray-100 text-gray-800 text-sm font-medium px-2.5 py-0.5 rounded">
                        {{ $insumo->movimientos->count() }} movimientos
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                @if($insumo->movimientos->count() > 0)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Observaciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($insumo->movimientos as $movimiento)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div>{{ $movimiento->fecMovInv ? $movimiento->fecMovInv->format('d/m/Y') : 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ $movimiento->fecMovInv ? $movimiento->fecMovInv->format('H:i') : '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $movimiento->tipMovInv ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="font-medium">{{ $movimiento->canMovInv ?? 'N/A' }}</span> {{ $insumo->uniIns }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $movimiento->usuario->name ?? 'Sistema' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $movimiento->obsMovInv ?? 'Sin observaciones' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Sin Movimientos</h3>
                    <p class="mt-1 text-sm text-gray-500">Este insumo no tiene movimientos registrados aún.</p>
                    <div class="mt-6">
                        <a href="#" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Registrar Primer Movimiento
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Información del Sistema -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 bg-gray-600 rounded-t-lg">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    </svg>
                    Información del Sistema
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Fechas</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Fecha de Creación:</span>
                                <span class="text-gray-900">{{ $insumo->created_at->format('d/m/Y H:i:s') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Última Actualización:</span>
                                <span class="text-gray-900">{{ $insumo->updated_at->format('d/m/Y H:i:s') }}</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Estadísticas</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Total de Movimientos:</span>
                                <span class="font-bold text-gray-900">{{ $insumo->movimientos->count() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Valor en Stock:</span>
                                <span class="font-bold text-green-600">$0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Total Consumido:</span>
                                <span class="font-bold text-gray-900">0 {{ $insumo->uniIns }}</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-2">
                                <em>Los valores se calcularán con el módulo de movimientos</em>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Eliminar Insumo -->
@if($showDeleteModal)
    <div class="fixed inset-0 bg-black/20 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg max-w-sm w-full">
            <h2 class="text-xl font-semibold mb-4">Confirmar eliminación</h2>
            <p class="mb-4 text-sm text-gray-600">
                ¿Está seguro que desea eliminar este insumo? Esta acción no se puede deshacer.
            </p>
            
            <div class="mb-4">
                <p><strong>Insumo:</strong> {{ $insumo->nomIns }}</p>
                <p><strong>Tipo:</strong> {{ $insumo->tipIns }}</p>
                <p><strong>ID:</strong> {{ $insumo->idIns }}</p>
            </div>
            
            <div class="flex justify-end gap-3">
                <button wire:click="$set('showDeleteModal', false)"
                        class="cursor-pointer px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">Cancelar</button>
                <button wire:click="deleteInsumo"
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