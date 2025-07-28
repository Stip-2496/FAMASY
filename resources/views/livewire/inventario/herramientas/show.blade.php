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
}; ?>

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        </svg>
                        {{ $herramienta->nomHer }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">ID: {{ $herramienta->idHer }} - Categoría: {{ ucfirst($herramienta->catHer) }}</p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <a href="{{ route('inventario.herramientas.edit', $herramienta->idHer) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar
                    </a>
                    <a href="{{ route('inventario.herramientas.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Alerta de Stock -->
        @if($alertaStock)
            <div class="mb-6 {{ $tipoAlerta == 'danger' ? 'bg-red-50 border-red-200 text-red-600' : 'bg-yellow-50 border-yellow-200 text-yellow-600' }} border px-4 py-3 rounded-lg flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <strong>Alerta de Stock:</strong> {{ $mensajeAlerta }}
            </div>
        @endif

        <!-- Información Principal -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Información General -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 bg-blue-600 rounded-t-lg">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Información General
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                        <span class="font-medium text-gray-500">ID:</span>
                        <span class="text-gray-900">{{ $herramienta->idHer }}</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                        <span class="font-medium text-gray-500">Nombre:</span>
                        <span class="text-gray-900">{{ $herramienta->nomHer }}</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                        <span class="font-medium text-gray-500">Categoría:</span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ ucfirst($herramienta->catHer) }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                        <span class="font-medium text-gray-500">Estado:</span>
                        @php
                            $estadoClasses = match($herramienta->estHer) {
                                'bueno' => 'bg-green-100 text-green-800',
                                'regular' => 'bg-yellow-100 text-yellow-800', 
                                'malo' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                        @endphp
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $estadoClasses }}">
                            {{ ucfirst($herramienta->estHer) }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                        <span class="font-medium text-gray-500">Ubicación:</span>
                        <span class="text-gray-900">{{ $herramienta->ubiHer ?? 'No especificada' }}</span>
                    </div>
                    @if($herramienta->proveedor)
                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                        <span class="font-medium text-gray-500">Proveedor:</span>
                        <span class="text-blue-600 hover:text-blue-800 cursor-pointer">
                            {{ $herramienta->proveedor->nomProve }}
                        </span>
                    </div>
                    @endif
                    @if($herramienta->obsHer)
                    <div class="border-b border-gray-200 pb-2">
                        <span class="font-medium text-gray-500 block mb-1">Observaciones:</span>
                        <span class="text-gray-900 text-sm">{{ $herramienta->obsHer }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Control de Stock -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 bg-green-600 rounded-t-lg">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        Control de Stock
                    </h3>
                </div>
                <div class="p-6">
                    <div class="text-center mb-6">
                        <div class="text-4xl font-bold {{ $alertaStock && $tipoAlerta == 'danger' ? 'text-red-600' : ($alertaStock ? 'text-yellow-600' : 'text-green-600') }} mb-2">
                            {{ $herramienta->stockActual ?? 0 }}
                        </div>
                        <p class="text-gray-600 mb-2">unidades disponibles</p>
                        @if($alertaStock)
                            <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full {{ $tipoAlerta == 'danger' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                @if($tipoAlerta == 'danger')
                                    Stock Crítico
                                @else
                                    Stock Bajo
                                @endif
                            </span>
                        @else
                            <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-800">
                                Stock Normal
                            </span>
                        @endif
                    </div>

                    @if($herramienta->stockMinHer || $herramienta->stockMaxHer)
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        @if($herramienta->stockMinHer)
                        <div class="text-center p-3 bg-red-50 rounded-lg">
                            <div class="text-sm text-gray-600 mb-1">Stock Mínimo</div>
                            <div class="text-xl font-bold text-red-600">{{ $herramienta->stockMinHer }}</div>
                        </div>
                        @endif
                        @if($herramienta->stockMaxHer)
                        <div class="text-center p-3 bg-green-50 rounded-lg">
                            <div class="text-sm text-gray-600 mb-1">Stock Máximo</div>
                            <div class="text-xl font-bold text-green-600">{{ $herramienta->stockMaxHer }}</div>
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Progreso de Stock -->
                    @if($herramienta->stockMinHer && $herramienta->stockMaxHer)
                    <div class="mb-4">
                        @php
                            $porcentaje = min(100, (($herramienta->stockActual ?? 0) / $herramienta->stockMaxHer) * 100);
                            $colorProgreso = $porcentaje <= 30 ? 'bg-red-500' : ($porcentaje <= 50 ? 'bg-yellow-500' : 'bg-green-500');
                        @endphp
                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                            <span>Nivel de Stock</span>
                            <span>{{ round($porcentaje) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="{{ $colorProgreso }} h-2 rounded-full transition-all duration-300" style="width: {{ $porcentaje }}%"></div>
                        </div>
                    </div>
                    @endif

                    <!-- Información Adicional -->
                    <div class="grid grid-cols-2 gap-4 text-center">
                        @if($herramienta->cantidadPrestada && $herramienta->cantidadPrestada > 0)
                        <div class="p-2 bg-yellow-50 rounded">
                            <div class="text-sm text-gray-600">Prestadas</div>
                            <div class="font-bold text-yellow-600">{{ $herramienta->cantidadPrestada }}</div>
                        </div>
                        @endif
                        <div class="p-2 bg-blue-50 rounded">
                            <div class="text-sm text-gray-600">Valor Inventario</div>
                            <div class="font-bold text-blue-600">${{ number_format($herramienta->valorInventario ?? 0, 0, ',', '.') }}</div>
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
                    <a href="#" class="flex flex-col items-center p-4 bg-yellow-50 hover:bg-yellow-100 rounded-lg transition duration-150 ease-in-out">
                        <svg class="w-8 h-8 text-yellow-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h4a1 1 0 011 1v2h4a1 1 0 110 2h-1v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6H3a1 1 0 110-2h4z"></path>
                        </svg>
                        <span class="text-sm font-medium text-yellow-800">Préstamo</span>
                    </a>
                    <a href="#" class="flex flex-col items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition duration-150 ease-in-out">
                        <svg class="w-8 h-8 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        </svg>
                        <span class="text-sm font-medium text-blue-800">Mantenimiento</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Historial de Movimientos -->
        <div class="bg-white shadow rounded-lg mb-8">
            <div class="px-6 py-4 bg-purple-600 rounded-t-lg">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Historial de Movimientos
                    </h3>
                    <span class="bg-purple-100 text-purple-800 text-sm font-medium px-2.5 py-0.5 rounded">
                        {{ $this->movimientos()->total() }} movimientos
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                @if($this->movimientos()->count() > 0)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Costo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Observaciones</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($this->movimientos() as $movimiento)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div>{{ $movimiento->fecMovInv->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $movimiento->fecMovInv->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
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
                                        'prestamo_salida' => 'Préstamo Salida',
                                        'prestamo_retorno' => 'Préstamo Retorno',
                                        'mantenimiento' => 'Mantenimiento',
                                        'apertura' => 'Apertura',
                                        default => ucfirst($movimiento->tipMovInv)
                                    };
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $tipoClasses }}">
                                    {{ $tipoTexto }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="font-medium">{{ $movimiento->cantMovInv }}</span> {{ $movimiento->uniMovInv }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($movimiento->costoTotInv)
                                    <span class="font-medium text-green-600">
                                        ${{ number_format($movimiento->costoTotInv, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $movimiento->obsInv ?? 'Sin observaciones' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
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

                <!-- Paginación -->
                <div class="px-6 py-4 bg-gray-50">
                    {{ $this->movimientos()->links() }}
                </div>
                @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Sin Movimientos</h3>
                    <p class="mt-1 text-sm text-gray-500">Esta herramienta no tiene movimientos registrados aún.</p>
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
                                <span class="text-gray-900">{{ $herramienta->created_at->format('d/m/Y H:i:s') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Última Actualización:</span>
                                <span class="text-gray-900">{{ $herramienta->updated_at->format('d/m/Y H:i:s') }}</span>
                            </div>
                            @if($herramienta->ultimoMovimiento)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Último Movimiento:</span>
                                <span class="text-gray-900">{{ $herramienta->ultimoMovimiento->format('d/m/Y H:i') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Estadísticas</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Total de Movimientos:</span>
                                <span class="font-bold text-gray-900">{{ $herramienta->totalMovimientos ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Valor en Inventario:</span>
                                <span class="font-bold text-green-600">${{ number_format($herramienta->valorInventario ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>