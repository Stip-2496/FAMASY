<?php
// resources/views/livewire/contabilidad/reportes/index.blade.php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

new #[Layout('layouts.auth')] class extends Component {

    // Propiedades para filtros
    public $periodo = 'mes_actual';
    public $fechaInicio = '';
    public $fechaFin = '';
    public $tipoReporte = '';
    public $estadisticas = [];
public $tendencias_data = [];
public $categorias_data = [];
public $cuentas_pendientes_data = [];
public $fecha_personalizada_desde = '';
public $fecha_personalizada_hasta = '';

    // Datos de reportes
    public $flujoCaja = [];
    public $estadoResultados = [];
    public $balanceGeneral = [];
    public $cuentasPendientes = [];
    public $indicadoresClave = [];
    public $gastosPorCategoria = [];
    public $tendenciasFinancieras = [];

    public function mount()
{
    $this->fechaInicio = now()->startOfMonth()->format('Y-m-d');
    $this->fechaFin = now()->endOfMonth()->format('Y-m-d');
    $this->fecha_personalizada_desde = $this->fechaInicio;
    $this->fecha_personalizada_hasta = $this->fechaFin;
    
    // Cargar datos inmediatamente
    $this->cargarReportes();
    
    Log::info('Reportes montados con datos:', [
        'tendencias' => count($this->tendencias_data['labels'] ?? []),
        'categorias' => count($this->categorias_data['labels'] ?? []),
        'cuentas' => count($this->cuentas_pendientes_data['labels'] ?? [])
    ]);
}

public function actualizarGraficos()
{
    $this->cargarReportes();
    $this->dispatch('reporte-actualizado');
    session()->flash('success', 'Gráficos actualizados');
}

    public function cargarReportes()
    {
        $this->calcularFlujoCaja();
        $this->calcularEstadoResultados();
        $this->calcularBalanceGeneral();
        $this->calcularCuentasPendientes();
        $this->calcularIndicadoresClave();
        $this->calcularGastosPorCategoria();
        $this->calcularTendenciasFinancieras();
    }

    public function calcularFlujoCaja()
    {
        try {
            $fechaInicio = $this->fechaInicio;
            $fechaFin = $this->fechaFin;

            // Ingresos del período
            $ingresos = DB::table('movimientoscontables')
                ->where('tipoMovCont', 'ingreso')
                ->whereBetween('fecMovCont', [$fechaInicio, $fechaFin])
                ->sum('montoMovCont') ?? 0;

            // Egresos del período
            $egresos = DB::table('movimientoscontables')
                ->where('tipoMovCont', 'egreso')
                ->whereBetween('fecMovCont', [$fechaInicio, $fechaFin])
                ->sum('montoMovCont') ?? 0;

            // Flujo neto
            $flujoNeto = $ingresos - $egresos;

            // Flujo por días (últimos 30 días)
            $flujoDiario = DB::table('movimientoscontables')
                ->select(
                    'fecMovCont',
                    DB::raw('SUM(CASE WHEN tipoMovCont = "ingreso" THEN montoMovCont ELSE 0 END) as ingresos_dia'),
                    DB::raw('SUM(CASE WHEN tipoMovCont = "egreso" THEN montoMovCont ELSE 0 END) as egresos_dia')
                )
                ->whereBetween('fecMovCont', [now()->subDays(30), now()])
                ->groupBy('fecMovCont')
                ->orderBy('fecMovCont', 'desc')
                ->limit(10)
                ->get();

            $this->flujoCaja = [
                'total_ingresos' => $ingresos,
                'total_egresos' => $egresos,
                'flujo_neto' => $flujoNeto,
                'flujo_diario' => $flujoDiario,
                'margen' => $ingresos > 0 ? (($flujoNeto / $ingresos) * 100) : 0
            ];

        } catch (\Exception $e) {
            Log::error('Error calculando flujo de caja: ' . $e->getMessage());
            $this->flujoCaja = [
                'total_ingresos' => 0,
                'total_egresos' => 0,
                'flujo_neto' => 0,
                'flujo_diario' => collect(),
                'margen' => 0
            ];
        }
    }

    public function calcularEstadoResultados()
    {
        try {
            $fechaInicio = $this->fechaInicio;
            $fechaFin = $this->fechaFin;

            // Ingresos por categoría
            $ingresosPorCategoria = DB::table('movimientoscontables')
                ->where('tipoMovCont', 'ingreso')
                ->whereBetween('fecMovCont', [$fechaInicio, $fechaFin])
                ->select('catMovCont', DB::raw('SUM(montoMovCont) as total'))
                ->groupBy('catMovCont')
                ->orderBy('total', 'desc')
                ->get();

            // Gastos por categoría
            $gastosPorCategoria = DB::table('movimientoscontables')
                ->where('tipoMovCont', 'egreso')
                ->whereBetween('fecMovCont', [$fechaInicio, $fechaFin])
                ->select('catMovCont', DB::raw('SUM(montoMovCont) as total'))
                ->groupBy('catMovCont')
                ->orderBy('total', 'desc')
                ->get();

            $totalIngresos = $ingresosPorCategoria->sum('total');
            $totalGastos = $gastosPorCategoria->sum('total');

            $this->estadoResultados = [
                'ingresos_por_categoria' => $ingresosPorCategoria,
                'gastos_por_categoria' => $gastosPorCategoria,
                'total_ingresos' => $totalIngresos,
                'total_gastos' => $totalGastos,
                'utilidad_bruta' => $totalIngresos - $totalGastos,
                'margen_utilidad' => $totalIngresos > 0 ? ((($totalIngresos - $totalGastos) / $totalIngresos) * 100) : 0
            ];

        } catch (\Exception $e) {
            Log::error('Error calculando estado de resultados: ' . $e->getMessage());
            $this->estadoResultados = [
                'ingresos_por_categoria' => collect(),
                'gastos_por_categoria' => collect(),
                'total_ingresos' => 0,
                'total_gastos' => 0,
                'utilidad_bruta' => 0,
                'margen_utilidad' => 0
            ];
        }
    }

    public function calcularBalanceGeneral()
    {
        try {
            // Activos (simplificado)
            $efectivo = $this->flujoCaja['flujo_neto'] ?? 0;
            
            // Cuentas por cobrar
            $cuentasPorCobrar = DB::table('cuentaspendientes')
                ->where('tipCuePen', 'por_cobrar')
                ->where('estCuePen', '!=', 'pagado')
                ->sum('montoSaldo') ?? 0;

            // Pasivos
            $cuentasPorPagar = DB::table('cuentaspendientes')
                ->where('tipCuePen', 'por_pagar')
                ->where('estCuePen', '!=', 'pagado')
                ->sum('montoSaldo') ?? 0;

            $totalActivos = $efectivo + $cuentasPorCobrar;
            $totalPasivos = $cuentasPorPagar;
            $patrimonio = $totalActivos - $totalPasivos;

            $this->balanceGeneral = [
                'efectivo' => max(0, $efectivo),
                'cuentas_por_cobrar' => $cuentasPorCobrar,
                'total_activos' => $totalActivos,
                'cuentas_por_pagar' => $cuentasPorPagar,
                'total_pasivos' => $totalPasivos,
                'patrimonio' => $patrimonio,
                'razon_liquidez' => $totalPasivos > 0 ? ($totalActivos / $totalPasivos) : 0
            ];

        } catch (\Exception $e) {
            Log::error('Error calculando balance general: ' . $e->getMessage());
            $this->balanceGeneral = [
                'efectivo' => 0,
                'cuentas_por_cobrar' => 0,
                'total_activos' => 0,
                'cuentas_por_pagar' => 0,
                'total_pasivos' => 0,
                'patrimonio' => 0,
                'razon_liquidez' => 0
            ];
        }
    }

    public function calcularCuentasPendientes()
{
    try {
        // Análisis de vencimientos
        $vencidas = DB::table('cuentaspendientes')
            ->where('fecVencimiento', '<', now())
            ->where('estCuePen', '!=', 'pagado')
            ->selectRaw('tipCuePen, COUNT(*) as cantidad, SUM(montoSaldo) as total')
            ->groupBy('tipCuePen')
            ->get();

        $proximasVencer = DB::table('cuentaspendientes')
            ->whereBetween('fecVencimiento', [now(), now()->addDays(7)])
            ->where('estCuePen', '!=', 'pagado')
            ->selectRaw('tipCuePen, COUNT(*) as cantidad, SUM(montoSaldo) as total')
            ->groupBy('tipCuePen')
            ->get();

        $alDia = DB::table('cuentaspendientes')
            ->where('fecVencimiento', '>', now()->addDays(7))
            ->where('estCuePen', '!=', 'pagado')
            ->count();

        $this->cuentasPendientes = [
            'vencidas' => $vencidas,
            'proximas_vencer' => $proximasVencer,
            'total_vencidas' => $vencidas->sum('total'),
            'total_proximas' => $proximasVencer->sum('total')
        ];

        // ✅ AGREGAR: Datos para el gráfico
        $this->cuentas_pendientes_data = [
            'labels' => ['Al día', 'Próximas a vencer', 'Vencidas'],
            'data' => [
                $alDia,
                $proximasVencer->sum('cantidad'),
                $vencidas->sum('cantidad')
            ]
        ];

    } catch (\Exception $e) {
        Log::error('Error calculando cuentas pendientes: ' . $e->getMessage());
        $this->cuentasPendientes = [
            'vencidas' => collect(),
            'proximas_vencer' => collect(),
            'total_vencidas' => 0,
            'total_proximas' => 0
        ];
        $this->cuentas_pendientes_data = [
            'labels' => [],
            'data' => []
        ];
    }
}

public function generarReporte($tipo)
{
    try {
        Log::info("Generando reporte: $tipo");
        
        switch ($tipo) {
            case 'flujo-caja':
                $this->calcularFlujoCaja();
                break;
            case 'estado-resultados':
                $this->calcularEstadoResultados();
                break;
            case 'balance-general':
                $this->calcularBalanceGeneral();
                break;
            case 'cuentas-pendientes':
                $this->calcularCuentasPendientes();
                break;
            case 'categorias':
                $this->calcularGastosPorCategoria();
                break;
            case 'tendencias':
                $this->calcularTendenciasFinancieras();
                break;
            case 'proyecciones':
                // Lógica para proyecciones
                break;
        }
        
        // ✅ IMPORTANTE: Forzar la recarga de todos los datos
        $this->cargarReportes();
        
        // ✅ IMPORTANTE: Emitir evento para actualizar gráficos  
        $this->dispatch('reporte-actualizado');
        
        session()->flash('success', "Reporte '$tipo' generado exitosamente");
        
    } catch (\Exception $e) {
        Log::error("Error generando reporte $tipo: " . $e->getMessage());
        session()->flash('error', "Error al generar el reporte '$tipo'");
    }
}



public function exportarTodo()
{
    session()->flash('info', 'Función de exportación completa en desarrollo');
}

public function actualizarReportes()
{
    $this->cargarReportes();
    
    // ✅ IMPORTANTE: Emitir evento para actualizar gráficos
    $this->dispatch('reporte-actualizado');
    
    session()->flash('success', 'Reportes actualizados correctamente');
}

public function aplicarPeriodoPersonalizado()
{
    if ($this->fecha_personalizada_desde && $this->fecha_personalizada_hasta) {
        $this->fechaInicio = $this->fecha_personalizada_desde;
        $this->fechaFin = $this->fecha_personalizada_hasta;
        $this->cargarReportes();
        
        // ✅ IMPORTANTE: Emitir evento para actualizar gráficos
        $this->dispatch('reporte-actualizado');
        
        session()->flash('success', 'Período personalizado aplicado');
    } else {
        session()->flash('error', 'Debe seleccionar ambas fechas');
    }
}

    public function calcularIndicadoresClave()
{
    try {
        $mesAnterior = now()->subMonth();
        $mesActual = now();

        // Ingresos mes actual vs anterior
        $ingresosMesActual = DB::table('movimientoscontables')
            ->where('tipoMovCont', 'ingreso')
            ->whereMonth('fecMovCont', $mesActual->month)
            ->whereYear('fecMovCont', $mesActual->year)
            ->sum('montoMovCont') ?? 0;

        $ingresosMesAnterior = DB::table('movimientoscontables')
            ->where('tipoMovCont', 'ingreso')
            ->whereMonth('fecMovCont', $mesAnterior->month)
            ->whereYear('fecMovCont', $mesAnterior->year)
            ->sum('montoMovCont') ?? 0;

        // Gastos mes actual vs anterior
        $gastosMesActual = DB::table('movimientoscontables')
            ->where('tipoMovCont', 'egreso')
            ->whereMonth('fecMovCont', $mesActual->month)
            ->whereYear('fecMovCont', $mesActual->year)
            ->sum('montoMovCont') ?? 0;

        $gastosMesAnterior = DB::table('movimientoscontables')
            ->where('tipoMovCont', 'egreso')
            ->whereMonth('fecMovCont', $mesAnterior->month)
            ->whereYear('fecMovCont', $mesAnterior->year)
            ->sum('montoMovCont') ?? 0;

        // Calcular variaciones
        $variacionIngresos = $ingresosMesAnterior > 0 
            ? (($ingresosMesActual - $ingresosMesAnterior) / $ingresosMesAnterior) * 100 
            : 0;

        $variacionGastos = $gastosMesAnterior > 0 
            ? (($gastosMesActual - $gastosMesAnterior) / $gastosMesAnterior) * 100 
            : 0;

        $margenActual = $ingresosMesActual > 0 ? ((($ingresosMesActual - $gastosMesActual) / $ingresosMesActual) * 100) : 0;

        // ✅ CORREGIR: Asignar tanto a indicadoresClave como a estadisticas
        $this->indicadoresClave = [
            'ingresos_mes_actual' => $ingresosMesActual,
            'ingresos_mes_anterior' => $ingresosMesAnterior,
            'variacion_ingresos' => $variacionIngresos,
            'gastos_mes_actual' => $gastosMesActual,
            'gastos_mes_anterior' => $gastosMesAnterior,
            'variacion_gastos' => $variacionGastos,
            'margen_mes_actual' => $margenActual
        ];

        // ✅ AGREGAR: Estadísticas para la vista
        $this->estadisticas = [
            'totalIngresos' => $ingresosMesActual,
            'totalGastos' => $gastosMesActual,
            'balance' => $ingresosMesActual - $gastosMesActual,
            'margenGanancia' => $margenActual,
            'roi' => $ingresosMesActual > 0 ? (($ingresosMesActual - $gastosMesActual) / $gastosMesActual * 100) : 0,
            'liquidez' => $this->balanceGeneral['razon_liquidez'] ?? 'N/A',
            'diasPromedioCobro' => $this->calcularDiasPromedioCobro()
        ];

    } catch (\Exception $e) {
        Log::error('Error calculando indicadores clave: ' . $e->getMessage());
        
        // Valores por defecto
        $this->indicadoresClave = [
            'ingresos_mes_actual' => 0,
            'ingresos_mes_anterior' => 0,
            'variacion_ingresos' => 0,
            'gastos_mes_actual' => 0,
            'gastos_mes_anterior' => 0,
            'variacion_gastos' => 0,
            'margen_mes_actual' => 0
        ];

        $this->estadisticas = [
            'totalIngresos' => 0,
            'totalGastos' => 0,
            'balance' => 0,
            'margenGanancia' => 0,
            'roi' => 0,
            'liquidez' => 'N/A',
            'diasPromedioCobro' => 0
        ];
    }
}

public function calcularDiasPromedioCobro()
{
    try {
        $cuentasPorCobrar = DB::table('cuentaspendientes')
            ->where('tipCuePen', 'por_cobrar')
            ->where('estCuePen', '!=', 'pagado')
            ->get();

        if ($cuentasPorCobrar->isEmpty()) {
            return 0;
        }

        $totalDias = 0;
        $contador = 0;

        foreach ($cuentasPorCobrar as $cuenta) {
            $diasVencimiento = Carbon::parse($cuenta->fecVencimiento)->diffInDays(Carbon::now());
            $totalDias += $diasVencimiento;
            $contador++;
        }

        return $contador > 0 ? round($totalDias / $contador) : 0;

    } catch (\Exception $e) {
        Log::error('Error calculando días promedio de cobro: ' . $e->getMessage());
        return 0;
    }
}

    public function calcularGastosPorCategoria()
{
    try {
        $gastos = DB::table('comprasgastos')
            ->where('tipComGas', 'gasto')
            ->whereBetween('fecComGas', [$this->fechaInicio, $this->fechaFin])
            ->select('catComGas', DB::raw('SUM(monComGas) as total'), DB::raw('COUNT(*) as cantidad'))
            ->groupBy('catComGas')
            ->orderBy('total', 'desc')
            ->get();

        $this->gastosPorCategoria = $gastos;

        // ✅ AGREGAR: Datos para el gráfico
        $this->categorias_data = [
            'labels' => $gastos->pluck('catComGas')->toArray(),
            'data' => $gastos->pluck('total')->toArray()
        ];

    } catch (\Exception $e) {
        Log::error('Error calculando gastos por categoría: ' . $e->getMessage());
        $this->gastosPorCategoria = collect();
        $this->categorias_data = [
            'labels' => [],
            'data' => []
        ];
    }
}

  public function calcularTendenciasFinancieras()
{
    try {
        // Obtener datos de los últimos 12 meses
        $tendencias = [];
        $labels = [];
        $datosIngresos = [];
        $datosGastos = [];

        for ($i = 11; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            
            $ingresos = DB::table('movimientoscontables')
                ->where('tipoMovCont', 'ingreso')
                ->whereMonth('fecMovCont', $fecha->month)
                ->whereYear('fecMovCont', $fecha->year)
                ->sum('montoMovCont') ?? 0;

            $egresos = DB::table('movimientoscontables')
                ->where('tipoMovCont', 'egreso')
                ->whereMonth('fecMovCont', $fecha->month)
                ->whereYear('fecMovCont', $fecha->year)
                ->sum('montoMovCont') ?? 0;

            $mesLabel = $fecha->format('M Y');
            $labels[] = $mesLabel;
            $datosIngresos[] = $ingresos;
            $datosGastos[] = $egresos;

            $tendencias[] = [
                'mes' => $mesLabel,
                'ingresos' => $ingresos,
                'egresos' => $egresos,
                'utilidad' => $ingresos - $egresos
            ];
        }

        $this->tendenciasFinancieras = collect($tendencias);
        
        // ✅ AGREGAR: Datos para el gráfico
        $this->tendencias_data = [
            'labels' => $labels,
            'ingresos' => $datosIngresos,
            'gastos' => $datosGastos
        ];

    } catch (\Exception $e) {
        Log::error('Error calculando tendencias financieras: ' . $e->getMessage());
        $this->tendenciasFinancieras = collect();
        $this->tendencias_data = [
            'labels' => [],
            'ingresos' => [],
            'gastos' => []
        ];
    }
}

    public function actualizarPeriodo()
{
    switch ($this->periodo) {
        case 'hoy':
            $this->fechaInicio = now()->format('Y-m-d');
            $this->fechaFin = now()->format('Y-m-d');
            break;
        case 'semana':
            $this->fechaInicio = now()->startOfWeek()->format('Y-m-d');
            $this->fechaFin = now()->endOfWeek()->format('Y-m-d');
            break;
        case 'mes_actual':
            $this->fechaInicio = now()->startOfMonth()->format('Y-m-d');
            $this->fechaFin = now()->endOfMonth()->format('Y-m-d');
            break;
        case 'mes_anterior':
            $this->fechaInicio = now()->subMonth()->startOfMonth()->format('Y-m-d');
            $this->fechaFin = now()->subMonth()->endOfMonth()->format('Y-m-d');
            break;
        case 'trimestre':
            $this->fechaInicio = now()->startOfQuarter()->format('Y-m-d');
            $this->fechaFin = now()->endOfQuarter()->format('Y-m-d');
            break;
        case 'semestre':
            $this->fechaInicio = now()->month <= 6 
                ? now()->startOfYear()->format('Y-m-d')
                : now()->startOfYear()->addMonths(6)->format('Y-m-d');
            $this->fechaFin = now()->month <= 6 
                ? now()->startOfYear()->addMonths(5)->endOfMonth()->format('Y-m-d')
                : now()->endOfYear()->format('Y-m-d');
            break;
        case 'ano':
            $this->fechaInicio = now()->startOfYear()->format('Y-m-d');
            $this->fechaFin = now()->endOfYear()->format('Y-m-d');
            break;
        case 'personalizado':
            // Las fechas se configuran en aplicarPeriodoPersonalizado()
            return;
    }
    
    $this->cargarReportes();
    
    // ✅ IMPORTANTE: Emitir evento para actualizar gráficos
    $this->dispatch('reporte-actualizado');
}
public function verificarDatos()
{
    try {
        // Verificar movimientos contables
        $totalMovimientos = DB::table('movimientoscontables')->count();
        $ingresos = DB::table('movimientoscontables')->where('tipoMovCont', 'ingreso')->count();
        $egresos = DB::table('movimientoscontables')->where('tipoMovCont', 'egreso')->count();
        
        // Verificar gastos por categoría
        $totalGastos = DB::table('comprasgastos')->count();
        
        // Verificar cuentas pendientes
        $totalCuentas = DB::table('cuentaspendientes')->count();
        
        Log::info("DEBUG REPORTES:");
        Log::info("- Movimientos: $totalMovimientos (Ingresos: $ingresos, Egresos: $egresos)");
        Log::info("- Gastos: $totalGastos");
        Log::info("- Cuentas: $totalCuentas");
        Log::info("- Período: {$this->fechaInicio} - {$this->fechaFin}");
        Log::info("- Tendencias data: ", $this->tendencias_data);
        Log::info("- Categorías data: ", $this->categorias_data);
        
        session()->flash('info', "Datos verificados. Total movimientos: $totalMovimientos. Ver logs para detalles.");
        
    } catch (\Exception $e) {
        Log::error('Error verificando datos: ' . $e->getMessage());
        session()->flash('error', 'Error al verificar datos');
    }
}

}; ?>

@section('title', 'Reportes Contables')

<div class="w-full px-6 py-6 mx-auto">
    <!-- Header -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div class="mb-4 md:mb-0">
                    <nav class="text-sm text-gray-600 mb-2">
                        <a href="{{ route('contabilidad.index') }}" wire:navigate class="hover:text-blue-600">Dashboard</a>
                        <span class="mx-2">/</span>
                        <span class="text-gray-900">Reportes</span>
                    </nav>
                    <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-chart-line mr-3 text-indigo-600"></i> 
                        Reportes Contables
                    </h1>
                    <p class="text-gray-600 mt-1">Análisis detallado y reportes financieros</p>
                </div>
                <div class="flex space-x-3">
                    <button wire:click="exportarTodo" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                        <i class="fas fa-download mr-2"></i> Exportar Todo
                    </button>
                    <button wire:click="actualizarReportes" 
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                        <i class="fas fa-sync mr-2"></i> Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Selector de Período -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
            <div class="bg-white shadow-lg rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Período</label>
                        <select wire:model="periodo" wire:change="actualizarPeriodo" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="mes_actual">Mes Actual</option>
                            <option value="mes_anterior">Mes Anterior</option>
                            <option value="trimestre">Trimestre Actual</option>
                            <option value="semestre">Semestre Actual</option>
                            <option value="ano">Año Actual</option>
                            <option value="personalizado">Período Personalizado</option>
                        </select>
                    </div>
                    @if($periodo === 'personalizado')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Desde</label>
                        <input type="date" wire:model="fecha_personalizada_desde" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hasta</label>
                        <input type="date" wire:model="fecha_personalizada_hasta" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="flex items-end">
                        <button wire:click="aplicarPeriodoPersonalizado" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200">
                            <i class="fas fa-sync mr-2"></i> Aplicar
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Reportes Rápidos -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full md:w-1/4 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg hover:shadow-xl transition duration-300 cursor-pointer" wire:click="generarReporte('flujo-caja')">
                <div class="p-6 text-center">
                    <div class="bg-blue-100 p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-chart-line text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Flujo de Caja</h3>
                    <p class="text-sm text-gray-600 mb-4">Análisis de ingresos y egresos</p>
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                        <i class="fas fa-download mr-1"></i> Generar
                    </button>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg hover:shadow-xl transition duration-300 cursor-pointer" wire:click="generarReporte('estado-resultados')">
                <div class="p-6 text-center">
                    <div class="bg-green-100 p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-chart-pie text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Estado de Resultados</h3>
                    <p class="text-sm text-gray-600 mb-4">Ganancias y pérdidas del período</p>
                    <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                        <i class="fas fa-download mr-1"></i> Generar
                    </button>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg hover:shadow-xl transition duration-300 cursor-pointer" wire:click="generarReporte('balance-general')">
                <div class="p-6 text-center">
                    <div class="bg-purple-100 p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-balance-scale text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Balance General</h3>
                    <p class="text-sm text-gray-600 mb-4">Activos, pasivos y patrimonio</p>
                    <button class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                        <i class="fas fa-download mr-1"></i> Generar
                    </button>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg hover:shadow-xl transition duration-300 cursor-pointer" wire:click="generarReporte('cuentas-pendientes')">
                <div class="p-6 text-center">
                    <div class="bg-yellow-100 p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Cuentas Pendientes</h3>
                    <p class="text-sm text-gray-600 mb-4">Por cobrar y por pagar</p>
                    <button class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                        <i class="fas fa-download mr-1"></i> Generar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos de Análisis -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <!-- Gráfico de Tendencias -->
        <div class="w-full xl:w-2/3 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h6 class="text-lg font-semibold text-gray-800">Tendencias Financieras</h6>
                            <p class="text-sm text-gray-600">Evolución de ingresos y gastos</p>
                        </div>
                        <div class="flex space-x-2">
                            <select class="border border-gray-300 rounded-lg px-3 py-1 text-sm">
                                <option>Últimos 12 meses</option>
                                <option>Últimos 6 meses</option>
                                <option>Últimos 3 meses</option>
                            </select>
                            <button wire:click="generarReporte('tendencias')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded-lg text-sm transition duration-200">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="relative h-80">
                        <canvas id="tendenciasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPIs Principales -->
        <div class="w-full xl:w-1/3 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h6 class="text-lg font-semibold text-gray-800">Indicadores Clave</h6>
                    <p class="text-sm text-gray-600">KPIs del período seleccionado</p>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-blue-600">Margen de Ganancia</p>
                            <p class="text-2xl font-bold text-blue-800">{{ $estadisticas['margenGanancia'] ?? 0 }}%</p>
                        </div>
                        <div class="bg-blue-200 p-2 rounded-full">
                            <i class="fas fa-percentage text-blue-600"></i>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-green-600">ROI</p>
                            <p class="text-2xl font-bold text-green-800">{{ $estadisticas['roi'] ?? 0 }}%</p>
                        </div>
                        <div class="bg-green-200 p-2 rounded-full">
                            <i class="fas fa-arrow-up text-green-600"></i>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-yellow-600">Liquidez</p>
                            <p class="text-2xl font-bold text-yellow-800">{{ $estadisticas['liquidez'] ?? 'N/A' }}</p>
                        </div>
                        <div class="bg-yellow-200 p-2 rounded-full">
                            <i class="fas fa-tint text-yellow-600"></i>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-red-600">Días Promedio Cobro</p>
                            <p class="text-2xl font-bold text-red-800">{{ $estadisticas['diasPromedioCobro'] ?? 0 }}</p>
                        </div>
                        <div class="bg-red-200 p-2 rounded-full">
                            <i class="fas fa-calendar text-red-600"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reportes Detallados -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h6 class="text-lg font-semibold text-gray-800">Reportes Detallados</h6>
                            <p class="text-sm text-gray-600">Análisis profundo por categorías</p>
                        </div>
                        <div class="flex space-x-2">
                            <button wire:click="exportarTodo" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                                <i class="fas fa-file-excel mr-1"></i> Exportar Todo
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Reporte por Categorías -->
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition duration-200">
                            <div class="flex items-center mb-3">
                                <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-tags text-blue-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-800">Por Categorías</h4>
                                    <p class="text-sm text-gray-600">Gastos desglosados</p>
                                </div>
                            </div>
                            <div class="space-y-2 mb-4">
                                @if(count($categorias_data['labels'] ?? []) > 0)
                                    @foreach($categorias_data['labels'] as $index => $categoria)
                                    <div class="flex justify-between text-sm">
                                        <span>{{ $categoria }}:</span>
                                        <span class="font-medium">${{ number_format($categorias_data['data'][$index] ?? 0, 2) }}</span>
                                    </div>
                                    @endforeach
                                @else
                                <div class="flex justify-between text-sm">
                                    <span>Sin datos:</span>
                                    <span class="font-medium">$0.00</span>
                                </div>
                                @endif
                            </div>
                            <button wire:click="generarReporte('categorias')" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm transition duration-200">
                                <i class="fas fa-download mr-1"></i> Generar Reporte
                            </button>
                        </div>

                        <!-- Reporte de Flujo de Caja -->
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition duration-200">
                            <div class="flex items-center mb-3">
                                <div class="bg-green-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-chart-line text-green-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-800">Flujo de Caja</h4>
                                    <p class="text-sm text-gray-600">Análisis de liquidez</p>
                                </div>
                            </div>
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-sm">
                                    <span>Total Ingresos:</span>
                                    <span class="font-medium">${{ number_format($estadisticas['totalIngresos'] ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Total Gastos:</span>
                                    <span class="font-medium">${{ number_format($estadisticas['totalGastos'] ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Balance:</span>
                                    <span class="font-medium {{ ($estadisticas['balance'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        ${{ number_format($estadisticas['balance'] ?? 0, 2) }}
                                    </span>
                                </div>
                            </div>
                            <button wire:click="generarReporte('flujo-caja')" class="w-full bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm transition duration-200">
                                <i class="fas fa-download mr-1"></i> Generar Reporte
                            </button>
                        </div>

                        <!-- Reporte de Proyecciones -->
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition duration-200">
                            <div class="flex items-center mb-3">
                                <div class="bg-purple-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-crystal-ball text-purple-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-800">Proyecciones</h4>
                                    <p class="text-sm text-gray-600">Análisis predictivo</p>
                                </div>
                            </div>
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-sm">
                                    <span>Próximo Mes:</span>
                                    <span class="font-medium">${{ number_format(($estadisticas['totalIngresos'] ?? 0) * 1.05, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Tendencia:</span>
                                    <span class="font-medium {{ ($estadisticas['balance'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ ($estadisticas['balance'] ?? 0) >= 0 ? 'Positiva' : 'Negativa' }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Confianza:</span>
                                    <span class="font-medium">{{ ($estadisticas['totalIngresos'] ?? 0) > 0 ? 'Alta' : 'Baja' }}</span>
                                </div>
                            </div>
                            <button wire:click="generarReporte('proyecciones')" class="w-full bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded text-sm transition duration-200">
                                <i class="fas fa-download mr-1"></i> Generar Reporte
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos Adicionales -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <!-- Gráfico de Categorías -->
        <div class="w-full md:w-1/2 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h6 class="text-lg font-semibold text-gray-800">Gastos por Categoría</h6>
                    <p class="text-sm text-gray-600">Distribución porcentual</p>
                </div>
                <div class="p-6">
                    <div class="relative h-64">
                        <canvas id="categoriasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Cuentas Pendientes -->
        <div class="w-full md:w-1/2 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h6 class="text-lg font-semibold text-gray-800">Cuentas Pendientes</h6>
                    <p class="text-sm text-gray-600">Estado actual</p>
                </div>
                <div class="p-6">
                    <div class="relative h-64">
                        <canvas id="cuentasPendientesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de Reportes -->
    <div class="flex flex-wrap -mx-3">
        <div class="w-full px-3">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h6 class="text-lg font-semibold text-gray-800">Historial de Reportes</h6>
                            <p class="text-sm text-gray-600">Reportes generados recientemente</p>
                        </div>
                        <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                            <i class="fas fa-trash mr-1"></i> Limpiar Historial
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reporte</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Período</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Generación</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    <div class="py-8">
                                        <i class="fas fa-chart-line text-4xl text-gray-300 mb-4"></i>
                                        <p class="text-lg font-medium mb-2">No hay reportes generados</p>
                                        <p class="text-sm text-gray-400 mb-4">Los reportes que generes aparecerán aquí</p>
                                        <button wire:click="generarReporte('flujo-caja')" 
                                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition duration-200">
                                            <i class="fas fa-plus mr-2"></i> Generar Primer Reporte
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

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

// REEMPLAZAR TODO EL SCRIPT ACTUAL POR ESTE:
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Variables globales para los gráficos
let tendenciasChart = null;
let categoriasChart = null;
let cuentasPendientesChart = null;

// Función para obtener datos actualizados desde PHP
function obtenerDatos() {
    return {
        tendencias: @json($tendencias_data ?? ['labels' => [], 'ingresos' => [], 'gastos' => []]),
        categorias: @json($categorias_data ?? ['labels' => [], 'data' => []]),
        cuentasPendientes: @json($cuentas_pendientes_data ?? ['labels' => [], 'data' => []])
    };
}

// Función para inicializar todos los gráficos
function inicializarGraficos() {
    console.log('🔄 Inicializando gráficos...');
    
    // ✅ IMPORTANTE: Esperar un poco para que el DOM esté listo
    setTimeout(() => {
        const datos = obtenerDatos();
        console.log('📊 Datos obtenidos:', datos);
        
        inicializarTendenciasChart(datos.tendencias);
        inicializarCategoriasChart(datos.categorias);
        inicializarCuentasPendientesChart(datos.cuentasPendientes);
    }, 100);
}

// Gráfico de Tendencias
function inicializarTendenciasChart(data) {
    const canvas = document.getElementById('tendenciasChart');
    if (!canvas) {
        console.warn('⚠️ Canvas tendenciasChart no encontrado');
        return;
    }
    
    const ctx = canvas.getContext('2d');
    
    // Destruir gráfico anterior si existe
    if (tendenciasChart) {
        tendenciasChart.destroy();
    }
    
    console.log('📈 Creando gráfico de tendencias con:', data);
    
    tendenciasChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels || [],
            datasets: [{
                label: 'Ingresos',
                data: data.ingresos || [],
                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                borderColor: '#10b981',
                borderWidth: 1
            }, {
                label: 'Gastos',
                data: data.gastos || [],
                backgroundColor: 'rgba(239, 68, 68, 0.8)',
                borderColor: '#ef4444',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
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
            }
        }
    });
}

// Gráfico de Categorías
function inicializarCategoriasChart(data) {
    const canvas = document.getElementById('categoriasChart');
    if (!canvas) {
        console.warn('⚠️ Canvas categoriasChart no encontrado');
        return;
    }
    
    const ctx = canvas.getContext('2d');
    
    // Destruir gráfico anterior si existe
    if (categoriasChart) {
        categoriasChart.destroy();
    }
    
    console.log('🥧 Creando gráfico de categorías con:', data);
    
    categoriasChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels || [],
            datasets: [{
                data: data.data || [],
                backgroundColor: [
                    '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#6b7280'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
}

// Gráfico de Cuentas Pendientes
function inicializarCuentasPendientesChart(data) {
    const canvas = document.getElementById('cuentasPendientesChart');
    if (!canvas) {
        console.warn('⚠️ Canvas cuentasPendientesChart no encontrado');
        return;
    }
    
    const ctx = canvas.getContext('2d');
    
    // Destruir gráfico anterior si existe
    if (cuentasPendientesChart) {
        cuentasPendientesChart.destroy();
    }
    
    console.log('⏰ Creando gráfico de cuentas pendientes con:', data);
    
    cuentasPendientesChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels || [],
            datasets: [{
                data: data.data || [],
                backgroundColor: ['#27ae60', '#f39c12', '#e74c3c'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
}

// ✅ SOLUCIÓN PRINCIPAL: Función para actualizar gráficos después de cambios de Livewire
function actualizarGraficos() {
    console.log('🔄 Actualizando gráficos después del cambio de Livewire...');
    
    // Esperar un poco más para que Livewire termine de actualizar el DOM
    setTimeout(() => {
        inicializarGraficos();
    }, 200);
}

// Auto-cerrar notificaciones
setTimeout(function() {
    const notifications = document.querySelectorAll('.fixed.top-4.right-4');
    notifications.forEach(notification => {
        notification.style.display = 'none';
    });
}, 5000);

// ✅ EVENTOS DE LIVEWIRE - CRUCIAL PARA QUE FUNCIONE
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 DOM cargado, inicializando gráficos...');
    inicializarGraficos();
});

// ✅ EVENTOS ESPECÍFICOS DE LIVEWIRE
document.addEventListener('livewire:init', () => {
    console.log('⚡ Livewire inicializado');
    
    // Escuchar eventos personalizados
    Livewire.on('reporte-actualizado', () => {
        console.log('📊 Evento reporte-actualizado recibido');
        actualizarGraficos();
    });
});

// ✅ IMPORTANTE: Actualizar después de cualquier actualización de Livewire
document.addEventListener('livewire:navigated', () => {
    console.log('🔄 Livewire navegated - reinicializando gráficos');
    setTimeout(inicializarGraficos, 300);
});

// ✅ CRUCIAL: Actualizar después de updates de Livewire
document.addEventListener('livewire:update', () => {
    console.log('🔄 Livewire updated - reinicializando gráficos');
    setTimeout(inicializarGraficos, 100);
});
</script>