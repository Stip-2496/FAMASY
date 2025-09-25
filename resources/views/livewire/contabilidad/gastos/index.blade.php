
 <?php
// resources/views/livewire/contabilidad/gastos/index.blade.php - CORREGIDO

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;

    // Propiedades para filtros
    public $categoria = '';
    public $metodo_pago = '';
    public $proveedor_buscar = '';
    public $fecha = '';
    public $fecha_inicio = '';
    public $fecha_fin = '';
    public $monto_min = '';
    public $monto_max = '';
    public $per_page = 15;

    // Propiedades para el modal de nuevo gasto
    public $modalAbierto = false;
    public $gastoEditando = null;
    public $categoriaGasto = '';
    public $descripcion = '';
    public $montoGasto = '';
    public $fechaGasto = '';
    public $metodoPago = '';
    public $proveedorGasto = '';
    public $idProveedorSeleccionado = null;
    public $documento = '';
    public $observaciones = '';
    public $esPago = false;
    public $fechaVencimiento = '';
    public $facturaProveedor = '';

    // Propiedades para análisis completos
    public $resumenFinanciero = [];
    public $topProveedores = [];
    public $gastosRecurrentes = [];
    public $comparativoMensual = [];
    public $alertasVencimientos = [];
    public $auditoria = [];
    public $relacionesAnimales = [];
    public $inventarioAfectado = [];




    // Propiedades adicionales para integraciones
    public $estadisticas = [];
    public $proveedores = [];
    public $categorias = [];
    public $alertasStock = [];
    public $modalDetalleAbierto = false;
    public $gastoDetalle = null;

  public function mount()
{
    $this->fechaGasto = date('Y-m-d');
    $this->fechaVencimiento = date('Y-m-d', strtotime('+30 days'));
    
    // NO establecer fechas de filtro por defecto - dejar vacío
    $this->fecha_inicio = '';
    $this->fecha_fin = '';

    // Inicializar arrays
    $this->estadisticas = [];
    $this->resumenFinanciero = [];
    $this->topProveedores = collect();
    $this->gastosRecurrentes = collect();
    $this->alertasVencimientos = collect();

    try {
        $this->cargarDatosIniciales();
        $this->verificarAlertasStock();
        // Calcular estadísticas (estas sí usarán rango inteligente)
        $this->calcularEstadisticasCompletas();
        
        Log::info('Mount completado - Tabla sin filtros, estadísticas con rango inteligente');
        
    } catch (\Exception $e) {
        Log::error('Error en mount: ' . $e->getMessage());
        session()->flash('warning', 'Algunos datos pueden no estar disponibles temporalmente.');
    }
}

    private function cargarDatosIniciales()
    {
        $this->cargarProveedores();
        $this->cargarCategoriasContables();
    }

    private function cargarProveedores()
    {
        try {
            $proveedoresRaw = DB::table('proveedores')
                ->select('idProve', 'nomProve', 'nitProve', 'tipSumProve', 'dirProve', 'telProve', 'emailProve')
                // NO USAR WHERE estProve = 1 porque no existe ese campo
                ->orderBy('nomProve')
                ->get();

            $this->proveedores = $proveedoresRaw->map(function ($proveedor) {
                return (object)[
                    'idProve' => (int) $proveedor->idProve,
                    'nomProve' => (string) ($proveedor->nomProve ?? ''),
                    'nitProve' => (string) ($proveedor->nitProve ?? ''),
                    'tipSumProve' => (string) ($proveedor->tipSumProve ?? ''),
                    'dirProve' => (string) ($proveedor->dirProve ?? ''),
                    'telProve' => (string) ($proveedor->telProve ?? ''),
                    'emailProve' => (string) ($proveedor->emailProve ?? '')
                ];
            });

            Log::info('Proveedores cargados:', ['count' => $this->proveedores->count()]);
        } catch (\Exception $e) {
            Log::error('Error cargando proveedores: ' . $e->getMessage());
            $this->proveedores = collect();
        }
    }



    // ¿Tienes este método agregado?
    public function seleccionarProveedor($proveedorId)
    {
        try {
            $proveedor = $this->proveedores->firstWhere('idProve', $proveedorId);

            if ($proveedor) {
                $this->idProveedorSeleccionado = $proveedor->idProve;
                $this->proveedorGasto = $proveedor->nomProve;

                session()->flash('info', "Proveedor seleccionado: {$proveedor->nomProve}");

                Log::info('Proveedor seleccionado:', [
                    'id' => $proveedor->idProve,
                    'nombre' => $proveedor->nomProve
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error al seleccionar proveedor: ' . $e->getMessage());
        }
    }


    // Listeners para recalcular automáticamente
public function updatedCategoria()
{
    $this->resetPage();
    $this->calcularEstadisticasCompletas();
    Log::info("Filtro categoría cambiado a: {$this->categoria}");
}

public function updatedMetodoPago()
{
    $this->resetPage();
    $this->calcularEstadisticasCompletas();
    Log::info("Filtro método pago cambiado a: {$this->metodo_pago}");
}

public function updatedProveedorBuscar()
{
    $this->resetPage();
    $this->calcularEstadisticasCompletas();
    Log::info("Filtro proveedor cambiado a: {$this->proveedor_buscar}");
}

public function updatedFechaInicio()
{
    $this->resetPage();
    $this->calcularEstadisticasCompletas();
    Log::info("Fecha inicio cambiada a: {$this->fecha_inicio}");
}

public function updatedFechaFin()
{
    $this->resetPage();
    $this->calcularEstadisticasCompletas();
    Log::info("Fecha fin cambiada a: {$this->fecha_fin}");
}

    private function cargarCategoriasContables()
    {
        try {
            // Cargar categorías oficiales
            $categoriasOficiales = DB::table('categoriascontables')
                ->where('tipCatCont', 'egreso')
                ->where('estCatCont', 1)
                ->pluck('nomCatCont')
                ->filter()
                ->map(function ($cat) {
                    return (string) $cat;
                });

            // Cargar categorías ya utilizadas
            $categoriasUsadas = DB::table('comprasgastos')
                ->where('tipComGas', 'gasto')
                ->whereNotNull('catComGas')
                ->where('catComGas', '!=', '')
                ->distinct()
                ->pluck('catComGas')
                ->filter()
                ->map(function ($cat) {
                    return (string) $cat;
                });

            // Combinar y filtrar
            $this->categorias = $categoriasOficiales
                ->merge($categoriasUsadas)
                ->filter(function ($categoria) {
                    return !empty($categoria) && is_string($categoria);
                })
                ->unique()
                ->sort()
                ->values();
        } catch (\Exception $e) {
            Log::error('Error cargando categorías: ' . $e->getMessage());
            $this->categorias = collect(['Servicios Públicos', 'Mantenimiento', 'Otros']);
        }
    }

    private function verificarAlertasStock()
    {
        try {
            $alertas = DB::table('v_alertas_stock_bajo')
                ->where('nivel_alerta', 'critico')
                ->limit(5)
                ->get();

            $this->alertasStock = $alertas->map(function ($alerta) {
                return (object)[
                    'nombre_item' => (string) ($alerta->nombre_item ?? ''),
                    'stockActual' => (float) ($alerta->stockActual ?? 0),
                    'tipo_item' => (string) ($alerta->tipo_item ?? '')
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error verificando alertas de stock: ' . $e->getMessage());
            $this->alertasStock = collect();
        }
    }

private function calcularEstadisticasCompletas()
{
    try {
        Log::info('=== CALCULANDO ESTADÍSTICAS (NO AFECTA TABLA) ===');

        // Para estadísticas: usar filtros o rango inteligente
        $fechaInicioStats = $this->fecha_inicio;
        $fechaFinStats = $this->fecha_fin;

        // Si no hay filtros de fecha, usar rango inteligente para estadísticas
        if (empty($fechaInicioStats) && empty($fechaFinStats)) {
            $rangoConDatos = DB::table('comprasgastos')
                ->where('tipComGas', 'gasto')
                ->selectRaw('MIN(fecComGas) as fecha_min')
                ->first();

            $fechaInicioStats = $rangoConDatos->fecha_min ?? date('Y-01-01');
            $fechaFinStats = date('Y-m-d');
            
            Log::info("Estadísticas sin filtros - usando rango: {$fechaInicioStats} a {$fechaFinStats}");
        } else {
            $fechaInicioStats = $fechaInicioStats ?: date('Y-01-01');
            $fechaFinStats = $fechaFinStats ?: date('Y-m-d');
            
            Log::info("Estadísticas con filtros - fechas: {$fechaInicioStats} a {$fechaFinStats}");
        }

        // Query para estadísticas (puede usar filtros)
        $queryStats = DB::table('comprasgastos as cg')
            ->leftJoin('proveedores as p', 'cg.idProve', '=', 'p.idProve')
            ->where('cg.tipComGas', 'gasto')
            ->whereBetween('cg.fecComGas', [$fechaInicioStats, $fechaFinStats]);

        // Aplicar filtros a estadísticas si están definidos
        if (!empty($this->categoria)) {
            $queryStats->where('cg.catComGas', 'LIKE', "%{$this->categoria}%");
        }

        if (!empty($this->metodo_pago)) {
            $queryStats->where('cg.metPagComGas', $this->metodo_pago);
        }

        if (!empty($this->proveedor_buscar)) {
            $queryStats->where(function ($q) {
                $q->where('cg.provComGas', 'LIKE', "%{$this->proveedor_buscar}%")
                  ->orWhere('p.nomProve', 'LIKE', "%{$this->proveedor_buscar}%");
            });
        }

        // Calcular estadísticas
        $gastosPeriodo = (clone $queryStats)->sum('cg.monComGas') ?? 0;
        $transaccionesPeriodo = (clone $queryStats)->count() ?? 0;

        $diasPeriodo = max(1, Carbon::parse($fechaInicioStats)->diffInDays(Carbon::parse($fechaFinStats)) + 1);
        $promedioDiario = $gastosPeriodo > 0 ? $gastosPeriodo / $diasPeriodo : 0;

        $categoriasActivas = (clone $queryStats)
            ->whereNotNull('cg.catComGas')
            ->where('cg.catComGas', '!=', '')
            ->distinct()
            ->count('cg.catComGas') ?? 0;

        $cuentasPorPagar = DB::table('cuentaspendientes')
            ->where('tipCuePen', 'por_pagar')
            ->whereIn('estCuePen', ['pendiente', 'vencido'])
            ->sum('montoSaldo') ?? 0;

        $this->estadisticas = [
            'gastos_periodo' => (float) $gastosPeriodo,
            'transacciones_periodo' => (int) $transaccionesPeriodo,
            'promedio_diario' => round($promedioDiario, 2),
            'categorias_activas' => (int) $categoriasActivas,
            'cuentas_por_pagar' => (float) $cuentasPorPagar,
            'periodo_inicio' => $fechaInicioStats,
            'periodo_fin' => $fechaFinStats
        ];

        Log::info('Estadísticas calculadas:', $this->estadisticas);

    } catch (\Exception $e) {
        Log::error('Error calculando estadísticas: ' . $e->getMessage());
        
        $this->estadisticas = [
            'gastos_periodo' => 0,
            'transacciones_periodo' => 0,
            'promedio_diario' => 0,
            'categorias_activas' => 0,
            'cuentas_por_pagar' => 0,
            'periodo_inicio' => date('Y-01-01'),
            'periodo_fin' => date('Y-m-d')
        ];
    }
}

private function cargarDatosAdicionales($fechaInicio, $fechaFin)
{
    try {
        // RESUMEN FINANCIERO
        $totalIngresos = (float) DB::table('movimientoscontables')
            ->where('tipoMovCont', 'ingreso')
            ->whereBetween('fecMovCont', [$fechaInicio, $fechaFin])
            ->sum('montoMovCont') ?? 0;

        $totalEgresos = (float) DB::table('movimientoscontables')
            ->where('tipoMovCont', 'egreso')
            ->whereBetween('fecMovCont', [$fechaInicio, $fechaFin])
            ->sum('montoMovCont') ?? 0;

        $this->resumenFinanciero = [
            'total_ingresos' => $totalIngresos,
            'total_egresos' => $totalEgresos,
            'utilidad_neta' => $totalIngresos - $totalEgresos,
            'cuentas_por_pagar' => $this->estadisticas['cuentas_por_pagar'],
            'cuentas_por_cobrar' => (float) DB::table('cuentaspendientes')
                ->where('tipCuePen', 'por_cobrar')
                ->whereIn('estCuePen', ['pendiente', 'vencido'])
                ->sum('montoSaldo') ?? 0
        ];

        // TOP PROVEEDORES
        $this->topProveedores = DB::table('comprasgastos as cg')
            ->leftJoin('proveedores as p', 'cg.idProve', '=', 'p.idProve')
            ->select(
                DB::raw('COALESCE(p.nomProve, cg.provComGas) as nombre_proveedor'),
                DB::raw('COUNT(cg.idComGas) as total_transacciones'),
                DB::raw('SUM(cg.monComGas) as total_gastado'),
                DB::raw('AVG(cg.monComGas) as promedio_gasto')
            )
            ->where('cg.tipComGas', 'gasto')
            ->whereBetween('cg.fecComGas', [$fechaInicio, $fechaFin])
            ->groupBy('nombre_proveedor')
            ->orderBy('total_gastado', 'desc')
            ->limit(5)
            ->get();

        // GASTOS RECURRENTES
        $this->gastosRecurrentes = DB::table('comprasgastos')
            ->select(
                'catComGas',
                DB::raw('COUNT(*) as frecuencia'),
                DB::raw('SUM(monComGas) as total'),
                DB::raw('AVG(monComGas) as promedio')
            )
            ->where('tipComGas', 'gasto')
            ->whereBetween('fecComGas', [$fechaInicio, $fechaFin])
            ->whereNotNull('catComGas')
            ->where('catComGas', '!=', '')
            ->groupBy('catComGas')
            ->having('frecuencia', '>=', 2)
            ->orderBy('frecuencia', 'desc')
            ->get();

        // ALERTAS DE VENCIMIENTOS
        $this->alertasVencimientos = DB::table('v_proximos_vencer')
            ->where('dias_para_vencer', '<=', 30)
            ->where('dias_para_vencer', '>=', 0)
            ->orderBy('dias_para_vencer')
            ->limit(5)
            ->get();

    } catch (\Exception $e) {
        Log::error('Error cargando datos adicionales: ' . $e->getMessage());
        
        $this->resumenFinanciero = [];
        $this->topProveedores = collect();
        $this->gastosRecurrentes = collect();
        $this->alertasVencimientos = collect();
    }
}

private function cargarAuditoria()
{
    try {
        $this->auditoria = DB::table('auditoria as a')
            ->leftJoin('users as u', 'a.idUsuAud', '=', 'u.id')
            ->select('a.*', 'u.nomUsu', 'u.apeUsu')
            ->where('a.tablaAud', 'comprasgastos')
            ->orWhere('a.desAud', 'LIKE', '%gasto%')
            ->orderBy('a.fecAud', 'desc')
            ->limit(10)
            ->get();
    } catch (\Exception $e) {
        Log::error('Error cargando auditoría: ' . $e->getMessage());
        $this->auditoria = collect();
    }
}
    
    // Corregir la función que causaba el error
public function getGastosProperty()
{
    try {
        $query = DB::table('comprasgastos as cg')
            ->leftJoin('proveedores as p', 'cg.idProve', '=', 'p.idProve')
            ->select(
                'cg.idComGas', 'cg.tipComGas', 'cg.catComGas', 'cg.desComGas', 'cg.monComGas',
                'cg.fecComGas', 'cg.metPagComGas', 'cg.provComGas', 'cg.docComGas', 'cg.obsComGas',
                'cg.idProve', 'cg.created_at', 'cg.updated_at',
                'p.nomProve', 'p.nitProve', 'p.tipSumProve'
            )
            ->where('cg.tipComGas', 'gasto');

        // SOLO aplicar filtros si realmente están definidos por el usuario
        if (!empty($this->categoria)) {
            $query->where('cg.catComGas', 'LIKE', "%{$this->categoria}%");
        }

        if (!empty($this->metodo_pago)) {
            $query->where('cg.metPagComGas', $this->metodo_pago);
        }

        if (!empty($this->proveedor_buscar)) {
            $query->where(function ($q) {
                $q->where('cg.provComGas', 'LIKE', "%{$this->proveedor_buscar}%")
                  ->orWhere('p.nomProve', 'LIKE', "%{$this->proveedor_buscar}%");
            });
        }

        // SOLO aplicar filtro de fecha si el usuario los definió explícitamente
        if (!empty($this->fecha_inicio)) {
            $query->where('cg.fecComGas', '>=', $this->fecha_inicio);
        }

        if (!empty($this->fecha_fin)) {
            $query->where('cg.fecComGas', '<=', $this->fecha_fin);
        }

        return $query->orderBy('cg.fecComGas', 'desc')
            ->orderBy('cg.created_at', 'desc')
            ->paginate($this->per_page);
            
    } catch (\Exception $e) {
        Log::error('Error al obtener gastos: ' . $e->getMessage());
        
        return DB::table('comprasgastos')
            ->where('tipComGas', 'gasto')
            ->orderBy('fecComGas', 'desc')
            ->paginate($this->per_page);
    }
}

    public function guardarGasto()
{
    // Validar datos
    $this->validate([
        'categoriaGasto' => 'required|string|max:100',
        'descripcion' => 'required|string|max:500',
        'montoGasto' => 'required|numeric|min:0.01|max:999999.99',
        'fechaGasto' => 'required|date|before_or_equal:today',
        'metodoPago' => 'required|in:efectivo,transferencia,cheque,tarjeta_credito,tarjeta_debito',
        'proveedorGasto' => 'required|string|max:100',
    ]);

    try {
        DB::beginTransaction();

        Log::info('Iniciando guardado de gasto:', [
            'categoria' => $this->categoriaGasto,
            'descripcion' => $this->descripcion,
            'monto' => $this->montoGasto,
            'fecha' => $this->fechaGasto,
            'metodo' => $this->metodoPago,
            'proveedor' => $this->proveedorGasto
        ]);

        // Convertir monto a float
        $montoNumerico = (float) $this->montoGasto;

        // Verificar o crear proveedor
        $proveedorId = $this->verificarOCrearProveedor();

        // Insertar gasto
        $gastoId = DB::table('comprasgastos')->insertGetId([
            'tipComGas' => 'gasto',
            'catComGas' => $this->categoriaGasto,
            'desComGas' => $this->descripcion,
            'monComGas' => $montoNumerico,
            'fecComGas' => $this->fechaGasto,
            'metPagComGas' => $this->metodoPago,
            'provComGas' => $this->proveedorGasto,
            'idProve' => $proveedorId,
            'docComGas' => $this->documento ?: null,
            'obsComGas' => $this->observaciones ?: null,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        Log::info('Gasto insertado con ID:', ['gastoId' => $gastoId]);

        // Crear movimiento contable
        DB::table('movimientoscontables')->insert([
            'fecMovCont' => $this->fechaGasto,
            'tipoMovCont' => 'egreso',
            'catMovCont' => $this->categoriaGasto,
            'conceptoMovCont' => "Gasto #{$gastoId} - {$this->descripcion}",
            'montoMovCont' => $montoNumerico,
            'idComGasMovCont' => $gastoId,
            'obsMovCont' => "Gasto registrado. Método: {$this->metodoPago}",
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::commit();

        // Limpiar formulario
        $this->limpiarFormulario();
        
        // Cerrar modal
        $this->modalAbierto = false;
        
        // Recalcular estadísticas
        $this->calcularEstadisticasCompletas();
        
        // Resetear paginación para ver el nuevo registro
        $this->resetPage();

        // Mensaje de éxito
        session()->flash('success', "¡Gasto registrado exitosamente! ID: #{$gastoId} por $" . number_format($montoNumerico, 2));

        Log::info('Gasto guardado exitosamente');

    } catch (\Exception $e) {
        DB::rollback();
        Log::error('Error al guardar gasto: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        session()->flash('error', 'Error al registrar el gasto: ' . $e->getMessage());
    }
}

private function limpiarFormulario()
{
    $this->categoriaGasto = '';
    $this->descripcion = '';
    $this->montoGasto = '';
    $this->fechaGasto = date('Y-m-d'); // Mantener fecha actual
    $this->metodoPago = '';
    $this->proveedorGasto = '';
    $this->documento = '';
    $this->observaciones = '';
    $this->idProveedorSeleccionado = null;
    $this->gastoEditando = null;

    Log::info('Formulario limpiado');
}

    public function hayFiltrosActivos()
    {
        return !empty($this->categoria) ||
            !empty($this->metodo_pago) ||
            !empty($this->proveedor_buscar) ||
            !empty($this->fecha_inicio) ||
            !empty($this->fecha_fin);
    }

    private function verificarOCrearProveedor()
{
    try {
        // Si ya hay un proveedor seleccionado, usarlo
        if ($this->idProveedorSeleccionado) {
            Log::info('Usando proveedor seleccionado:', ['id' => $this->idProveedorSeleccionado]);
            return $this->idProveedorSeleccionado;
        }

        // Buscar proveedor existente por nombre
        $proveedorExistente = DB::table('proveedores')
            ->where('nomProve', $this->proveedorGasto)
            ->first();

        if ($proveedorExistente) {
            Log::info('Proveedor encontrado:', ['id' => $proveedorExistente->idProve]);
            return $proveedorExistente->idProve;
        }

        // Crear nuevo proveedor
        $nuevoProveedorId = DB::table('proveedores')->insertGetId([
            'nomProve' => $this->proveedorGasto,
            'tipSumProve' => $this->categoriaGasto,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        Log::info('Nuevo proveedor creado:', [
            'id' => $nuevoProveedorId,
            'nombre' => $this->proveedorGasto
        ]);

        // Recargar lista de proveedores
        $this->cargarProveedores();

        return $nuevoProveedorId;

    } catch (\Exception $e) {
        Log::error('Error verificando/creando proveedor: ' . $e->getMessage());
        throw $e;
    }
}
    public function aplicarFiltrosAvanzados()
{
    $this->resetPage();
    $this->calcularEstadisticasCompletas();
    
    Log::info('Filtros aplicados, estadísticas recalculadas');
}

public function hayFiltrosRealmenteActivos()
{
    return !empty($this->categoria) ||
           !empty($this->metodo_pago) ||
           !empty($this->proveedor_buscar) ||
           !empty($this->fecha_inicio) ||
           !empty($this->fecha_fin);
}

public function limpiarFiltros()
{
    Log::info('Limpiando filtros...');
    
    // Limpiar todos los filtros
    $this->categoria = '';
    $this->metodo_pago = '';
    $this->proveedor_buscar = '';
    $this->fecha_inicio = '';
    $this->fecha_fin = '';

    $this->resetPage();
    
    // Solo recalcular estadísticas (la tabla se actualizará automáticamente)
    $this->calcularEstadisticasCompletas();
    
    session()->flash('info', 'Filtros eliminados. Mostrando todos los gastos.');
}

    public function abrirModal()
{
    $this->modalAbierto = true;
    $this->limpiarFormulario(); // Limpiar al abrir
    
    Log::info('Modal abierto para nuevo gasto');
}

    private function resetearCamposModal()
    {
        $this->reset([
            'categoriaGasto',
            'descripcion',
            'montoGasto',
            'metodoPago',
            'proveedorGasto',
            'documento',
            'observaciones',
            'facturaProveedor',
            'esPago',
            'idProveedorSeleccionado',
            'gastoEditando' // Agregar gastoEditando
        ]);

        $this->fechaGasto = date('Y-m-d');
        $this->fechaVencimiento = date('Y-m-d', strtotime('+30 days'));
    }

    public function cerrarModal()
    {
        $this->modalAbierto = false;
        $this->modalDetalleAbierto = false;

        // Limpiar datos del formulario     
        $this->gastoEditando = null;
        $this->categoriaGasto = '';
        $this->descripcion = '';
        $this->montoGasto = '';
        $this->fechaGasto = date('Y-m-d');
        $this->metodoPago = '';
        $this->proveedorGasto = '';
        $this->documento = '';
        $this->observaciones = '';
        $this->idProveedorSeleccionado = null;
        $this->limpiarFormulario();

        // Limpiar detalles     
        $this->gastoDetalle = null;

        // ✅ AGREGAR: Log para debug
        Log::info('Modal cerrado y campos reseteados');
    }

    public function exportarGastos()
    {
        session()->flash('info', 'Función de exportación en desarrollo');
    }

    public function eliminarGasto($gastoId)
    {
        try {
            // ✅ Verificar que el gasto existe antes de eliminar
            $gasto = DB::table('comprasgastos')
                ->where('idComGas', $gastoId)
                ->where('tipComGas', 'gasto')
                ->first();

            if (!$gasto) {
                session()->flash('error', 'El gasto no existe o ya fue eliminado');
                return;
            }

            DB::beginTransaction();

            // ✅ Eliminar movimiento contable relacionado primero (si existe)
            DB::table('movimientoscontables')
                ->where('idComGasMovCont', $gastoId)
                ->delete();

            // ✅ Eliminar el gasto
            $eliminado = DB::table('comprasgastos')
                ->where('idComGas', $gastoId)
                ->where('tipComGas', 'gasto')
                ->delete();

            if ($eliminado) {
                DB::commit();

                // ✅ Recalcular estadísticas
                $this->calcularEstadisticasCompletas();

                // ✅ Resetear paginación
                $this->resetPage();

                // ✅ Mensaje de éxito
                session()->flash('success', 'Gasto eliminado correctamente');

                Log::info('Gasto eliminado exitosamente:', ['id' => $gastoId]);
            } else {
                DB::rollback();
                session()->flash('error', 'No se pudo eliminar el gasto');
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al eliminar gasto: ' . $e->getMessage());
            session()->flash('error', 'Error al eliminar el gasto: ' . $e->getMessage());
        }
    }

    public function duplicarGasto($gastoId)
    {
        try {
            $gasto = DB::table('comprasgastos')->where('idComGas', $gastoId)->first();

            if ($gasto) {
                // Limpiar datos del modal primero
                $this->resetearCamposModal();

                // Llenar con datos del gasto a duplicar
                $this->categoriaGasto = $gasto->catComGas ?? '';
                $this->descripcion = "COPIA - " . ($gasto->desComGas ?? '');
                $this->montoGasto = $gasto->monComGas ?? '';
                $this->metodoPago = $gasto->metPagComGas ?? '';
                $this->proveedorGasto = $gasto->provComGas ?? '';
                $this->documento = ''; // Limpiar documento para evitar duplicados
                $this->observaciones = $gasto->obsComGas ?? '';
                $this->facturaProveedor = '';
                $this->fechaGasto = date('Y-m-d'); // Fecha actual
                $this->fechaVencimiento = date('Y-m-d', strtotime('+30 days'));
                $this->esPago = false;

                // Asegurar que no esté en modo edición
                $this->gastoEditando = null;

                $this->abrirModal();
                session()->flash('info', 'Datos del gasto copiados. Revisa la información antes de guardar.');

                Log::info('Gasto duplicado preparado:', [
                    'gasto_original' => $gastoId,
                    'nueva_descripcion' => $this->descripcion
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error al duplicar gasto: ' . $e->getMessage());
            session()->flash('error', 'Error al duplicar el gasto');
        }
    }

    public function verDetalleGasto($gastoId)
    {
        try {
            $this->gastoDetalle = DB::table('comprasgastos as cg')
                ->leftJoin('proveedores as p', 'cg.idProve', '=', 'p.idProve')
                ->select(
                    'cg.*',
                    'p.nomProve',
                    'p.nitProve',
                    'p.telProve',
                    'p.emailProve',
                    'p.dirProve'
                )
                ->where('cg.idComGas', $gastoId)
                ->where('cg.tipComGas', 'gasto')
                ->first();

            if ($this->gastoDetalle) {
                $this->modalDetalleAbierto = true;
            } else {
                session()->flash('error', 'Gasto no encontrado');
            }
        } catch (\Exception $e) {
            Log::error('Error al ver detalle del gasto: ' . $e->getMessage());
            session()->flash('error', 'Error al cargar los detalles');
        }
    }

    public function cerrarModalDetalle()
    {
        $this->modalDetalleAbierto = false;
        $this->gastoDetalle = null;
    }

    public function editarGasto($gastoId)
    {
        try {
            $gasto = DB::table('comprasgastos as cg')
                ->leftJoin('proveedores as p', 'cg.idProve', '=', 'p.idProve')
                ->select(
                    'cg.idComGas',
                    'cg.catComGas',
                    'cg.desComGas',
                    'cg.monComGas',
                    'cg.fecComGas',
                    'cg.metPagComGas',
                    'cg.provComGas',
                    'cg.docComGas',
                    'cg.obsComGas',
                    'cg.idProve',
                    'p.nomProve',
                    'p.nitProve'
                )
                ->where('cg.idComGas', $gastoId)
                ->where('cg.tipComGas', 'gasto')
                ->first();

            if ($gasto) {
                // Llenar los campos del formulario
                $this->gastoEditando = $gasto->idComGas;
                $this->categoriaGasto = $gasto->catComGas;
                $this->descripcion = $gasto->desComGas;
                $this->montoGasto = $gasto->monComGas;
                $this->fechaGasto = $gasto->fecComGas;
                $this->metodoPago = $gasto->metPagComGas;
                $this->proveedorGasto = $gasto->provComGas;
                $this->documento = $gasto->docComGas;
                $this->observaciones = $gasto->obsComGas;

                // Si tiene proveedor relacionado
                if ($gasto->idProve) {
                    $this->idProveedorSeleccionado = $gasto->idProve;
                }

                // Cerrar modal de detalles y abrir modal de edición
                $this->modalDetalleAbierto = false;
                $this->modalAbierto = true;

                session()->flash('info', 'Modo de edición activado para el gasto #' . $gastoId);
            } else {
                session()->flash('error', 'Gasto no encontrado');
            }
        } catch (\Exception $e) {
            Log::error('Error al cargar gasto para editar: ' . $e->getMessage());
            session()->flash('error', 'Error al cargar el gasto');
        }
    }

    public function descargarPDFGasto($gastoId)
{
    return $this->redirect(route('contabilidad.gastos.pdf', $gastoId));
}

    public function actualizarGasto()
    {
        $this->validate([
            'categoriaGasto' => 'required|string|max:100',
            'descripcion' => 'required|string|max:500',
            'montoGasto' => 'required|numeric|min:0.01|max:999999.99',
            'fechaGasto' => 'required|date|before_or_equal:today',
            'metodoPago' => 'required|in:efectivo,transferencia,cheque,tarjeta_credito,tarjeta_debito',
            'proveedorGasto' => 'required|string|max:100',
        ]);

        try {
            DB::beginTransaction();

            // ✅ Verificar o crear proveedor
            $proveedorId = $this->verificarOCrearProveedor();

            // ✅ Actualizar gasto con relación
            DB::table('comprasgastos')
                ->where('idComGas', $this->gastoEditando)
                ->update([
                    'catComGas' => $this->categoriaGasto,
                    'desComGas' => $this->descripcion,
                    'monComGas' => $this->montoGasto,
                    'fecComGas' => $this->fechaGasto,
                    'metPagComGas' => $this->metodoPago,
                    'provComGas' => $this->proveedorGasto,
                    'idProve' => $proveedorId, // ✅ Actualizar relación
                    'docComGas' => $this->documento,
                    'obsComGas' => $this->observaciones,
                    'updated_at' => now()
                ]);

            // ✅ Actualizar movimiento contable si existe
            DB::table('movimientoscontables')
                ->where('idComGasMovCont', $this->gastoEditando)
                ->update([
                    'fecMovCont' => $this->fechaGasto,
                    'catMovCont' => $this->categoriaGasto,
                    'conceptoMovCont' => "Gasto #{$this->gastoEditando} - {$this->descripcion} (Actualizado)",
                    'montoMovCont' => $this->montoGasto,
                    'obsMovCont' => "Actualizado: {$this->metodoPago}. {$this->observaciones}",
                    'updated_at' => now()
                ]);

            DB::commit();

            $this->cerrarModal();
            $this->calcularEstadisticasCompletas();

            session()->flash('success', 'Gasto actualizado exitosamente');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al actualizar gasto: ' . $e->getMessage());
            session()->flash('error', 'Error al actualizar el gasto: ' . $e->getMessage());
        }
    }

    public function getColorCategoria($categoria)
    {
        $categoria = (string) ($categoria ?? 'Otros');

        $colores = [
            'Servicios Públicos' => 'bg-blue-100 text-blue-800',
            'Mantenimiento' => 'bg-orange-100 text-orange-800',
            'Transporte' => 'bg-green-100 text-green-800',
            'Suministros' => 'bg-purple-100 text-purple-800',
            'Alimentación Animal' => 'bg-yellow-100 text-yellow-800',
            'Veterinario' => 'bg-red-100 text-red-800',
            'Combustible' => 'bg-gray-100 text-gray-800',
            'Marketing' => 'bg-pink-100 text-pink-800',
            'Seguros' => 'bg-indigo-100 text-indigo-800',
            'Impuestos' => 'bg-teal-100 text-teal-800',
            'Reparaciones' => 'bg-rose-100 text-rose-800',
            'Otros' => 'bg-slate-100 text-slate-800'
        ];

        return $colores[$categoria] ?? 'bg-gray-100 text-gray-800';
    }

    public function getIconoMetodoPago($metodo)
    {
        $metodo = (string) ($metodo ?? '');

        $iconos = [
            'efectivo' => 'fas fa-money-bill-wave',
            'transferencia' => 'fas fa-university',
            'cheque' => 'fas fa-file-alt',
            'tarjeta_credito' => 'fas fa-credit-card',
            'tarjeta_debito' => 'fas fa-credit-card'
        ];

        return $iconos[$metodo] ?? 'fas fa-money-bill';
    }

    public function debugGasto($gastoId)
    {
        $gasto = DB::table('comprasgastos')->where('idComGas', $gastoId)->first();
        Log::info('Debug gasto:', ['gasto' => $gasto]);
        dd($gasto); // Esto mostrará los datos en pantalla
    }

    public function analizarTendencias()
{
    try {
        // Análisis de tendencias mensuales
        $tendencias = DB::table('v_flujo_caja_diario')
            ->where('fecha', '>=', date('Y-m-d', strtotime('-90 days')))
            ->orderBy('fecha')
            ->get();

        session()->flash('info', 'Análisis de tendencias completado. Se encontraron ' . $tendencias->count() . ' registros.');
        
        return $tendencias;
    } catch (\Exception $e) {
        Log::error('Error analizando tendencias: ' . $e->getMessage());
        session()->flash('error', 'Error al analizar tendencias');
        return collect();
    }
}

public function getEstadoFinancieroProperty()
{
    try {
        return DB::table('v_estado_financiero')
            ->orderBy('mes', 'desc')
            ->limit(6)
            ->get();
    } catch (\Exception $e) {
        Log::error('Error obteniendo estado financiero: ' . $e->getMessage());
        return collect();
    }
}

public function getCuentasVencidasProperty()
{
    try {
        return DB::table('v_cuentas_vencidas')
            ->where('tipCuePen', 'por_pagar')
            ->orderBy('dias_vencido', 'desc')
            ->get();
    } catch (\Exception $e) {
        Log::error('Error obteniendo cuentas vencidas: ' . $e->getMessage());
        return collect();
    }
}

public function getMovimientosRecientesProperty()
{
    try {
        return DB::table('v_movimientos_recientes')
            ->limit(10)
            ->get();
    } catch (\Exception $e) {
        Log::error('Error obteniendo movimientos recientes: ' . $e->getMessage());
        return collect();
    }
}

public function sincronizarInventario($gastoId)
{
    try {
        // Buscar si el gasto afecta inventario
        $gasto = DB::table('comprasgastos')->where('idComGas', $gastoId)->first();
        
        if ($gasto && in_array($gasto->catComGas, ['Insumos', 'Herramientas', 'Mantenimiento'])) {
            // Crear entrada de inventario automática
            DB::table('inventario')->insert([
                'tipMovInv' => 'entrada',
                'cantMovInv' => 1,
                'uniMovInv' => 'unidad',
                'costoUnitInv' => $gasto->monComGas,
                'costoTotInv' => $gasto->monComGas,
                'fecMovInv' => $gasto->fecComGas,
                'idComGas' => $gastoId,
                'idUsuReg' => Auth::id(),
                'obsInv' => "Entrada automática por gasto: {$gasto->desComGas}",
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            Log::info("Inventario sincronizado para gasto #{$gastoId}");
        }
        
    } catch (\Exception $e) {
        Log::error('Error sincronizando inventario: ' . $e->getMessage());
    }
}

public function diagnosticarProblema()
{
    try {
        // PASO 1: Verificar conexión a BD
        $conexion = DB::connection()->getPdo();
        Log::info('Conexión a BD: OK');

        // PASO 2: Verificar tabla comprasgastos
        $totalRegistros = DB::table('comprasgastos')->count();
        Log::info("Total registros en comprasgastos: {$totalRegistros}");

        // PASO 3: Verificar registros de tipo 'gasto'
        $totalGastos = DB::table('comprasgastos')->where('tipComGas', 'gasto')->count();
        Log::info("Total gastos: {$totalGastos}");

        // PASO 4: Ver estructura de un registro
        $muestraGasto = DB::table('comprasgastos')
            ->where('tipComGas', 'gasto')
            ->first();
        Log::info('Muestra de gasto:', (array) $muestraGasto);

        // PASO 5: Verificar fechas
        $fechaInicio = !empty($this->fecha_inicio) ? $this->fecha_inicio : date('Y-m-01');
        $fechaFin = !empty($this->fecha_fin) ? $this->fecha_fin : date('Y-m-d');
        Log::info("Rango de fechas: {$fechaInicio} a {$fechaFin}");

        // PASO 6: Consulta específica con fechas
        $gastosEnRango = DB::table('comprasgastos')
            ->where('tipComGas', 'gasto')
            ->whereBetween('fecComGas', [$fechaInicio, $fechaFin])
            ->get(['idComGas', 'fecComGas', 'monComGas', 'catComGas']);

        Log::info("Gastos en rango: {$gastosEnRango->count()}");
        
        foreach ($gastosEnRango->take(3) as $gasto) {
            Log::info('Gasto encontrado:', (array) $gasto);
        }

        // PASO 7: Suma manual
        $sumaManual = $gastosEnRango->sum('monComGas');
        Log::info("Suma manual: {$sumaManual}");

        // PASO 8: Verificar tabla movimientoscontables
        $movimientos = DB::table('movimientoscontables')
            ->where('tipoMovCont', 'egreso')
            ->count();
        Log::info("Movimientos contables (egresos): {$movimientos}");

        // PASO 9: Verificar cuentaspendientes
        $cuentasPendientes = DB::table('cuentaspendientes')
            ->where('tipCuePen', 'por_pagar')
            ->count();
        Log::info("Cuentas pendientes (por pagar): {$cuentasPendientes}");

        $resultado = [
            'total_registros' => $totalRegistros,
            'total_gastos' => $totalGastos,
            'gastos_en_rango' => $gastosEnRango->count(),
            'suma_manual' => $sumaManual,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'muestra_gasto' => $muestraGasto,
            'movimientos_contables' => $movimientos,
            'cuentas_pendientes' => $cuentasPendientes
        ];

        session()->flash('info', 'Diagnóstico completado. Revisa los logs para detalles.');
        
        dd($resultado);

    } catch (\Exception $e) {
        Log::error('Error en diagnóstico: ' . $e->getMessage());
        dd(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    }
}

public function debugEstadisticas()
{
    Log::info('Debug estadísticas:', [
        'estadisticas_array' => $this->estadisticas,
        'gastos_periodo' => $this->estadisticas['gastos_periodo'] ?? 'NO_DEFINIDO',
        'transacciones_periodo' => $this->estadisticas['transacciones_periodo'] ?? 'NO_DEFINIDO',
        'promedio_diario' => $this->estadisticas['promedio_diario'] ?? 'NO_DEFINIDO',
        'categorias_activas' => $this->estadisticas['categorias_activas'] ?? 'NO_DEFINIDO',
        'cuentas_por_pagar' => $this->estadisticas['cuentas_por_pagar'] ?? 'NO_DEFINIDO'
    ]);

    dd([
        'estadisticas' => $this->estadisticas,
        'resumenFinanciero' => $this->resumenFinanciero,
        'fecha_inicio' => $this->fecha_inicio,
        'fecha_fin' => $this->fecha_fin
    ]);
}

};
?>

@section('title', 'Gestión de Gastos - FAMASY')

<div>
    <div class="w-full px-6 py-6 mx-auto">
        <!-- Flash Messages -->
        @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
        @endif

        @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
        @endif

        @if (session()->has('info'))
        <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-lg">
            <i class="fas fa-info-circle mr-2"></i>{{ session('info') }}
        </div>
        @endif

        <!-- Header -->
        <div class="flex flex-wrap -mx-3 mb-6">
            <div class="w-full px-3">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                    <div class="mb-4 md:mb-0">
                        <nav class="text-sm text-gray-600 mb-2">
                            <a href="{{ route('contabilidad.index') }}" wire:navigate class="hover:text-blue-600">Dashboard</a>
                            <span class="mx-2">/</span>
                            <span class="text-gray-900">Gastos</span>
                        </nav>
                        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-receipt mr-3 text-red-600"></i>
                            Gestión de Gastos
                        </h1>
                        <p class="text-gray-600 mt-1">Control y registro de gastos operacionales</p>
                    </div>
                    <div class="flex space-x-3">
                        <button wire:click="abrirModal"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                            <i class="fas fa-plus mr-2"></i> Nuevo Gasto
                        </button>
                        <a href="{{ route('contabilidad.reportes.index') }}" wire:navigate
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                            <i class="fas fa-chart-bar mr-2"></i> Reportes
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPIs CON DEBUG -->
<div class="flex flex-wrap -mx-3 mb-6">
     <div class="w-full md:w-1/4 px-3 mb-6">
        <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 {{ $this->hayFiltrosActivos() ? 'border-green-500' : 'border-red-500' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold {{ $this->hayFiltrosActivos() ? 'text-green-600' : 'text-red-600' }} uppercase tracking-wide mb-1">
                        Gastos del Período
                        @if($this->hayFiltrosActivos())
                        <i class="fas fa-filter ml-1" title="Con filtros aplicados"></i>
                        @endif
                    </p>
                    <p class="text-2xl font-bold text-gray-800">
                        ${{ number_format($estadisticas['gastos_periodo'] ?? 0, 2) }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $estadisticas['transacciones_periodo'] ?? 0 }} transacciones
                    </p>
                    @if($this->hayFiltrosActivos())
                    <p class="text-xs text-green-600 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>Filtrado
                    </p>
                    @endif
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <i class="fas fa-money-bill-wave text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="w-full md:w-1/4 px-3 mb-6">
        <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-red-600 uppercase tracking-wide mb-1">Gastos del Período</p>
                    <p class="text-2xl font-bold text-gray-800">
                        ${{ number_format($estadisticas['gastos_periodo'] ?? 0, 2) }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $estadisticas['transacciones_periodo'] ?? 0 }} transacciones
                    </p>
                    <!-- DEBUG INFO -->
                    <p class="text-xs text-blue-500 mt-1">
                        Debug: {{ json_encode($estadisticas['gastos_periodo'] ?? 'NULL') }}
                    </p>
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <i class="fas fa-money-bill-wave text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="w-full md:w-1/4 px-3 mb-6">
        <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide mb-1">Promedio Diario</p>
                    <p class="text-2xl font-bold text-gray-800">
                        ${{ number_format($estadisticas['promedio_diario'] ?? 0, 2) }}
                    </p>
                    <p class="text-xs text-blue-500 mt-1">Este período</p>
                    <!-- DEBUG INFO -->
                    <p class="text-xs text-blue-500 mt-1">
                        Debug: {{ json_encode($estadisticas['promedio_diario'] ?? 'NULL') }}
                    </p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-calculator text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="w-full md:w-1/4 px-3 mb-6">
        <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-green-600 uppercase tracking-wide mb-1">Categorías Activas</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $estadisticas['categorias_activas'] ?? 0 }}</p>
                    <p class="text-xs text-green-500 mt-1">Con movimientos</p>
                    <!-- DEBUG INFO -->
                    <p class="text-xs text-blue-500 mt-1">
                        Debug: {{ json_encode($estadisticas['categorias_activas'] ?? 'NULL') }}
                    </p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-tags text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="w-full md:w-1/4 px-3 mb-6">
        <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-orange-600 uppercase tracking-wide mb-1">Cuentas Por Pagar</p>
                    <p class="text-2xl font-bold text-gray-800">
                        ${{ number_format($estadisticas['cuentas_por_pagar'] ?? 0, 2) }}
                    </p>
                    <p class="text-xs text-orange-500 mt-1">Pendientes</p>
                    <!-- DEBUG INFO -->
                    <p class="text-xs text-blue-500 mt-1">
                        Debug: {{ json_encode($estadisticas['cuentas_por_pagar'] ?? 'NULL') }}
                    </p>
                </div>
                <div class="bg-orange-100 p-3 rounded-full">
                    <i class="fas fa-clock text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>
</div>
        <!-- Filtros Simplificados -->
        <div class="bg-white shadow-lg rounded-lg mb-6 p-6">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <!-- CATEGORÍA -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Categoría
                        @if($categoria)
                        <span class="text-xs text-green-600">(Filtrado)</span>
                        @endif
                    </label>
                    <select wire:model.live="categoria"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 @if($categoria) border-green-500 @endif">
                        <option value="">Todas las categorías</option>
                        @foreach($categorias as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- MÉTODO DE PAGO -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Método de Pago
                        @if($metodo_pago)
                        <span class="text-xs text-green-600">(Filtrado)</span>
                        @endif
                    </label>
                    <select wire:model.live="metodo_pago"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 @if($metodo_pago) border-green-500 @endif">
                        <option value="">Todos los métodos</option>
                        <option value="efectivo">💵 Efectivo</option>
                        <option value="transferencia">🏦 Transferencia</option>
                        <option value="cheque">📝 Cheque</option>
                        <option value="tarjeta_credito">💳 Tarjeta de Crédito</option>
                        <option value="tarjeta_debito">💳 Tarjeta de Débito</option>
                    </select>
                </div>

                <!-- PROVEEDOR -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Proveedor
                        @if($proveedor_buscar)
                        <span class="text-xs text-green-600">(Filtrado)</span>
                        @endif
                    </label>
                    <div class="relative">
                        <input type="text"
                            wire:model.live.debounce.300ms="proveedor_buscar"
                            placeholder="Buscar por nombre de proveedor..."
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-red-500 @if($proveedor_buscar) border-green-500 @endif">

                        @if($proveedor_buscar)
                        <button wire:click="$set('proveedor_buscar', '')"
                            class="absolute right-2 top-2 text-gray-400 hover:text-red-500">
                            <i class="fas fa-times"></i>
                        </button>
                        @else
                        <div class="absolute right-2 top-2 text-gray-400">
                            <i class="fas fa-search"></i>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- FECHA INICIO -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Fecha Desde
                        @if($fecha_inicio)
                        <span class="text-xs text-green-600">({{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }})</span>
                        @endif
                    </label>
                    <input type="date"
                        wire:model.live="fecha_inicio"
                        max="{{ date('Y-m-d') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 @if($fecha_inicio) border-green-500 @endif">
                </div>

                <!-- FECHA FIN -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Fecha Hasta
                        @if($fecha_fin)
                        <span class="text-xs text-green-600">({{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }})</span>
                        @endif
                    </label>
                    <input type="date"
                        wire:model.live="fecha_fin"
                        max="{{ date('Y-m-d') }}"
                        @if($fecha_inicio) min="{{ $fecha_inicio }}" @endif
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 @if($fecha_fin) border-green-500 @endif">
                </div>

                <!-- BOTONES DE ACCIÓN -->
                <div class="flex flex-col items-end justify-end space-y-2">
                    <!-- Contador de resultados -->
                    <div class="text-xs text-gray-600 text-center w-full">
                        @if($this->hayFiltrosActivos())
                        <span class="text-green-600 font-medium">
                            <i class="fas fa-filter mr-1"></i>
                            {{ $this->gastos->total() }} resultado(s)
                        </span>
                        @else
                        <span class="text-gray-500">
                            Total: {{ $this->gastos->total() }} gastos
                        </span>
                        @endif
                    </div>

                    <!-- Botón limpiar -->
                    <button wire:click="limpiarFiltros"
                        class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center justify-center
                           @if(!$this->hayFiltrosActivos()) opacity-50 cursor-not-allowed @endif"
                        @if(!$this->hayFiltrosActivos()) disabled @endif>
                        <i class="fas fa-times mr-2"></i>
                        {{ $this->hayFiltrosActivos() ? 'Limpiar Filtros' : 'Sin Filtros' }}
                    </button>
                </div>
            </div>

            <!-- Información de filtros activos -->
            @if($this->hayFiltrosActivos())
            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <span class="text-blue-700 font-medium">
                        <i class="fas fa-info-circle mr-1"></i>Filtros activos:
                    </span>

                    @if($categoria)
                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                        Categoría: {{ $categoria }}
                    </span>
                    @endif

                    @if($metodo_pago)
                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                        Método: {{ ucfirst(str_replace('_', ' ', $metodo_pago)) }}
                    </span>
                    @endif

                    @if($proveedor_buscar)
                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                        Proveedor: "{{ $proveedor_buscar }}"
                    </span>
                    @endif

                    @if($fecha_inicio)
                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                        Desde: {{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }}
                    </span>
                    @endif

                    @if($fecha_fin)
                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                        Hasta: {{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}
                    </span>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Tabla de Gastos -->
        <div class="bg-white shadow-lg rounded-lg">
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-lg font-semibold text-gray-800">Historial de Gastos</h6>
                        <p class="text-sm text-gray-600">{{ $this->gastos->total() }} gastos encontrados</p>
                    </div>
                    <div class="flex space-x-2">
                        <button wire:click="exportarGastos" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                            <i class="fas fa-download mr-1"></i> Exportar
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proveedor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($this->gastos as $gasto)
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ !empty($gasto->fecComGas) ? date('d/m/Y', strtotime($gasto->fecComGas)) : 'Sin fecha' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $this->getColorCategoria($gasto->catComGas ?? '') }}">
                                    {{ $gasto->catComGas ?? 'Sin categoría' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="max-w-xs truncate" title="{{ $gasto->desComGas ?? '' }}">
                                    {{ $gasto->desComGas ?? 'Sin descripción' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $gasto->provComGas ?? 'Sin proveedor' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    {{ ucfirst(str_replace('_', ' ', $gasto->metPagComGas ?? 'Sin método')) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">
                                ${{ number_format($gasto->monComGas ?? 0, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button wire:click="verDetalleGasto({{ $gasto->idComGas ?? 0 }})"
                                        class="text-blue-600 hover:text-blue-900" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    <!-- NUEVO ÍCONO PDF -->
                                    <button wire:click="descargarPDFGasto({{ $gasto->idComGas ?? 0 }})"
                                        class="text-red-600 hover:text-red-900" title="Descargar PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>

                                    <button wire:click="duplicarGasto({{ $gasto->idComGas ?? 0 }})"
                                        class="text-purple-600 hover:text-purple-900" title="Duplicar">
                                        <i class="fas fa-copy"></i>
                                    </button>

                                    <button wire:click="editarGasto({{ $gasto->idComGas ?? 0 }})"
                                        class="text-yellow-600 hover:text-yellow-900" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button wire:click="eliminarGasto({{ $gasto->idComGas ?? 0 }})"
                                        wire:confirm="¿Estás seguro de eliminar este gasto?"
                                        class="text-red-600 hover:text-red-900" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-receipt text-4xl text-gray-300 mb-4"></i>
                                    <p class="text-lg font-medium mb-2">No hay gastos registrados</p>
                                    <p class="text-sm text-gray-400 mb-4">Comienza registrando tu primer gasto</p>
                                    <button wire:click="abrirModal"
                                        class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition duration-200">
                                        <i class="fas fa-plus mr-2"></i> Registrar Primer Gasto
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($this->gastos->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $this->gastos->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Modal Nuevo/Editar Gasto -->
    @if($modalAbierto)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-lg bg-white max-h-[90vh] overflow-y-auto">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ $gastoEditando ? 'Editar Gasto' : 'Registrar Nuevo Gasto' }}
                    </h3>
                    <button wire:click="cerrarModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form wire:submit.prevent="guardarGasto" class="space-y-4"></form>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- CATEGORÍA -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Categoría *</label>
                            <select wire:model="categoriaGasto"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <option value="">Seleccionar categoría</option>
                                @foreach($categorias as $categoria)
                                <option value="{{ $categoria }}">{{ $categoria }}</option>
                                @endforeach
                            </select>
                            @error('categoriaGasto') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- PROVEEDOR CON AUTOCOMPLETADO -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Proveedor *
                                <span class="text-xs text-gray-500">({{ $proveedores->count() }} disponibles)</span>
                            </label>
                            <div class="relative">
                                <input type="text"
                                    wire:model.live.debounce.300ms="proveedorGasto"
                                    list="proveedores-datalist"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-red-500 @error('proveedorGasto') border-red-500 @enderror"
                                    placeholder="Escriba o seleccione un proveedor..."
                                    autocomplete="off">

                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>

                                <!-- DATALIST CON PROVEEDORES -->
                                <datalist id="proveedores-datalist">
                                    @if($proveedores && $proveedores->count() > 0)
                                    @foreach($proveedores as $proveedor)
                                    <option value="{{ $proveedor->nomProve }}"
                                        data-id="{{ $proveedor->idProve }}"
                                        data-nit="{{ $proveedor->nitProve }}">
                                        {{ $proveedor->nomProve }}
                                        @if($proveedor->nitProve) - NIT: {{ $proveedor->nitProve }} @endif
                                    </option>
                                    @endforeach
                                    @else
                                    <option value="">No hay proveedores disponibles</option>
                                    @endif
                                </datalist>
                            </div>

                            @error('proveedorGasto')
                            <span class="text-red-500 text-xs mt-1 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </span>
                            @enderror

                            <!-- Indicador de proveedor seleccionado -->
                            @if($idProveedorSeleccionado)
                            <div class="mt-2 p-2 bg-green-50 border border-green-200 rounded-lg">
                                <div class="flex items-center text-green-700">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <span class="text-sm font-medium">Proveedor registrado seleccionado</span>
                                </div>
                                @php
                                $proveedorSel = $proveedores->firstWhere('idProve', $idProveedorSeleccionado);
                                @endphp
                                @if($proveedorSel)
                                <div class="text-xs text-green-600 mt-1">
                                    <strong>ID:</strong> {{ $proveedorSel->idProve }} |
                                    <strong>NIT:</strong> {{ $proveedorSel->nitProve ?? 'No especificado' }} |
                                    <strong>Tel:</strong> {{ $proveedorSel->telProve ?? 'No especificado' }}
                                </div>
                                @endif
                            </div>
                            @else
                            <div class="mt-1 text-xs text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                Si no encuentra el proveedor, se creará uno nuevo automáticamente
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- DESCRIPCIÓN -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Descripción *</label>
                        <input type="text" wire:model="descripcion"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                            placeholder="Descripción detallada del gasto (ej: 20 galones de gasolina)">
                        @error('descripcion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- MONTO, FECHA Y MÉTODO DE PAGO -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Monto *</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">$</span>
                                <input type="number" wire:model="montoGasto" step="0.01" min="0.01" max="999999.99"
                                    class="w-full border border-gray-300 rounded-lg pl-8 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                                    placeholder="0.00">
                            </div>
                            @error('montoGasto') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha del Gasto *</label>
                            <input type="date" wire:model="fechaGasto" max="{{ date('Y-m-d') }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
                            @error('fechaGasto') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Método de Pago *</label>
                            <select wire:model="metodoPago"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <option value="">Seleccionar método</option>
                                <option value="efectivo">💵 Efectivo</option>
                                <option value="transferencia">🏦 Transferencia</option>
                                <option value="cheque">📝 Cheque</option>
                                <option value="tarjeta_credito">💳 Tarjeta de Crédito</option>
                                <option value="tarjeta_debito">💳 Tarjeta de Débito</option>
                            </select>
                            @error('metodoPago') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- DOCUMENTO DE RESPALDO -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Documento de Respaldo</label>
                        <input type="text" wire:model="documento"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                            placeholder="Número de factura, recibo, comprobante, etc.">
                    </div>

                    <!-- OBSERVACIONES -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                        <textarea wire:model="observaciones" rows="3"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                            placeholder="Notas adicionales, detalles específicos, condiciones especiales..."></textarea>
                    </div>

                    <!-- BOTONES -->
                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <button type="button" wire:click="cerrarModal"
                            class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </button>
                        <button type="submit" 
    wire:loading.attr="disabled"
    wire:target="guardarGasto"
    class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 disabled:opacity-50">
    
    <span wire:loading.remove wire:target="guardarGasto">
        <i class="fas fa-save mr-2"></i>
        {{ $gastoEditando ? 'Actualizar Gasto' : 'Registrar Gasto' }}
    </span>
    
    <span wire:loading wire:target="guardarGasto">
        <i class="fas fa-spinner fa-spin mr-2"></i>
        Guardando...
    </span>
</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal de Detalles del Gasto -->
    @if($modalDetalleAbierto && $gastoDetalle)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-lg bg-white max-h-[90vh] overflow-y-auto">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-receipt mr-2 text-blue-600"></i>
                        Detalles del Gasto #{{ $gastoDetalle->idComGas }}
                    </h3>
                    <button wire:click="cerrarModalDetalle" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="space-y-4">
                    <!-- ID y Fecha -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label class="block text-sm font-medium text-gray-700">ID del Gasto</label>
                            <p class="text-lg font-semibold text-gray-900">#{{ $gastoDetalle->idComGas }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label class="block text-sm font-medium text-gray-700">Fecha</label>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ \Carbon\Carbon::parse($gastoDetalle->fecComGas)->format('d/m/Y') }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ \Carbon\Carbon::parse($gastoDetalle->fecComGas)->diffForHumans() }}
                            </p>
                        </div>
                    </div>

                    <!-- Categoría -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $this->getColorCategoria($gastoDetalle->catComGas ?? '') }}">
                            {{ $gastoDetalle->catComGas ?? 'Sin categoría' }}
                        </span>
                    </div>

                    <!-- Descripción -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                        <p class="text-gray-900">{{ $gastoDetalle->desComGas ?? 'Sin descripción' }}</p>
                    </div>

                    <!-- Proveedor y Monto -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Proveedor</label>
                            <p class="text-lg font-semibold text-gray-900">{{ $gastoDetalle->provComGas ?? 'Sin proveedor' }}</p>
                            @if(!empty($gastoDetalle->nitProve))
                            <p class="text-sm text-gray-600">NIT: {{ $gastoDetalle->nitProve }}</p>
                            @endif
                            @if(!empty($gastoDetalle->idProve))
                            <p class="text-xs text-blue-600">ID Proveedor: {{ $gastoDetalle->idProve }}</p>
                            @endif
                        </div>
                        <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                            <label class="block text-sm font-medium text-red-700 mb-2">Monto Total</label>
                            <p class="text-3xl font-bold text-red-600">${{ number_format($gastoDetalle->monComGas ?? 0, 2) }}</p>
                        </div>
                    </div>

                    <!-- Método de Pago y Documento -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Método de Pago</label>
                            <p class="text-gray-900">
                                {{ ucfirst(str_replace('_', ' ', $gastoDetalle->metPagComGas ?? 'Sin método')) }}
                            </p>
                        </div>
                        @if(!empty($gastoDetalle->docComGas))
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Documento</label>
                            <p class="text-gray-900">{{ $gastoDetalle->docComGas }}</p>
                        </div>
                        @endif
                    </div>

                    <!-- Observaciones -->
                    @if(!empty($gastoDetalle->obsComGas))
                    <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                        <label class="block text-sm font-medium text-yellow-700 mb-2">
                            <i class="fas fa-sticky-note mr-1"></i>Observaciones
                        </label>
                        <p class="text-yellow-800">{{ $gastoDetalle->obsComGas }}</p>
                    </div>
                    @endif

                    <!-- Información del Sistema -->
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <label class="block text-sm font-medium text-blue-700 mb-2">
                            <i class="fas fa-info-circle mr-1"></i>Información del Sistema
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-800">
                            <div>
                                <span class="font-medium">Creado:</span>
                                {{ \Carbon\Carbon::parse($gastoDetalle->created_at)->format('d/m/Y H:i') }}
                            </div>
                            <div>
                                <span class="font-medium">Actualizado:</span>
                                {{ \Carbon\Carbon::parse($gastoDetalle->updated_at)->format('d/m/Y H:i') }}
                            </div>
                            @if(!empty($gastoDetalle->idMovCont))
                            <div class="md:col-span-2">
                                <span class="font-medium">Mov. Contable:</span> #{{ $gastoDetalle->idMovCont }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                    <button wire:click="cerrarModalDetalle"
                        class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200">
                        <i class="fas fa-times mr-2"></i>Cerrar
                    </button>

                    <div class="flex space-x-3">
                        <button wire:click="duplicarGasto({{ $gastoDetalle->idComGas }})"
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition duration-200">
                            <i class="fas fa-copy mr-2"></i>Duplicar
                        </button>
                        <button wire:click="editarGasto({{ $gastoDetalle->idComGas }})"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                            <i class="fas fa-edit mr-2"></i>Editar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Loading Overlay -->
    <div wire:loading.flex wire:target="guardarGasto,actualizarGasto"
        class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 items-center justify-center">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center space-x-3">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-red-600"></div>
                <span class="text-gray-700">Procesando...</span>
            </div>
        </div>
    </div>

    <!-- Sección de Análisis Completo -->
@if(count($resumenFinanciero) > 0 || count($topProveedores) > 0)
<div class="bg-white shadow-lg rounded-lg mt-6 p-6">
    <h6 class="text-lg font-semibold text-gray-800 mb-4">
        <i class="fas fa-chart-line mr-2 text-blue-600"></i>
        Análisis Financiero Completo
    </h6>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Resumen Financiero -->
        @if(count($resumenFinanciero) > 0)
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg">
            <h6 class="font-medium text-blue-800 mb-3">Estado Financiero</h6>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span>Ingresos:</span>
                    <span class="text-green-600 font-medium">${{ number_format($resumenFinanciero['total_ingresos'] ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Egresos:</span>
                    <span class="text-red-600 font-medium">${{ number_format($resumenFinanciero['total_egresos'] ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between border-t pt-2">
                    <span class="font-medium">Utilidad:</span>
                    <span class="font-bold {{ ($resumenFinanciero['utilidad_neta'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        ${{ number_format($resumenFinanciero['utilidad_neta'] ?? 0, 2) }}
                    </span>
                </div>
            </div>
        </div>
        @endif

        <!-- Top Proveedores -->
        @if(count($topProveedores) > 0)
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-lg">
            <h6 class="font-medium text-purple-800 mb-3">Top Proveedores</h6>
            <div class="space-y-2">
                @foreach($topProveedores->take(3) as $proveedor)
                <div class="flex justify-between text-sm">
                    <span class="truncate mr-2">{{ $proveedor->nombre_proveedor }}</span>
                    <span class="text-purple-600 font-medium">${{ number_format($proveedor->total_gastado, 0) }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Alertas -->
        @if(count($alertasVencimientos) > 0)
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-4 rounded-lg">
            <h6 class="font-medium text-yellow-800 mb-3">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                Próximos a Vencer
            </h6>
            <div class="space-y-2">
                @foreach($alertasVencimientos->take(3) as $alerta)
                <div class="text-sm">
                    <div class="font-medium text-yellow-700">{{ $alerta->producto }}</div>
                    <div class="text-yellow-600">{{ $alerta->dias_para_vencer }} días</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Botón de análisis completo -->
    <div class="mt-4 text-center">
        <button wire:click="analizarTendencias" 
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200">
            <i class="fas fa-analytics mr-2"></i>
            Generar Análisis Completo
        </button>
    </div>
</div>
@endif

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-cerrar notificaciones
            setTimeout(function() {
                const notifications = document.querySelectorAll('.bg-green-100, .bg-red-100, .bg-blue-100');
                notifications.forEach(notification => {
                    if (notification.classList.contains('mb-4')) {
                        notification.style.transition = 'all 0.5s ease';
                        notification.style.opacity = '0';
                        setTimeout(() => {
                            if (notification.parentNode) {
                                notification.remove();
                            }
                        }, 500);
                    }
                });
            }, 5000);

            // Shortcuts de teclado
            document.addEventListener('keydown', function(e) {
                // Ctrl/Cmd + N = Nuevo gasto
                if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                    e.preventDefault();
                    const livewireComponent = document.querySelector('[wire\\:id]');
                    if (livewireComponent) {
                        window.livewire.find(livewireComponent.getAttribute('wire:id')).abrirModal();
                    }
                }

                // Escape = Cerrar modal
                if (e.key === 'Escape') {
                    const livewireComponent = document.querySelector('[wire\\:id]');
                    if (livewireComponent) {
                        window.livewire.find(livewireComponent.getAttribute('wire:id')).cerrarModal();
                    }
                }
            });

            // Validación en tiempo real para montos
            document.addEventListener('input', function(e) {
                if (e.target.type === 'number' && e.target.step === '0.01') {
                    const value = parseFloat(e.target.value);
                    const max = 999999.99;
                    const min = 0.01;

                    // Limpiar errores previos
                    const existingError = e.target.parentNode.parentNode.querySelector('.validation-error');
                    if (existingError) {
                        existingError.remove();
                    }
                    e.target.classList.remove('border-red-500');

                    // Validar y mostrar errores
                    if (value > max) {
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'validation-error text-red-500 text-xs mt-1';
                        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i>El monto máximo permitido es $${max.toLocaleString()}`;
                        e.target.parentNode.parentNode.appendChild(errorDiv);
                        e.target.classList.add('border-red-500');
                    } else if (value < min && value !== 0) {
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'validation-error text-red-500 text-xs mt-1';
                        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i>El monto mínimo permitido es $${min}`;
                        e.target.parentNode.parentNode.appendChild(errorDiv);
                        e.target.classList.add('border-red-500');
                    }
                }
            });

            // NUEVO: Autocompletado de proveedores
            setupProveedorAutocomplete();

            function setupProveedorAutocomplete() {
                const proveedorInput = document.querySelector('input[wire\\:model\\.live\\.debounce\\.300ms="proveedorGasto"]');

                if (proveedorInput) {
                    // Detectar cuando se selecciona una opción del datalist
                    proveedorInput.addEventListener('input', function(e) {
                        const valor = e.target.value;
                        const datalist = document.getElementById('proveedores-datalist');

                        if (datalist) {
                            const opciones = datalist.querySelectorAll('option');

                            // Buscar si el valor coincide exactamente con alguna opción
                            for (let opcion of opciones) {
                                if (opcion.value === valor) {
                                    const proveedorId = opcion.getAttribute('data-id');
                                    const nit = opcion.getAttribute('data-nit');

                                    console.log('Proveedor seleccionado:', {
                                        id: proveedorId,
                                        nombre: valor,
                                        nit: nit
                                    });

                                    // Notificar a Livewire
                                    const livewireComponent = document.querySelector('[wire\\:id]');
                                    if (livewireComponent && proveedorId) {
                                        window.livewire.find(livewireComponent.getAttribute('wire:id'))
                                            .seleccionarProveedor(parseInt(proveedorId));
                                    }
                                    break;
                                }
                            }
                        }
                    });

                    // Limpiar selección cuando el input cambia manualmente
                    proveedorInput.addEventListener('keydown', function(e) {
                        if (e.key === 'Backspace' || e.key === 'Delete') {
                            const livewireComponent = document.querySelector('[wire\\:id]');
                            if (livewireComponent) {
                                // Limpiar selección después de un pequeño delay
                                setTimeout(() => {
                                    window.livewire.find(livewireComponent.getAttribute('wire:id'))
                                        .set('idProveedorSeleccionado', null);
                                }, 100);
                            }
                        }
                    });
                }
            }

            console.log('Scripts de gastos cargados correctamente');
        });

        // Event listeners para Livewire
        document.addEventListener('livewire:init', () => {
            // Escuchar eventos personalizados
            Livewire.on('gasto-creado', (data) => {
                // Vibración en dispositivos móviles
                if (navigator.vibrate) {
                    navigator.vibrate([100, 50, 100]);
                }
            });

            Livewire.on('gasto-actualizado', (data) => {
                console.log('Gasto actualizado exitosamente');
            });

            Livewire.on('gasto-eliminado', (data) => {
                console.log('Gasto eliminado exitosamente');
            });
        });
    </script>
</div>