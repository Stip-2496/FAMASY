<?php
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\Herramienta;
use App\Models\Insumo;
use App\Models\Mantenimiento;
use App\Models\PrestamoHerramienta;
use App\Models\Inventario;
use App\Models\MovimientoContable;

new #[Layout('layouts.auth')] class extends Component {
    public $totalHerramientas;
    public $prestamosActivos;
    public $prestamosPorVencer;
    public $alertasStock;
    public $totalMantenimientos;
    public $mantenimientosProgramados;
    public $totalMovimientosMes;
    public $movimientosIngresos;
    public $movimientosEgresos;
    public $balanceMes;
    public $movimientosRecientes;
    public $categoriaMasMovimientos;
    public $insumosProximosVencer;
    public $estadisticasInsumos;
    public $ultimosMovimientos;
    public $ultimosMovimientosContables;
    public $herramientasChartData;
    public $insumosChartData;
    public $mantenimientosChartData;
    public $prestamosChartData;
    public $movimientosChartData;
    public $inventarioChartData;

    private array $colors = [
        '#4BC0C0', '#FF6384', '#FFCE56', '#36A2EB', '#8A2BE2', 
        '#FF9F40', '#9966FF', '#7ACB5E', '#FF6B6B', '#6A5ACD',
        '#20B2AA', '#FFA07A', '#9370DB', '#32CD32', '#BA55D3'
    ];

    public function mount()
    {
        $this->loadStatistics();
        $this->loadAdditionalData();
        $this->loadChartData();
    }

    protected function loadStatistics()
    {
        $this->totalHerramientas = Herramienta::activas()->count();
        $this->prestamosActivos = PrestamoHerramienta::where('estPre', 'prestado')->count();
        $this->prestamosPorVencer = PrestamoHerramienta::where('estPre', 'prestado')
            ->where('fecDev', '<=', now()->addDays(3))
            ->count();
        
        // CORREGIDO: Calcular alertas de stock dinámicamente
        $this->alertasStock = Insumo::activos()
            ->where(function($query) {
                $query->where('fecVenIns', '<=', now()->addDays(30))
                      ->where('fecVenIns', '>=', now());
            })
            ->count();
        
        $this->totalMantenimientos = Mantenimiento::count();
        $this->mantenimientosProgramados = Mantenimiento::where('estMan', 'pendiente')->count();
        $this->totalMovimientosMes = MovimientoContable::delMes()->count();
        $this->movimientosIngresos = MovimientoContable::ingresos()->delMes()->sum('montoMovCont') ?? 0;
        $this->movimientosEgresos = MovimientoContable::egresos()->delMes()->sum('montoMovCont') ?? 0;
        $this->balanceMes = $this->movimientosIngresos - $this->movimientosEgresos;
        $this->categoriaMasMovimientos = $this->obtenerCategoriaMasMovimientos();
    }

    protected function loadAdditionalData()
    {
        $this->insumosProximosVencer = Insumo::activos()
            ->whereBetween('fecVenIns', [now(), now()->addDays(30)])
            ->orderBy('fecVenIns')
            ->take(5)
            ->get();
        $this->estadisticasInsumos = [
            'total' => Insumo::activos()->count(),
            'disponibles' => Insumo::activos()->where('estIns', 'disponible')->count(),
            'por_vencer' => Insumo::activos()->whereBetween('fecVenIns', [now(), now()->addDays(30)])->count(),
            'vencidos' => Insumo::activos()->where('fecVenIns', '<', now())->count(),
        ];
        $this->ultimosMovimientos = Inventario::with(['insumo', 'herramienta', 'usuario'])
            ->orderBy('fecMovInv', 'desc')
            ->take(5)
            ->get();
        $this->ultimosMovimientosContables = MovimientoContable::with(['factura', 'compraGasto'])
            ->orderBy('fecMovCont', 'desc')
            ->take(5)
            ->get();
    }

    protected function loadChartData(): void
    {
        // Herramientas por Estado
        $herramientasPorEstado = Herramienta::selectRaw('estHer, count(*) as count')
            ->groupBy('estHer')
            ->get();
        $this->herramientasChartData = [
            'labels' => $herramientasPorEstado->pluck('estHer')->toArray(),
            'data' => $herramientasPorEstado->pluck('count')->toArray(),
            'colors' => $herramientasPorEstado->map(function ($item) {
                return $this->getColor($item->estHer);
            })->toArray()
        ];

        // Insumos por Estado
        $insumosPorEstado = Insumo::selectRaw('estIns, count(*) as count')
            ->groupBy('estIns')
            ->get();
        $this->insumosChartData = [
            'labels' => $insumosPorEstado->pluck('estIns')->toArray(),
            'data' => $insumosPorEstado->pluck('count')->toArray(),
            'colors' => $insumosPorEstado->map(function ($item) {
                return $this->getColor($item->estIns);
            })->toArray()
        ];

        // Mantenimientos por Estado
        $mantenimientosPorEstado = Mantenimiento::selectRaw('estMan, count(*) as count')
            ->groupBy('estMan')
            ->get();
        $this->mantenimientosChartData = [
            'labels' => $mantenimientosPorEstado->pluck('estMan')->toArray(),
            'data' => $mantenimientosPorEstado->pluck('count')->toArray(),
            'colors' => $mantenimientosPorEstado->map(function ($item) {
                return $this->getColor($item->estMan);
            })->toArray()
        ];

        // Préstamos por Estado
        $prestamosPorEstado = PrestamoHerramienta::selectRaw('estPre, count(*) as count')
            ->groupBy('estPre')
            ->get();
        $this->prestamosChartData = [
            'labels' => $prestamosPorEstado->pluck('estPre')->toArray(),
            'data' => $prestamosPorEstado->pluck('count')->toArray(),
            'colors' => $prestamosPorEstado->map(function ($item) {
                return $this->getColor($item->estPre);
            })->toArray()
        ];

        // CORREGIDO: Movimientos Contables por Categoría con fallback
        $movimientosPorCategoria = MovimientoContable::delMes()
            ->selectRaw('catMovCont, count(*) as count')
            ->groupBy('catMovCont')
            ->having('count', '>', 0)
            ->get();
        
        // Si no hay datos del mes actual, usar todos los movimientos
        if ($movimientosPorCategoria->isEmpty()) {
            $movimientosPorCategoria = MovimientoContable::selectRaw('catMovCont, count(*) as count')
                ->groupBy('catMovCont')
                ->having('count', '>', 0)
                ->get();
        }

        $this->movimientosChartData = [
            'labels' => $movimientosPorCategoria->pluck('catMovCont')->toArray(),
            'data' => $movimientosPorCategoria->pluck('count')->toArray(),
            'colors' => $movimientosPorCategoria->map(function ($item) {
                return $this->getColor($item->catMovCont);
            })->toArray()
        ];

        // CORREGIDO: Inventario por Tipo de Movimiento con fallback
        $inventarioPorTipo = Inventario::selectRaw('tipMovInv, count(*) as count')
            ->where('fecMovInv', '>=', now()->subMonth())
            ->groupBy('tipMovInv')
            ->having('count', '>', 0)
            ->get();

        // Si no hay datos del último mes, usar todos los datos
        if ($inventarioPorTipo->isEmpty()) {
            $inventarioPorTipo = Inventario::selectRaw('tipMovInv, count(*) as count')
                ->groupBy('tipMovInv')
                ->having('count', '>', 0)
                ->get();
        }

        $this->inventarioChartData = [
            'labels' => $inventarioPorTipo->pluck('tipMovInv')->toArray(),
            'data' => $inventarioPorTipo->pluck('count')->toArray(),
            'colors' => $inventarioPorTipo->map(function ($item) {
                return $this->getColor($item->tipMovInv);
            })->toArray()
        ];

        // Logging para depuración (comentar en producción)
        \Log::info('Movimientos Chart Data:', $this->movimientosChartData);
        \Log::info('Inventario Chart Data:', $this->inventarioChartData);

        $this->dispatch('datosCargados');
    }

    protected function obtenerCategoriaMasMovimientos()
    {
        $categorias = MovimientoContable::delMes()
            ->select('catMovCont', \DB::raw('COUNT(*) as total'))
            ->groupBy('catMovCont')
            ->orderBy('total', 'desc')
            ->first();
        return $categorias ? $categorias->catMovCont : 'Sin movimientos';
    }

    public function getColor($key): string
    {
        $index = abs(crc32($key)) % count($this->colors);
        return $this->colors[$index];
    }

    public function with(): array
    {
        return [
            'totalHerramientas' => $this->totalHerramientas,
            'prestamosActivos' => $this->prestamosActivos,
            'prestamosPorVencer' => $this->prestamosPorVencer,
            'alertasStock' => $this->alertasStock,
            'totalMantenimientos' => $this->totalMantenimientos,
            'mantenimientosProgramados' => $this->mantenimientosProgramados,
            'totalMovimientosMes' => $this->totalMovimientosMes,
            'movimientosIngresos' => $this->movimientosIngresos,
            'movimientosEgresos' => $this->movimientosEgresos,
            'balanceMes' => $this->balanceMes,
            'categoriaMasMovimientos' => $this->categoriaMasMovimientos,
            'insumosProximosVencer' => $this->insumosProximosVencer,
            'estadisticasInsumos' => $this->estadisticasInsumos,
            'ultimosMovimientos' => $this->ultimosMovimientos,
            'ultimosMovimientosContables' => $this->ultimosMovimientosContables,
            'herramientasChartData' => $this->herramientasChartData,
            'insumosChartData' => $this->insumosChartData,
            'mantenimientosChartData' => $this->mantenimientosChartData,
            'prestamosChartData' => $this->prestamosChartData,
            'movimientosChartData' => $this->movimientosChartData,
            'inventarioChartData' => $this->inventarioChartData,
        ];
    }
}; ?>

@section('title', 'Dashboard Inventario')

<div class="min-h-full py-2">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-2 text-center">
            <h1 class="text-2xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent mb-1">Módulo de Inventario</h1>
            <p class="text-xs text-gray-600 max-w-2xl mx-auto leading-relaxed">Panel de control para la gestión de herramientas, insumos y movimientos</p>
        </div>

        <!-- Dashboard Cards -->
        <div class="mt-4 grid gap-y-4 gap-x-4 md:grid-cols-2 xl:grid-cols-3">
            <!-- Herramientas -->
            <div class="relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md hover:shadow-lg transition-all duration-300">
                <a href="{{ route('inventario.herramientas.index') }}" wire:navigate>
                    <div class="bg-clip-border mx-2 rounded-xl overflow-hidden bg-gradient-to-tr from-blue-600 to-blue-400 text-white shadow-blue-500/40 shadow-lg absolute -mt-2 grid h-10 w-10 place-items-center">
                        <i class="fas fa-tools text-sm"></i>
                    </div>
                </a>
                <div class="p-2 text-right">
                    <p class="block antialiased font-sans text-xs leading-normal font-normal text-blue-gray-600">Herramientas</p>
                    <h4 class="block antialiased tracking-normal font-sans text-lg font-semibold leading-snug text-blue-gray-900">{{ number_format($totalHerramientas ?? 0) }}</h4>
                </div>
                <div class="border-t border-blue-gray-50 py-1 px-2">
                    <p class="block antialiased font-sans text-xs leading-relaxed font-normal text-blue-gray-600">
                        <strong class="text-green-500">+{{ count($herramientasChartData['data'] ?? []) > 0 ? round((array_sum($herramientasChartData['data']) / max($totalHerramientas, 1)) * 100) : 0 }}%</strong>
                    </p>
                </div>
                <div class="border-t border-blue-gray-50 p-2 h-[160px] flex items-center justify-center">
                    <canvas id="herramientasChart"></canvas>
                </div>
                <div class="border-t border-blue-gray-50 py-1 px-2">
                    <div class="flex gap-1">
                        <a href="{{ route('inventario.herramientas.index') }}" wire:navigate class="flex-1 bg-indigo-50 text-indigo-600 font-medium py-0.5 px-1.5 rounded-lg text-xs text-center hover:bg-indigo-100 transition-colors">Listado</a>
                        <a href="{{ route('inventario.herramientas.create') }}" wire:navigate class="flex-1 bg-indigo-50 text-indigo-600 font-medium py-0.5 px-1.5 rounded-lg text-xs text-center hover:bg-indigo-100 transition-colors">Registrar</a>
                    </div>
                </div>
            </div>

            <!-- Préstamos -->
            <div class="relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md hover:shadow-lg transition-all duration-300">
                <a href="{{ route('inventario.prestamos.index') }}" wire:navigate>
                    <div class="bg-clip-border mx-2 rounded-xl overflow-hidden bg-gradient-to-tr from-yellow-600 to-yellow-400 text-white shadow-yellow-500/40 shadow-lg absolute -mt-2 grid h-10 w-10 place-items-center">
                        <i class="fas fa-handshake text-sm"></i>
                    </div>
                </a>
                <div class="p-2 text-right">
                    <p class="block antialiased font-sans text-xs leading-normal font-normal text-blue-gray-600">Préstamos Activos</p>
                    <h4 class="block antialiased tracking-normal font-sans text-lg font-semibold leading-snug text-blue-gray-900">{{ number_format($prestamosActivos ?? 0) }}</h4>
                </div>
                <div class="border-t border-blue-gray-50 py-1 px-2">
                    <p class="block antialiased font-sans text-xs leading-relaxed font-normal text-blue-gray-600">
                        <strong class="text-yellow-600">{{ $prestamosPorVencer }} por vencer</strong>
                    </p>
                </div>
                <div class="border-t border-blue-gray-50 p-2 h-[160px] flex items-center justify-center">
                    <canvas id="prestamosChart"></canvas>
                </div>
                <div class="border-t border-blue-gray-50 py-1 px-2">
                    <div class="flex gap-1">
                        <a href="{{ route('inventario.prestamos.index') }}" wire:navigate class="flex-1 bg-indigo-50 text-indigo-600 font-medium py-0.5 px-1.5 rounded-lg text-xs text-center hover:bg-indigo-100 transition-colors">Listado</a>
                        <a href="{{ route('inventario.prestamos.create') }}" wire:navigate class="flex-1 bg-indigo-50 text-indigo-600 font-medium py-0.5 px-1.5 rounded-lg text-xs text-center hover:bg-indigo-100 transition-colors">Nuevo</a>
                    </div>
                </div>
            </div>

            <!-- Movimientos Contables -->
            <div class="relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md hover:shadow-lg transition-all duration-300">
                <a href="{{ route('inventario.movimientos.index') }}" wire:navigate>
                    <div class="bg-clip-border mx-2 rounded-xl overflow-hidden bg-gradient-to-tr from-green-600 to-green-400 text-white shadow-green-500/40 shadow-lg absolute -mt-2 grid h-10 w-10 place-items-center">
                        <i class="fas fa-chart-bar text-sm"></i>
                    </div>
                </a>
                <div class="p-2 text-right">
                    <p class="block antialiased font-sans text-xs leading-normal font-normal text-blue-gray-600">Movimientos</p>
                    <h4 class="block antialiased tracking-normal font-sans text-lg font-semibold leading-snug text-blue-gray-900">{{ number_format($totalMovimientosMes ?? 0) }}</h4>
                </div>
                <div class="border-t border-blue-gray-50 py-1 px-2">
                    <p class="block antialiased font-sans text-xs leading-relaxed font-normal text-blue-gray-600">
                        <strong class="{{ $balanceMes >= 0 ? 'text-green-500' : 'text-red-500' }}">Balance: ${{ number_format(abs($balanceMes), 2) }}</strong>
                    </p>
                </div>
                <div class="border-t border-blue-gray-50 p-2 h-[160px] flex items-center justify-center">
                    <canvas id="movimientosChart"></canvas>
                </div>
                <div class="border-t border-blue-gray-50 py-1 px-2">
                    <div class="flex gap-1">
                        <a href="{{ route('inventario.movimientos.index') }}" wire:navigate class="flex-1 bg-indigo-50 text-indigo-600 font-medium py-0.5 px-1.5 rounded-lg text-xs text-center hover:bg-indigo-100 transition-colors">Listado</a>
                        <a href="{{ route('inventario.movimientos.create') }}" wire:navigate class="flex-1 bg-indigo-50 text-indigo-600 font-medium py-0.5 px-1.5 rounded-lg text-xs text-center hover:bg-indigo-100 transition-colors">Nuevo</a>
                    </div>
                </div>
            </div>

            <!-- Insumos -->
            <div class="relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md hover:shadow-lg transition-all duration-300">
                <a href="{{ route('inventario.insumos.index') }}" wire:navigate>
                    <div class="bg-clip-border mx-2 rounded-xl overflow-hidden bg-gradient-to-tr from-orange-600 to-orange-400 text-white shadow-orange-500/40 shadow-lg absolute -mt-2 grid h-10 w-10 place-items-center">
                        <i class="fas fa-boxes text-sm"></i>
                    </div>
                </a>
                <div class="p-2 text-right">
                    <p class="block antialiased font-sans text-xs leading-normal font-normal text-blue-gray-600">Insumos</p>
                    <h4 class="block antialiased tracking-normal font-sans text-lg font-semibold leading-snug text-blue-gray-900">{{ number_format($estadisticasInsumos['total'] ?? 0) }}</h4>
                </div>
                <div class="border-t border-blue-gray-50 py-1 px-2">
                    <p class="block antialiased font-sans text-xs leading-relaxed font-normal text-blue-gray-600">
                        <strong class="text-red-500">{{ $estadisticasInsumos['por_vencer'] ?? 0 }} por vencer</strong>
                    </p>
                </div>
                <div class="border-t border-blue-gray-50 p-2 h-[160px] flex items-center justify-center">
                    <canvas id="insumosChart"></canvas>
                </div>
                <div class="border-t border-blue-gray-50 py-1 px-2">
                    <div class="flex gap-1">
                        <a href="{{ route('inventario.insumos.index') }}" wire:navigate class="flex-1 bg-indigo-50 text-indigo-600 font-medium py-0.5 px-1.5 rounded-lg text-xs text-center hover:bg-indigo-100 transition-colors">Listado</a>
                        <a href="{{ route('inventario.insumos.create') }}" wire:navigate class="flex-1 bg-indigo-50 text-indigo-600 font-medium py-0.5 px-1.5 rounded-lg text-xs text-center hover:bg-indigo-100 transition-colors">Registrar</a>
                    </div>
                </div>
            </div>

            <!-- Mantenimientos -->
            <div class="relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md hover:shadow-lg transition-all duration-300">
                <a href="{{ route('inventario.mantenimientos.index') }}" wire:navigate>
                    <div class="bg-clip-border mx-2 rounded-xl overflow-hidden bg-gradient-to-tr from-purple-600 to-purple-400 text-white shadow-purple-500/40 shadow-lg absolute -mt-2 grid h-10 w-10 place-items-center">
                        <i class="fas fa-wrench text-sm"></i>
                    </div>
                </a>
                <div class="p-2 text-right">
                    <p class="block antialiased font-sans text-xs leading-normal font-normal text-blue-gray-600">Mantenimientos</p>
                    <h4 class="block antialiased tracking-normal font-sans text-lg font-semibold leading-snug text-blue-gray-900">{{ number_format($totalMantenimientos ?? 0) }}</h4>
                </div>
                <div class="border-t border-blue-gray-50 py-1 px-2">
                    <p class="block antialiased font-sans text-xs leading-relaxed font-normal text-blue-gray-600">
                        <strong class="text-purple-600">{{ $mantenimientosProgramados }} programados</strong>
                    </p>
                </div>
                <div class="border-t border-blue-gray-50 p-2 h-[160px] flex items-center justify-center">
                    <canvas id="mantenimientosChart"></canvas>
                </div>
                <div class="border-t border-blue-gray-50 py-1 px-2">
                    <div class="flex gap-1">
                        <a href="{{ route('inventario.mantenimientos.index') }}" wire:navigate class="flex-1 bg-indigo-50 text-indigo-600 font-medium py-0.5 px-1.5 rounded-lg text-xs text-center hover:bg-indigo-100 transition-colors">Listado</a>
                        <a href="{{ route('inventario.mantenimientos.create') }}" wire:navigate class="flex-1 bg-indigo-50 text-indigo-600 font-medium py-0.5 px-1.5 rounded-lg text-xs text-center hover:bg-indigo-100 transition-colors">Programar</a>
                    </div>
                </div>
            </div>

            <!-- Alertas de Stock -->
            <div class="relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md hover:shadow-lg transition-all duration-300">
                <a href="{{ route('inventario.movimientos.index') }}" wire:navigate>
                    <div class="bg-clip-border mx-2 rounded-xl overflow-hidden bg-gradient-to-tr from-red-600 to-red-400 text-white shadow-red-500/40 shadow-lg absolute -mt-2 grid h-10 w-10 place-items-center">
                        <i class="fas fa-exclamation-triangle text-sm"></i>
                    </div>
                </a>
                <div class="p-2 text-right">
                    <p class="block antialiased font-sans text-xs leading-normal font-normal text-blue-gray-600">Alertas de Stock</p>
                    <h4 class="block antialiased tracking-normal font-sans text-lg font-semibold leading-snug text-blue-gray-900">{{ number_format($alertasStock ?? 0) }}</h4>
                </div>
                <div class="border-t border-blue-gray-50 py-1 px-2">
                    <p class="block antialiased font-sans text-xs leading-relaxed font-normal text-blue-gray-600">
                        <strong class="text-red-600">Requiere atención</strong>
                    </p>
                </div>
                <div class="border-t border-blue-gray-50 p-2 h-[160px] flex items-center justify-center">
                    <canvas id="inventarioChart"></canvas>
                </div>
                <div class="border-t border-blue-gray-50 py-1 px-2">
                    <div class="flex gap-1">
                        <a href="{{ route('inventario.movimientos.index') }}" wire:navigate class="flex-1 bg-indigo-50 text-indigo-600 font-medium py-0.5 px-1.5 rounded-lg text-xs text-center hover:bg-indigo-100 transition-colors">Listado</a>
                        <a href="{{ route('inventario.movimientos.create') }}" wire:navigate class="flex-1 bg-indigo-50 text-indigo-600 font-medium py-0.5 px-1.5 rounded-lg text-xs text-center hover:bg-indigo-100 transition-colors">Registrar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let graficosInicializados = false;
let chartsInstances = {
    herramientasChart: null,
    insumosChart: null,
    mantenimientosChart: null,
    prestamosChart: null,
    movimientosChart: null,
    inventarioChart: null
};

function chartJsEstaCargado() {
    return typeof Chart !== 'undefined' && typeof Chart === 'function';
}

function resetearInicializacionGraficos() {
    graficosInicializados = false;
    Object.values(chartsInstances).forEach(chart => {
        if (chart) chart.destroy();
    });
    chartsInstances = {
        herramientasChart: null,
        insumosChart: null,
        mantenimientosChart: null,
        prestamosChart: null,
        movimientosChart: null,
        inventarioChart: null
    };
    console.log('Estado de gráficos reseteado para re-inicialización');
}

function inicializarGraficosInventario() {
    if (graficosInicializados) {
        console.log('Gráficos ya inicializados, omitiendo...');
        return;
    }
    if (!chartJsEstaCargado()) {
        console.log('Chart.js no está cargado, reintentando en 100ms...');
        setTimeout(inicializarGraficosInventario, 100);
        return;
    }
    console.log('Inicializando gráficos del dashboard de inventario...');
    const ctxHerramientas = document.getElementById('herramientasChart');
    const ctxInsumos = document.getElementById('insumosChart');
    const ctxMantenimientos = document.getElementById('mantenimientosChart');
    const ctxPrestamos = document.getElementById('prestamosChart');
    const ctxMovimientos = document.getElementById('movimientosChart');
    const ctxInventario = document.getElementById('inventarioChart');
    const canvases = [ctxHerramientas, ctxInsumos, ctxMantenimientos, ctxPrestamos, ctxMovimientos, ctxInventario];
    if (canvases.some(canvas => !canvas)) {
        console.log('Elementos canvas no encontrados en el DOM, reintentando en 100ms...');
        setTimeout(inicializarGraficosInventario, 100);
        return;
    }
    const herramientasData = @json($herramientasChartData);
    const insumosData = @json($insumosChartData);
    const mantenimientosData = @json($mantenimientosChartData);
    const prestamosData = @json($prestamosChartData);
    const movimientosData = @json($movimientosChartData);
    const inventarioData = @json($inventarioChartData);
    
    // CORREGIDO: Mostrar datos en consola para depuración
    console.log('Movimientos Data:', movimientosData);
    console.log('Inventario Data:', inventarioData);
    
    const datosRequeridos = [herramientasData, insumosData, mantenimientosData, prestamosData, movimientosData, inventarioData];
    if (datosRequeridos.some(data => !data || !data.labels || !data.data)) {
        console.log('Datos no disponibles, reintentando en 100ms...');
        setTimeout(inicializarGraficosInventario, 100);
        return;
    }
    Object.values(chartsInstances).forEach(chart => {
        if (chart) chart.destroy();
    });
    try {
        if (herramientasData.labels.length > 0) {
            chartsInstances.herramientasChart = new Chart(ctxHerramientas, {
                type: 'pie',
                data: {
                    labels: herramientasData.labels,
                    datasets: [{
                        data: herramientasData.data,
                        backgroundColor: herramientasData.colors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                font: { size: 10 }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const value = context.raw;
                                    const percentage = Math.round((value / total) * 100);
                                    return `${context.label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
            console.log('✅ Gráfico de Herramientas inicializado');
        } else {
            console.warn('⚠️ No hay datos para gráfico de Herramientas');
        }
        
        if (insumosData.labels.length > 0) {
            chartsInstances.insumosChart = new Chart(ctxInsumos, {
                type: 'pie',
                data: {
                    labels: insumosData.labels,
                    datasets: [{
                        data: insumosData.data,
                        backgroundColor: insumosData.colors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: { font: { size: 10 } }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const value = context.raw;
                                    const percentage = Math.round((value / total) * 100);
                                    return `${context.label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
            console.log('✅ Gráfico de Insumos inicializado');
        } else {
            console.warn('⚠️ No hay datos para gráfico de Insumos');
        }
        
        if (mantenimientosData.labels.length > 0) {
            chartsInstances.mantenimientosChart = new Chart(ctxMantenimientos, {
                type: 'bar',
                data: {
                    labels: mantenimientosData.labels,
                    datasets: [{
                        label: 'Mantenimientos',
                        data: mantenimientosData.data,
                        backgroundColor: mantenimientosData.colors,
                        borderColor: mantenimientosData.colors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
            console.log('✅ Gráfico de Mantenimientos inicializado');
        } else {
            console.warn('⚠️ No hay datos para gráfico de Mantenimientos');
        }
        
        if (prestamosData.labels.length > 0) {
            chartsInstances.prestamosChart = new Chart(ctxPrestamos, {
                type: 'bar',
                data: {
                    labels: prestamosData.labels,
                    datasets: [{
                        label: 'Préstamos',
                        data: prestamosData.data,
                        backgroundColor: prestamosData.colors,
                        borderColor: prestamosData.colors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
            console.log('✅ Gráfico de Préstamos inicializado');
        } else {
            console.warn('⚠️ No hay datos para gráfico de Préstamos');
        }
        
        // CORREGIDO: Movimientos Contables con mensaje de depuración
        if (movimientosData.labels.length > 0) {
            chartsInstances.movimientosChart = new Chart(ctxMovimientos, {
                type: 'bar',
                data: {
                    labels: movimientosData.labels,
                    datasets: [{
                        label: 'Movimientos',
                        data: movimientosData.data,
                        backgroundColor: movimientosData.colors,
                        borderColor: movimientosData.colors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
            console.log('✅ Gráfico de Movimientos Contables inicializado');
        } else {
            console.warn('⚠️ No hay datos para gráfico de Movimientos Contables');
        }
        
        // CORREGIDO: Inventario con mensaje de depuración
        if (inventarioData.labels.length > 0) {
            chartsInstances.inventarioChart = new Chart(ctxInventario, {
                type: 'bar',
                data: {
                    labels: inventarioData.labels,
                    datasets: [{
                        label: 'Movimientos de Inventario',
                        data: inventarioData.data,
                        backgroundColor: inventarioData.colors,
                        borderColor: inventarioData.colors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
            console.log('✅ Gráfico de Alertas de Stock (Inventario) inicializado');
        } else {
            console.warn('⚠️ No hay datos para gráfico de Alertas de Stock (Inventario)');
        }
        
        graficosInicializados = true;
        console.log('✅ Todos los gráficos de inventario inicializados correctamente');
    } catch (error) {
        console.error('❌ Error inicializando gráficos:', error);
        console.log('Reintentando en 200ms...');
        setTimeout(inicializarGraficosInventario, 200);
    }
}

document.addEventListener('livewire:load', function() {
    console.log('Livewire cargado - Programando inicialización de gráficos de inventario');
    setTimeout(inicializarGraficosInventario, 800);
});

Livewire.on('datosCargados', () => {
    console.log('Datos cargados - Programando inicialización de gráficos de inventario');
    setTimeout(inicializarGraficosInventario, 50);
});

Livewire.on('destroyed', () => {
    console.log('Componente destruido - Reseteando gráficos de inventario');
    resetearInicializacionGraficos();
});

document.addEventListener('livewire:navigated', function() {
    console.log('Livewire navigated - Verificando si estamos en dashboard de inventario');
    if (window.location.href.includes('/inventario') || document.getElementById('herramientasChart')) {
        console.log('Dashboard de inventario detectado - Reseteando gráficos para re-inicialización');
        resetearInicializacionGraficos();
        setTimeout(inicializarGraficosInventario, 300);
    }
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM cargado - Intentando inicializar gráficos de inventario');
        setTimeout(inicializarGraficosInventario, 500);
    });
} else {
    console.log('DOM ya listo - Intentando inicializar gráficos de inventario');
    setTimeout(inicializarGraficosInventario, 500);
}
</script>