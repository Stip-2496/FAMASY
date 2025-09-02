<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;
    
    // Propiedades del componente
    public $periodoActual = 30;
    public $metricas = [];
    public $movimientos_recientes = [];
    public $cuentas_vencer = [];
    public $categorias_gastos = [];
    public $categorias_chart = [];
    public $flujo_caja = [];
    public $alertas = [];
    public $categorias = [];
    
    // Propiedades para el modal de nuevo movimiento
    public $modalAbierto = false;
    public $tipoMovimiento = '';
    public $descripcion = '';
    public $monto = '';
    public $categoria_id = '';
    public $fecha = '';

    public function mount()
    {
        $this->fecha = date('Y-m-d');
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        try {
            // Simulamos los datos - aquí harías las consultas reales a la BD
            $this->metricas = [
                'ingresos_mes' => 25000.50,
                'gastos_mes' => 18500.75,
                'balance' => 6499.75,
                'cuentas_pendientes' => 12800.00
            ];

            // Movimientos recientes simulados
            $this->movimientos_recientes = collect([
                (object)[
                    'fecha' => '2024-01-15',
                    'descripcion' => 'Venta de servicios profesionales',
                    'categoria' => 'Servicios',
                    'tipo' => 'ingreso',
                    'monto' => 1500.00
                ],
                (object)[
                    'fecha' => '2024-01-14',
                    'descripcion' => 'Pago de servicios públicos',
                    'categoria' => 'Servicios',
                    'tipo' => 'egreso',
                    'monto' => 350.00
                ],
                (object)[
                    'fecha' => '2024-01-13',
                    'descripcion' => 'Compra de materiales',
                    'categoria' => 'Materiales',
                    'tipo' => 'egreso',
                    'monto' => 850.00
                ]
            ]);

            // Cuentas por vencer simuladas
            $this->cuentas_vencer = collect([
                (object)[
                    'descripcion' => 'Factura Cliente ABC',
                    'monto' => 2500.00,
                    'fecha_vencimiento' => '2024-01-20'
                ],
                (object)[
                    'descripcion' => 'Pago Proveedor XYZ',
                    'monto' => 1800.00,
                    'fecha_vencimiento' => '2024-01-25'
                ]
            ]);

            // Datos para gráficos
            $this->flujo_caja = [
                'fechas' => ['Ene 1', 'Ene 8', 'Ene 15', 'Ene 22', 'Ene 29'],
                'ingresos' => [5000, 7500, 6200, 8100, 9200],
                'egresos' => [3200, 4100, 3800, 4500, 4200]
            ];

            $this->categorias_chart = [
                'labels' => ['Servicios', 'Materiales', 'Marketing', 'Oficina', 'Otros'],
                'data' => [4500, 3200, 2100, 1800, 1200]
            ];

            $this->categorias_gastos = [
                ['nombre' => 'Servicios', 'color' => '#3b82f6'],
                ['nombre' => 'Materiales', 'color' => '#10b981'],
                ['nombre' => 'Marketing', 'color' => '#f59e0b'],
                ['nombre' => 'Oficina', 'color' => '#ef4444'],
                ['nombre' => 'Otros', 'color' => '#8b5cf6']
            ];

            // Categorías para el select
            $this->categorias = collect([
                (object)['id' => 1, 'nombre' => 'Servicios'],
                (object)['id' => 2, 'nombre' => 'Materiales'],
                (object)['id' => 3, 'nombre' => 'Marketing'],
                (object)['id' => 4, 'nombre' => 'Oficina'],
                (object)['id' => 5, 'nombre' => 'Otros']
            ]);

            // Alertas simuladas
            $this->alertas = [
                'Tienes 3 facturas próximas a vencer en los próximos 7 días',
                'El gasto en Marketing ha aumentado un 15% este mes'
            ];

        } catch (\Exception $e) {
            Log::error('Error al cargar datos del dashboard: ' . $e->getMessage());
            $this->alertas[] = 'Error al cargar algunos datos del dashboard';
        }
    }

    public function cambiarPeriodo($dias)
    {
        $this->periodoActual = $dias;
        $this->cargarDatos(); // Recargar datos con el nuevo período
        $this->dispatch('periodo-cambiado', periodo: $dias);
    }

    public function abrirModal()
    {
        $this->modalAbierto = true;
        $this->reset(['tipoMovimiento', 'descripcion', 'monto', 'categoria_id']);
        $this->fecha = date('Y-m-d');
    }

    public function cerrarModal()
    {
        $this->modalAbierto = false;
        $this->resetValidation();
    }

    public function guardarMovimiento()
    {
        $this->validate([
            'tipoMovimiento' => 'required|in:ingreso,egreso',
            'descripcion' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date'
        ], [
            'tipoMovimiento.required' => 'Selecciona el tipo de movimiento',
            'tipoMovimiento.in' => 'Tipo de movimiento inválido',
            'descripcion.required' => 'La descripción es obligatoria',
            'descripcion.max' => 'La descripción no puede exceder 255 caracteres',
            'monto.required' => 'El monto es obligatorio',
            'monto.numeric' => 'El monto debe ser un número válido',
            'monto.min' => 'El monto debe ser mayor a 0',
            'fecha.required' => 'La fecha es obligatoria',
            'fecha.date' => 'Formato de fecha inválido'
        ]);

        try {
            // Aquí guardarías en la base de datos
            Log::info('Nuevo movimiento creado', [
                'tipo' => $this->tipoMovimiento,
                'descripcion' => $this->descripcion,
                'monto' => $this->monto,
                'categoria_id' => $this->categoria_id,
                'fecha' => $this->fecha
            ]);

            $this->cerrarModal();
            $this->cargarDatos(); // Recargar datos
            
            session()->flash('success', 'Movimiento registrado correctamente');
            
        } catch (\Exception $e) {
            Log::error('Error al guardar movimiento: ' . $e->getMessage());
            session()->flash('error', 'Error al guardar el movimiento');
        }
    }

    public function actualizarDashboard()
    {
        $this->cargarDatos();
        session()->flash('info', 'Dashboard actualizado');
    }

    public function cerrarAlertas()
    {
        $this->alertas = [];
    }
}; ?>

@section('title', 'Contabilidad')

<div class="w-full px-6 py-6 mx-auto">
    <!-- Header del Dashboard -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div class="mb-4 md:mb-0">
                    <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-calculator mr-3 text-blue-600"></i> 
                        Dashboard Contabilidad
                    </h1>
                    <p class="text-gray-600 mt-1">Resumen financiero al {{ date('d/m/Y') }}</p>
                </div>
                <div class="flex space-x-3">
                    <!-- Nuevo Movimiento -->
                    <button wire:click="abrirModal" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                        <i class="fas fa-plus mr-2"></i> Nuevo Movimiento
                    </button>
                    
                    <!-- Reportes -->
                    <a href="{{ route('contabilidad.reportes.index') }}" 
                       class="bg-white border border-blue-600 text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-lg flex items-center transition duration-200">
                        <i class="fas fa-chart-line mr-2"></i> Reportes
                    </a>
                    
                    <!-- Actualizar -->
                    <button wire:click="actualizarDashboard" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                        <i class="fas fa-sync-alt mr-2"></i> Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas y Notificaciones -->
    @if(count($alertas) > 0)
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-lg relative" role="alert">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <h5 class="font-bold">Alertas Financieras</h5>
                    <button wire:click="cerrarAlertas" class="absolute top-2 right-2 text-yellow-600 hover:text-yellow-800">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($alertas as $alerta)
                    <li>{{ $alerta }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <!-- Selector de Período -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
            <div class="bg-white shadow-lg rounded-lg p-4">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 md:mb-0">Período de Análisis</h3>
                    <div class="flex flex-wrap gap-2">
                        <button wire:click="cambiarPeriodo(7)" 
                                class="periodo-btn px-3 py-1 text-sm rounded-lg border transition duration-200 {{ $periodoActual == 7 ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                            7 días
                        </button>
                        <button wire:click="cambiarPeriodo(30)" 
                                class="periodo-btn px-3 py-1 text-sm rounded-lg border transition duration-200 {{ $periodoActual == 30 ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                            30 días
                        </button>
                        <button wire:click="cambiarPeriodo(90)" 
                                class="periodo-btn px-3 py-1 text-sm rounded-lg border transition duration-200 {{ $periodoActual == 90 ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                            3 meses
                        </button>
                        <button wire:click="cambiarPeriodo(365)" 
                                class="periodo-btn px-3 py-1 text-sm rounded-lg border transition duration-200 {{ $periodoActual == 365 ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                            1 año
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Métricas Principales con Enlaces -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <!-- Ingresos del Mes -->
        <div class="w-full md:w-1/2 xl:w-1/4 px-3 mb-6">
            <a href="{{ route('contabilidad.movimientos.index') }}?tipo=ingreso" 
               class="block bg-white shadow-lg rounded-lg p-6 border-l-4 border-green-500 hover:shadow-xl transition duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-green-600 uppercase tracking-wide mb-1">
                            Ingresos del Mes
                        </p>
                        <p class="text-2xl font-bold text-gray-800">
                            ${{ number_format($metricas['ingresos_mes'] ?? 0, 2) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            <span class="text-green-600">
                                <i class="fas fa-arrow-up"></i> +12.5%
                            </span> vs mes anterior
                        </p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-arrow-up text-green-600 text-xl"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Gastos del Mes -->
        <div class="w-full md:w-1/2 xl:w-1/4 px-3 mb-6">
            <a href="{{ route('contabilidad.gastos.index') }}" 
               class="block bg-white shadow-lg rounded-lg p-6 border-l-4 border-red-500 hover:shadow-xl transition duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-red-600 uppercase tracking-wide mb-1">
                            Gastos del Mes
                        </p>
                        <p class="text-2xl font-bold text-gray-800">
                            ${{ number_format($metricas['gastos_mes'] ?? 0, 2) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            <span class="text-red-600">
                                <i class="fas fa-arrow-up"></i> +8.3%
                            </span> vs mes anterior
                        </p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-arrow-down text-red-600 text-xl"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Balance -->
        <div class="w-full md:w-1/2 xl:w-1/4 px-3 mb-6">
            <a href="{{ route('contabilidad.reportes.index') }}" 
               class="block bg-white shadow-lg rounded-lg p-6 border-l-4 border-{{ ($metricas['balance'] ?? 0) >= 0 ? 'green' : 'red' }}-500 hover:shadow-xl transition duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-{{ ($metricas['balance'] ?? 0) >= 0 ? 'green' : 'red' }}-600 uppercase tracking-wide mb-1">
                            Balance del Mes
                        </p>
                        <p class="text-2xl font-bold text-gray-800">
                            ${{ number_format($metricas['balance'] ?? 0, 2) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            <span class="text-{{ ($metricas['balance'] ?? 0) >= 0 ? 'green' : 'red' }}-600">
                                <i class="fas fa-{{ ($metricas['balance'] ?? 0) >= 0 ? 'arrow-up' : 'arrow-down' }}"></i> 
                                {{ ($metricas['balance'] ?? 0) >= 0 ? '+' : '' }}{{ number_format((($metricas['balance'] ?? 0) / max(($metricas['ingresos_mes'] ?? 1), 1)) * 100, 1) }}%
                            </span> margen
                        </p>
                    </div>
                    <div class="bg-{{ ($metricas['balance'] ?? 0) >= 0 ? 'green' : 'red' }}-100 p-3 rounded-full">
                        <i class="fas fa-balance-scale text-{{ ($metricas['balance'] ?? 0) >= 0 ? 'green' : 'red' }}-600 text-xl"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Cuentas Pendientes -->
        <div class="w-full md:w-1/2 xl:w-1/4 px-3 mb-6">
            <a href="{{ route('contabilidad.cuentas-pendientes.index') }}" 
               class="block bg-white shadow-lg rounded-lg p-6 border-l-4 border-yellow-500 hover:shadow-xl transition duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-yellow-600 uppercase tracking-wide mb-1">
                            Cuentas Pendientes
                        </p>
                        <p class="text-2xl font-bold text-gray-800">
                            ${{ number_format($metricas['cuentas_pendientes'] ?? 0, 2) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            <span class="text-yellow-600">
                                {{ count($cuentas_vencer) }} cuentas
                            </span> por vencer
                        </p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Gráficos y Análisis -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <!-- Flujo de Caja Interactivo -->
        <div class="w-full xl:w-2/3 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h6 class="text-lg font-semibold text-gray-800">Flujo de Caja</h6>
                            <p class="text-sm text-gray-600">Análisis de ingresos y egresos por período</p>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="toggleTipoGrafico()" 
                                    class="text-blue-600 hover:text-blue-800 px-3 py-1 text-sm border border-blue-300 rounded transition duration-200">
                                <i class="fas fa-chart-line mr-1"></i> <span id="tipo-grafico-text">Barras</span>
                            </button>
                            <a href="{{ route('contabilidad.reportes.index') }}" 
                               class="text-green-600 hover:text-green-800 px-3 py-1 text-sm border border-green-300 rounded transition duration-200">
                                <i class="fas fa-external-link-alt mr-1"></i> Ver Reporte
                            </a>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="relative h-80">
                        <canvas id="flujoCajaChart"></canvas>
                    </div>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t border-gray-200">
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Promedio Ingresos</p>
                            <p class="text-lg font-semibold text-green-600" id="promedio-ingresos">$0</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Promedio Gastos</p>
                            <p class="text-lg font-semibold text-red-600" id="promedio-gastos">$0</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Tendencia</p>
                            <p class="text-lg font-semibold" id="tendencia">
                                <i class="fas fa-arrow-up text-green-600"></i> Positiva
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Distribución por Categorías -->
        <div class="w-full xl:w-1/3 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h6 class="text-lg font-semibold text-gray-800">Gastos por Categoría</h6>
                            <p class="text-sm text-gray-600">Distribución del gasto actual</p>
                        </div>
                        <a href="{{ route('contabilidad.gastos.index') }}" 
                           class="text-green-600 hover:text-green-800 text-sm">
                            <i class="fas fa-external-link-alt"></i> Ver Todo
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    <div class="relative h-64 mb-4">
                        <canvas id="categoriasChart"></canvas>
                    </div>
                    <div class="space-y-2 text-sm">
                        @foreach($categorias_gastos as $index => $categoria)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-circle mr-2" style="color: {{ $categoria['color'] }}"></i> 
                                <span>{{ $categoria['nombre'] }}</span>
                            </div>
                            <span class="font-medium">${{ isset($categorias_chart['data'][$index]) ? number_format($categorias_chart['data'][$index], 0) : '0' }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tablas de Datos -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <!-- Movimientos Recientes -->
        <div class="w-full xl:w-2/3 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h6 class="text-lg font-semibold text-gray-800">Movimientos Recientes</h6>
                            <p class="text-sm text-gray-600">Últimas transacciones registradas</p>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('contabilidad.movimientos.index') }}" 
                               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                                <i class="fas fa-list mr-1"></i> Ver Todos
                            </a>
                            <button wire:click="abrirModal" 
                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                                <i class="fas fa-plus mr-1"></i> Nuevo
                            </button>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($movimientos_recientes as $movimiento)
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ date('d/m/Y', strtotime($movimiento->fecha)) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="max-w-xs truncate" title="{{ $movimiento->descripcion }}">
                                        {{ $movimiento->descripcion }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ $movimiento->categoria ?? 'Sin categoría' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                               {{ $movimiento->tipo == 'ingreso' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        <i class="fas fa-{{ $movimiento->tipo == 'ingreso' ? 'arrow-up' : 'arrow-down' }} mr-1"></i>
                                        {{ ucfirst($movimiento->tipo) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium 
                                        {{ $movimiento->tipo == 'ingreso' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $movimiento->tipo == 'ingreso' ? '+' : '-' }}${{ number_format($movimiento->monto, 2) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    <div class="py-8">
                                        <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                                        <p>No hay movimientos recientes</p>
                                        <button wire:click="abrirModal" 
                                                class="mt-2 text-blue-600 hover:text-blue-800">
                                            Crear el primer movimiento
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Cuentas por Vencer -->
        <div class="w-full xl:w-1/3 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h6 class="text-lg font-semibold text-gray-800">Cuentas por Vencer</h6>
                            <p class="text-sm text-gray-600">Próximos 15 días</p>
                        </div>
                        <a href="{{ route('contabilidad.cuentas-pendientes.index') }}" 
                           class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                            <i class="fas fa-list mr-1"></i> Ver Todas
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    @forelse($cuentas_vencer as $cuenta)
                        @php
                            $diasVencimiento = \Carbon\Carbon::parse($cuenta->fecha_vencimiento)->diffInDays(\Carbon\Carbon::now(), false);
                            $urgencia = $diasVencimiento <= 0 ? 'urgente' : ($diasVencimiento <= 7 ? 'pronto' : 'normal');
                            $colorClase = $urgencia == 'urgente' ? 'border-red-500 bg-red-50' : ($urgencia == 'pronto' ? 'border-yellow-500 bg-yellow-50' : 'border-gray-300 bg-gray-50');
                        @endphp
                        <div class="border-l-4 {{ $colorClase }} p-4 rounded-lg mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">{{ $cuenta->descripcion }}</div>
                                    <div class="text-sm text-gray-500">
                                        Vence: {{ date('d/m/Y', strtotime($cuenta->fecha_vencimiento)) }}
                                        @if($diasVencimiento <= 0)
                                            <span class="text-red-600 font-medium">(¡Vencida!)</span>
                                        @elseif($diasVencimiento <= 7)
                                            <span class="text-yellow-600 font-medium">({{ $diasVencimiento }} días)</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-semibold text-gray-900">${{ number_format($cuenta->monto, 2) }}</div>
                                    @if($urgencia == 'urgente')
                                        <i class="fas fa-exclamation-triangle text-red-500"></i>
                                    @elseif($urgencia == 'pronto')
                                        <i class="fas fa-clock text-yellow-500"></i>
                                    @else
                                        <i class="fas fa-calendar text-gray-400"></i>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <i class="fas fa-check-circle text-6xl text-green-500 mb-4"></i>
                            <p class="text-gray-500 font-medium">¡Excelente!</p>
                            <p class="text-gray-400 text-sm">No hay cuentas próximas a vencer</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Accesos Rápidos -->
    <div class="flex flex-wrap -mx-3">
        <div class="w-full px-3">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h6 class="text-lg font-semibold text-gray-800">Accesos Rápidos</h6>
                            <p class="text-sm text-gray-600">Navegación rápida a módulos principales</p>
                        </div>
                       <a href="{{ route('contabilidad.configuracion.index') }}" 
   class="text-gray-600 hover:text-gray-300 text-sm">
   <i class="fas fa-cog mr-1"></i> Configuración
</a>

                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        <!-- Movimientos -->
                        <a href="{{ route('contabilidad.movimientos.index') }}" 
                           class="flex flex-col items-center p-6 border border-blue-200 rounded-lg hover:bg-blue-50 hover:shadow-md transition duration-200 group">
                            <i class="fas fa-exchange-alt text-3xl text-blue-600 mb-3 group-hover:text-blue-700"></i>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700">Movimientos</span>
                            <span class="text-xs text-gray-500 mt-1 text-center">Ingresos y egresos</span>
                        </a>
                        
                        <!-- Facturas -->
                        <a href="{{ route('contabilidad.facturas.index') }}" 
                           class="flex flex-col items-center p-6 border border-green-200 rounded-lg hover:bg-green-50 hover:shadow-md transition duration-200 group">
                            <i class="fas fa-file-invoice text-3xl text-green-600 mb-3 group-hover:text-green-700"></i>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-green-700">Facturas</span>
                            <span class="text-xs text-gray-500 mt-1 text-center">Gestionar facturas</span>
                        </a>
                        
                        <!-- Gastos -->
                        <a href="{{ route('contabilidad.gastos.index') }}" 
                           class="flex flex-col items-center p-6 border border-red-200 rounded-lg hover:bg-red-50 hover:shadow-md transition duration-200 group">
                            <i class="fas fa-shopping-cart text-3xl text-red-600 mb-3 group-hover:text-red-700"></i>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-red-700">Gastos</span>
                            <span class="text-xs text-gray-500 mt-1 text-center">Registrar gastos</span>
                        </a>
                        
                        <!-- Pagos -->
                        <a href="{{ route('contabilidad.pagos.index') }}" 
                           class="flex flex-col items-center p-6 border border-purple-200 rounded-lg hover:bg-purple-50 hover:shadow-md transition duration-200 group">
                            <i class="fas fa-credit-card text-3xl text-purple-600 mb-3 group-hover:text-purple-700"></i>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-purple-700">Pagos</span>
                            <span class="text-xs text-gray-500 mt-1 text-center">Control de pagos</span>
                        </a>
                        
                        <!-- Cuentas Pendientes -->
                        <a href="{{ route('contabilidad.cuentas-pendientes.index') }}" 
                           class="flex flex-col items-center p-6 border border-yellow-200 rounded-lg hover:bg-yellow-50 hover:shadow-md transition duration-200 group">
                            <i class="fas fa-clock text-3xl text-yellow-600 mb-3 group-hover:text-yellow-700"></i>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-yellow-700">Pendientes</span>
                            <span class="text-xs text-gray-500 mt-1 text-center">Cuentas por cobrar</span>
                        </a>
                        
                        <!-- Reportes -->
                        <a href="{{ route('contabilidad.reportes.index') }}" 
                           class="flex flex-col items-center p-6 border border-indigo-200 rounded-lg hover:bg-indigo-50 hover:shadow-md transition duration-200 group">
                            <i class="fas fa-chart-line text-3xl text-indigo-600 mb-3 group-hover:text-indigo-700"></i>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">Reportes</span>
                            <span class="text-xs text-gray-500 mt-1 text-center">Análisis detallado</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo Movimiento -->
@if($modalAbierto)
<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Nuevo Movimiento Contable</h3>
                <button wire:click="cerrarModal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form wire:submit="guardarMovimiento" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Movimiento</label>
                    <select wire:model="tipoMovimiento" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccionar...</option>
                        <option value="ingreso">💰 Ingreso</option>
                        <option value="egreso">💸 Egreso</option>
                    </select>
                    @error('tipoMovimiento') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                    <input type="text" wire:model="descripcion" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="Ej: Venta de producto, pago de servicios...">
                    @error('descripcion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Monto</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">$</span>
                        <input type="number" wire:model="monto" step="0.01" class="w-full border border-gray-300 rounded-lg pl-8 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               placeholder="0.00">
                    </div>
                    @error('monto') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
                    <select wire:model="categoria_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Sin categoría</option>
                        @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha</label>
                    <input type="date" wire:model="fecha" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('fecha') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" wire:click="cerrarModal" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                        <i class="fas fa-save mr-2"></i> Guardar Movimiento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Notificaciones Flash -->
@if(session('success'))
<div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
    <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        {{ session('success') }}
    </div>
</div>
@endif

@if(session('error'))
<div class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
    <div class="flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i>
        {{ session('error') }}
    </div>
</div>
@endif

@if(session('info'))
<div class="fixed top-4 right-4 bg-blue-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
    <div class="flex items-center">
        <i class="fas fa-info-circle mr-2"></i>
        {{ session('info') }}
    </div>
</div>
@endif


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Variables globales
let flujoCajaChart = null;
let categoriasChart = null;
let tipoGraficoActual = 'line';

// ✅ CORRECCIÓN 1: Datos desde PHP (asegurarse de que lleguen correctamente)
const datosFlujoCaja = {
    fechas: @json($flujo_caja['fechas'] ?? []),
    ingresos: @json($flujo_caja['ingresos'] ?? []),
    egresos: @json($flujo_caja['egresos'] ?? [])
};

const datosCategorias = {
    labels: @json($categorias_chart['labels'] ?? []),
    data: @json($categorias_chart['data'] ?? [])
};

console.log('📊 Datos de flujo de caja:', datosFlujoCaja);
console.log('🥧 Datos de categorías:', datosCategorias);

// Función para toggle tipo de gráfico
function toggleTipoGrafico() {
    tipoGraficoActual = tipoGraficoActual === 'line' ? 'bar' : 'line';
    document.getElementById('tipo-grafico-text').textContent = tipoGraficoActual === 'line' ? 'Barras' : 'Líneas';
    actualizarGraficos();
}

// Función para actualizar gráficos
function actualizarGraficos() {
    if (flujoCajaChart) {
        flujoCajaChart.destroy();
    }
    if (categoriasChart) {
        categoriasChart.destroy();
    }
    
    // Esperar un poco antes de recrear
    setTimeout(() => {
        inicializarGraficos();
    }, 100);
}

// ✅ CORRECCIÓN 2: Función mejorada para inicializar gráficos
function inicializarGraficos() {
    console.log('🚀 Inicializando gráficos del dashboard...');
    
    // Verificar que los canvas existan
    const canvasFlujoCaja = document.getElementById('flujoCajaChart');
    const canvasCategorias = document.getElementById('categoriasChart');
    
    if (!canvasFlujoCaja || !canvasCategorias) {
        console.warn('⚠️ Canvas no encontrados');
        return;
    }

    // ✅ GRÁFICO DE FLUJO DE CAJA
    const ctxFlujo = canvasFlujoCaja.getContext('2d');
    flujoCajaChart = new Chart(ctxFlujo, {
        type: tipoGraficoActual,
        data: {
            labels: datosFlujoCaja.fechas,
            datasets: [{
                label: 'Ingresos',
                data: datosFlujoCaja.ingresos,
                borderColor: '#10b981',
                backgroundColor: tipoGraficoActual === 'bar' ? 'rgba(16, 185, 129, 0.7)' : 'rgba(16, 185, 129, 0.1)',
                borderWidth: 3,
                fill: tipoGraficoActual === 'line' ? false : true,
                tension: 0.4
            }, {
                label: 'Egresos',
                data: datosFlujoCaja.egresos,
                borderColor: '#ef4444',
                backgroundColor: tipoGraficoActual === 'bar' ? 'rgba(239, 68, 68, 0.7)' : 'rgba(239, 68, 68, 0.1)',
                borderWidth: 3,
                fill: tipoGraficoActual === 'line' ? false : true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            }
        }
    });

    // ✅ GRÁFICO DE CATEGORÍAS (DOUGHNUT)
    const ctxCategorias = canvasCategorias.getContext('2d');
    categoriasChart = new Chart(ctxCategorias, {
        type: 'doughnut',
        data: {
            labels: datosCategorias.labels,
            datasets: [{
                data: datosCategorias.data,
                backgroundColor: [
                    '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'
                ],
                borderWidth: 0,
                hoverBorderWidth: 3,
                hoverBorderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false // La leyenda se muestra manualmente en el HTML
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed * 100) / total).toFixed(1);
                            return context.label + ': $' + context.parsed.toLocaleString() + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    console.log('✅ Gráficos inicializados correctamente');
    
    // Calcular estadísticas después de crear los gráficos
    calcularEstadisticas();
}

// ✅ CORRECCIÓN 3: Función corregida para calcular estadísticas
function calcularEstadisticas() {
    const ingresos = datosFlujoCaja.ingresos;
    const egresos = datosFlujoCaja.egresos;
    
    if (ingresos.length > 0) {
        const promedioIngresos = ingresos.reduce((a, b) => a + b, 0) / ingresos.length;
        const promedioEgresos = egresos.reduce((a, b) => a + b, 0) / egresos.length;
        
        // Actualizar elementos del DOM
        const elemPromedioIngresos = document.getElementById('promedio-ingresos');
        const elemPromedioGastos = document.getElementById('promedio-gastos');
        const elemTendencia = document.getElementById('tendencia');
        
        if (elemPromedioIngresos) {
            elemPromedioIngresos.textContent = '$' + promedioIngresos.toLocaleString(undefined, {maximumFractionDigits: 0});
        }
        
        if (elemPromedioGastos) {
            elemPromedioGastos.textContent = '$' + promedioEgresos.toLocaleString(undefined, {maximumFractionDigits: 0});
        }
        
        // Calcular tendencia
        if (elemTendencia) {
            const tendencia = promedioIngresos > promedioEgresos ? 'Positiva' : 'Negativa';
            const iconoTendencia = promedioIngresos > promedioEgresos ? 'fa-arrow-up text-green-600' : 'fa-arrow-down text-red-600';
            
            elemTendencia.innerHTML = `<i class="fas ${iconoTendencia}"></i> ${tendencia}`;
        }
    }
}

// ✅ EVENTOS DE LIVEWIRE
document.addEventListener('livewire:init', () => {
    Livewire.on('periodo-cambiado', (data) => {
        console.log('📅 Período cambiado a:', data.periodo);
        // Aquí podrías recargar los datos si fuera necesario
        setTimeout(() => {
            actualizarGraficos();
        }, 500);
    });
});

// Auto-cerrar notificaciones
setTimeout(function() {
    const notifications = document.querySelectorAll('.fixed.top-4.right-4');
    notifications.forEach(notification => {
        notification.style.opacity = '0';
        setTimeout(() => {
            notification.style.display = 'none';
        }, 300);
    });
}, 5000);

// ✅ INICIALIZACIÓN PRINCIPAL
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 DOM cargado, inicializando gráficos del dashboard...');
    
    // Esperar un poco para asegurar que todo esté listo
    setTimeout(() => {
        inicializarGraficos();
    }, 100);
});

// ✅ REINICIALIZAR SI HAY CAMBIOS DE LIVEWIRE
document.addEventListener('livewire:navigated', () => {
    console.log('🔄 Livewire navegated - reinicializando gráficos del dashboard');
    setTimeout(inicializarGraficos, 300);
});
</script>